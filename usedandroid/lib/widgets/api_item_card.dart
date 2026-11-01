import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../services/product_service.dart';
import '../theme/app_colors.dart';
import 'favorite_heart.dart';
import 'retry_network_image.dart';

class ApiItemCard extends StatelessWidget {
  final ApiProduct product;
  final VoidCallback? onTap;

  const ApiItemCard({super.key, required this.product, this.onTap});

  @override
  Widget build(BuildContext context) {
    final imageUrl = product.displayImageUrl;

    return GestureDetector(
      onTap: onTap,
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
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                    child: imageUrl.isNotEmpty
                        ? RetryNetworkImage(
                            imageUrl: imageUrl,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            maxRetries: AppConfig.imageMaxRetries,
                            placeholder: Container(color: AppColors.inputFill),
                            errorWidget: _Placeholder(),
                          )
                        : _Placeholder(),
                  ),
                  Positioned(
                    top: 6,
                    right: 6,
                    child: FavoriteHeart(product: product),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (product.brand != null)
                    Text(
                      product.brand!,
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  Text(
                    product.title,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${product.price.toStringAsFixed(0)} MAD',
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                      ),
                      if (product.size != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.inputFill,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            product.size!,
                            style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Placeholder extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.inputFill,
      child: const Center(child: Icon(Icons.image_not_supported, color: AppColors.textSecondary)),
    );
  }
}
