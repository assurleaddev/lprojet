import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/category_service.dart';
import '../../services/product_service.dart';
import '../../services/profile_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';
import '../../widgets/rating_stars.dart';
import '../../widgets/search_bar_widget.dart';

/// What the search bar looks for — same "Items / Members" switch as the web.
enum _SearchMode { items, members }

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

  _SearchMode _mode = _SearchMode.items;
  List<ApiProduct> _results = [];
  List<ApiMember> _members = [];
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
    final q = query.trim();
    if (q.isEmpty) {
      setState(() { _results = []; _members = []; });
      return;
    }
    final mode = _mode;
    if (mode == _SearchMode.members && q.length < 2) {
      setState(() { _members = []; _searchError = 'Saisissez au moins 2 caractères.'; });
      return;
    }
    setState(() { _searchLoading = true; _searchError = null; });
    try {
      if (mode == _SearchMode.members) {
        final members = await ProfileService().searchMembers(q);
        if (mounted && _mode == mode) setState(() { _members = members; _searchLoading = false; });
      } else {
        final page = await _productService.getProducts(query: q);
        if (mounted && _mode == mode) setState(() { _results = page.items; _searchLoading = false; });
      }
    } on DioException {
      if (mounted) {
        setState(() { _searchError = 'Recherche impossible. Vérifiez votre connexion.'; _searchLoading = false; });
      }
    }
  }

  void _setMode(_SearchMode mode) {
    if (mode == _mode) return;
    setState(() { _mode = mode; _searchError = null; });
    if (_query.trim().isNotEmpty) _search(_query);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: SearchBarWidget(
          controller: _controller,
          hintText: _mode == _SearchMode.items ? 'Rechercher des articles...' : 'Rechercher des membres...',
          onChanged: (v) {
            setState(() => _query = v);
            if (v.isEmpty) setState(() { _results = []; _members = []; });
          },
          onSubmitted: _search,
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
            child: Row(
              children: [
                _ModeChip(
                  label: 'Articles',
                  selected: _mode == _SearchMode.items,
                  onTap: () => _setMode(_SearchMode.items),
                ),
                const SizedBox(width: 8),
                _ModeChip(
                  label: 'Membres',
                  selected: _mode == _SearchMode.members,
                  onTap: () => _setMode(_SearchMode.members),
                ),
              ],
            ),
          ),
        ),
        actions: [
          if (_query.isNotEmpty)
            TextButton(
              onPressed: () {
                _controller.clear();
                setState(() { _query = ''; _results = []; _members = []; _searchError = null; });
              },
              child: const Text('Annuler'),
            ),
        ],
      ),
      body: _query.isEmpty
          ? (_mode == _SearchMode.members
              ? const _Hint(icon: Icons.people_outline, text: "Recherchez un membre par son nom d'utilisateur")
              : _BrowseCategories(
                  categories: _categories,
                  loading: _categoriesLoading,
                  onTap: (cat) => context.push('/categories/${cat.id}', extra: cat),
                ))
          : _mode == _SearchMode.members
              ? _MemberResults(
                  members: _members,
                  loading: _searchLoading,
                  error: _searchError,
                  query: _query,
                  onRetry: () => _search(_query),
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

// ── Items / Members switch ──────────────────────────────────────────────────

class _ModeChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _ModeChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
          decoration: BoxDecoration(
            color: selected ? AppColors.primary : AppColors.inputFill,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: selected ? AppColors.primary : AppColors.border),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: selected ? Colors.white : AppColors.textPrimary,
            ),
          ),
        ),
      );
}

class _Hint extends StatelessWidget {
  final IconData icon;
  final String text;

  const _Hint({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 56, color: AppColors.textSecondary),
              const SizedBox(height: 12),
              Text(text, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.textSecondary)),
            ],
          ),
        ),
      );
}

// ── Member results ──────────────────────────────────────────────────────────

class _MemberResults extends StatelessWidget {
  final List<ApiMember> members;
  final bool loading;
  final String? error;
  final String query;
  final VoidCallback onRetry;

  const _MemberResults({
    required this.members,
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
            Text(error!, style: const TextStyle(color: AppColors.textSecondary)),
            TextButton(onPressed: onRetry, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (members.isEmpty) {
      return _Hint(icon: Icons.person_search_outlined, text: 'Aucun membre trouvé pour "$query"');
    }
    return ListView.separated(
      itemCount: members.length,
      separatorBuilder: (_, __) => const Divider(height: 1, indent: 76),
      itemBuilder: (context, i) {
        final m = members[i];
        return ListTile(
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          leading: CircleAvatar(
            radius: 24,
            backgroundColor: AppColors.inputFill,
            backgroundImage: m.avatarUrl != null ? CachedNetworkImageProvider(m.avatarUrl!) : null,
            child: m.avatarUrl == null
                ? Text(m.displayName.isNotEmpty ? m.displayName[0].toUpperCase() : '?',
                    style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary))
                : null,
          ),
          title: Text(m.username ?? m.displayName, style: const TextStyle(fontWeight: FontWeight.w600)),
          subtitle: Padding(
            padding: const EdgeInsets.only(top: 2),
            child: Row(
              children: [
                RatingStars(average: m.ratingAvg, count: m.ratingCount, size: 13),
                const SizedBox(width: 8),
                Flexible(
                  child: Text(
                    '${m.followersCount} abonné${m.followersCount > 1 ? 's' : ''} · ${m.productsCount} article${m.productsCount > 1 ? 's' : ''}',
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                  ),
                ),
              ],
            ),
          ),
          trailing: const Icon(Icons.chevron_right, color: AppColors.textSecondary),
          onTap: () => context.push('/user/${m.id}'),
        );
      },
    );
  }
}
