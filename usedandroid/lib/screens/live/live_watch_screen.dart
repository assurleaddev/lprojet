import 'dart:async';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../services/live_service.dart';
import '../../theme/app_colors.dart';

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

  // Realtime via polling (until push websockets are wired)
  Timer? _pollTimer;
  Timer? _tick;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _live = widget.live;
    _load();
    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (mounted && (_live.isLive || _live.isScheduled)) _load();
    });
    _tick = Timer.periodic(const Duration(seconds: 1), (_) => _updateCountdown());
    if (_live.isLive) _initAgora();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _tick?.cancel();
    _commentCtrl.dispose();
    _disposeAgora();
    super.dispose();
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
    setState(() => _live = _copyWithLikes(_live.likesCount + 1));
    try {
      await LiveService().like(_live.id);
    } catch (_) {}
  }

  ApiLive _copyWithLikes(int likes) => ApiLive(
        id: _live.id, title: _live.title, status: _live.status, auctionStatus: _live.auctionStatus,
        thumbnailUrl: _live.thumbnailUrl, likesCount: likes, startingBid: _live.startingBid,
        currentBid: _live.currentBid, minNextBid: _live.minNextBid, countdownEndsAt: _live.countdownEndsAt,
        agoraChannel: _live.agoraChannel, seller: _live.seller, currentProduct: _live.currentProduct,
        currentBidderName: _live.currentBidderName, products: _live.products,
      );

  void _snack(String msg, Color color) => ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: color),
      );

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
                child: RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      if (_live.isEnded)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 8),
                          child: Text('Ce live est terminé.', style: TextStyle(color: AppColors.textSecondary)),
                        ),
                      if (_live.auctionActive && _live.currentProduct != null) _auctionCard(),
                      const SizedBox(height: 12),
                      const Text('Commentaires', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      if (_comments.isEmpty)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 12),
                          child: Text('Soyez le premier à commenter', style: TextStyle(color: AppColors.textSecondary)),
                        )
                      else
                        ..._comments.map(_commentTile),
                    ],
                  ),
                ),
              ),
            ),
            _commentBar(),
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
          // Live video if the seller is publishing; otherwise the thumbnail.
          if (_engine != null && _remoteUid != null && _channel != null)
            AgoraVideoView(
              controller: VideoViewController.remote(
                rtcEngine: _engine!,
                canvas: VideoCanvas(uid: _remoteUid),
                connection: RtcConnection(channelId: _channel),
              ),
            )
          else ...[
            if (_live.thumbnailUrl != null)
              CachedNetworkImage(imageUrl: _live.thumbnailUrl!, fit: BoxFit.cover, errorWidget: (_, __, ___) => Container(color: Colors.black))
            else
              Container(color: Colors.black),
            Container(color: Colors.black.withValues(alpha: 0.35)),
            Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                if (_live.isLive) ...[
                  const SizedBox(height: 4),
                  const CircularProgressIndicator(color: Colors.white70, strokeWidth: 2),
                  const SizedBox(height: 10),
                  const Text('Connexion au direct…', style: TextStyle(color: Colors.white70, fontSize: 12)),
                ] else if (_live.isScheduled)
                  const Text('Ce live n\'a pas encore commencé', style: TextStyle(color: Colors.white70, fontSize: 12))
                else
                  const Text('Live terminé', style: TextStyle(color: Colors.white70, fontSize: 12)),
              ]),
            ),
          ],
          // top bar
          Positioned(
            top: 8, left: 4, right: 8,
            child: Row(children: [
              IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.of(context).maybePop()),
              if (_live.isLive) _pill('EN DIRECT', AppColors.primary),
              const Spacer(),
              GestureDetector(
                onTap: _live.isEnded ? null : _like,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(color: Colors.black45, borderRadius: BorderRadius.circular(20)),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(Icons.favorite, size: 15, color: Colors.white),
                    const SizedBox(width: 5),
                    Text('${_live.likesCount}', style: const TextStyle(color: Colors.white, fontSize: 12)),
                  ]),
                ),
              ),
            ]),
          ),
          // seller + title
          Positioned(
            left: 12, right: 12, bottom: 10,
            child: Row(children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: Colors.white24,
                backgroundImage: _live.seller?.avatarUrl != null ? CachedNetworkImageProvider(_live.seller!.avatarUrl!) : null,
                child: _live.seller?.avatarUrl == null ? const Icon(Icons.person, size: 15, color: Colors.white) : null,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
                  Text(_live.seller?.name ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
                  Text(_live.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white70, fontSize: 11)),
                ]),
              ),
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

  Widget _auctionCard() {
    final p = _live.currentProduct!;
    final hasBid = _live.currentBid != null;
    final ending = _remaining.inSeconds > 0;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: AppColors.inputFill, borderRadius: BorderRadius.circular(14)),
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
                Text(p.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
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
            child: ElevatedButton(
              onPressed: _bidding ? null : _placeBid,
              child: _bidding
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : Text('Enchérir ${_live.minNextBid.toStringAsFixed(0)} MAD'),
            ),
          ),
        ],
      ),
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

  Widget _commentBar() {
    final canComment = _live.isLive;
    return Container(
      padding: EdgeInsets.only(left: 12, right: 8, top: 8, bottom: MediaQuery.of(context).padding.bottom + 8),
      color: AppColors.surface,
      child: Row(children: [
        Expanded(
          child: TextField(
            controller: _commentCtrl,
            enabled: canComment,
            decoration: InputDecoration(
              hintText: canComment ? 'Ajouter un commentaire…' : 'Live inactif',
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            ),
            onSubmitted: (_) => _sendComment(),
          ),
        ),
        IconButton(
          icon: _sending
              ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2))
              : const Icon(Icons.send, color: AppColors.primary),
          onPressed: canComment && !_sending ? _sendComment : null,
        ),
      ]),
    );
  }
}
