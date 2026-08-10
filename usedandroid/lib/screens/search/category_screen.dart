import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/category_service.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/api_item_card.dart';

// ── filter state ──────────────────────────────────────────────────────────────

class _Filters {
  final String sort;
  final String? condition;
  final double? minPrice;
  final double? maxPrice;
  // attribute_id → set of selected option_ids
  final Map<int, Set<int>> attributeOptions;

  const _Filters({
    this.sort = 'newest',
    this.condition,
    this.minPrice,
    this.maxPrice,
    this.attributeOptions = const {},
  });

  _Filters copyWith({
    String? sort,
    Object? condition = _sentinel,
    Object? minPrice = _sentinel,
    Object? maxPrice = _sentinel,
    Map<int, Set<int>>? attributeOptions,
  }) =>
      _Filters(
        sort: sort ?? this.sort,
        condition:
            condition == _sentinel ? this.condition : condition as String?,
        minPrice: minPrice == _sentinel ? this.minPrice : minPrice as double?,
        maxPrice: maxPrice == _sentinel ? this.maxPrice : maxPrice as double?,
        attributeOptions: attributeOptions ?? this.attributeOptions,
      );

  List<int> get allOptionIds =>
      attributeOptions.values.expand((s) => s).toList();

  bool get hasActiveFilters =>
      sort != 'newest' ||
      condition != null ||
      minPrice != null ||
      maxPrice != null ||
      attributeOptions.values.any((s) => s.isNotEmpty);
}

const _sentinel = Object();

// ── screen ────────────────────────────────────────────────────────────────────

class CategoryScreen extends StatefulWidget {
  final ApiCategory category;

  const CategoryScreen({super.key, required this.category});

  @override
  State<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends State<CategoryScreen> {
  bool _showAll = false;
  _Filters _filters = const _Filters();
  List<ApiAttribute> _attributes = [];
  bool _attributesLoaded = false;

  bool get _hasChildren => widget.category.children.isNotEmpty;

  @override
  void initState() {
    super.initState();
    if (!_hasChildren) _loadAttributes();
  }

  Future<void> _loadAttributes() async {
    if (_attributesLoaded) return;
    try {
      final attrs = await CategoryService()
          .getCategoryAttributes(widget.category.id);
      if (mounted) {
        setState(() {
          _attributes = attrs.where((a) => a.options.isNotEmpty).toList();
          _attributesLoaded = true;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _attributesLoaded = true);
    }
  }

  void _goToAll() {
    setState(() => _showAll = true);
    _loadAttributes();
  }

  void _applyFilters(_Filters f) => setState(() => _filters = f);

  void _reset() => setState(() => _filters = const _Filters());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.category.name),
        actions: (_showAll || !_hasChildren) && _filters.hasActiveFilters
            ? [
                TextButton(
                  onPressed: _reset,
                  child: const Text('Réinitialiser'),
                ),
              ]
            : null,
      ),
      body: (_hasChildren && !_showAll)
          ? _SubCategoryList(
              category: widget.category,
              onTap: (sub) =>
                  context.push('/categories/${sub.id}', extra: sub),
              onShowAll: _goToAll,
            )
          : Column(
              children: [
                _FilterBar(
                  filters: _filters,
                  attributes: _attributes,
                  onChanged: _applyFilters,
                ),
                Expanded(
                  child: _ProductGrid(
                    key: ValueKey(_filters),
                    categoryId: widget.category.id,
                    includeSubcategories: _showAll || _hasChildren,
                    filters: _filters,
                  ),
                ),
              ],
            ),
    );
  }
}

// ── subcategory list ──────────────────────────────────────────────────────────

class _SubCategoryList extends StatelessWidget {
  final ApiCategory category;
  final ValueChanged<ApiCategory> onTap;
  final VoidCallback onShowAll;

  const _SubCategoryList({
    required this.category,
    required this.onTap,
    required this.onShowAll,
  });

  @override
  Widget build(BuildContext context) {
    final subs = category.children;
    return ListView.separated(
      itemCount: subs.length + 1,
      separatorBuilder: (_, __) => const Divider(height: 1, indent: 16),
      itemBuilder: (context, index) {
        if (index == 0) {
          return ListTile(
            leading: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.grid_view_rounded,
                  color: AppColors.primary, size: 20),
            ),
            title: const Text('Tous les articles',
                style:
                    TextStyle(fontSize: 15, fontWeight: FontWeight.w500)),
            trailing: const Icon(Icons.chevron_right,
                color: AppColors.textSecondary),
            onTap: onShowAll,
          );
        }
        final sub = subs[index - 1];
        return ListTile(
          leading: sub.imageUrl != null
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: CachedNetworkImage(
                    imageUrl: sub.imageUrl!,
                    width: 40,
                    height: 40,
                    fit: BoxFit.cover,
                    errorWidget: (_, __, ___) => _CategoryIcon(),
                  ),
                )
              : _CategoryIcon(),
          title: Text(sub.name, style: const TextStyle(fontSize: 15)),
          trailing: const Icon(Icons.chevron_right,
              color: AppColors.textSecondary),
          onTap: () => onTap(sub),
        );
      },
    );
  }
}

class _CategoryIcon extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Icon(Icons.category_outlined,
            color: AppColors.textSecondary, size: 20),
      );
}

// ── filter bar ────────────────────────────────────────────────────────────────

const _sortOptions = [
  ('newest', 'Plus récents'),
  ('price_asc', 'Prix croissant'),
  ('price_desc', 'Prix décroissant'),
];

const _conditionOptions = [
  ('new_with_tags', 'Neuf avec étiquette'),
  ('new_without_tags', 'Neuf sans étiquette'),
  ('very_good', 'Très bon état'),
  ('good', 'Bon état'),
  ('satisfactory', 'Satisfaisant'),
  ('heavily_worn', 'Très usagé'),
];

class _FilterBar extends StatelessWidget {
  final _Filters filters;
  final List<ApiAttribute> attributes;
  final ValueChanged<_Filters> onChanged;

  const _FilterBar({
    required this.filters,
    required this.attributes,
    required this.onChanged,
  });

  String get _sortLabel {
    final opt =
        _sortOptions.where((o) => o.$1 == filters.sort).firstOrNull;
    return (opt != null && opt.$1 != 'newest') ? opt.$2 : 'Trier';
  }

  String get _conditionLabel {
    final opt = _conditionOptions
        .where((o) => o.$1 == filters.condition)
        .firstOrNull;
    return opt?.$2 ?? 'État';
  }

  String get _priceLabel {
    if (filters.minPrice != null && filters.maxPrice != null) {
      return '${filters.minPrice!.toInt()}–${filters.maxPrice!.toInt()} DA';
    } else if (filters.minPrice != null) {
      return '≥ ${filters.minPrice!.toInt()} DA';
    } else if (filters.maxPrice != null) {
      return '≤ ${filters.maxPrice!.toInt()} DA';
    }
    return 'Prix';
  }

  String _attrLabel(ApiAttribute attr) {
    final selected = filters.attributeOptions[attr.id];
    if (selected == null || selected.isEmpty) return attr.name;
    if (selected.length == 1) {
      final opt =
          attr.options.where((o) => o.id == selected.first).firstOrNull;
      return opt?.value ?? attr.name;
    }
    return '${attr.name} (${selected.length})';
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        children: [
          _Chip(
            label: _sortLabel,
            active: filters.sort != 'newest',
            icon: Icons.sort,
            onTap: () => _showSort(context),
          ),
          const SizedBox(width: 8),
          _Chip(
            label: _conditionLabel,
            active: filters.condition != null,
            onTap: () => _showCondition(context),
          ),
          const SizedBox(width: 8),
          _Chip(
            label: _priceLabel,
            active: filters.minPrice != null || filters.maxPrice != null,
            onTap: () => _showPrice(context),
          ),
          ...attributes.map((attr) => Padding(
                padding: const EdgeInsets.only(left: 8),
                child: _Chip(
                  label: _attrLabel(attr),
                  active: filters.attributeOptions[attr.id]?.isNotEmpty ==
                      true,
                  onTap: () => _showAttributeOptions(context, attr),
                ),
              )),
        ],
      ),
    );
  }

  void _showSort(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
          borderRadius:
              BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _PickerSheet(
        title: 'Trier par',
        options: _sortOptions.map((o) => (o.$1, o.$2)).toList(),
        selected: {filters.sort},
        multiSelect: false,
        allowClear: false,
        onApply: (vals) =>
            onChanged(filters.copyWith(sort: vals.firstOrNull ?? 'newest')),
      ),
    );
  }

  void _showCondition(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
          borderRadius:
              BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _PickerSheet(
        title: 'État',
        options: _conditionOptions.map((o) => (o.$1, o.$2)).toList(),
        selected:
            filters.condition != null ? {filters.condition!} : {},
        multiSelect: false,
        allowClear: true,
        onApply: (vals) =>
            onChanged(filters.copyWith(condition: vals.firstOrNull)),
      ),
    );
  }

  void _showPrice(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius:
              BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _PriceSheet(
        minPrice: filters.minPrice,
        maxPrice: filters.maxPrice,
        onApply: (min, max) =>
            onChanged(filters.copyWith(minPrice: min, maxPrice: max)),
      ),
    );
  }

  void _showAttributeOptions(BuildContext context, ApiAttribute attr) {
    final current = filters.attributeOptions[attr.id] ?? {};
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius:
              BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _PickerSheet(
        title: attr.name,
        options: attr.options.map((o) => (o.id.toString(), o.value)).toList(),
        selected: current.map((id) => id.toString()).toSet(),
        multiSelect: true,
        allowClear: true,
        onApply: (vals) {
          final newMap = Map<int, Set<int>>.from(
            filters.attributeOptions.map(
              (k, v) => MapEntry(k, Set<int>.from(v)),
            ),
          );
          final ids = vals.map(int.parse).toSet();
          if (ids.isEmpty) {
            newMap.remove(attr.id);
          } else {
            newMap[attr.id] = ids;
          }
          onChanged(filters.copyWith(attributeOptions: newMap));
        },
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final bool active;
  final IconData? icon;
  final VoidCallback onTap;

  const _Chip({
    required this.label,
    required this.active,
    this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: active ? AppColors.primary : AppColors.inputFill,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: active ? AppColors.primary : Colors.transparent,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(icon,
                  size: 14,
                  color: active
                      ? Colors.white
                      : AppColors.textSecondary),
              const SizedBox(width: 4),
            ],
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: active ? Colors.white : AppColors.textPrimary,
              ),
            ),
            const SizedBox(width: 4),
            Icon(Icons.keyboard_arrow_down,
                size: 14,
                color:
                    active ? Colors.white : AppColors.textSecondary),
          ],
        ),
      ),
    );
  }
}

// ── picker sheet (single or multi-select) ─────────────────────────────────────

class _PickerSheet extends StatefulWidget {
  final String title;
  final List<(String, String)> options;
  final Set<String> selected;
  final bool multiSelect;
  final bool allowClear;
  final ValueChanged<Set<String>> onApply;

  const _PickerSheet({
    required this.title,
    required this.options,
    required this.selected,
    required this.multiSelect,
    required this.allowClear,
    required this.onApply,
  });

  @override
  State<_PickerSheet> createState() => _PickerSheetState();
}

class _PickerSheetState extends State<_PickerSheet> {
  late Set<String> _selected;

  @override
  void initState() {
    super.initState();
    _selected = Set.from(widget.selected);
  }

  void _toggle(String value) {
    setState(() {
      if (widget.multiSelect) {
        if (_selected.contains(value)) {
          _selected.remove(value);
        } else {
          _selected.add(value);
        }
      } else {
        _selected = {value};
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.5,
      maxChildSize: 0.9,
      builder: (_, controller) => Column(
        children: [
          const SizedBox(height: 8),
          Center(
            child: Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Text(widget.title,
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.w600)),
                const Spacer(),
                if (widget.allowClear && _selected.isNotEmpty)
                  TextButton(
                    onPressed: () => setState(() => _selected.clear()),
                    child: const Text('Effacer'),
                  ),
              ],
            ),
          ),
          const Divider(),
          Expanded(
            child: ListView.builder(
              controller: controller,
              itemCount: widget.options.length,
              itemBuilder: (_, i) {
                final (value, label) = widget.options[i];
                final checked = _selected.contains(value);
                return ListTile(
                  title: Text(label),
                  trailing: widget.multiSelect
                      ? Checkbox(
                          value: checked,
                          activeColor: AppColors.primary,
                          onChanged: (_) => _toggle(value),
                        )
                      : (checked
                          ? const Icon(Icons.check,
                              color: AppColors.primary)
                          : null),
                  onTap: () => _toggle(value),
                );
              },
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  widget.onApply(_selected);
                  Navigator.pop(context);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Appliquer',
                    style: TextStyle(
                        fontSize: 15, fontWeight: FontWeight.w600)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── price sheet ───────────────────────────────────────────────────────────────

class _PriceSheet extends StatefulWidget {
  final double? minPrice;
  final double? maxPrice;
  final void Function(double? min, double? max) onApply;

  const _PriceSheet(
      {this.minPrice, this.maxPrice, required this.onApply});

  @override
  State<_PriceSheet> createState() => _PriceSheetState();
}

class _PriceSheetState extends State<_PriceSheet> {
  late final TextEditingController _minCtrl;
  late final TextEditingController _maxCtrl;

  @override
  void initState() {
    super.initState();
    _minCtrl = TextEditingController(
        text: widget.minPrice?.toInt().toString() ?? '');
    _maxCtrl = TextEditingController(
        text: widget.maxPrice?.toInt().toString() ?? '');
  }

  @override
  void dispose() {
    _minCtrl.dispose();
    _maxCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding:
          EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 36,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text('Fourchette de prix',
                  style: TextStyle(
                      fontSize: 16, fontWeight: FontWeight.w600)),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _minCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Min (DA)',
                        border: OutlineInputBorder(),
                        isDense: true,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: _maxCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Max (DA)',
                        border: OutlineInputBorder(),
                        isDense: true,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        widget.onApply(null, null);
                        Navigator.pop(context);
                      },
                      child: const Text('Effacer'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        final min = double.tryParse(_minCtrl.text);
                        final max = double.tryParse(_maxCtrl.text);
                        widget.onApply(min, max);
                        Navigator.pop(context);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Appliquer'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── product grid ──────────────────────────────────────────────────────────────

class _ProductGrid extends StatefulWidget {
  final int categoryId;
  final bool includeSubcategories;
  final _Filters filters;

  const _ProductGrid({
    super.key,
    required this.categoryId,
    required this.includeSubcategories,
    required this.filters,
  });

  @override
  State<_ProductGrid> createState() => _ProductGridState();
}

class _ProductGridState extends State<_ProductGrid> {
  final _service = ProductService();
  final _scrollController = ScrollController();

  List<ApiProduct> _products = [];
  bool _loading = true;
  bool _loadingMore = false;
  bool _hasMore = true;
  int _page = 1;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent - 200 &&
        !_loadingMore &&
        _hasMore) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final page = await _service.getProducts(
        categoryId: widget.categoryId,
        includeSubcategories: widget.includeSubcategories,
        condition: widget.filters.condition,
        minPrice: widget.filters.minPrice,
        maxPrice: widget.filters.maxPrice,
        optionIds: widget.filters.allOptionIds,
        sort: widget.filters.sort,
        page: 1,
      );
      if (mounted) {
        setState(() {
          _products = page.items;
          _hasMore = page.hasMore;
          _page = 1;
          _loading = false;
        });
      }
    } on DioException catch (e) {
      if (mounted) {
        setState(() {
          _error = e.message;
          _loading = false;
        });
      }
    }
  }

  Future<void> _loadMore() async {
    setState(() => _loadingMore = true);
    try {
      final page = await _service.getProducts(
        categoryId: widget.categoryId,
        includeSubcategories: widget.includeSubcategories,
        condition: widget.filters.condition,
        minPrice: widget.filters.minPrice,
        maxPrice: widget.filters.maxPrice,
        optionIds: widget.filters.allOptionIds,
        sort: widget.filters.sort,
        page: _page + 1,
      );
      if (mounted) {
        setState(() {
          _products.addAll(page.items);
          _hasMore = page.hasMore;
          _page++;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline,
                size: 48, color: AppColors.textSecondary),
            const SizedBox(height: 12),
            Text(_error!,
                style:
                    const TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 12),
            ElevatedButton(
                onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }

    if (_products.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.inventory_2_outlined,
                size: 56, color: AppColors.textSecondary),
            SizedBox(height: 12),
            Text('Aucun article trouvé',
                style: TextStyle(
                    color: AppColors.textSecondary, fontSize: 15)),
          ],
        ),
      );
    }

    return GridView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
        childAspectRatio: 0.62,
      ),
      itemCount: _products.length + (_loadingMore ? 2 : 0),
      itemBuilder: (context, index) {
        if (index >= _products.length) {
          return const Center(child: CircularProgressIndicator());
        }
        final product = _products[index];
        return ApiItemCard(
          product: product,
          onTap: () =>
              context.push('/listing/${product.id}', extra: product),
        );
      },
    );
  }
}
