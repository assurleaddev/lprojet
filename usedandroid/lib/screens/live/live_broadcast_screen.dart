import 'dart:async';

import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:share_plus/share_plus.dart';

import '../../config/app_config.dart';
import '../../services/live_realtime.dart';
import '../../services/live_service.dart';
import '../../theme/app_colors.dart';
import 'live_ui.dart';

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
  final _commentCtrl = TextEditingController();
  bool _sendingComment = false;

  RtcEngine? _engine;
  Map<String, dynamic>? _agoraToken;
  bool _joined = false;
  bool _camChecking = true; // silently checking existing grant on entry
  bool _permDenied = false; // requested and refused
  bool _permPermanent = false; // denied for good → must open OS settings

  LiveRealtime? _rt;
  Timer? _poll;
  Timer? _tick;
  Duration _remaining = Duration.zero;
  bool _closing = false;
  bool _busy = false;
  double? _balance;

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
    _initIfGranted();
    _initRealtime();
    _loadBalance();
  }

  Future<void> _loadBalance() async {
    try {
      final b = await LiveService().balance();
      if (mounted) setState(() => _balance = b);
    } catch (_) {}
  }

  Future<void> _share() async {
    final web = AppConfig.apiBaseUrl.replaceAll('/api', '');
    await Share.share('$web/lives/${_live.id}', subject: _live.title);
  }

  void _showBalance() {
    final b = _balance;
    _snack(b == null ? 'Solde indisponible' : 'Solde du portefeuille : ${b.toStringAsFixed(0)} MAD', AppColors.primary);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _poll?.cancel();
    _tick?.cancel();
    _rt?.stop();
    _commentCtrl.dispose();
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

  /// On entry: only START the camera if permission is ALREADY granted — never
  /// prompt here. If it isn't granted, show a neutral call-to-action and let
  /// the seller trigger the request in-context (tap to enable / go live).
  Future<void> _initIfGranted() async {
    final cam = await Permission.camera.status;
    final mic = await Permission.microphone.status;
    if (cam.isGranted && mic.isGranted) {
      await _initAgoraEngine();
      if (mounted && _engine == null) setState(() => _camChecking = false); // agora unavailable
    } else if (mounted) {
      setState(() => _camChecking = false); // show the neutral "Activer la caméra" CTA
    }
  }

  /// Request camera + mic (in-context), then set up Agora. Called when the
  /// seller taps to enable the camera or "Passer en direct". On permanent
  /// denial it flags [_permPermanent] so the UI offers "open settings".
  Future<void> _requestPermsAndInit() async {
    if (mounted) setState(() => _camChecking = true);
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
          _camChecking = false;
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
    if (mounted && _engine == null) setState(() => _camChecking = false);
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
    // Camera not up yet → request permission in-context first. Bail if refused
    // (the video area already surfaces why + how to fix).
    if (_engine == null) {
      await _requestPermsAndInit();
      if (_engine == null) return;
    }
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

  Future<void> _sendComment() async {
    final text = _commentCtrl.text.trim();
    if (text.isEmpty) return;
    setState(() => _sendingComment = true);
    try {
      final c = await LiveService().comment(_live.id, text);
      if (mounted) setState(() { _comments = [..._comments, c]; _commentCtrl.clear(); });
    } on DioException catch (e) {
      if (mounted) _snack(e.response?.statusCode == 422 ? 'Le live n’est pas actif' : 'Envoi impossible', Colors.red);
    } finally {
      if (mounted) setState(() => _sendingComment = false);
    }
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
          // Right action rail (share + wallet)
          Positioned(
            right: 10,
            bottom: 260,
            child: SafeArea(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                LiveSideButton(icon: Icons.reply, label: 'Partager', onTap: _share),
                const SizedBox(height: 16),
                LiveSideButton(
                  icon: Icons.account_balance_wallet,
                  label: _balance != null ? _balance!.toStringAsFixed(0) : 'Solde',
                  onTap: _showBalance,
                ),
              ]),
            ),
          ),
          SafeArea(
            bottom: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 0),
              child: _topBar(),
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: EdgeInsets.only(left: 10, right: 76, bottom: 8 + MediaQuery.of(context).viewInsets.bottom),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.end,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Align(alignment: Alignment.centerLeft, child: LiveCommentsOverlay(comments: _comments)),
                  const SizedBox(height: 10),
                  _sellerPanel(),
                  const SizedBox(height: 8),
                  LiveCommentInput(
                    controller: _commentCtrl,
                    enabled: _live.isLive,
                    sending: _sendingComment,
                    onSend: _sendComment,
                  ),
                ],
              ),
            ),
          ),
          if (_live.isEnded)
            Positioned.fill(
              child: LiveEndedOverlay(
                username: _live.seller?.name ?? '',
                avatarUrl: _live.seller?.avatarUrl,
                likes: _live.likesCount,
                onBack: () => context.pop(),
              ),
            ),
        ],
      ),
    );
  }

  Widget _videoLayer() {
    if (_engine != null) {
      return AgoraVideoView(
        controller: VideoViewController(
          rtcEngine: _engine!,
          canvas: const VideoCanvas(uid: 0), // local camera
        ),
      );
    }
    return Container(
      color: Colors.black,
      alignment: Alignment.center,
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
          : _camChecking
              ? const CircularProgressIndicator(color: Colors.white70, strokeWidth: 2)
              : Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(Icons.videocam_outlined, color: Colors.white54, size: 34),
                    const SizedBox(height: 10),
                    const Text(
                      'Activez la caméra pour vérifier votre cadrage avant de diffuser.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.white70),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton.icon(
                      onPressed: _requestPermsAndInit,
                      icon: const Icon(Icons.videocam),
                      label: const Text('Activer la caméra'),
                    ),
                  ]),
                ),
    );
  }

  Widget _topBar() {
    return Row(children: [
      LiveGlassButton(
        // × ends the live when on air, otherwise just leaves the console.
        onTap: () => _live.isLive ? _endLive() : context.pop(),
        child: const Icon(Icons.close, color: Colors.white, size: 20),
      ),
      const SizedBox(width: 8),
      LiveSellerChip(
        name: _live.seller?.name ?? 'Vous',
        avatarUrl: _live.seller?.avatarUrl,
        status: _live.status,
      ),
      const Spacer(),
      LiveGlassButton(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.favorite, size: 16, color: AppColors.primary),
          const SizedBox(width: 6),
          Text('${_live.likesCount}', style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
        ]),
      ),
    ]);
  }

  Widget _sellerPanel() {
    if (_live.isScheduled) {
      return SizedBox(
        width: double.infinity,
        height: 52,
        child: ElevatedButton.icon(
          onPressed: _busy ? null : _goLive,
          icon: const Icon(Icons.videocam),
          label: _busy
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Passer en direct'),
        ),
      );
    }
    if (!_live.isLive) return const SizedBox.shrink();

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
        Row(children: [
          Expanded(
            child: OutlinedButton(
              onPressed: _closing ? null : () => _closeAuction(),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: const BorderSide(color: Colors.white54),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: const Text('Clôturer l’enchère'),
            ),
          ),
          const SizedBox(width: 8),
          _shopButton(),
        ]),
      ]);
    }

    // Live, no active auction → prompt to put an item up.
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton.icon(
        onPressed: _busy ? null : _openProductSheet,
        icon: const Icon(Icons.storefront_outlined),
        label: const Text('Mettre un article aux enchères'),
      ),
    );
  }

  Widget _shopButton() {
    return SizedBox(
      height: 48,
      child: ElevatedButton(
        onPressed: _busy ? null : _openProductSheet,
        style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 16)),
        child: const Icon(Icons.storefront_outlined),
      ),
    );
  }

  void _openProductSheet() {
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: const Color(0xFF121212),
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 16),
            child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
              Center(
                child: Container(width: 40, height: 4, margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2))),
              ),
              const Text('Choisir un article à mettre aux enchères',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
              const SizedBox(height: 12),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _live.products.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) => _sheetProductTile(ctx, _live.products[i]),
                ),
              ),
            ]),
          ),
        );
      },
    );
  }

  Widget _sheetProductTile(BuildContext sheetCtx, ApiLiveProduct p) {
    final isCurrent = _live.auctionActive && _live.currentProduct?.id == p.id;
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.06), borderRadius: BorderRadius.circular(12)),
      child: Row(children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: p.image != null
              ? SizedBox(width: 44, height: 44, child: _NetImg(url: p.image!))
              : Container(width: 44, height: 44, color: Colors.white12, child: const Icon(Icons.image_outlined, color: Colors.white54)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(p.title, maxLines: 1, overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
            Text('Pré-enchère dès ${p.preBidMin.toStringAsFixed(0)} MAD',
                style: const TextStyle(color: Colors.white54, fontSize: 12)),
          ]),
        ),
        if (isCurrent)
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 8),
            child: Text('En cours', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 12)),
          )
        else
          ElevatedButton(
            onPressed: _busy
                ? null
                : () {
                    Navigator.pop(sheetCtx);
                    _startAuction(p);
                  },
            style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8)),
            child: const Text('Lancer'),
          ),
      ]),
    );
  }
}

/// Small cached network image used inside the product sheet.
class _NetImg extends StatelessWidget {
  final String url;
  const _NetImg({required this.url});
  @override
  Widget build(BuildContext context) => Image.network(url, fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(color: Colors.white12, child: const Icon(Icons.image_outlined, color: Colors.white54)));
}
