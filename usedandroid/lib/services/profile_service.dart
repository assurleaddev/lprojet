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
  final int followersCount;
  final int followingCount;
  final double ratingAvg;
  final int ratingCount;

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
    this.followersCount = 0,
    this.followingCount = 0,
    this.ratingAvg = 0,
    this.ratingCount = 0,
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
      followersCount: json['stats']?['followers_count'] ?? 0,
      followingCount: json['stats']?['following_count'] ?? 0,
      ratingAvg: (json['stats']?['rating_avg'] as num?)?.toDouble() ?? 0,
      ratingCount: json['stats']?['rating_count'] ?? 0,
    );
  }
}

/// Another member's public profile (seller page) or a member-search result.
class ApiMember {
  final int id;
  final String? username;
  final String fullName;
  final String? avatarUrl;
  final double ratingAvg;
  final int ratingCount;
  final int followersCount;
  final int followingCount;
  final int productsCount;
  final int? memberSince;
  final String? about;
  final String? city;
  final bool isFollowing;
  final bool isMe;

  const ApiMember({
    required this.id,
    this.username,
    required this.fullName,
    this.avatarUrl,
    this.ratingAvg = 0,
    this.ratingCount = 0,
    this.followersCount = 0,
    this.followingCount = 0,
    this.productsCount = 0,
    this.memberSince,
    this.about,
    this.city,
    this.isFollowing = false,
    this.isMe = false,
  });

  /// Name to show: the full name, else the username.
  String get displayName => fullName.trim().isNotEmpty ? fullName : (username ?? '');

  factory ApiMember.fromJson(Map<String, dynamic> json) => ApiMember(
        id: json['id'],
        username: json['username'],
        fullName: json['full_name'] ?? '',
        avatarUrl: json['avatar_url'] != null ? ApiClient.fixImageUrl(json['avatar_url']) : null,
        ratingAvg: (json['rating_avg'] as num?)?.toDouble() ?? 0,
        ratingCount: json['rating_count'] ?? 0,
        followersCount: json['followers_count'] ?? 0,
        followingCount: json['following_count'] ?? 0,
        productsCount: json['products_count'] ?? 0,
        memberSince: json['member_since'],
        about: json['about'],
        city: json['city'],
        isFollowing: json['is_following'] ?? false,
        isMe: json['is_me'] ?? false,
      );
}

class ProfileService {
  final _client = ApiClient();

  /// A member's public profile (works signed in or not).
  Future<ApiMember> getMember(int id) async {
    final response = await _client.dio.get('/mobile/users/$id');
    return ApiMember.fromJson(response.data);
  }

  /// Member search ("Membres" in the search bar). [query] needs 2+ characters.
  Future<List<ApiMember>> searchMembers(String query) async {
    final response = await _client.dio.get('/mobile/users/search', queryParameters: {'q': query});
    return (response.data['data'] as List).map((j) => ApiMember.fromJson(j)).toList();
  }

  /// Follow / unfollow a member. Returns the new state and follower count.
  Future<({bool following, int followersCount})> toggleFollow(int userId) async {
    final response = await _client.dio.post('/mobile/users/$userId/follow');
    return (
      following: response.data['following'] == true,
      followersCount: (response.data['followers_count'] as num?)?.toInt() ?? 0,
    );
  }

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
