import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/category_service.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';
import '../../widgets/search_bar_widget.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _controller = TextEditingController();
  final _categoryService = CategoryService();
  final _productService = ProductService();

  String _query = '';
  List<ApiCategory> _categories = [];
  bool _categoriesLoading = true;

  List<ApiProduct> _results = [];
  bool _searchLoading = false;
  String? _searchError;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _loadCategories() async {
    try {
      final cats = await _categoryService.getRootCategories();
      if (mounted) setState(() { _categories = cats; _categoriesLoading = false; });
    } catch (_) {
      if (mounted) setState(() => _categoriesLoading = false);
    }
  }

  Future<void> _search(String query) async {
    if (query.trim().isEmpty) {
      setState(() => _results = []);
      return;
    }
    setState(() { _searchLoading = true; _searchError = null; });
    try {
      final page = await _productService.getProducts(query: query.trim());
      if (mounted) setState(() { _results = page.items; _searchLoading = false; });
    } on DioException catch (e) {
      if (mounted) setState(() { _searchError = e.message; _searchLoading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: SearchBarWidget(
          controller: _controller,
          hintText: 'Marque, article, membre...',
          onChanged: (v) {
            setState(() => _query = v);
            if (v.isEmpty) setState(() => _results = []);
          },
          onSubmitted: _search,
        ),
        actions: [
          if (_query.isNotEmpty)
            TextButton(
              onPressed: () {
                _controller.clear();
                setState(() { _query = ''; _results = []; _searchError = null; });
              },
              child: const Text('Annuler'),
            ),
        ],
      ),
      body: _query.isEmpty
          ? _BrowseCategories(
              categories: _categories,
              loading: _categoriesLoading,
              onTap: (cat) => context.push('/categories/${cat.id}', extra: cat),
            )
          : _SearchResults(
              results: _results,
              loading: _searchLoading,
              error: _searchError,
              query: _query,
              onRetry: () => _search(_query),
            ),
    );
  }
}

// ── Browse categories ─────────────────────────────────────────────────────────

class _BrowseCategories extends StatelessWidget {
  final List<ApiCategory> categories;
  final bool loading;
  final ValueChanged<ApiCategory> onTap;

  const _BrowseCategories({
    required this.categories,
    required this.loading,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    if (loading) return const Center(child: CircularProgressIndicator());

    return CustomScrollView(
      slivers: [
        const SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text(
              'Parcourir par catégorie',
              style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary),
            ),
          ),
        ),
        if (categories.isEmpty)
          const SliverFillRemaining(
            child: Center(
              child: Text('Aucune catégorie',
                  style: TextStyle(color: AppColors.textSecondary)),
            ),
          )
        else
          SliverList(
            delegate: SliverChildBuilderDelegate(
              (context, index) {
                final cat = categories[index];
                return Column(
                  children: [
                    ListTile(
                      leading: cat.imageUrl != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: CachedNetworkImage(
                                imageUrl: cat.imageUrl!,
                                width: 40,
                                height: 40,
                                fit: BoxFit.cover,
                                errorWidget: (_, __, ___) => _PlaceholderIcon(),
                              ),
                            )
                          : _PlaceholderIcon(),
                      title: Text(cat.name,
                          style: const TextStyle(
                              fontSize: 15, fontWeight: FontWeight.w500)),
                      subtitle: cat.children.isNotEmpty
                          ? Text('${cat.children.length} sous-catégories',
                              style: const TextStyle(
                                  fontSize: 12, color: AppColors.textSecondary))
                          : null,
                      trailing: const Icon(Icons.chevron_right,
                          color: AppColors.textSecondary),
                      onTap: () => onTap(cat),
                    ),
                    const Divider(height: 1, indent: 72),
                  ],
                );
              },
              childCount: categories.length,
            ),
          ),
      ],
    );
  }
}

class _PlaceholderIcon extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(8),
        ),
        child:
            const Icon(Icons.category_outlined, color: AppColors.textSecondary, size: 20),
      );
}

// ── Search results ────────────────────────────────────────────────────────────

class _SearchResults extends StatelessWidget {
  final List<ApiProduct> results;
  final bool loading;
  final String? error;
  final String query;
  final VoidCallback onRetry;

  const _SearchResults({
    required this.results,
    required this.loading,
    required this.error,
    required this.query,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    if (loading) return const Center(child: CircularProgressIndicator());

    if (error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppColors.textSecondary),
            const SizedBox(height: 12),
            Text(error!, style: const TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 12),
            ElevatedButton(onPressed: onRetry, child: const Text('Réessayer')),
          ],
        ),
      );
    }

    if (results.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.search_off, size: 64, color: AppColors.textSecondary),
            const SizedBox(height: 12),
            Text('Aucun résultat pour "$query"',
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 15)),
            const SizedBox(height: 4),
            const Text('Essayez un autre mot-clé',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
          child: Text('${results.length} résultats',
              style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        ),
        Expanded(
          child: GridView.builder(
            padding: const EdgeInsets.all(12),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
              childAspectRatio: 0.62,
            ),
            itemCount: results.length,
            itemBuilder: (context, index) {
              final product = results[index];
              return ApiItemCard(
                product: product,
                onTap: () => context.push('/listing/${product.id}', extra: product),
              );
            },
          ),
        ),
      ],
    );
  }
}
