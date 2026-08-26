import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/live_service.dart';
import '../../theme/app_colors.dart';

class LivesScreen extends StatefulWidget {
  const LivesScreen({super.key});

  @override
  State<LivesScreen> createState() => _LivesScreenState();
}

class _LivesScreenState extends State<LivesScreen> {
  List<ApiLive> _lives = [];
  bool _loading = true;
  bool _needLogin = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _needLogin = false;
    });
    try {
      final lives = await LiveService().getLives();
      if (mounted) setState(() { _lives = lives; _loading = false; });
    } on DioException catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          if (e.response?.statusCode == 401) {
            _needLogin = true;
          } else {
            _error = 'Erreur de chargement';
          }
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _error = 'Erreur de chargement'; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ventes en direct')),
      body: RefreshIndicator(onRefresh: _load, child: _body()),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_needLogin) {
      return _centered(
        Icons.lock_outline,
        'Connectez-vous pour voir les ventes en direct',
        ElevatedButton(onPressed: () async { await context.push('/login'); _load(); }, child: const Text('Se connecter')),
      );
    }
    if (_error != null) {
      return _centered(Icons.wifi_off, _error!, TextButton(onPressed: _load, child: const Text('Réessayer')));
    }
    if (_lives.isEmpty) {
      return ListView(children: [
        const SizedBox(height: 120),
        _centered(Icons.live_tv_outlined, 'Aucune vente en direct pour le moment', null),
      ]);
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _lives.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, i) => _LiveCard(
        live: _lives[i],
        onTap: () => context.push('/lives/watch/${_lives[i].id}', extra: _lives[i]),
      ),
    );
  }

  Widget _centered(IconData icon, String msg, Widget? action) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 48, color: AppColors.textSecondary),
          const SizedBox(height: 10),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(msg, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.textSecondary)),
          ),
          if (action != null) ...[const SizedBox(height: 8), action],
        ],
      ),
    );
  }
}

class _LiveCard extends StatelessWidget {
  final ApiLive live;
  final VoidCallback onTap;

  const _LiveCard({required this.live, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: AspectRatio(
              aspectRatio: 16 / 10,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (live.thumbnailUrl != null)
                    CachedNetworkImage(
                      imageUrl: live.thumbnailUrl!,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: AppColors.inputFill),
                      errorWidget: (_, __, ___) => Container(color: AppColors.inputFill, child: const Icon(Icons.live_tv_outlined, color: AppColors.textSecondary)),
                    )
                  else
                    Container(color: AppColors.inputFill, child: const Icon(Icons.live_tv_outlined, color: AppColors.textSecondary)),
                  // status badge
                  Positioned(top: 10, left: 10, child: _StatusPill(status: live.status)),
                  // likes
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(color: Colors.black45, borderRadius: BorderRadius.circular(20)),
                      child: Row(mainAxisSize: MainAxisSize.min, children: [
                        const Icon(Icons.favorite, size: 13, color: Colors.white),
                        const SizedBox(width: 4),
                        Text('${live.likesCount}', style: const TextStyle(color: Colors.white, fontSize: 12)),
                      ]),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(live.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
          const SizedBox(height: 4),
          Row(children: [
            CircleAvatar(
              radius: 11,
              backgroundColor: AppColors.inputFill,
              backgroundImage: live.seller?.avatarUrl != null ? CachedNetworkImageProvider(live.seller!.avatarUrl!) : null,
              child: live.seller?.avatarUrl == null ? const Icon(Icons.person, size: 13, color: AppColors.textSecondary) : null,
            ),
            const SizedBox(width: 8),
            Expanded(child: Text(live.seller?.name ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
          ]),
        ],
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  final String status;
  const _StatusPill({required this.status});

  @override
  Widget build(BuildContext context) {
    if (status == 'live') {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(6)),
        child: Row(mainAxisSize: MainAxisSize.min, children: const [
          Icon(Icons.circle, size: 8, color: Colors.white),
          SizedBox(width: 5),
          Text('EN DIRECT', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
        ]),
      );
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(6)),
      child: const Text('PROGRAMMÉ', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
    );
  }
}
