import 'dart:async';

import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../services/live_realtime.dart';
import '../../services/live_service.dart';
import '../../theme/app_colors.dart';

/// Seller flow — step 2: the broadcast console. Publishes the seller's camera
/// via Agora, runs auctions on the curated products (10s countdown, auto-closed
/// on this device when it expires — mirrors the website), and ends the live.
class LiveBroadcastScreen extends StatefulWidget {
  final ApiLive live;
  const LiveBroadcastScreen({super.key, required this.live});

  @override
  State<LiveBroadcastScreen> createState() => _LiveBroadcastScreenState();
}

class _LiveBroadcastScreenState extends State<LiveBroadcastScreen> with WidgetsBindingObserver {
  late ApiLive _live;
  List<ApiLiveComment> _comments = [];

  RtcEngine? _engine;
  Map<String, dynamic>? _agoraToken;
  bool _joined = false;
  bool _permDenied = false;
  bool _permPermanent = false; // denied for good → must open OS settings

  LiveRealtime? _rt;
  Timer? _poll;
  Timer? _tick;
  Duration _remaining = Duration.zero;
  bool _closing = false;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _live = widget.live;
    _reload();
    _poll = Timer.periodic(const Duration(seconds: 5), (_) {
      if (mounted && !_live.isEnded) _reload();
    });
    _tick = Timer.periodic(const Duration(seconds: 1), (_) => _onTick());
    _requestPermsAndInit();
    _initRealtime();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _poll?.cancel();
    _tick?.cancel();
    _rt?.stop();
    _teardownAgora();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Coming back from the OS settings page (after granting) → retry.
    if (state == AppLifecycleState.resumed && _permDenied && _engine == null) {
      _requestPermsAndInit();
    }
  }

  // ---- Agora publisher ----

  /// Request camera + mic, then set up Agora. Retriable from the denied UI and
  /// from app-resume. On permanent denial it flags [_permPermanent] so the UI
  /// offers "open settings" instead of a no-op re-request.
  Future<void> _requestPermsAndInit() async {
    final statuses = await [Permission.camera, Permission.microphone].request();
    final cam = statuses[Permission.camera];
    final mic = statuses[Permission.microphone];
    final granted = cam == PermissionStatus.granted && mic == PermissionStatus.granted;
    if (!granted) {
      final permanent = cam == PermissionStatus.permanentlyDenied ||
          mic == PermissionStatus.permanentlyDenied ||
          cam == PermissionStatus.restricted ||
          mic == PermissionStatus.restricted;
      if (mounted) {
        setState(() {
          _permDenied = true;
          _permPermanent = permanent;
        });
      }
      return;
    }
    if (mounted) {
      setState(() {
        _permDenied = false;
        _permPermanent = false;
      });
    }
    await _initAgoraEngine();
  }

  Future<void> _initAgoraEngine() async {
    if (_engine != null) return;
    try {
      final t = await LiveService().agoraToken(_live.id);
      final appId = (t['app_id'] as String?) ?? '';
      if (appId.isEmpty) return; // Agora not configured
      final engine = createAgoraRtcEngine();
      await engine.initialize(RtcEngineContext(appId: appId));
      await engine.enableVideo();
      await engine.startPreview();
      await engine.setChannelProfile(ChannelProfileType.channelProfileLiveBroadcasting);
      await engine.setClientRole(role: ClientRoleType.clientRoleBroadcaster);
      _engine = engine;
      _agoraToken = t;
      if (_live.isLive) await _joinAndPublish();
      if (mounted) setState(() {});
    } catch (_) {
      await _teardownAgora();
    }
  }

  Future<void> _joinAndPublish() async {
    final t = _agoraToken;
    final engine = _engine;
    if (t == null || engine == null || _joined) return;
    await engine.joinChannel(
      token: (t['token'] as String?) ?? '',
      channelId: (t['channel'] as String?) ?? '',
      uid: (t['uid'] as num?)?.toInt() ?? 0,
      options: const ChannelMediaOptions(
        clientRoleType: ClientRoleType.clientRoleBroadcaster,
        channelProfile: ChannelProfileType.channelProfileLiveBroadcasting,
        publishCameraTrack: true,
        publishMicrophoneTrack: true,
      ),
    );
    _joined = true;
  }

  Future<void> _teardownAgora() async {
    final engine = _engine;
    _engine = null;
    if (engine != null) {
      try {
        await engine.leaveChannel();
        await engine.stopPreview();
        await engine.release();
      } catch (_) {}
    }
  }

  // ---- Realtime + polling ----

  Future<void> _initRealtime() async {
    final rt = LiveRealtime(_live.id)
      ..onConnected = (connected) {
        if (mounted && connected) _poll?.cancel();
      }
      ..onBid = (d) {
        if (!mounted) return;
        setState(() => _live = _live.copyWith(
              auctionStatus: 'active',
              currentBid: (d['current_bid'] as num?)?.toDouble(),
              minNextBid: (d['min_next_bid'] as num?)?.toDouble(),
              currentBidderName: d['bidder_username']?.toString(),
              countdownEndsAt: d['countdown_ends_at'] != null ? DateTime.tryParse('${d['countdown_ends_at']}') : null,
            ));
        _onTick();
      }
      ..onComment = (d) {
        if (!mounted) return;
        final c = ApiLiveComment.fromJson(Map<String, dynamic>.from(d));
        if (_comments.any((x) => x.id == c.id)) return;
        setState(() => _comments = [..._comments, c]);
      }
      ..onLiked = (d) {
        if (!mounted) return;
        final n = (d['likes_count'] as num?)?.toInt();
        if (n != null) setState(() => _live = _live.copyWith(likesCount: n));
      }
      ..onProductChanged = (_) {
        _reload();
      }
      ..onAuctionClosed = (_) {
        _reload();
      }
      ..onStatusChanged = (_) {
        _reload();
      };
    final ok = await rt.start();
    if (ok) _rt = rt;
  }

  Future<void> _reload() async {
    try {
      final live = await LiveService().getLive(_live.id);
      final comments = await LiveService().getComments(_live.id);
      if (!mounted) return;
      setState(() {
        _live = live;
        _comments = comments;
      });
    } catch (_) {}
  }

  void _onTick() {
    if (!mounted) return;
    if (_live.auctionActive && _live.countdownEndsAt != null) {
      final r = _live.countdownEndsAt!.difference(DateTime.now());
      setState(() => _remaining = r.isNegative ? Duration.zero : r);
      // Seller's device closes the auction when the countdown expires.
      if (r.inMilliseconds <= 0 && !_closing) _closeAuction(auto: true);
    } else if (_remaining != Duration.zero) {
      setState(() => _remaining = Duration.zero);
    }
  }

  // ---- Seller actions ----

  Future<void> _goLive() async {
    setState(() => _busy = true);
    try {
      await LiveService().goLive(_live.id);
      await _joinAndPublish();
      if (mounted) setState(() => _live = _live.copyWith(status: 'live'));
    } on DioException catch (e) {
      if (mounted) _snack(_msg(e, 'Impossible de passer en direct'), Colors.red);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _startAuction(ApiLiveProduct p) async {
    final startingBid = await _askStartingBid(p);
    if (startingBid == null) return;
    setState(() => _busy = true);
    try {
      await LiveService().setProduct(_live.id, productId: p.id, startingBid: startingBid);
      await _reload();
    } on DioException catch (e) {
      if (mounted) _snack(_msg(e, 'Impossible de lancer l’enchère'), Colors.red);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _closeAuction({bool auto = false}) async {
    if (_closing) return;
    _closing = true;
    try {
      final res = await LiveService().closeAuction(_live.id);
      if (!mounted) return;
      final winner = res['winner_username'];
      final bid = (res['winning_bid'] as num?)?.toStringAsFixed(0);
      _snack(
        winner != null ? 'Adjugé à $winner — $bid MAD' : 'Enchère clôturée (aucune offre)',
        AppColors.primary,
      );
      await _reload();
    } on DioException catch (_) {
      // Likely already closed elsewhere; refetch to sync.
      await _reload();
    } finally {
      _closing = false;
    }
  }

  Future<void> _endLive() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Terminer le live ?'),
        content: const Text('Le direct sera clôturé pour tous les spectateurs.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Terminer', style: TextStyle(color: AppColors.primary))),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await LiveService().endLive(_live.id);
    } catch (_) {}
    await _teardownAgora();
    if (mounted) context.pop();
  }

  Future<double?> _askStartingBid(ApiLiveProduct p) async {
    final ctrl = TextEditingController(text: p.preBidMin > 0 ? p.preBidMin.toStringAsFixed(0) : '10');
    return showDialog<double>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Mise de départ — ${p.title}', maxLines: 2, style: const TextStyle(fontSize: 16)),
        content: TextField(
          controller: ctrl,
          autofocus: true,
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          decoration: const InputDecoration(suffixText: 'MAD'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          TextButton(
            onPressed: () {
              final v = double.tryParse(ctrl.text.trim());
              if (v == null || v < 1) return;
              Navigator.pop(ctx, v);
            },
            child: const Text('Lancer'),
          ),
        ],
      ),
    );
  }

  String _msg(DioException e, String fallback) {
    final data = e.response?.data;
    return data is Map && data['message'] != null ? data['message'].toString() : fallback;
  }

  void _snack(String msg, Color color) =>
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: color));

  // ---- UI ----

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Column(
          children: [
            _videoArea(),
            Expanded(
              child: Container(
                color: AppColors.surface,
                child: ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    if (_live.isScheduled) _goLivePanel(),
                    if (_live.isLive) ...[
                      if (_live.auctionActive && _live.currentProduct != null) _activeAuctionCard(),
                      const SizedBox(height: 8),
                      const Text('Vos articles', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      ..._live.products.map(_sessionProductTile),
                    ],
                    const SizedBox(height: 16),
                    const Text('Commentaires', style: TextStyle(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    if (_comments.isEmpty)
                      const Text('Aucun commentaire', style: TextStyle(color: AppColors.textSecondary))
                    else
                      ..._comments.map(_commentTile),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _videoArea() {
    return AspectRatio(
      aspectRatio: 16 / 11,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (_engine != null)
            AgoraVideoView(
              controller: VideoViewController(
                rtcEngine: _engine!,
                canvas: const VideoCanvas(uid: 0), // local camera
              ),
            )
          else
            Container(
              color: Colors.black,
              child: Center(
                child: _permDenied
                    ? Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 24),
                        child: Column(mainAxisSize: MainAxisSize.min, children: [
                          const Icon(Icons.videocam_off_outlined, color: Colors.white54, size: 34),
                          const SizedBox(height: 10),
                          Text(
                            _permPermanent
                                ? 'Caméra et micro refusés. Activez-les dans les réglages pour diffuser.'
                                : 'Autorisez la caméra et le micro pour diffuser.',
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.white70),
                          ),
                          const SizedBox(height: 12),
                          ElevatedButton(
                            onPressed: () async {
                              if (_permPermanent) {
                                await openAppSettings();
                              } else {
                                await _requestPermsAndInit();
                              }
                            },
                            child: Text(_permPermanent ? 'Ouvrir les réglages' : 'Autoriser'),
                          ),
                        ]),
                      )
                    : const CircularProgressIndicator(color: Colors.white70, strokeWidth: 2),
              ),
            ),
          Positioned(
            top: 8,
            left: 4,
            right: 8,
            child: Row(children: [
              IconButton(icon: const Icon(Icons.close, color: Colors.white), onPressed: _endLive),
              if (_live.isLive)
                _pill('EN DIRECT', AppColors.primary)
              else
                _pill('PROGRAMMÉ', Colors.black54),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(color: Colors.black45, borderRadius: BorderRadius.circular(20)),
                child: Row(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.favorite, size: 15, color: Colors.white),
                  const SizedBox(width: 5),
                  Text('${_live.likesCount}', style: const TextStyle(color: Colors.white, fontSize: 12)),
                ]),
              ),
              if (_live.isLive) ...[
                const SizedBox(width: 8),
                TextButton(
                  onPressed: _endLive,
                  style: TextButton.styleFrom(
                    backgroundColor: Colors.black45,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  ),
                  child: const Text('Terminer', style: TextStyle(color: Colors.white, fontSize: 12)),
                ),
              ],
            ]),
          ),
        ],
      ),
    );
  }

  Widget _pill(String text, Color color) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(6)),
        child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
      );

  Widget _goLivePanel() {
    return Column(
      children: [
        const Text('Prêt à diffuser ?', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
        const SizedBox(height: 6),
        const Text('Vérifiez votre caméra ci-dessus, puis passez en direct.',
            textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary)),
        const SizedBox(height: 14),
        SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton.icon(
            onPressed: (_busy || _engine == null) ? null : _goLive,
            icon: const Icon(Icons.videocam),
            label: _busy
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Text('Passer en direct'),
          ),
        ),
      ],
    );
  }

  Widget _activeAuctionCard() {
    final p = _live.currentProduct!;
    final hasBid = _live.currentBid != null;
    final ending = _remaining.inSeconds > 0;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.inputFill,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: p.image != null
                  ? CachedNetworkImage(imageUrl: p.image!, width: 56, height: 56, fit: BoxFit.cover)
                  : Container(width: 56, height: 56, color: AppColors.border, child: const Icon(Icons.image_outlined, color: AppColors.textSecondary)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('Aux enchères : ${p.title}', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700)),
                Text(hasBid ? 'Enchère actuelle' : 'Enchère de départ', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                Text('${(hasBid ? _live.currentBid! : _live.startingBid).toStringAsFixed(0)} MAD',
                    style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 18)),
                if (hasBid && _live.currentBidderName != null)
                  Text('Meilleure enchère : ${_live.currentBidderName}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 11)),
              ]),
            ),
            if (ending)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(8)),
                child: Text('${_remaining.inSeconds}s',
                    style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w800, fontSize: 18)),
              ),
          ]),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: _closing ? null : () => _closeAuction(),
              child: const Text('Clôturer l’enchère'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _sessionProductTile(ApiLiveProduct p) {
    final isCurrent = _live.auctionActive && _live.currentProduct?.id == p.id;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: p.image != null
              ? CachedNetworkImage(imageUrl: p.image!, width: 44, height: 44, fit: BoxFit.cover)
              : Container(width: 44, height: 44, color: AppColors.inputFill, child: const Icon(Icons.image_outlined, color: AppColors.textSecondary)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(p.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
            Text('Pré-enchère dès ${p.preBidMin.toStringAsFixed(0)} MAD', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
          ]),
        ),
        if (isCurrent)
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 8),
            child: Text('En cours', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 12)),
          )
        else
          ElevatedButton(
            onPressed: _busy ? null : () => _startAuction(p),
            style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8)),
            child: const Text('Enchérir'),
          ),
      ]),
    );
  }

  Widget _commentTile(ApiLiveComment c) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        CircleAvatar(
          radius: 13,
          backgroundColor: AppColors.inputFill,
          backgroundImage: c.avatarUrl != null ? CachedNetworkImageProvider(c.avatarUrl!) : null,
          child: c.avatarUrl == null ? const Icon(Icons.person, size: 14, color: AppColors.textSecondary) : null,
        ),
        const SizedBox(width: 8),
        Expanded(
          child: RichText(
            text: TextSpan(style: const TextStyle(color: AppColors.textPrimary, fontSize: 13), children: [
              TextSpan(text: '${c.username}  ', style: const TextStyle(fontWeight: FontWeight.w700)),
              TextSpan(text: c.content, style: const TextStyle(color: AppColors.textSecondary)),
            ]),
          ),
        ),
      ]),
    );
  }
}
