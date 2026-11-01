import 'package:flutter/material.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import 'listing_detail_screen.dart';

/// Opens a product page from just its id (e.g. from a notification), loading
/// the product first.
class ListingLoaderScreen extends StatefulWidget {
  final int productId;

  const ListingLoaderScreen({super.key, required this.productId});

  @override
  State<ListingLoaderScreen> createState() => _ListingLoaderScreenState();
}

class _ListingLoaderScreenState extends State<ListingLoaderScreen> {
  ApiProduct? _product;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _failed = false);
    try {
      final p = await ProductService().getProduct(widget.productId);
      if (mounted) setState(() => _product = p);
    } catch (_) {
      if (mounted) setState(() => _failed = true);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_product != null) return ListingDetailScreen(product: _product!);

    return Scaffold(
      appBar: AppBar(),
      body: Center(
        child: _failed
            ? Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.remove_shopping_cart_outlined, size: 48, color: AppColors.textSecondary),
                  const SizedBox(height: 12),
                  const Text("Cet article n'est plus disponible",
                      style: TextStyle(color: AppColors.textSecondary)),
                  const SizedBox(height: 12),
                  TextButton(onPressed: _load, child: const Text('Réessayer')),
                ],
              )
            : const CircularProgressIndicator(),
      ),
    );
  }
}
