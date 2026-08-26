import 'dart:async';
import 'dart:convert';

import 'package:web_socket_channel/status.dart' as ws_status;
import 'package:web_socket_channel/web_socket_channel.dart';

import 'api_client.dart';

/// Realtime bridge to the live-auction Reverb channel `live.{id}`, speaking the
/// Pusher WebSocket protocol directly (Reverb is Pusher-compatible). Purely
/// additive on top of polling: if the socket can't connect, the watch screen
/// keeps its poll as the fallback (we signal that via [onConnected]).
///
/// Client params (app key, host, port, scheme) come from `/mobile/live-config`
/// so the endpoint is never hardcoded.
class LiveRealtime {
  LiveRealtime(this.liveId);

  final int liveId;

  WebSocketChannel? _ch;
  StreamSubscription<dynamic>? _sub;
  Timer? _ping;
  bool _stopped = false;

  String get _channelName => 'live.$liveId';

  // Event callbacks wired by the screen.
  void Function(Map<String, dynamic> data)? onBid;
  void Function(Map<String, dynamic> data)? onComment;
  void Function(Map<String, dynamic> data)? onProductChanged;
  void Function(Map<String, dynamic> data)? onAuctionClosed;
  void Function(Map<String, dynamic> data)? onLiked;
  void Function(Map<String, dynamic> data)? onStatusChanged;
  void Function(bool connected)? onConnected;

  /// Opens the socket. Returns false immediately if realtime isn't configured;
  /// connection success/failure is later reported through [onConnected].
  Future<bool> start() async {
    try {
      final resp = await ApiClient().dio.get('/mobile/live-config');
      final reverb = Map<String, dynamic>.from((resp.data['reverb'] ?? {}) as Map);
      final key = (reverb['key'] ?? '').toString();
      final host = (reverb['host'] ?? '').toString();
      if (key.isEmpty || host.isEmpty) return false; // realtime not configured

      final port = reverb['port'] is num
          ? (reverb['port'] as num).toInt()
          : int.tryParse('${reverb['port']}') ?? 443;
      final scheme = (reverb['scheme'] ?? 'https').toString() == 'https' ? 'wss' : 'ws';

      final uri = Uri.parse(
        '$scheme://$host:$port/app/$key?protocol=7&client=used-flutter&version=1.0.0',
      );
      final ch = WebSocketChannel.connect(uri);
      _ch = ch;
      _sub = ch.stream.listen(
        _onMessage,
        onError: (_) => _fail(),
        onDone: _fail,
        cancelOnError: false,
      );
      return true;
    } catch (_) {
      return false;
    }
  }

  void _onMessage(dynamic raw) {
    Map<String, dynamic> msg;
    try {
      msg = Map<String, dynamic>.from(jsonDecode(raw as String) as Map);
    } catch (_) {
      return;
    }

    final event = msg['event'] as String?;
    if (event == null) return;

    // Protocol frames.
    switch (event) {
      case 'pusher:connection_established':
        _subscribe();
        _startPing();
        onConnected?.call(true);
        return;
      case 'pusher:ping':
        _send({'event': 'pusher:pong', 'data': <String, dynamic>{}});
        return;
      case 'pusher:pong':
      case 'pusher_internal:subscription_succeeded':
      case 'pusher:error':
        return;
    }

    // Application events for our channel only.
    if (msg['channel'] != _channelName) return;
    final data = _decodeData(msg['data']);

    // Event names are the broadcast FQCN (no broadcastAs on the events).
    switch (event) {
      case 'App\\Events\\BidPlaced':
        onBid?.call(data);
        break;
      case 'App\\Events\\CommentPosted':
        onComment?.call(data);
        break;
      case 'App\\Events\\AuctionProductChanged':
        onProductChanged?.call(data);
        break;
      case 'App\\Events\\AuctionClosed':
        onAuctionClosed?.call(data);
        break;
      case 'App\\Events\\LiveLiked':
        onLiked?.call(data);
        break;
      case 'App\\Events\\LiveStatusChanged':
        onStatusChanged?.call(data);
        break;
    }
  }

  /// Pusher wraps event payloads as a JSON *string* in the `data` field.
  Map<String, dynamic> _decodeData(dynamic d) {
    try {
      if (d is String && d.isNotEmpty) {
        final j = jsonDecode(d);
        if (j is Map) return Map<String, dynamic>.from(j);
      } else if (d is Map) {
        return Map<String, dynamic>.from(d);
      }
    } catch (_) {}
    return <String, dynamic>{};
  }

  void _subscribe() => _send({
        'event': 'pusher:subscribe',
        'data': {'channel': _channelName},
      });

  void _send(Map<String, dynamic> m) {
    try {
      _ch?.sink.add(jsonEncode(m));
    } catch (_) {}
  }

  void _startPing() {
    _ping?.cancel();
    _ping = Timer.periodic(
      const Duration(seconds: 30),
      (_) => _send({'event': 'pusher:ping', 'data': <String, dynamic>{}}),
    );
  }

  void _fail() {
    if (_stopped) return;
    _ping?.cancel();
    // The screen's poll fallback takes over on a false signal.
    onConnected?.call(false);
  }

  Future<void> stop() async {
    _stopped = true;
    _ping?.cancel();
    await _sub?.cancel();
    try {
      await _ch?.sink.close(ws_status.normalClosure);
    } catch (_) {}
    _ch = null;
  }
}
