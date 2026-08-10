import 'package:dio/dio.dart';
import 'api_client.dart';

class AuthUser {
  final int id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String email;

  const AuthUser({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.email,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) => AuthUser(
        id: json['id'],
        firstName: json['first_name'] ?? '',
        lastName: json['last_name'] ?? '',
        fullName: json['full_name'] ?? '',
        email: json['email'] ?? '',
      );
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
    return AuthUser.fromJson(response.data['user']);
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
    return AuthUser.fromJson(response.data['user']);
  }

  Future<AuthUser> getUser() async {
    final response = await _client.dio.get('/auth/user');
    return AuthUser.fromJson(response.data);
  }

  Future<void> logout() async {
    try {
      await _client.dio.post('/auth/logout');
    } on DioException {
      // ignore network errors on logout
    } finally {
      await ApiClient.clearToken();
    }
  }
}

