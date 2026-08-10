import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../services/product_service.dart';
import '../theme/app_colors.dart';
import 'retry_network_image.dart';

class ApiItemCard extends StatefulWidget {
  final ApiProduct product;
  final VoidCallback? onTap;

  const ApiItemCard({super.key, required this.product, this.onTap});

  @override
  State<ApiItemCard> createState() => _ApiItemCardState();
}

class _ApiItemCardState extends State<ApiItemCard> {
  bool _favorited = false;

  @override
  void initState() {
    super.initState();
    _favorited = widget.product.isFavorited;
  }

  @override
  Widget build(BuildContext context) {
    final imageUrl = widget.product.displayImageUrl;

    return GestureDetector(
      onTap: widget.onTap,
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
                    child: GestureDetector(
                      onTap: () => setState(() => _favorited = !_favorited),
                      child: Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.9),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          _favorited ? Icons.favorite : Icons.favorite_border,
                          size: 16,
                          color: _favorited ? AppColors.pink : AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (widget.product.brand != null)
                    Text(
                      widget.product.brand!,
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  Text(
                    widget.product.title,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${widget.product.price.toStringAsFixed(0)} MAD',
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                      ),
                      if (widget.product.size != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.inputFill,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            widget.product.size!,
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
