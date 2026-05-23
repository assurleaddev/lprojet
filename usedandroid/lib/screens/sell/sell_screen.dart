import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../data/mock_data.dart';
import '../../providers/sell_item_provider.dart';
import '../../theme/app_colors.dart';

class SellScreen extends StatefulWidget {
  const SellScreen({super.key});

  @override
  State<SellScreen> createState() => _SellScreenState();
}

class _SellScreenState extends State<SellScreen> {
  final _picker = ImagePicker();

  Future<void> _pickImages(SellItemProvider provider) async {
    final files = await _picker.pickMultiImage();
    if (files.length + provider.images.length > 5) {
      return;
    }
    for (final f in files) {
      provider.addImage(File(f.path));
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
              TextButton(
                onPressed: provider.canSubmit ? () => _submit(context, provider) : null,
                child: Text(
                  'Publier',
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
                  decoration: const InputDecoration(hintText: 'Ex: Robe fleurie Zara taille S'),
                  onChanged: provider.setTitle,
                ),
              ),
              const SizedBox(height: 16),
              _Section(
                title: 'Description',
                child: TextFormField(
                  decoration: const InputDecoration(hintText: 'Décrivez votre article...'),
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
              const SizedBox(height: 16),
              _Section(
                title: 'Taille',
                child: _SizePicker(provider: provider),
              ),
              const SizedBox(height: 16),
              _Section(
                title: 'Prix (DZD)',
                child: TextFormField(
                  decoration: const InputDecoration(hintText: '0'),
                  keyboardType: TextInputType.number,
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

  void _submit(BuildContext context, SellItemProvider provider) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Article publié avec succès !'), backgroundColor: AppColors.primary),
    );
    provider.reset();
  }
}

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
        const Text('Ajoutez jusqu\'à 5 photos', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
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

class _CategoryPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _CategoryPicker({required this.provider});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => _showCategorySheet(context, provider),
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
                provider.categoryName ?? 'Sélectionner une catégorie',
                style: TextStyle(
                  color: provider.categoryName != null ? AppColors.textPrimary : AppColors.textSecondary,
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

  void _showCategorySheet(BuildContext context, SellItemProvider provider) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 12),
        itemCount: rootCategories.length,
        separatorBuilder: (_, __) => const Divider(height: 1, indent: 16),
        itemBuilder: (ctx, i) {
          final cat = rootCategories[i];
          return ListTile(
            leading: Text(cat.icon ?? '', style: const TextStyle(fontSize: 22)),
            title: Text(cat.name),
            onTap: () {
              provider.setCategory(cat.id, cat.name);
              Navigator.of(ctx).pop();
            },
          );
        },
      ),
    );
  }
}

class _ConditionPicker extends StatelessWidget {
  final SellItemProvider provider;

  const _ConditionPicker({required this.provider});

  static const _conditions = [
    'Neuf avec étiquette',
    'Neuf sans étiquette',
    'Très bon état',
    'Bon état',
    'État satisfaisant',
  ];

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: _conditions.map((c) {
        final selected = provider.condition == c;
        return ChoiceChip(
          label: Text(c),
          selected: selected,
          onSelected: (_) => provider.setCondition(c),
          selectedColor: AppColors.primary,
          labelStyle: TextStyle(color: selected ? Colors.white : AppColors.textPrimary, fontSize: 12),
        );
      }).toList(),
    );
  }
}

class _SizePicker extends StatelessWidget {
  final SellItemProvider provider;

  const _SizePicker({required this.provider});

  static const _sizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '37', '38', '39', '40', '41', '42'];

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: _sizes.map((s) {
        final selected = provider.size == s;
        return ChoiceChip(
          label: Text(s),
          selected: selected,
          onSelected: (_) => provider.setSize(s),
          selectedColor: AppColors.primary,
          labelStyle: TextStyle(color: selected ? Colors.white : AppColors.textPrimary, fontSize: 12),
        );
      }).toList(),
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
