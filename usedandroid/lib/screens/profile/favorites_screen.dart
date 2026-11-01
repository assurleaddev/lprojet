import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';

/// "Mes favoris": the products the user liked, most recent first.
class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  final _service = ProductService();
  final _scroll = ScrollController();
  List<ApiProduct> _items = [];
  int _page = 1;
  bool _hasMore = false;
  bool _loading = true;
  bool _loadingMore = false;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200) _loadMore();
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _failed = false; });
    try {
      final page = await _service.getFavorites();
      if (mounted) {
        setState(() { _items = page.items; _page = 1; _hasMore = page.hasMore; _loading = false; });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _failed = true; });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMore) return;
    setState(() => _loadingMore = true);
    try {
      final page = await _service.getFavorites(page: _page + 1);
      if (mounted) {
        setState(() { _items.addAll(page.items); _page++; _hasMore = page.hasMore; _loadingMore = false; });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  Future<void> _open(ApiProduct p) async {
    await context.push('/listing/${p.id}', extra: p);
    _load(); // an item may have been un-liked on its page
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Mes favoris')),
      body: RefreshIndicator(onRefresh: _load, child: _body()),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_failed || _items.isEmpty) {
      return ListView(
        children: [
          const SizedBox(height: 140),
          Icon(_failed ? Icons.wifi_off : Icons.favorite_border, size: 56, color: AppColors.textSecondary),
          const SizedBox(height: 12),
          Text(
            _failed ? 'Impossible de charger vos favoris' : 'Aucun favori pour le moment',
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 15),
          ),
          if (!_failed)
            const Padding(
              padding: EdgeInsets.only(top: 4),
              child: Text('Touchez ♥ sur un article pour le retrouver ici.',
                  textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
            ),
          if (_failed) Center(child: TextButton(onPressed: _load, child: const Text('Réessayer'))),
        ],
      );
    }
    return GridView.builder(
      controller: _scroll,
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
        childAspectRatio: 0.62,
      ),
      itemCount: _items.length,
      itemBuilder: (_, i) => ApiItemCard(product: _items[i], onTap: () => _open(_items[i])),
    );
  }
}
