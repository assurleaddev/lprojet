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

  /// The single-threaded local dev server drops some concurrent image
  /// requests; retry on the emulator only. Production needs no retries.
  static int get imageMaxRetries => isLocalDev ? 6 : 0;
}
