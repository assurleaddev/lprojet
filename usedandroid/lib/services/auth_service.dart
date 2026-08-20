import 'package:dio/dio.dart';
import 'api_client.dart';
import 'auth_state.dart';

class AuthUser {
  final int id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String email;
  final bool emailVerified;
  final bool phoneVerified;
  final String? phoneNumber;

  const AuthUser({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.email,
    this.emailVerified = false,
    this.phoneVerified = false,
    this.phoneNumber,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) => AuthUser(
        id: json['id'],
        firstName: json['first_name'] ?? '',
        lastName: json['last_name'] ?? '',
        fullName: json['full_name'] ?? '',
        email: json['email'] ?? '',
        emailVerified: json['email_verified'] ?? false,
        phoneVerified: json['phone_verified'] ?? false,
        phoneNumber: json['phone_number'],
      );

  /// Next step required before the account is fully usable.
  bool get needsEmailVerification => !emailVerified;
  bool get needsPhoneVerification => emailVerified && !phoneVerified;
}

class AuthService {
  final _client = ApiClient();

  Future<AuthUser> login(String email, String password) async {
    final response = await _client.dio.post('/auth/login', data: {
      'email': email,
      'password': password,
    });
    final token = response.data['token'] as String;
    await ApiClient.saveToken(token);
    final user = AuthUser.fromJson(response.data['user']);
    AuthState.instance.apply(email: user.emailVerified, phone: user.phoneVerified);
    return user;
  }

  Future<AuthUser> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
  }) async {
    final response = await _client.dio.post('/auth/register', data: {
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'password': password,
      'password_confirmation': password,
    });
    final token = response.data['token'] as String;
    await ApiClient.saveToken(token);
    final user = AuthUser.fromJson(response.data['user']);
    AuthState.instance.apply(email: user.emailVerified, phone: user.phoneVerified);
    return user;
  }

  Future<AuthUser> getUser() async {
    final response = await _client.dio.get('/auth/user');
    final user = AuthUser.fromJson(response.data);
    AuthState.instance.apply(email: user.emailVerified, phone: user.phoneVerified);
    return user;
  }

  /// Called once at app launch: if a token is stored, load the user so the
  /// router knows the verification state (to gate unverified sessions).
  Future<void> bootstrap() async {
    if (!await ApiClient.isLoggedIn) {
      AuthState.instance.clear();
      return;
    }
    try {
      await getUser();
    } catch (_) {
      // Transient/offline error: don't gate. A real 401 clears the token elsewhere.
      AuthState.instance.clear();
    }
  }

  // ── Verification ──────────────────────────────────────────────────────────

  /// (Re)send the 4-digit email verification code.
  Future<void> sendEmailCode() => _client.dio.post('/auth/email/send');

  /// Confirm the email code. Throws DioException (422) if invalid/expired.
  Future<void> verifyEmailCode(String code) =>
      _client.dio.post('/auth/email/verify', data: {'code': code});

  /// Store the phone and send a 6-digit OTP (WhatsApp, SMS fallback).
  Future<void> sendPhoneCode(String countryCode, String phoneNumber) =>
      _client.dio.post('/auth/phone/send', data: {
        'country_code': countryCode,
        'phone_number': phoneNumber,
      });

  /// Confirm the phone OTP. Throws DioException (422) if invalid/expired.
  Future<void> verifyPhoneCode(String code) =>
      _client.dio.post('/auth/phone/verify', data: {'code': code});

  Future<void> logout() async {
    try {
      await _client.dio.post('/auth/logout');
    } on DioException {
      // ignore network errors on logout
    } finally {
      await ApiClient.clearToken();
      AuthState.instance.clear();
    }
  }
}
