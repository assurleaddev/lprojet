import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/category_service.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';
import '../../widgets/search_bar_widget.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _productService = ProductService();
  final _categoryService = CategoryService();

  List<ApiProduct> _products = [];
  List<ApiCategory> _categories = [];
  int? _selectedCategoryId;
  bool _loading = true;
  bool _loadingMore = false;
  int _page = 1;
  bool _hasMore = true;
  String? _error;

  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadInitial();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadInitial() async {
    try {
      final results = await Future.wait([
        _productService.getProducts(categoryId: _selectedCategoryId),
        _categoryService.getRootCategories(),
      ]);
      if (mounted) {
        setState(() {
          _products = (results[0] as ProductPage).items;
          _hasMore = (results[0] as ProductPage).hasMore;
          _categories = results[1] as List<ApiCategory>;
          _loading = false;
        });
      }
    } catch (e, st) {
      debugPrint('HomeScreen error: $e\n$st');
      if (mounted) setState(() { _loading = false; _error = e.toString(); });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMore) return;
    setState(() => _loadingMore = true);
    try {
      final page = await _productService.getProducts(
        categoryId: _selectedCategoryId,
        page: _page + 1,
      );
      setState(() {
        _products.addAll(page.items);
        _page++;
        _hasMore = page.hasMore;
        _loadingMore = false;
      });
    } catch (_) {
      setState(() => _loadingMore = false);
    }
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _onCategorySelected(int? id) async {
    setState(() { _selectedCategoryId = id; _loading = true; _page = 1; _products = []; });
    try {
      final page = await _productService.getProducts(categoryId: id);
      setState(() { _products = page.items; _hasMore = page.hasMore; _loading = false; });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: CustomScrollView(
        controller: _scrollController,
        slivers: [
          SliverAppBar(
            floating: true,
            snap: true,
            backgroundColor: AppColors.surface,
            titleSpacing: 16,
            title: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => context.push('/search'),
                    child: const SearchBarWidget(
                      hintText: 'Rechercher des articles...',
                      readOnly: true,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                const _LiveButton(),
              ],
            ),
            bottom: _categories.isEmpty
                ? null
                : PreferredSize(
                    preferredSize: const Size.fromHeight(56),
                    child: _CategoryPills(
                      categories: _categories,
                      selectedId: _selectedCategoryId,
                      onSelected: (id) => _onCategorySelected(
                        _selectedCategoryId == id ? null : id,
                      ),
                    ),
                  ),
          ),
          if (_loading)
            const SliverFillRemaining(child: Center(child: CircularProgressIndicator()))
          else if (_error != null)
            SliverFillRemaining(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.wifi_off, size: 48, color: AppColors.textSecondary),
                    const SizedBox(height: 12),
                    const Text('Impossible de charger les articles'),
                  const SizedBox(height: 8),
                  Text(_error ?? '', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), textAlign: TextAlign.center),
                    const SizedBox(height: 12),
                    ElevatedButton(onPressed: _loadInitial, child: const Text('Réessayer')),
                  ],
                ),
              ),
            )
          else ...[
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
                  (context, index) => ApiItemCard(
                    product: _products[index],
                    onTap: () => context.push('/listing/${_products[index].id}', extra: _products[index]),
                  ),
                  childCount: _products.length,
                ),
              ),
            ),
            if (_loadingMore)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(child: CircularProgressIndicator()),
                ),
              ),
          ],
        ],
      ),
    );
  }
}

class _CategoryPills extends StatelessWidget {
  final List<ApiCategory> categories;
  final int? selectedId;
  final ValueChanged<int> onSelected;

  const _CategoryPills({required this.categories, required this.selectedId, required this.onSelected});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 56,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final cat = categories[index];
          final selected = selectedId == cat.id;
          return GestureDetector(
            onTap: () => onSelected(cat.id),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: BoxDecoration(
                color: selected ? AppColors.primary : AppColors.inputFill,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: selected ? AppColors.primary : AppColors.border),
              ),
              child: Text(
                cat.name,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                  color: selected ? Colors.white : AppColors.textPrimary,
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

/// Entry point to the live auctions (replaces the old notification bell).
class _LiveButton extends StatelessWidget {
  const _LiveButton();

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        IconButton(
          icon: const Icon(Icons.live_tv_outlined),
          color: AppColors.primary,
          tooltip: 'Lives',
          onPressed: () => context.push('/lives'),
        ),
        Positioned(
          top: 6,
          right: 6,
          child: Container(
            width: 8,
            height: 8,
            decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
          ),
        ),
      ],
    );
  }
}
