import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/api_client.dart';
import '../../services/product_service.dart';
import '../../services/profile_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';
import '../../widgets/rating_stars.dart';

/// Another member's public profile: rating, followers, follow button and
/// their items for sale (reached from a product page, a chat or the search).
class MemberProfileScreen extends StatefulWidget {
  final int userId;

  const MemberProfileScreen({super.key, required this.userId});

  @override
  State<MemberProfileScreen> createState() => _MemberProfileScreenState();
}

class _MemberProfileScreenState extends State<MemberProfileScreen> {
  final _profileService = ProfileService();
  ApiMember? _member;
  List<ApiProduct> _products = [];
  bool _loading = true;
  bool _failed = false;
  bool _following = false;
  bool _followBusy = false;
  int _followers = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _failed = false; });
    try {
      final results = await Future.wait([
        _profileService.getMember(widget.userId),
        ProductService().getVendorProducts(widget.userId),
      ]);
      final member = results[0] as ApiMember;
      if (!mounted) return;
      setState(() {
        _member = member;
        _products = results[1] as List<ApiProduct>;
        _following = member.isFollowing;
        _followers = member.followersCount;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() { _loading = false; _failed = true; });
    }
  }

  Future<void> _toggleFollow() async {
    if (!await ApiClient.isLoggedIn) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connectez-vous pour suivre ce membre.')),
      );
      context.push('/login');
      return;
    }
    setState(() => _followBusy = true);
    try {
      final r = await _profileService.toggleFollow(widget.userId);
      if (mounted) setState(() { _following = r.following; _followers = r.followersCount; });
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Action impossible';
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
    } finally {
      if (mounted) setState(() => _followBusy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final member = _member;
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: Text(member?.username ?? 'Profil')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : (_failed || member == null)
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.person_off_outlined, size: 48, color: AppColors.textSecondary),
                      const SizedBox(height: 12),
                      const Text('Profil introuvable', style: TextStyle(color: AppColors.textSecondary)),
                      TextButton(onPressed: _load, child: const Text('Réessayer')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: CustomScrollView(
                    slivers: [
                      SliverToBoxAdapter(child: _header(member)),
                      SliverToBoxAdapter(
                        child: Padding(
                          padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
                          child: Text(
                            '${_products.length} article${_products.length > 1 ? 's' : ''}',
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                      if (_products.isEmpty)
                        const SliverToBoxAdapter(
                          child: Padding(
                            padding: EdgeInsets.symmetric(vertical: 40),
                            child: Center(
                              child: Text('Aucun article en vente',
                                  style: TextStyle(color: AppColors.textSecondary)),
                            ),
                          ),
                        )
                      else
                        SliverPadding(
                          padding: const EdgeInsets.all(12),
                          sliver: SliverGrid(
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              crossAxisSpacing: 8,
                              mainAxisSpacing: 8,
                              childAspectRatio: 0.62,
                            ),
                            delegate: SliverChildBuilderDelegate(
                              (context, i) => ApiItemCard(
                                product: _products[i],
                                onTap: () => context.push('/listing/${_products[i].id}', extra: _products[i]),
                              ),
                              childCount: _products.length,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }

  Widget _header(ApiMember member) {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 36,
                backgroundColor: AppColors.inputFill,
                backgroundImage:
                    member.avatarUrl != null ? CachedNetworkImageProvider(member.avatarUrl!) : null,
                child: member.avatarUrl == null
                    ? Text(
                        member.displayName.isNotEmpty ? member.displayName[0].toUpperCase() : '?',
                        style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w700, color: AppColors.primary),
                      )
                    : null,
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(member.displayName, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    RatingStars(average: member.ratingAvg, count: member.ratingCount, size: 16),
                    const SizedBox(height: 4),
                    Text(
                      [
                        if (member.city != null && member.city!.isNotEmpty) member.city!,
                        if (member.memberSince != null) 'Membre depuis ${member.memberSince}',
                      ].join(' · '),
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              _Stat(value: '${member.productsCount}', label: 'Articles'),
              _Stat(value: '$_followers', label: 'Abonnés'),
              _Stat(value: '${member.followingCount}', label: 'Abonnements'),
            ],
          ),
          if (!member.isMe) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: _following
                  ? OutlinedButton(
                      onPressed: _followBusy ? null : _toggleFollow,
                      child: const Text('Abonné'),
                    )
                  : ElevatedButton(
                      onPressed: _followBusy ? null : _toggleFollow,
                      child: const Text("S'abonner"),
                    ),
            ),
          ],
          if (member.about != null && member.about!.isNotEmpty) ...[
            const SizedBox(height: 16),
            Text(member.about!, style: const TextStyle(color: AppColors.textSecondary, height: 1.4)),
          ],
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final String value;
  final String label;

  const _Stat({required this.value, required this.label});

  @override
  Widget build(BuildContext context) => Expanded(
        child: Column(
          children: [
            Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          ],
        ),
      );
}
