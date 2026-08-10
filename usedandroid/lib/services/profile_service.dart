import 'dart:io';
import 'package:dio/dio.dart';
import 'api_client.dart';

/// Full editable profile + settings, mirroring the web settings pages.
class MobileProfile {
  final int id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String email;
  final String? username;
  final String? avatarUrl;
  final String? phoneNumber;
  final String? about;
  final String? country;
  final String? city;
  final bool showCity;
  final String? language;
  final String? gender;
  final String? birthday;
  final int? memberSince;
  final Map<String, bool> notifications;
  final String notificationLimit;
  final int productsCount;

  const MobileProfile({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.email,
    this.username,
    this.avatarUrl,
    this.phoneNumber,
    this.about,
    this.country,
    this.city,
    this.showCity = false,
    this.language,
    this.gender,
    this.birthday,
    this.memberSince,
    this.notifications = const {},
    this.notificationLimit = 'unlimited',
    this.productsCount = 0,
  });

  String get location => [city, country].where((e) => e != null && e.isNotEmpty).join(', ');

  factory MobileProfile.fromJson(Map<String, dynamic> json) {
    final notif = (json['notifications'] as Map?) ?? {};
    return MobileProfile(
      id: json['id'],
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      fullName: json['full_name'] ?? '',
      email: json['email'] ?? '',
      username: json['username'],
      avatarUrl: json['avatar_url'] != null ? ApiClient.fixImageUrl(json['avatar_url']) : null,
      phoneNumber: json['phone_number'],
      about: json['about'],
      country: json['country'],
      city: json['city'],
      showCity: json['show_city'] ?? false,
      language: json['language'],
      gender: json['gender'],
      birthday: json['birthday'],
      memberSince: json['member_since'],
      notifications: notif.map((k, v) => MapEntry(k.toString(), v == true)),
      notificationLimit: json['notifications']?['notification_limit']?.toString() ?? 'unlimited',
      productsCount: json['stats']?['products_count'] ?? 0,
    );
  }
}

class ProfileService {
  final _client = ApiClient();

  Future<MobileProfile> getProfile() async {
    final response = await _client.dio.get('/mobile/profile');
    return MobileProfile.fromJson(response.data);
  }

  Future<MobileProfile> updateProfile({
    String? firstName,
    String? lastName,
    String? username,
    String? about,
    String? country,
    String? city,
    bool? showCity,
    String? language,
    String? gender,
    String? birthday,
    File? avatar,
  }) async {
    final formData = FormData.fromMap({
      if (firstName != null) 'first_name': firstName,
      if (lastName != null) 'last_name': lastName,
      if (username != null && username.isNotEmpty) 'username': username,
      if (about != null) 'about': about,
      if (country != null) 'country': country,
      if (city != null) 'city': city,
      if (showCity != null) 'show_city': showCity ? 1 : 0,
      if (language != null) 'language': language,
      if (gender != null) 'gender': gender,
      if (birthday != null) 'birthday': birthday,
    });
    if (avatar != null) {
      formData.files.add(MapEntry('avatar', await MultipartFile.fromFile(avatar.path)));
    }
    final response = await _client.dio.post('/mobile/profile', data: formData);
    return MobileProfile.fromJson(response.data);
  }

  Future<void> changePassword(String currentPassword, String newPassword) async {
    await _client.dio.post('/mobile/profile/password', data: {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': newPassword,
    });
  }

  Future<Map<String, bool>> updateNotifications(Map<String, bool> toggles) async {
    final response = await _client.dio.post('/mobile/profile/notifications', data: toggles);
    final data = (response.data as Map);
    return data.map((k, v) => MapEntry(k.toString(), v == true))..remove('notification_limit');
  }
}
