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

  /// Tapping an item opens its product page (editing is in the ⋮ menu there and here).
  Future<void> _open(ApiProduct product) async {
    await context.push('/listing/${product.id}', extra: product);
    _load(); // it may have been edited, sold or deleted there
  }

  Future<void> _changeStatus(ApiProduct product, String status) async {
    final msg = switch (status) {
      'sold' => 'Marqué comme vendu',
      'reserved' => 'Marqué comme réservé',
      'hidden' => 'Article masqué',
      'approved' => 'Article remis en ligne',
      _ => 'Mis à jour',
    };
    try {
      await ProductService().updateStatus(product.id, status);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.primary),
        );
      }
      _load();
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Action impossible'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _confirmDelete(ApiProduct product) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        title: const Text('Supprimer l\'article'),
        content: const Text('Cette action est irréversible. Continuer ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx, false), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () => Navigator.pop(dialogCtx, true),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ProductService().deleteProduct(product.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Article supprimé'), backgroundColor: AppColors.primary),
        );
      }
      _load();
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Suppression impossible'), backgroundColor: Colors.red),
        );
      }
    }
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
    // Two-column grid like the website's dressing.
    return GridView.builder(
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
        childAspectRatio: 0.66,
      ),
      itemCount: _products.length,
      itemBuilder: (_, i) => _ListingCard(
        product: _products[i],
        onOpen: () => _open(_products[i]),
        onEdit: () => _edit(_products[i]),
        onStatus: (s) => _changeStatus(_products[i], s),
        onDelete: () => _confirmDelete(_products[i]),
      ),
    );
  }
}

class _ListingCard extends StatelessWidget {
  final ApiProduct product;
  final VoidCallback onOpen;
  final VoidCallback onEdit;
  final ValueChanged<String> onStatus;
  final VoidCallback onDelete;

  const _ListingCard({
    required this.product,
    required this.onOpen,
    required this.onEdit,
    required this.onStatus,
    required this.onDelete,
  });

  PopupMenuItem<String> _item(String value, IconData icon, String label, {Color? color}) {
    return PopupMenuItem<String>(
      value: value,
      child: Row(children: [
        Icon(icon, size: 20, color: color ?? AppColors.textPrimary),
        const SizedBox(width: 12),
        Text(label, style: TextStyle(color: color)),
      ]),
    );
  }

  List<PopupMenuEntry<String>> _menu(String status) {
    final items = <PopupMenuEntry<String>>[_item('edit', Icons.edit_outlined, 'Modifier')];
    switch (status) {
      case 'sold':
        items.add(_item('approved', Icons.refresh, 'Remettre en ligne'));
        break;
      case 'reserved':
        items.add(_item('approved', Icons.undo, 'Annuler la réservation'));
        items.add(_item('sold', Icons.sell_outlined, 'Marquer comme vendu'));
        items.add(_item('hidden', Icons.visibility_off_outlined, 'Masquer'));
        break;
      case 'hidden':
        items.add(_item('approved', Icons.visibility_outlined, 'Afficher'));
        items.add(_item('sold', Icons.sell_outlined, 'Marquer comme vendu'));
        break;
      default: // approved / pending
        items.add(_item('reserved', Icons.bookmark_outline, 'Marquer comme réservé'));
        items.add(_item('sold', Icons.sell_outlined, 'Marquer comme vendu'));
        items.add(_item('hidden', Icons.visibility_off_outlined, 'Masquer'));
    }
    items.add(const PopupMenuDivider());
    items.add(_item('delete', Icons.delete_outline, 'Supprimer', color: Colors.red));
    return items;
  }

  @override
  Widget build(BuildContext context) {
    final img = product.displayImageUrl;
    return GestureDetector(
      onTap: onOpen,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Positioned.fill(
                    child: ClipRRect(
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                      child: img.isEmpty
                          ? Container(
                              color: AppColors.inputFill,
                              child: const Icon(Icons.image_not_supported, color: AppColors.textSecondary),
                            )
                          : CachedNetworkImage(
                              imageUrl: img,
                              fit: BoxFit.cover,
                              placeholder: (_, __) => Container(color: AppColors.inputFill),
                              errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                            ),
                    ),
                  ),
                  Positioned(
                    left: 8,
                    top: 8,
                    // White backing keeps the tinted badge readable over the photo.
                    child: DecoratedBox(
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(6)),
                      child: _StatusBadge(status: product.status ?? ''),
                    ),
                  ),
                  Positioned(
                    right: 4,
                    top: 4,
                    child: Material(
                      color: Colors.white.withValues(alpha: 0.9),
                      shape: const CircleBorder(),
                      child: PopupMenuButton<String>(
                        icon: const Icon(Icons.more_vert, size: 20, color: AppColors.textPrimary),
                        tooltip: 'Gérer',
                        onSelected: (v) {
                          switch (v) {
                            case 'edit':
                              onEdit();
                            case 'delete':
                              onDelete();
                            default:
                              onStatus(v);
                          }
                        },
                        itemBuilder: (_) => _menu(product.status ?? ''),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(product.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  if (product.size != null || product.brand != null)
                    Text(
                      [if (product.brand != null) product.brand!, if (product.size != null) product.size!].join(' · '),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                  const SizedBox(height: 4),
                  Text('${product.price.toStringAsFixed(0)} MAD',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                ],
              ),
            ),
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
