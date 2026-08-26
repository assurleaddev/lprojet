import 'dart:async';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../services/live_realtime.dart';
import '../../services/live_service.dart';
import '../../theme/app_colors.dart';
import 'live_ui.dart';

class LiveWatchScreen extends StatefulWidget {
  final ApiLive live;
  const LiveWatchScreen({super.key, required this.live});

  @override
  State<LiveWatchScreen> createState() => _LiveWatchScreenState();
}

class _LiveWatchScreenState extends State<LiveWatchScreen> {
  late ApiLive _live;
  List<ApiLiveComment> _comments = [];
  final _commentCtrl = TextEditingController();
  bool _bidding = false;
  bool _sending = false;

  // Agora (viewer subscribe)
  RtcEngine? _engine;
  String? _channel;
  int? _remoteUid;

  // Realtime: Reverb push (primary) with a poll as the safety-net fallback.
  LiveRealtime? _rt;
  Timer? _pollTimer;
  Duration _pollEvery = const Duration(seconds: 3);
  Timer? _tick;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _live = widget.live;
    _load();
    _startPoll(const Duration(seconds: 3));
    _tick = Timer.periodic(const Duration(seconds: 1), (_) => _updateCountdown());
    if (_live.isLive) _initAgora();
    _initRealtime();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _tick?.cancel();
    _rt?.stop();
    _commentCtrl.dispose();
    _disposeAgora();
    super.dispose();
  }

  /// (Re)starts the reconciliation poll at [every]. While push is connected we
  /// poll slowly (safety net); when it drops we poll fast (primary source).
  void _startPoll(Duration every) {
    if (_pollTimer != null && _pollEvery == every && _pollTimer!.isActive) return;
    _pollEvery = every;
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(every, (_) {
      if (mounted && (_live.isLive || _live.isScheduled)) _load();
    });
  }

  Future<void> _initRealtime() async {
    final rt = LiveRealtime(_live.id)
      ..onConnected = (connected) {
        if (!mounted) return;
        // Push carries the deltas; poll just reconciles occasionally.
        _startPoll(connected ? const Duration(seconds: 20) : const Duration(seconds: 3));
      }
      ..onBid = (d) {
        if (!mounted) return;
        setState(() => _live = _live.copyWith(
              auctionStatus: 'active',
              currentBid: (d['current_bid'] as num?)?.toDouble(),
              minNextBid: (d['min_next_bid'] as num?)?.toDouble(),
              currentBidderName: d['bidder_username']?.toString(),
              countdownEndsAt: d['countdown_ends_at'] != null
                  ? DateTime.tryParse('${d['countdown_ends_at']}')
                  : null,
            ));
        _updateCountdown();
      }
      ..onComment = (d) {
        if (!mounted) return;
        final c = ApiLiveComment.fromJson(Map<String, dynamic>.from(d));
        if (_comments.any((x) => x.id == c.id)) return; // de-dupe our own echo
        setState(() => _comments = [..._comments, c]);
      }
      ..onLiked = (d) {
        if (!mounted) return;
        final n = (d['likes_count'] as num?)?.toInt();
        if (n != null) setState(() => _live = _live.copyWith(likesCount: n));
      }
      // Structural changes → refetch authoritative state.
      ..onProductChanged = (_) {
        _load();
      }
      ..onAuctionClosed = (_) {
        _load();
      }
      ..onStatusChanged = (_) {
        _load();
      };

    final ok = await rt.start();
    if (!ok) return; // realtime unavailable → poll fallback already running
    _rt = rt;
  }

  Future<void> _initAgora() async {
    try {
      final t = await LiveService().agoraToken(_live.id);
      final appId = (t['app_id'] as String?) ?? '';
      if (appId.isEmpty) return; // Agora not configured → keep placeholder
      final engine = createAgoraRtcEngine();
      await engine.initialize(RtcEngineContext(appId: appId));
      engine.registerEventHandler(RtcEngineEventHandler(
        onUserJoined: (conn, uid, elapsed) {
          if (mounted) setState(() => _remoteUid = uid);
        },
        onUserOffline: (conn, uid, reason) {
          if (mounted) setState(() => _remoteUid = null);
        },
      ));
      await engine.setChannelProfile(ChannelProfileType.channelProfileLiveBroadcasting);
      await engine.setClientRole(role: ClientRoleType.clientRoleAudience);
      await engine.enableVideo();
      await engine.joinChannel(
        token: (t['token'] as String?) ?? '',
        channelId: (t['channel'] as String?) ?? '',
        uid: (t['uid'] as num?)?.toInt() ?? 0,
        options: const ChannelMediaOptions(
          clientRoleType: ClientRoleType.clientRoleAudience,
          channelProfile: ChannelProfileType.channelProfileLiveBroadcasting,
        ),
      );
      if (mounted) setState(() { _engine = engine; _channel = t['channel'] as String?; });
    } catch (_) {
      // Streaming unavailable → the thumbnail placeholder stays.
      await _disposeAgora();
    }
  }

  Future<void> _disposeAgora() async {
    final engine = _engine;
    _engine = null;
    if (engine != null) {
      try {
        await engine.leaveChannel();
        await engine.release();
      } catch (_) {}
    }
  }

  void _updateCountdown() {
    if (!mounted) return;
    if (_live.auctionActive && _live.countdownEndsAt != null) {
      final r = _live.countdownEndsAt!.difference(DateTime.now());
      setState(() => _remaining = r.isNegative ? Duration.zero : r);
    } else if (_remaining != Duration.zero) {
      setState(() => _remaining = Duration.zero);
    }
  }

  Future<void> _load() async {
    try {
      final live = await LiveService().getLive(_live.id);
      final comments = await LiveService().getComments(_live.id);
      if (!mounted) return;
      final wasLive = _live.isLive;
      setState(() { _live = live; _comments = comments; });
      // Stream just started while we were on the screen.
      if (!wasLive && live.isLive && _engine == null) _initAgora();
    } catch (_) {}
  }

  Future<void> _placeBid() async {
    final amount = _live.minNextBid;
    setState(() => _bidding = true);
    try {
      final res = await LiveService().placeBid(_live.id, amount);
      if (!mounted) return;
      if (res['ok'] == true) {
        _snack('Enchère placée : ${amount.toStringAsFixed(0)} MAD', AppColors.primary);
        _load();
      }
    } on DioException catch (e) {
      if (!mounted) return;
      final data = e.response?.data;
      if (data is Map && data['insufficient_balance'] == true) {
        _snack('Solde insuffisant. Rechargez votre portefeuille.', Colors.red);
      } else {
        _snack(data is Map && data['message'] != null ? data['message'].toString() : 'Enchère impossible', Colors.red);
      }
    } finally {
      if (mounted) setState(() => _bidding = false);
    }
  }

  Future<void> _sendComment() async {
    final text = _commentCtrl.text.trim();
    if (text.isEmpty) return;
    setState(() => _sending = true);
    try {
      final c = await LiveService().comment(_live.id, text);
      if (mounted) setState(() { _comments = [..._comments, c]; _commentCtrl.clear(); });
    } on DioException catch (e) {
      if (mounted) _snack(e.response?.statusCode == 422 ? 'Le live n\'est pas actif' : 'Envoi impossible', Colors.red);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  Future<void> _like() async {
    setState(() => _live = _live.copyWith(likesCount: _live.likesCount + 1));
    try {
      await LiveService().like(_live.id);
    } catch (_) {}
  }

  void _snack(String msg, Color color) => ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: color),
      );

  // ---------------------------------------------------------------- UI

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      resizeToAvoidBottomInset: false,
      body: Stack(
        children: [
          Positioned.fill(child: _videoLayer()),
          const Positioned.fill(child: LiveScrim()),
          // Top chrome
          SafeArea(
            bottom: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 0),
              child: _topBar(),
            ),
          ),
          // Bottom stack: comments → auction panel → comment input
          SafeArea(
            top: false,
            child: Padding(
              padding: EdgeInsets.only(left: 10, right: 10, bottom: 8 + MediaQuery.of(context).viewInsets.bottom),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.end,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Align(alignment: Alignment.centerLeft, child: LiveCommentsOverlay(comments: _comments)),
                  const SizedBox(height: 10),
                  _auctionPanel(),
                  const SizedBox(height: 8),
                  LiveCommentInput(
                    controller: _commentCtrl,
                    enabled: _live.isLive,
                    sending: _sending,
                    onSend: _sendComment,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _videoLayer() {
    if (_engine != null && _remoteUid != null && _channel != null) {
      return AgoraVideoView(
        controller: VideoViewController.remote(
          rtcEngine: _engine!,
          canvas: VideoCanvas(uid: _remoteUid),
          connection: RtcConnection(channelId: _channel),
        ),
      );
    }
    return Stack(fit: StackFit.expand, children: [
      if (_live.thumbnailUrl != null)
        CachedNetworkImage(imageUrl: _live.thumbnailUrl!, fit: BoxFit.cover, errorWidget: (_, __, ___) => Container(color: Colors.black))
      else
        Container(color: Colors.black),
      Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          if (_live.isLive) ...[
            const CircularProgressIndicator(color: Colors.white70, strokeWidth: 2),
            const SizedBox(height: 10),
            const Text('Connexion au direct…', style: TextStyle(color: Colors.white70, fontSize: 12)),
          ] else if (_live.isScheduled)
            const Text('Ce live n’a pas encore commencé', style: TextStyle(color: Colors.white70, fontSize: 13))
          else
            const Text('Live terminé', style: TextStyle(color: Colors.white70, fontSize: 13)),
        ]),
      ),
    ]);
  }

  Widget _topBar() {
    return Row(children: [
      LiveGlassButton(
        onTap: () => Navigator.of(context).maybePop(),
        child: const Icon(Icons.arrow_back, color: Colors.white, size: 20),
      ),
      const SizedBox(width: 8),
      LiveSellerChip(
        name: _live.seller?.name ?? '',
        avatarUrl: _live.seller?.avatarUrl,
        status: _live.status,
      ),
      const Spacer(),
      _likeButton(),
    ]);
  }

  Widget _likeButton() {
    return LiveGlassButton(
      onTap: _live.isEnded ? null : _like,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.favorite, size: 16, color: AppColors.primary),
        const SizedBox(width: 6),
        Text('${_live.likesCount}', style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
      ]),
    );
  }

  Widget _auctionPanel() {
    if (_live.auctionActive && _live.currentProduct != null) {
      final p = _live.currentProduct!;
      final hasBid = _live.currentBid != null;
      final ending = _remaining.inSeconds > 0;
      return Column(mainAxisSize: MainAxisSize.min, children: [
        LiveProductCardH(
          title: p.title,
          image: p.image,
          amount: hasBid ? _live.currentBid! : _live.startingBid,
          hasBid: hasBid,
          bidderName: _live.currentBidderName,
          trailing: ending
              ? Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(10)),
                  child: Text('${_remaining.inSeconds}s',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                )
              : null,
        ),
        const SizedBox(height: 10),
        SlideToBid(
          label: 'Glisser pour enchérir ${_live.minNextBid.toStringAsFixed(0)} MAD',
          enabled: !_bidding,
          onConfirm: _placeBid,
        ),
      ]);
    }
    if (_live.isLive) return const LiveAwaitingRow();
    return const SizedBox.shrink();
  }
}
