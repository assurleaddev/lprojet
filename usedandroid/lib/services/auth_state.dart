import 'package:flutter/foundation.dart';

/// Global auth + verification state used by the router to enforce that a
/// logged-in user has verified BOTH email and phone before using the app.
///
/// Guests (not logged in) are never gated — they can browse freely.
class AuthState extends ChangeNotifier {
  AuthState._();
  static final AuthState instance = AuthState._();

  bool loggedIn = false;
  bool emailVerified = false;
  bool phoneVerified = false;

  /// Email not yet verified → must go to the email screen first.
  bool get needsEmail => loggedIn && !emailVerified;

  /// Email done but phone pending → must go to the phone screen.
  bool get needsPhone => loggedIn && emailVerified && !phoneVerified;

  /// Fully verified logged-in user.
  bool get verified => loggedIn && emailVerified && phoneVerified;

  void apply({required bool email, required bool phone}) {
    loggedIn = true;
    emailVerified = email;
    phoneVerified = phone;
    notifyListeners();
  }

  void markEmailVerified() {
    emailVerified = true;
    notifyListeners();
  }

  void markPhoneVerified() {
    phoneVerified = true;
    notifyListeners();
  }

  void clear() {
    loggedIn = false;
    emailVerified = false;
    phoneVerified = false;
    notifyListeners();
  }
}
