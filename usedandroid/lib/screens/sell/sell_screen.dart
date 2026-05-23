import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../providers/sell_item_provider.dart';
import '../../services/category_service.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';

// API condition value → display label
const _conditionOptions = [
  ('new_with_tags', 'Neuf avec étiquette'),
  ('new_without_tags', 'Neuf sans étiquette'),
  ('very_good', 'Très bon état'),
  ('good', 'Bon état'),
  ('satisfactory', 'État satisfaisant'),
  ('heavily_worn', 'Très usagé'),
];

class SellScreen extends StatefulWidget {
  const SellScreen({super.key});

  @override
  State<SellScreen> createState() => _SellScreenState();
}

class _SellScreenState extends State<SellScreen> {
  final _picker = ImagePicker();
  bool _submitting = false;

  Future<void> _pickImages(SellItemProvider provider) async {
    final files = await _picker.pickMultiImage();
    final remaining = 5 - provider.images.length;
    for (final f in files.take(remaining)) {
      provider.addImage(File(f.path));
    }
  }

  Future<void> _submit(SellItemProvider provider) async {
    setState(() => _submitting = true);
    try {
      await ProductService().createProduct(
        name: provider.title,
        description: provider.description.isEmpty ? null : provider.description,
        price: provider.price!,
        categoryId: provider.categoryId!,
        condition: provider.condition,
        size: provider.size,
        optionIds: provider.allOptionIds,
        images: provider.images,
      );
      if (mounted) {
        provider.reset();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Article publié avec succès !'),
            backgroundColor: AppColors.primary,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => SellItemProvider(),
      child: Consumer<SellItemProvider>(
        builder: (context, provider, _) => Scaffold(
          appBar: AppBar(
            title: const Text('Vendre un article'),
            actions: [
              if (_submitting)
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  child: SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: AppColors.primary),
                  ),
                )
              else
                TextButton(
                  onPressed:
                      provider.canSubmit ? () => _submit(provider) : null,
                  child: Text(
                    'Publier',
                    style: TextStyle(
                      color: provider.canSubmit
                          ? AppColors.primary
                          : AppColors.textSecondary,
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                ),
            ],
          ),
          body: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _PhotosSection(
                  provider: provider,
                  onAdd: () => _pickImages(provider)),
              const SizedBox(height: 24),
              _Section(
                title: 'Titre',
                child: TextFormField(
                  decoration: const InputDecoration(
                      hintText: 'Ex: Robe fleurie Zara taille S'),
                  onChanged: provider.setTitle,
                ),
              ),
              const SizedBox(height: 16),
              _Section(
                title: 'Description',
                child: TextFormField(
                  decoration: const InputDecoration(
                      hintText: 'Décrivez votre article...'),
                  maxLines: 4,
                  onChanged: provider.setDescription,
                ),
              ),
              const SizedBox(height: 16),
              _Section(
                title: 'Catégorie',
                child: _CategoryPicker(provider: provider),
              ),
              const SizedBox(height: 16),
              _Section(
                title: 'État',
                child: _ConditionPicker(provider: provider),
              ),
              // dynamic attributes for selected category
              if (provider.loadingAttributes) ...[
                const SizedBox(height: 16),
                const Center(child: CircularProgressIndicator()),
              ] else ...[
                for (final attr in provider.attributes) ...[
                  const SizedBox(height: 16),
                  _Section(
                    title: attr.name,
                    child: _AttributePicker(
                      attribute: attr,
                      selectedOptionId:
                          provider.selectedOptionIds[attr.id],
                      onSelect: (optId) =>
                          provider.setOption(attr.id, optId),
                      onClear: () => provider.clearOption(attr.id),
                    ),
                  ),
                ],
              ],
              const SizedBox(height: 16),
              _Section(
                title: 'Prix (DZD)',
                child: TextFormField(
                  decoration: const InputDecoration(hintText: '0'),
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  onChanged: (v) {
                    final parsed = double.tryParse(v);
                    if (parsed != null) provider.setPrice(parsed);
                  },
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }
}

// ── photos ────────────────────────────────────────────────────────────────────

class _PhotosSection extends StatelessWidget {
  final SellItemProvider provider;
  final VoidCallback onAdd;

  const _PhotosSection({required this.provider, required this.onAdd});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Photos',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        const Text("Ajoutez jusqu'à 5 photos",
            style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
        const SizedBox(height: 12),
        SizedBox(
          height: 100,
          child: ListView(
            scrollDirection: Axis.horizontal,
            children: [
              ...provider.images.asMap().entries.map(
                    (e) => _PhotoThumb(
                      file: e.value,
                      onRemove: () => provider.removeImage(e.key),
                    ),
                  ),
              if (provider.images.length < 5) _AddPhotoButton(onTap: onAdd),
            ],
          ),
        ),
      ],
    );
  }
}

class _PhotoThumb extends StatelessWidget {
  final File file;
  final VoidCallback onRemove;

  const _PhotoThumb({required this.file, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        Container(
          width: 90,
          height: 100,
          margin: const EdgeInsets.only(right: 8),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            image: DecorationImage(
                image: FileImage(file), fit: BoxFit.cover),
          ),
        ),
        Positioned(
          top: 4,
          right: 12,
          child: GestureDetector(
            onTap: onRemove,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(
                  color: Colors.black54, shape: BoxShape.circle),
              child:
                  const Icon(Icons.close, size: 14, color: Colors.white),
            ),
          ),
        ),
      ],
    );
  }
}

class _AddPhotoButton extends StatelessWidget {
  final VoidCallback onTap;

  const _AddPhotoButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 90,
        height: 100,
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: const Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.add_photo_alternate_outlined,
                color: AppColors.primary, size: 28),
            SizedBox(height: 4),
            Text('Ajouter',
                style: TextStyle(
                    fontSize: 11, color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}

// ── category picker ───────────────────────────────────────────────────────────

class _CategoryPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _CategoryPicker({required this.provider});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => _openCategorySheet(context),
      child: Container(
        padding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            Expanded(
              child: Text(
                provider.categoryPath ?? 'Sélectionner une catégorie',
                style: TextStyle(
                  color: provider.categoryPath != null
                      ? AppColors.textPrimary
                      : AppColors.textSecondary,
                  fontSize: 15,
                ),
              ),
            ),
            const Icon(Icons.chevron_right,
                color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }

  void _openCategorySheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius:
              BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _CategoryDrillSheet(
        onSelected: (cat, path) {
          provider.setCategory(cat.id, path);
          Navigator.pop(context);
        },
      ),
    );
  }
}

class _CategoryDrillSheet extends StatefulWidget {
  final void Function(ApiCategory cat, String path) onSelected;

  const _CategoryDrillSheet({required this.onSelected});

  @override
  State<_CategoryDrillSheet> createState() => _CategoryDrillSheetState();
}

class _CategoryDrillSheetState extends State<_CategoryDrillSheet> {
  // navigation stack: list of (categories, title)
  final List<(List<ApiCategory>, String)> _stack = [];
  bool _loading = true;
  String? _error;

  // breadcrumb path labels
  final List<String> _pathLabels = [];

  @override
  void initState() {
    super.initState();
    _loadRoot();
  }

  Future<void> _loadRoot() async {
    try {
      final cats = await CategoryService().getRootCategories();
      if (mounted) {
        setState(() {
          _stack.add((cats, 'Catégorie'));
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _error = 'Erreur de chargement'; _loading = false; });
    }
  }

  void _push(ApiCategory cat) {
    if (cat.children.isEmpty) {
      // leaf — select it
      final path = [..._pathLabels, cat.name].join(' › ');
      widget.onSelected(cat, path);
      return;
    }
    setState(() {
      _pathLabels.add(cat.name);
      _stack.add((cat.children, cat.name));
    });
  }

  void _pop() {
    if (_stack.length <= 1) return;
    setState(() {
      _stack.removeLast();
      _pathLabels.removeLast();
    });
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.6,
      maxChildSize: 0.92,
      builder: (_, controller) {
        if (_loading) {
          return const Center(child: CircularProgressIndicator());
        }
        if (_error != null) {
          return Center(child: Text(_error!));
        }

        final (categories, title) = _stack.last;

        return Column(
          children: [
            const SizedBox(height: 8),
            Center(
              child: Container(
                width: 36, height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            // header
            Padding(
              padding: const EdgeInsets.symmetric(
                  horizontal: 8, vertical: 4),
              child: Row(
                children: [
                  if (_stack.length > 1)
                    IconButton(
                      icon: const Icon(Icons.arrow_back),
                      onPressed: _pop,
                    )
                  else
                    const SizedBox(width: 48),
                  Expanded(
                    child: Text(
                      title,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 16, fontWeight: FontWeight.w600),
                    ),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            if (_pathLabels.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                child: Text(
                  _pathLabels.join(' › '),
                  style: const TextStyle(
                      color: AppColors.textSecondary, fontSize: 12),
                ),
              ),
            const Divider(height: 1),
            Expanded(
              child: ListView.separated(
                controller: controller,
                itemCount: categories.length,
                separatorBuilder: (_, __) =>
                    const Divider(height: 1, indent: 16),
                itemBuilder: (_, i) {
                  final cat = categories[i];
                  return ListTile(
                    title: Text(cat.name,
                        style: const TextStyle(fontSize: 15)),
                    trailing: cat.children.isNotEmpty
                        ? const Icon(Icons.chevron_right,
                            color: AppColors.textSecondary)
                        : const Icon(Icons.check_circle_outline,
                            color: AppColors.textSecondary, size: 18),
                    onTap: () => _push(cat),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }
}

// ── condition picker ──────────────────────────────────────────────────────────

class _ConditionPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _ConditionPicker({required this.provider});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: _conditionOptions.map((opt) {
        final (value, label) = opt;
        final selected = provider.condition == value;
        return ChoiceChip(
          label: Text(label),
          selected: selected,
          onSelected: (_) => provider.setCondition(value),
          selectedColor: AppColors.primary,
          labelStyle: TextStyle(
            color: selected ? Colors.white : AppColors.textPrimary,
            fontSize: 12,
          ),
        );
      }).toList(),
    );
  }
}

// ── attribute picker (dropdown) ───────────────────────────────────────────────

class _AttributePicker extends StatelessWidget {
  final ApiAttribute attribute;
  final int? selectedOptionId;
  final ValueChanged<int> onSelect;
  final VoidCallback onClear;

  const _AttributePicker({
    required this.attribute,
    this.selectedOptionId,
    required this.onSelect,
    required this.onClear,
  });

  @override
  Widget build(BuildContext context) {
    final currentValue = selectedOptionId?.toString();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: AppColors.inputFill,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: selectedOptionId != null
              ? AppColors.primary
              : Colors.transparent,
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: currentValue,
          hint: Text(
            'Sélectionner ${attribute.name.toLowerCase()}',
            style: const TextStyle(
                color: AppColors.textSecondary, fontSize: 15),
          ),
          isExpanded: true,
          icon: const Icon(Icons.keyboard_arrow_down,
              color: AppColors.textSecondary),
          style: const TextStyle(
              color: AppColors.textPrimary, fontSize: 15),
          items: [
            const DropdownMenuItem<String>(
              value: null,
              child: Text('—',
                  style: TextStyle(color: AppColors.textSecondary)),
            ),
            ...attribute.options.map(
              (opt) => DropdownMenuItem<String>(
                value: opt.id.toString(),
                child: Text(opt.value),
              ),
            ),
          ],
          onChanged: (val) {
            if (val == null) {
              onClear();
            } else {
              onSelect(int.parse(val));
            }
          },
        ),
      ),
    );
  }
}

// ── section wrapper ───────────────────────────────────────────────────────────

class _Section extends StatelessWidget {
  final String title;
  final Widget child;

  const _Section({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title,
            style: const TextStyle(
                fontSize: 15, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        child,
      ],
    );
  }
}
