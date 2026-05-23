import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';

class ListingDetailScreen extends StatefulWidget {
  final ApiProduct product;

  const ListingDetailScreen({super.key, required this.product});

  @override
  State<ListingDetailScreen> createState() => _ListingDetailScreenState();
}

class _ListingDetailScreenState extends State<ListingDetailScreen> {
  final _pageController = PageController();
  late final List<String> _images;

  @override
  void initState() {
    super.initState();
    _images = widget.product.images.isNotEmpty
        ? widget.product.images
        : (widget.product.featuredImage != null ? [widget.product.featuredImage!] : []);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final product = widget.product;

    return Scaffold(
      backgroundColor: AppColors.surface,
      body: CustomScrollView(
        slivers: [
          _ImageSliver(images: _images, pageController: _pageController),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${product.price.toStringAsFixed(0)} DZD',
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      _FavoriteButton(initial: product.isFavorited),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    product.title,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                  ),
                  if (product.brand != null) ...[
                    const SizedBox(height: 4),
                    Text(product.brand!, style: const TextStyle(fontSize: 14, color: AppColors.textSecondary)),
                  ],
                  const SizedBox(height: 16),
                  if (product.condition.isNotEmpty)
                    _DetailRow(label: 'État', value: _conditionLabel(product.condition)),
                  if (product.size != null)
                    _DetailRow(label: 'Taille', value: product.size!),
                  if (product.description != null && product.description!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    const Text('Description', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 6),
                    Text(product.description!, style: const TextStyle(color: AppColors.textSecondary, height: 1.5)),
                  ],
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 16),
                  _SellerCard(vendor: product.vendor),
                  const SizedBox(height: 100),
                ],
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: _BuyBar(price: product.price),
    );
  }

  String _conditionLabel(String condition) => switch (condition) {
        'new_with_tags' => 'Neuf avec étiquette',
        'new_without_tags' => 'Neuf sans étiquette',
        'very_good' => 'Très bon état',
        'good' => 'Bon état',
        'satisfactory' => 'État satisfaisant',
        'heavily_worn' => 'Très usé',
        _ => condition,
      };
}

class _ImageSliver extends StatefulWidget {
  final List<String> images;
  final PageController pageController;

  const _ImageSliver({required this.images, required this.pageController});

  @override
  State<_ImageSliver> createState() => _ImageSliverState();
}

class _ImageSliverState extends State<_ImageSliver> {
  @override
  Widget build(BuildContext context) {
    return SliverAppBar(
      expandedHeight: 420,
      pinned: true,
      backgroundColor: AppColors.surface,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () => Navigator.of(context).pop(),
      ),
      actions: [
        IconButton(icon: const Icon(Icons.share_outlined), onPressed: () {}),
      ],
      flexibleSpace: FlexibleSpaceBar(
        background: widget.images.isEmpty
            ? Container(color: AppColors.inputFill, child: const Icon(Icons.image_not_supported, size: 64, color: AppColors.textSecondary))
            : Stack(
                children: [
                  PageView.builder(
                    controller: widget.pageController,
                    itemCount: widget.images.length,
                    onPageChanged: (_) => setState(() {}),
                    itemBuilder: (_, index) => CachedNetworkImage(
                      imageUrl: widget.images[index],
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: AppColors.inputFill),
                      errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                    ),
                  ),
                  if (widget.images.length > 1)
                    Positioned(
                      bottom: 16,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: SmoothPageIndicator(
                          controller: widget.pageController,
                          count: widget.images.length,
                          effect: const ExpandingDotsEffect(
                            dotHeight: 6,
                            dotWidth: 6,
                            activeDotColor: AppColors.primary,
                            dotColor: Colors.white54,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 14)),
          ),
          Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

class _SellerCard extends StatelessWidget {
  final Map<String, dynamic>? vendor;

  const _SellerCard({this.vendor});

  @override
  Widget build(BuildContext context) {
    final name = vendor?['name'] ?? 'Vendeur';
    final since = vendor?['member_since']?.toString();
    final avatarUrl = vendor?['avatar_url'] as String?;

    return Row(
      children: [
        CircleAvatar(
          radius: 22,
          backgroundColor: AppColors.inputFill,
          backgroundImage: avatarUrl != null ? CachedNetworkImageProvider(avatarUrl) : null,
          child: avatarUrl == null ? const Icon(Icons.person, color: AppColors.textSecondary) : null,
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              if (since != null)
                Text('Membre depuis $since', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
            ],
          ),
        ),
        OutlinedButton(
          onPressed: () {},
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: AppColors.primary),
            foregroundColor: AppColors.primary,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
          child: const Text('Voir profil'),
        ),
      ],
    );
  }
}

class _FavoriteButton extends StatefulWidget {
  final bool initial;
  const _FavoriteButton({this.initial = false});

  @override
  State<_FavoriteButton> createState() => _FavoriteButtonState();
}

class _FavoriteButtonState extends State<_FavoriteButton> {
  late bool _favorited;

  @override
  void initState() {
    super.initState();
    _favorited = widget.initial;
  }

  @override
  Widget build(BuildContext context) {
    return IconButton(
      icon: Icon(_favorited ? Icons.favorite : Icons.favorite_border),
      color: _favorited ? AppColors.pink : AppColors.textSecondary,
      onPressed: () => setState(() => _favorited = !_favorited),
    );
  }
}

class _BuyBar extends StatelessWidget {
  final double price;

  const _BuyBar({required this.price});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton(
              onPressed: () {},
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
                side: const BorderSide(color: AppColors.darkPurple),
                foregroundColor: AppColors.darkPurple,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Faire une offre', style: TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: ElevatedButton(
              onPressed: () {},
              child: const Text('Acheter maintenant'),
            ),
          ),
        ],
      ),
    );
  }
}
