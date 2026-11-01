import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_client.dart';
import '../services/favorites_store.dart';
import '../services/product_service.dart';
import '../theme/app_colors.dart';

/// Like button backed by the server (via [FavoritesStore]).
///
/// Hidden on the viewer's own items: a seller cannot like their own article.
/// Signed-out users are sent to the login screen.
class FavoriteHeart extends StatelessWidget {
  final ApiProduct product;

  /// Small white bubble for product cards; plain icon button for the product page.
  final bool compact;

  const FavoriteHeart({super.key, required this.product, this.compact = true});

  Future<void> _toggle(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    if (!await ApiClient.isLoggedIn) {
      messenger.showSnackBar(
        const SnackBar(content: Text('Connectez-vous pour ajouter des favoris.')),
      );
      if (context.mounted) context.push('/login');
      return;
    }
    try {
      await FavoritesStore.instance.toggle(product);
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Impossible de mettre à jour vos favoris';
      messenger.showSnackBar(SnackBar(content: Text(msg.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    if (product.isOwner) return const SizedBox.shrink();

    return ListenableBuilder(
      listenable: FavoritesStore.instance,
      builder: (context, _) {
        final liked = FavoritesStore.instance.isFavorited(product);
        final icon = Icon(
          liked ? Icons.favorite : Icons.favorite_border,
          size: compact ? 16 : 24,
          color: liked ? AppColors.pink : AppColors.textSecondary,
        );

        if (!compact) {
          return IconButton(
            icon: icon,
            tooltip: liked ? 'Retirer des favoris' : 'Ajouter aux favoris',
            onPressed: () => _toggle(context),
          );
        }
        return GestureDetector(
          onTap: () => _toggle(context),
          child: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.9),
              shape: BoxShape.circle,
            ),
            child: icon,
          ),
        );
      },
    );
  }
}
