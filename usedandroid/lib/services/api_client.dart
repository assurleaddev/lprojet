import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/app_config.dart';

class ApiClient {
  // Base URL is resolved from AppConfig: production HTTPS by default, or the
  // emulator host loopback when launched with --dart-define=LOCAL_DEV=true.
  static final _baseUrl = AppConfig.apiBaseUrl;
  static const _tokenKey = 'auth_token';
  static const _tokenExpiryKey = 'auth_token_expiry';

  // Token lifetime matches SANCTUM_EXPIRATION (1440 min). Refresh 30 min early.
  static const _tokenLifetimeMs = 1440 * 60 * 1000;
  static const _refreshBufferMs = 30 * 60 * 1000;

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );

  // In local dev the API returns absolute image URLs built from Laravel's
  // APP_URL (127.0.0.1), which on the emulator points at the emulator itself —
  // rewrite it to the host loopback alias. In production this is a no-op.
  static String fixImageUrl(String url) => AppConfig.isLocalDev
      ? url
          .replaceFirst('http://127.0.0.1:8000', 'http://10.0.2.2:8000')
          .replaceFirst('http://localhost:8000', 'http://10.0.2.2:8000')
      : url;

  /// Broadcasts login/logout so kept-alive screens (e.g. the Profile tab in the
  /// shell) can react without relying on initState re-running. `true` = a token
  /// is present. Updated by [saveToken] / [clearToken] and seeded on startup.
  static final ValueNotifier<bool> authState = ValueNotifier<bool>(false);

  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio _dio;
  Timer? _refreshTimer;

  ApiClient._internal() {
    _dio = Dio(BaseOptions(
      baseUrl: _baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
    ));
  }

  Dio get dio => _dio;

  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
    final expiry = DateTime.now().millisecondsSinceEpoch + _tokenLifetimeMs;
    await _storage.write(key: _tokenExpiryKey, value: expiry.toString());
    ApiClient()._scheduleRefresh(expiry);
    authState.value = true;
  }

  static Future<String?> getToken() async {
    return _storage.read(key: _tokenKey);
  }

  static Future<void> clearToken() async {
    ApiClient()._refreshTimer?.cancel();
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _tokenExpiryKey);
    authState.value = false;
  }

  static Future<bool> get isLoggedIn async {
    final token = await getToken();
    final loggedIn = token != null;
    if (authState.value != loggedIn) authState.value = loggedIn;
    return loggedIn;
  }

  // Called on app start to re-arm the refresh timer for an existing session.
  static Future<void> resumeSession() async {
    final expiryStr = await _storage.read(key: _tokenExpiryKey);
    authState.value = await getToken() != null;
    if (expiryStr == null) return;
    final expiry = int.tryParse(expiryStr);
    if (expiry == null) return;
    ApiClient()._scheduleRefresh(expiry);
  }

  void _scheduleRefresh(int expiryEpochMs) {
    _refreshTimer?.cancel();
    final now = DateTime.now().millisecondsSinceEpoch;
    final delay = expiryEpochMs - now - _refreshBufferMs;
    if (delay <= 0) {
      _doRefresh();
      return;
    }
    _refreshTimer = Timer(Duration(milliseconds: delay), _doRefresh);
  }

  Future<void> _doRefresh() async {
    try {
      final response = await _dio.post('/auth/refresh');
      final newToken = response.data['token'] as String;
      await saveToken(newToken);
    } on DioException {
      // Token expired or network error — user will be prompted to log in again.
      await clearToken();
    }
  }
}
