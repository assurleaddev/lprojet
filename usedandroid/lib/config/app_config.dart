/// Central switch between local-dev and production configuration.
///
/// Enable local dev by running with:
///   flutter run --dart-define=LOCAL_DEV=true
///
/// When the flag is absent (any normal / release build), the app targets
/// production over HTTPS with no local-only workarounds. This keeps the
/// emulator hacks (host loopback, cleartext HTTP, image-host rewrite, retry)
/// out of anything you ship.
class AppConfig {
  const AppConfig._();

  /// True only when explicitly launched with --dart-define=LOCAL_DEV=true.
  static const bool isLocalDev = bool.fromEnvironment('LOCAL_DEV');

  static const String _prodApiBase = 'https://usedis.com/api';

  /// 10.0.2.2 is the Android emulator's alias to the host loopback, so it
  /// reaches `php artisan serve` on 127.0.0.1:8000.
  static const String _localApiBase = 'http://10.0.2.2:8000/api';

  static String get apiBaseUrl => isLocalDev ? _localApiBase : _prodApiBase;

  /// Public website root (legal pages, web-only settings). Always production:
  /// these pages are opened in the phone's browser, not the emulator.
  static const String siteUrl = 'https://usedis.com';

  /// Google Sign-In OAuth clients (public identifiers, not secrets).
  /// The "USED mobile server" web client: Google issues the app's ID tokens
  /// for it and the API verifies them (Android requires it as serverClientId).
  static const String googleServerClientId =
      '151137971968-annl42g99iph8paki883nft62pl6mgk8.apps.googleusercontent.com';

  /// The "USED iOS" client (also set as GIDClientID / URL scheme in Info.plist).
  static const String googleIosClientId =
      '151137971968-ihkadraaffkree4clcf2u9ike25dpmpk.apps.googleusercontent.com';

  /// The single-threaded local dev server drops some concurrent image
  /// requests; retry on the emulator only. Production needs no retries.
  static int get imageMaxRetries => isLocalDev ? 6 : 0;
}
