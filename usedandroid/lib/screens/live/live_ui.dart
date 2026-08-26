import 'dart:math' as math;

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../../services/live_service.dart';
import '../../theme/app_colors.dart';

/// Shared presentational pieces for the full-bleed, web-matching live layout
/// (used by both the viewer watch screen and the seller broadcast console).

/// Legibility scrim: darkens the top and bottom of the video so overlaid
/// chrome/text stays readable. Non-interactive.
class LiveScrim extends StatelessWidget {
  const LiveScrim({super.key});

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0x99000000), Color(0x00000000), Color(0x00000000), Color(0xB3000000)],
            stops: [0.0, 0.22, 0.55, 1.0],
          ),
        ),
      ),
    );
  }
}

/// LIVE / PROGRAMMÉ / TERMINÉ pill.
class LiveStatusBadge extends StatelessWidget {
  final String status; // scheduled | live | ended
  const LiveStatusBadge({super.key, required this.status});

  @override
  Widget build(BuildContext context) {
    if (status == 'live') {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(6)),
        child: const Row(mainAxisSize: MainAxisSize.min, children: [
          Icon(Icons.circle, size: 7, color: Colors.white),
          SizedBox(width: 5),
          Text('EN DIRECT', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
        ]),
      );
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: Colors.black.withValues(alpha: 0.45), borderRadius: BorderRadius.circular(6)),
      child: Text(
        status == 'ended' ? 'TERMINÉ' : 'PROGRAMMÉ',
        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
      ),
    );
  }
}

/// Seller identity chip for the top bar: avatar + username + status badge.
class LiveSellerChip extends StatelessWidget {
  final String name;
  final String? avatarUrl;
  final String status;
  const LiveSellerChip({super.key, required this.name, required this.status, this.avatarUrl});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(4, 4, 10, 4),
      decoration: BoxDecoration(color: Colors.black.withValues(alpha: 0.3), borderRadius: BorderRadius.circular(24)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        CircleAvatar(
          radius: 15,
          backgroundColor: Colors.white24,
          backgroundImage: avatarUrl != null ? CachedNetworkImageProvider(avatarUrl!) : null,
          child: avatarUrl == null ? const Icon(Icons.person, size: 16, color: Colors.white) : null,
        ),
        const SizedBox(width: 8),
        Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 120),
            child: Text(name, maxLines: 1, overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13)),
          ),
          const SizedBox(height: 2),
          LiveStatusBadge(status: status),
        ]),
      ]),
    );
  }
}

/// Round translucent chrome button used in the top bar (back/close/end/etc.).
class LiveGlassButton extends StatelessWidget {
  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsets padding;
  const LiveGlassButton({super.key, required this.child, this.onTap, this.padding = const EdgeInsets.all(8)});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.black.withValues(alpha: 0.35),
      shape: const StadiumBorder(),
      child: InkWell(
        customBorder: const StadiumBorder(),
        onTap: onTap,
        child: Padding(padding: padding, child: child),
      ),
    );
  }
}

/// Comments overlaid over the video, newest at the bottom, fading out at the top.
class LiveCommentsOverlay extends StatelessWidget {
  final List<ApiLiveComment> comments;
  const LiveCommentsOverlay({super.key, required this.comments});

  Color _avatarColor(String username) =>
      Color(0xFF000000 | (username.hashCode & 0x00FFFFFF)).withValues(alpha: 1);

  @override
  Widget build(BuildContext context) {
    if (comments.isEmpty) return const SizedBox.shrink();
    final shown = comments.length > 6 ? comments.sublist(comments.length - 6) : comments;
    return ConstrainedBox(
      constraints: const BoxConstraints(maxHeight: 220),
      child: ShaderMask(
        shaderCallback: (rect) => const LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Colors.transparent, Colors.black, Colors.black],
          stops: [0.0, 0.25, 1.0],
        ).createShader(rect),
        blendMode: BlendMode.dstIn,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.end,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: shown.map((c) => _tile(c)).toList(),
        ),
      ),
    );
  }

  Widget _tile(ApiLiveComment c) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
        CircleAvatar(
          radius: 11,
          backgroundColor: c.avatarUrl != null ? Colors.white24 : _avatarColor(c.username),
          backgroundImage: c.avatarUrl != null ? CachedNetworkImageProvider(c.avatarUrl!) : null,
          child: c.avatarUrl == null
              ? Text(c.username.isNotEmpty ? c.username[0].toUpperCase() : '?',
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700))
              : null,
        ),
        const SizedBox(width: 8),
        Flexible(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(color: Colors.black.withValues(alpha: 0.3), borderRadius: BorderRadius.circular(14)),
            child: RichText(
              text: TextSpan(style: const TextStyle(fontSize: 13, height: 1.25), children: [
                TextSpan(text: '${c.username}  ',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                TextSpan(text: c.content, style: const TextStyle(color: Color(0xFFEFEFEF))),
              ]),
            ),
          ),
        ),
      ]),
    );
  }
}

/// Horizontal product card shown in the bottom auction panel.
class LiveProductCardH extends StatelessWidget {
  final String title;
  final String? image;
  final double amount;
  final bool hasBid;
  final String? bidderName;
  final Widget? trailing; // e.g. seller countdown pill
  const LiveProductCardH({
    super.key,
    required this.title,
    required this.amount,
    required this.hasBid,
    this.image,
    this.bidderName,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.45),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
      ),
      child: Row(children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: image != null
              ? CachedNetworkImage(imageUrl: image!, width: 52, height: 52, fit: BoxFit.cover)
              : Container(width: 52, height: 52, color: Colors.white12, child: const Icon(Icons.image_outlined, color: Colors.white54)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Text(title, maxLines: 1, overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 14)),
            const SizedBox(height: 2),
            Text(hasBid ? 'Enchère actuelle' : 'Enchère de départ',
                style: const TextStyle(color: Colors.white60, fontSize: 11)),
            Text('${amount.toStringAsFixed(0)} MAD',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 17)),
            if (hasBid && bidderName != null)
              Text('par $bidderName', style: const TextStyle(color: Colors.white54, fontSize: 11)),
          ]),
        ),
        if (trailing != null) ...[const SizedBox(width: 8), trailing!],
      ]),
    );
  }
}

/// "Awaiting next item" placeholder for the auction panel between lots.
class LiveAwaitingRow extends StatelessWidget {
  const LiveAwaitingRow({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 12),
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.35),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
      ),
      child: const Text('En attente du prochain article',
          style: TextStyle(color: Colors.white70, fontWeight: FontWeight.w600, fontSize: 13)),
    );
  }
}

/// Comment input row overlaid at the very bottom.
class LiveCommentInput extends StatelessWidget {
  final TextEditingController controller;
  final bool enabled;
  final bool sending;
  final VoidCallback onSend;
  const LiveCommentInput({
    super.key,
    required this.controller,
    required this.onSend,
    this.enabled = true,
    this.sending = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Expanded(
        child: Container(
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: 0.4),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
          ),
          child: TextField(
            controller: controller,
            enabled: enabled,
            style: const TextStyle(color: Colors.white, fontSize: 14),
            textInputAction: TextInputAction.send,
            onSubmitted: (_) => onSend(),
            decoration: InputDecoration(
              hintText: enabled ? 'Dites quelque chose…' : 'Live inactif',
              hintStyle: const TextStyle(color: Colors.white54, fontSize: 14),
              border: InputBorder.none,
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            ),
          ),
        ),
      ),
      const SizedBox(width: 8),
      Material(
        color: AppColors.primary,
        shape: const CircleBorder(),
        child: InkWell(
          customBorder: const CircleBorder(),
          onTap: enabled && !sending ? onSend : null,
          child: Padding(
            padding: const EdgeInsets.all(11),
            child: sending
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Icon(Icons.send, color: Colors.white, size: 18),
          ),
        ),
      ),
    ]);
  }
}

/// Slide-to-confirm bid control (viewer). Fires [onConfirm] once the thumb is
/// dragged to the end, then snaps back.
class SlideToBid extends StatefulWidget {
  final String label;
  final bool enabled;
  final Future<void> Function() onConfirm;
  const SlideToBid({super.key, required this.label, required this.onConfirm, this.enabled = true});

  @override
  State<SlideToBid> createState() => _SlideToBidState();
}

class _SlideToBidState extends State<SlideToBid> {
  double _dx = 0;
  bool _firing = false;

  @override
  Widget build(BuildContext context) {
    const thumb = 48.0;
    const height = 56.0;
    return LayoutBuilder(builder: (ctx, c) {
      final maxDx = (c.maxWidth - thumb - 8).clamp(0.0, double.infinity);
      final active = widget.enabled && !_firing;
      return Container(
        height: height,
        decoration: BoxDecoration(
          color: active ? AppColors.primary : AppColors.primary.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(28),
        ),
        child: Stack(children: [
          Center(
            child: Padding(
              padding: const EdgeInsets.only(left: 40),
              child: Text(
                _firing ? 'Enchère en cours…' : widget.label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15),
              ),
            ),
          ),
          Positioned(
            left: 4 + _dx,
            top: 4,
            child: GestureDetector(
              onHorizontalDragUpdate: active
                  ? (d) => setState(() => _dx = (_dx + d.delta.dx).clamp(0.0, maxDx))
                  : null,
              onHorizontalDragEnd: active
                  ? (_) async {
                      if (_dx >= maxDx * 0.9) {
                        setState(() => _firing = true);
                        try {
                          await widget.onConfirm();
                        } finally {
                          if (mounted) {
                            setState(() {
                              _dx = 0;
                              _firing = false;
                            });
                          }
                        }
                      } else {
                        setState(() => _dx = 0);
                      }
                    }
                  : null,
              child: Container(
                width: thumb,
                height: thumb,
                decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                child: _firing
                    ? const Padding(padding: EdgeInsets.all(14), child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary))
                    : const Icon(Icons.chevron_right, color: AppColors.primary, size: 26),
              ),
            ),
          ),
        ]),
      );
    });
  }
}

/// A vertical action-rail button (icon bubble + label + optional badge),
/// matching the web's right sidebar.
class LiveSideButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback? onTap;
  final String? badge;
  final Color iconColor;
  const LiveSideButton({
    super.key,
    required this.icon,
    required this.label,
    this.onTap,
    this.badge,
    this.iconColor = Colors.white,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Stack(clipBehavior: Clip.none, children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(color: Colors.black.withValues(alpha: 0.35), shape: BoxShape.circle),
            child: Icon(icon, color: iconColor, size: 24),
          ),
          if (badge != null)
            Positioned(
              right: -2,
              top: -2,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                decoration: BoxDecoration(color: AppColors.greenBadge, borderRadius: BorderRadius.circular(10)),
                constraints: const BoxConstraints(minWidth: 18),
                child: Text(badge!, textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
              ),
            ),
        ]),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w600)),
      ]),
    );
  }
}

/// Small follow / following pill for the top bar.
class LiveFollowButton extends StatelessWidget {
  final bool following;
  final VoidCallback? onTap;
  const LiveFollowButton({super.key, required this.following, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: following ? Colors.black.withValues(alpha: 0.35) : AppColors.primary,
          borderRadius: BorderRadius.circular(20),
          border: following ? Border.all(color: Colors.white54) : null,
        ),
        child: Text(following ? 'Suivi' : 'Suivre',
            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
      ),
    );
  }
}

/// Emits floating hearts (call [FloatingHeartsState.add] via a GlobalKey).
class FloatingHearts extends StatefulWidget {
  const FloatingHearts({super.key});
  @override
  State<FloatingHearts> createState() => FloatingHeartsState();
}

class _HeartAnim {
  final AnimationController ctrl;
  final double startX; // 0..1
  final double scale;
  final double drift;
  _HeartAnim(this.ctrl, this.startX, this.scale, this.drift);
}

class FloatingHeartsState extends State<FloatingHearts> with TickerProviderStateMixin {
  final _rand = math.Random();
  final List<_HeartAnim> _hearts = [];

  void add() {
    final ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1700));
    final h = _HeartAnim(ctrl, _rand.nextDouble(), 0.7 + _rand.nextDouble() * 0.5, (_rand.nextDouble() - 0.5) * 36);
    ctrl.addStatusListener((s) {
      if (s == AnimationStatus.completed && mounted) {
        setState(() => _hearts.remove(h));
        ctrl.dispose();
      }
    });
    setState(() => _hearts.add(h));
    ctrl.forward();
  }

  @override
  void dispose() {
    for (final h in _hearts) {
      h.ctrl.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Stack(clipBehavior: Clip.none, children: _hearts.map((h) {
        return AnimatedBuilder(
          animation: h.ctrl,
          builder: (_, __) {
            final t = h.ctrl.value;
            final opacity = (t < 0.15 ? t / 0.15 : (1 - (t - 0.15) / 0.85)).clamp(0.0, 1.0);
            return Positioned(
              right: 6 + h.startX * 14 + h.drift * math.sin(t * math.pi * 2),
              bottom: 10 + t * 240,
              child: Opacity(
                opacity: opacity,
                child: Transform.scale(
                  scale: h.scale,
                  child: const Icon(Icons.favorite, color: AppColors.primary, size: 26),
                ),
              ),
            );
          },
        );
      }).toList()),
    );
  }
}

/// Full-screen "LIVE terminé" overlay shown when a live has ended.
class LiveEndedOverlay extends StatelessWidget {
  final String username;
  final String? avatarUrl;
  final int likes;
  final bool showFollow;
  final bool isFollowing;
  final VoidCallback? onFollow;
  final VoidCallback onBack;
  const LiveEndedOverlay({
    super.key,
    required this.username,
    required this.likes,
    required this.onBack,
    this.avatarUrl,
    this.showFollow = false,
    this.isFollowing = false,
    this.onFollow,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.black.withValues(alpha: 0.85),
      alignment: Alignment.center,
      padding: const EdgeInsets.symmetric(horizontal: 32),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Container(
          padding: const EdgeInsets.all(3),
          decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: AppColors.primary, width: 2)),
          child: CircleAvatar(
            radius: 40,
            backgroundColor: Colors.white12,
            backgroundImage: avatarUrl != null ? CachedNetworkImageProvider(avatarUrl!) : null,
            child: avatarUrl == null ? const Icon(Icons.person, size: 40, color: Colors.white54) : null,
          ),
        ),
        const SizedBox(height: 16),
        const Text('LIVE terminé', style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800)),
        const SizedBox(height: 6),
        Text('@$username', style: const TextStyle(color: Colors.white70, fontSize: 14)),
        const SizedBox(height: 18),
        Column(children: [
          Text('$likes', style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800)),
          const Text('J’aime', style: TextStyle(color: Colors.white54, fontSize: 12)),
        ]),
        const SizedBox(height: 22),
        if (showFollow)
          SizedBox(
            width: 200,
            child: ElevatedButton(
              onPressed: onFollow,
              style: ElevatedButton.styleFrom(
                backgroundColor: isFollowing ? Colors.white24 : AppColors.primary,
                foregroundColor: Colors.white,
              ),
              child: Text(isFollowing ? 'Suivi' : 'Suivre'),
            ),
          ),
        const SizedBox(height: 10),
        SizedBox(
          width: 200,
          child: OutlinedButton(
            onPressed: onBack,
            style: OutlinedButton.styleFrom(foregroundColor: Colors.white, side: const BorderSide(color: Colors.white38)),
            child: const Text('Retour'),
          ),
        ),
        const SizedBox(height: 16),
        const Text('Merci d’avoir regardé', style: TextStyle(color: Colors.white38, fontSize: 12)),
      ]),
    );
  }
}
