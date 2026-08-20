import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
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

// A few well-known colour names → swatch, for the "color" attribute type.
const _colorSwatches = <String, Color>{
  'red': Colors.red, 'rouge': Colors.red,
  'blue': Colors.blue, 'bleu': Colors.blue,
  'green': Colors.green, 'vert': Colors.green,
  'black': Colors.black, 'noir': Colors.black,
  'white': Colors.white, 'blanc': Colors.white,
  'yellow': Colors.yellow, 'jaune': Colors.yellow,
  'pink': Colors.pink, 'rose': Colors.pink,
  'grey': Colors.grey, 'gray': Colors.grey, 'gris': Colors.grey,
  'orange': Colors.orange,
  'purple': Colors.purple, 'violet': Colors.purple,
  'brown': Colors.brown, 'marron': Colors.brown,
  'beige': Color(0xFFF5F5DC),
};

class SellScreen extends StatelessWidget {
  /// When provided, the screen opens in edit mode and pre-fills the product.
  final ApiProduct? product;

  const SellScreen({super.key, this.product});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) {
        final p = SellItemProvider();
        if (product != null) p.initForEdit(product!);
        return p;
      },
      child: const _SellView(),
    );
  }
}

class _SellView extends StatefulWidget {
  const _SellView();

  @override
  State<_SellView> createState() => _SellViewState();
}

class _SellViewState extends State<_SellView> {
  final _picker = ImagePicker();
  bool _submitting = false;

  Future<void> _pickImages(SellItemProvider provider) async {
    final permission = await Permission.photos.request();
    if (!permission.isGranted) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Accès à la galerie requis pour ajouter des photos.'),
            action: SnackBarAction(label: 'Paramètres', onPressed: openAppSettings),
          ),
        );
      }
      return;
    }

    // Compress on pick — full-res phone photos routinely exceed the server's
    // 5 MB per-image limit and get rejected (422). 1920px / 80% keeps them well
    // under it with good quality.
    final files = await _picker.pickMultiImage(imageQuality: 80, maxWidth: 1920);
    final remaining = 5 - provider.totalImageCount;
    for (final f in files.take(remaining)) {
      provider.addImage(File(f.path));
    }
  }

  Future<void> _submit(SellItemProvider provider) async {
    setState(() => _submitting = true);
    try {
      if (provider.isEditing) {
        await ProductService().updateProduct(
          id: provider.editingProductId!,
          name: provider.title,
          description: provider.description.isEmpty ? null : provider.description,
          price: provider.price,
          categoryId: provider.categoryId,
          condition: provider.condition.isEmpty ? null : provider.condition,
          brandId: provider.brandId,
          fabric: provider.fabric,
          optionIds: provider.allOptionIds,
          images: provider.images,
        );
      } else {
        await ProductService().createProduct(
          name: provider.title,
          description: provider.description.isEmpty ? null : provider.description,
          price: provider.price!,
          categoryId: provider.categoryId!,
          condition: provider.condition,
          brandId: provider.brandId,
          fabric: provider.fabric,
          optionIds: provider.allOptionIds,
          images: provider.images,
        );
      }
      if (mounted) {
        final wasEditing = provider.isEditing;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(wasEditing
                ? 'Article mis à jour avec succès !'
                : 'Article publié avec succès !'),
            backgroundColor: AppColors.primary,
          ),
        );
        if (wasEditing) {
          Navigator.of(context).pop(true);
        } else {
          provider.reset();
        }
      }
    } on DioException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(_validationMessage(e)), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  /// Turn a 422 (or other) API error into a readable message showing the
  /// offending field(s) instead of a raw DioException.
  String _validationMessage(DioException e) {
    final data = e.response?.data;
    if (data is Map) {
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        return errors.values
            .map((v) => v is List && v.isNotEmpty ? v.first.toString() : v.toString())
            .join('\n');
      }
      if (data['message'] != null) return data['message'].toString();
    }
    return 'Impossible de publier. Vérifiez les champs et réessayez.';
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<SellItemProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text(provider.isEditing ? "Modifier l'article" : 'Vendre un article'),
        actions: [
          if (_submitting)
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
              ),
            )
          else
            TextButton(
              onPressed: provider.canSubmit ? () => _submit(provider) : null,
              child: Text(
                provider.isEditing ? 'Mettre à jour' : 'Publier',
                style: TextStyle(
                  color: provider.canSubmit ? AppColors.primary : AppColors.textSecondary,
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
          _PhotosSection(provider: provider, onAdd: () => _pickImages(provider)),
          const SizedBox(height: 24),
          _Section(
            title: 'Titre',
            child: TextFormField(
              initialValue: provider.title,
              decoration: const InputDecoration(hintText: 'Ex: Robe fleurie Zara taille S'),
              onChanged: provider.setTitle,
            ),
          ),
          const SizedBox(height: 16),
          _Section(
            title: 'Description',
            child: TextFormField(
              initialValue: provider.description,
              decoration: const InputDecoration(hintText: 'Décrivez votre article...'),
              maxLines: 4,
              onChanged: provider.setDescription,
            ),
          ),
          const SizedBox(height: 16),
          _Section(title: 'Catégorie', child: _CategoryPicker(provider: provider)),
          const SizedBox(height: 16),
          _Section(title: 'Marque', child: _BrandPicker(provider: provider)),
          const SizedBox(height: 16),
          _Section(title: 'État', child: _ConditionPicker(provider: provider)),
          const SizedBox(height: 16),
          _Section(
            title: 'Matière (max 2)',
            child: _FabricPicker(provider: provider),
          ),
          // dynamic attributes for the selected category
          if (provider.loadingAttributes) ...[
            const SizedBox(height: 16),
            const Center(child: CircularProgressIndicator()),
          ] else ...[
            for (final attr in provider.attributes) ...[
              const SizedBox(height: 16),
              _Section(
                title: attr.type == 'color' ? '${attr.name} (max 2)' : attr.name,
                child: _AttributePicker(attribute: attr, provider: provider),
              ),
            ],
          ],
          const SizedBox(height: 16),
          _Section(
            title: 'Prix (MAD)',
            child: TextFormField(
              initialValue: provider.price?.toStringAsFixed(0),
              decoration: const InputDecoration(hintText: '0'),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              onChanged: (v) {
                final parsed = double.tryParse(v);
                if (parsed != null) provider.setPrice(parsed);
              },
            ),
          ),
          const SizedBox(height: 32),
        ],
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
        const Text('Photos', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        const Text("Ajoutez jusqu'à 5 photos",
            style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
        const SizedBox(height: 12),
        SizedBox(
          height: 100,
          child: ListView(
            scrollDirection: Axis.horizontal,
            children: [
              ...provider.existingImageUrls.asMap().entries.map(
                    (e) => _NetworkThumb(
                      url: e.value,
                      onRemove: () => provider.removeExistingImage(e.key),
                    ),
                  ),
              ...provider.images.asMap().entries.map(
                    (e) => _PhotoThumb(
                      file: e.value,
                      onRemove: () => provider.removeImage(e.key),
                    ),
                  ),
              if (provider.totalImageCount < 5) _AddPhotoButton(onTap: onAdd),
            ],
          ),
        ),
      ],
    );
  }
}

class _NetworkThumb extends StatelessWidget {
  final String url;
  final VoidCallback onRemove;

  const _NetworkThumb({required this.url, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        Container(
          width: 90,
          height: 100,
          margin: const EdgeInsets.only(right: 8),
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(10)),
          child: CachedNetworkImage(
            imageUrl: url,
            fit: BoxFit.cover,
            placeholder: (_, __) => Container(color: AppColors.inputFill),
            errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
          ),
        ),
        Positioned(
          top: 4,
          right: 12,
          child: GestureDetector(
            onTap: onRemove,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
              child: const Icon(Icons.close, size: 14, color: Colors.white),
            ),
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
            image: DecorationImage(image: FileImage(file), fit: BoxFit.cover),
          ),
        ),
        Positioned(
          top: 4,
          right: 12,
          child: GestureDetector(
            onTap: onRemove,
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
              child: const Icon(Icons.close, size: 14, color: Colors.white),
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
            Icon(Icons.add_photo_alternate_outlined, color: AppColors.primary, size: 28),
            SizedBox(height: 4),
            Text('Ajouter', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
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
    return _PickerField(
      label: provider.categoryPath ?? 'Sélectionner une catégorie',
      isPlaceholder: provider.categoryPath == null,
      onTap: () => _openCategorySheet(context),
    );
  }

  void _openCategorySheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
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
  final List<(List<ApiCategory>, String)> _stack = [];
  bool _loading = true;
  String? _error;
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
        if (_loading) return const Center(child: CircularProgressIndicator());
        if (_error != null) return Center(child: Text(_error!));

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
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              child: Row(
                children: [
                  if (_stack.length > 1)
                    IconButton(icon: const Icon(Icons.arrow_back), onPressed: _pop)
                  else
                    const SizedBox(width: 48),
                  Expanded(
                    child: Text(title,
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                  ),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            if (_pathLabels.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                child: Text(_pathLabels.join(' › '),
                    style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
              ),
            const Divider(height: 1),
            Expanded(
              child: ListView.separated(
                controller: controller,
                itemCount: categories.length,
                separatorBuilder: (_, __) => const Divider(height: 1, indent: 16),
                itemBuilder: (_, i) {
                  final cat = categories[i];
                  return ListTile(
                    title: Text(cat.name, style: const TextStyle(fontSize: 15)),
                    trailing: cat.children.isNotEmpty
                        ? const Icon(Icons.chevron_right, color: AppColors.textSecondary)
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

// ── brand picker ──────────────────────────────────────────────────────────────

class _BrandPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _BrandPicker({required this.provider});

  @override
  Widget build(BuildContext context) {
    return _PickerField(
      label: provider.brandName ?? 'Sélectionner une marque (optionnel)',
      isPlaceholder: provider.brandName == null,
      onTap: () => showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
        builder: (_) => _BrandSheet(
          onSelected: (b) {
            provider.setBrand(b.id, b.name);
            Navigator.pop(context);
          },
        ),
      ),
    );
  }
}

class _BrandSheet extends StatefulWidget {
  final void Function(ApiBrand brand) onSelected;

  const _BrandSheet({required this.onSelected});

  @override
  State<_BrandSheet> createState() => _BrandSheetState();
}

class _BrandSheetState extends State<_BrandSheet> {
  List<ApiBrand> _brands = [];
  bool _loading = true;
  // Server-side search: only small payloads (the full brand list is too large
  // for the single-threaded dev server to stream reliably).
  int _seq = 0;

  @override
  void initState() {
    super.initState();
    _search('');
  }

  Future<void> _search(String query) async {
    final seq = ++_seq;
    setState(() => _loading = true);
    try {
      final brands = await ProductService().getBrands(query: query);
      if (mounted && seq == _seq) setState(() { _brands = brands; _loading = false; });
    } catch (_) {
      if (mounted && seq == _seq) setState(() { _brands = []; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _brands;

    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.7,
      maxChildSize: 0.92,
      builder: (_, controller) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
        child: Column(
          children: [
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: TextField(
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Rechercher une marque',
                  prefixIcon: const Icon(Icons.search),
                  filled: true,
                  fillColor: AppColors.inputFill,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                ),
                onChanged: _search,
              ),
            ),
            const SizedBox(height: 8),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : filtered.isEmpty
                      ? const Center(child: Text('Aucune marque trouvée'))
                      : ListView.separated(
                          controller: controller,
                          itemCount: filtered.length,
                          separatorBuilder: (_, __) => const Divider(height: 1, indent: 16),
                          itemBuilder: (_, i) => ListTile(
                            title: Text(filtered[i].name),
                            onTap: () => widget.onSelected(filtered[i]),
                          ),
                        ),
            ),
          ],
        ),
      ),
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

// ── fabric picker (multi-select, max 2) ─────────────────────────────────────────

class _FabricPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _FabricPicker({required this.provider});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: kFabricOptions.map((f) {
        final selected = provider.fabric.contains(f);
        final atMax = provider.fabric.length >= 2 && !selected;
        return FilterChip(
          label: Text(f),
          selected: selected,
          onSelected: atMax ? null : (_) => provider.toggleFabric(f),
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

// ── attribute picker (by type: color=multi, radio=chips, default=dropdown) ──────

class _AttributePicker extends StatelessWidget {
  final ApiAttribute attribute;
  final SellItemProvider provider;

  const _AttributePicker({required this.attribute, required this.provider});

  @override
  Widget build(BuildContext context) {
    final selected = provider.optionsFor(attribute.id);

    switch (attribute.type) {
      case 'color':
        return Wrap(
          spacing: 8,
          runSpacing: 8,
          children: attribute.options.map((opt) {
            final isSel = selected.contains(opt.id);
            final atMax = selected.length >= 2 && !isSel;
            final swatch = _colorSwatches[opt.value.toLowerCase()];
            return FilterChip(
              avatar: swatch != null
                  ? CircleAvatar(backgroundColor: swatch, radius: 8)
                  : null,
              label: Text(opt.value),
              selected: isSel,
              onSelected:
                  atMax ? null : (_) => provider.toggleMultiOption(attribute.id, opt.id, max: 2),
              selectedColor: AppColors.primary,
              labelStyle: TextStyle(
                color: isSel ? Colors.white : AppColors.textPrimary,
                fontSize: 12,
              ),
            );
          }).toList(),
        );

      case 'radio':
        return Wrap(
          spacing: 8,
          runSpacing: 8,
          children: attribute.options.map((opt) {
            final isSel = selected.contains(opt.id);
            return ChoiceChip(
              label: Text(opt.value),
              selected: isSel,
              onSelected: (_) => isSel
                  ? provider.clearOption(attribute.id)
                  : provider.setSingleOption(attribute.id, opt.id),
              selectedColor: AppColors.primary,
              labelStyle: TextStyle(
                color: isSel ? Colors.white : AppColors.textPrimary,
                fontSize: 12,
              ),
            );
          }).toList(),
        );

      default:
        final currentValue = selected.isNotEmpty ? selected.first.toString() : null;
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          decoration: BoxDecoration(
            color: AppColors.inputFill,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: selected.isNotEmpty ? AppColors.primary : Colors.transparent,
            ),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: currentValue,
              hint: Text('Sélectionner ${attribute.name.toLowerCase()}',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 15)),
              isExpanded: true,
              icon: const Icon(Icons.keyboard_arrow_down, color: AppColors.textSecondary),
              style: const TextStyle(color: AppColors.textPrimary, fontSize: 15),
              items: [
                const DropdownMenuItem<String>(
                  value: null,
                  child: Text('—', style: TextStyle(color: AppColors.textSecondary)),
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
                  provider.clearOption(attribute.id);
                } else {
                  provider.setSingleOption(attribute.id, int.parse(val));
                }
              },
            ),
          ),
        );
    }
  }
}

// ── shared pieces ───────────────────────────────────────────────────────────────

class _PickerField extends StatelessWidget {
  final String label;
  final bool isPlaceholder;
  final VoidCallback onTap;

  const _PickerField({
    required this.label,
    required this.isPlaceholder,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  color: isPlaceholder ? AppColors.textSecondary : AppColors.textPrimary,
                  fontSize: 15,
                ),
              ),
            ),
            const Icon(Icons.chevron_right, color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final String title;
  final Widget child;

  const _Section({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        child,
      ],
    );
  }
}
