import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';

class MyListingsScreen extends StatefulWidget {
  const MyListingsScreen({super.key});

  @override
  State<MyListingsScreen> createState() => _MyListingsScreenState();
}

class _MyListingsScreenState extends State<MyListingsScreen> {
  List<ApiProduct> _products = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final products = await ProductService().getMyProducts();
      if (mounted) setState(() { _products = products; _loading = false; });
    } catch (_) {
      if (mounted) setState(() { _error = 'Erreur de chargement'; _loading = false; });
    }
  }

  Future<void> _edit(ApiProduct product) async {
    final updated = await context.push<bool>('/profile/my-listings/edit', extra: product);
    if (updated == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mes articles')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return _CenteredMessage(
        icon: Icons.error_outline,
        message: _error!,
        action: TextButton(onPressed: _load, child: const Text('Réessayer')),
      );
    }
    if (_products.isEmpty) {
      return ListView(
        children: const [
          SizedBox(height: 120),
          _CenteredMessage(
            icon: Icons.inventory_2_outlined,
            message: 'Vous n\'avez aucun article',
          ),
        ],
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _products.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (_, i) => _ListingCard(product: _products[i], onTap: () => _edit(_products[i])),
    );
  }
}

class _ListingCard extends StatelessWidget {
  final ApiProduct product;
  final VoidCallback onTap;

  const _ListingCard({required this.product, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final img = product.displayImageUrl;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: SizedBox(
                width: 64,
                height: 64,
                child: img.isEmpty
                    ? Container(color: AppColors.inputFill, child: const Icon(Icons.image_not_supported, color: AppColors.textSecondary))
                    : CachedNetworkImage(
                        imageUrl: img,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: AppColors.inputFill),
                        errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                      ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(product.title,
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                  const SizedBox(height: 4),
                  Text('${product.price.toStringAsFixed(0)} MAD',
                      style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary)),
                  const SizedBox(height: 6),
                  _StatusBadge(status: product.status ?? ''),
                ],
              ),
            ),
            const Icon(Icons.edit_outlined, size: 20, color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final (label, color) = switch (status) {
      'approved' => ('En ligne', AppColors.greenBadge),
      'pending' => ('En attente', Colors.orange),
      'sold' => ('Vendu', AppColors.textSecondary),
      'reserved' => ('Réservé', Colors.amber),
      'hidden' => ('Masqué', AppColors.textSecondary),
      _ => (status, AppColors.textSecondary),
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
    );
  }
}

class _CenteredMessage extends StatelessWidget {
  final IconData icon;
  final String message;
  final Widget? action;

  const _CenteredMessage({required this.icon, required this.message, this.action});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 48, color: AppColors.textSecondary),
          const SizedBox(height: 8),
          Text(message, style: const TextStyle(color: AppColors.textSecondary)),
          if (action != null) action!,
        ],
      ),
    );
  }
}
