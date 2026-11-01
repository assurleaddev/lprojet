import 'dart:async';
import 'package:flutter/widgets.dart';
import 'api_client.dart';
import 'inbox_service.dart';

/// Unread messages + notifications for the bottom-bar badges.
///
/// Refreshed on login, when the app comes back to the foreground, every
/// [_pollEvery] while signed in, and on demand (e.g. after opening a chat).
class UnreadStore extends ValueNotifier<UnreadCounts> with WidgetsBindingObserver {
  UnreadStore._() : super(const UnreadCounts());

  static final UnreadStore instance = UnreadStore._();

  static const _pollEvery = Duration(seconds: 45);
  Timer? _timer;
  bool _started = false;

  /// Call once at startup.
  void start() {
    if (_started) return;
    _started = true;
    WidgetsBinding.instance.addObserver(this);
    ApiClient.authState.addListener(_onAuthChanged);
    _onAuthChanged();
  }

  void _onAuthChanged() {
    _timer?.cancel();
    if (ApiClient.authState.value) {
      refresh();
      _timer = Timer.periodic(_pollEvery, (_) => refresh());
    } else {
      value = const UnreadCounts();
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) refresh();
  }

  Future<void> refresh() async {
    if (!ApiClient.authState.value) return;
    try {
      value = await InboxService().getUnreadCounts();
    } catch (_) {
      // Keep the last known counts on a network hiccup.
    }
  }
}
