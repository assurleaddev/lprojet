import 'dart:io';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../services/live_service.dart';
import '../../theme/app_colors.dart';

/// Seller flow — step 1: pick a cover, name the live, and curate the products
/// (each with a pre-bid minimum), then create it and jump into the broadcast.
class LiveCreateScreen extends StatefulWidget {
  const LiveCreateScreen({super.key});

  @override
  State<LiveCreateScreen> createState() => _LiveCreateScreenState();
}

class _LiveCreateScreenState extends State<LiveCreateScreen> {
  final _titleCtrl = TextEditingController();
  final _picker = ImagePicker();
  File? _cover;

  List<ApiSellerProduct> _products = [];
  bool _loading = true;
  String? _loadError;

  // productId -> selected; productId -> pre-bid min controller
  final Set<int> _selected = {};
  final Map<int, TextEditingController> _preBidCtrls = {};

  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _titleCtrl.dispose();
    for (final c in _preBidCtrls.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _loadError = null;
    });
    try {
      final products = await LiveService().sellerProducts();
      if (!mounted) return;
      setState(() {
        _products = products;
        _loading = false;
      });
    } on DioException catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          _loadError = e.response?.statusCode == 401
              ? 'Connectez-vous pour démarrer un live'
              : 'Erreur de chargement';
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loading = false; _loadError = 'Erreur de chargement'; });
    }
  }

  Future<void> _pickCover() async {
    final f = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 80, maxWidth: 1600);
    if (f != null && mounted) setState(() => _cover = File(f.path));
  }

  TextEditingController _preBidCtrl(ApiSellerProduct p) {
    return _preBidCtrls.putIfAbsent(
      p.id,
      () => TextEditingController(text: (p.price > 0 ? p.price : 10).toStringAsFixed(0)),
    );
  }

  String? _validate() {
    if (_titleCtrl.text.trim().isEmpty) return 'Donnez un titre à votre live';
    if (_cover == null) return 'Choisissez une image de couverture';
    if (_selected.isEmpty) return 'Sélectionnez au moins un article';
    for (final id in _selected) {
      final v = double.tryParse(_preBidCtrls[id]?.text.trim() ?? '');
      if (v == null || v < 1) return 'Mise de départ invalide pour un article';
    }
    return null;
  }

  Future<void> _submit() async {
    final err = _validate();
    if (err != null) {
      _snack(err, Colors.red);
      return;
    }
    setState(() => _submitting = true);
    try {
      final picks = _selected
          .map((id) => LiveProductPick(id, double.parse(_preBidCtrls[id]!.text.trim())))
          .toList();
      final live = await LiveService().createLive(
        title: _titleCtrl.text.trim(),
        cover: _cover!,
        products: picks,
      );
      if (!mounted) return;
      // Replace so back doesn't return to the create form.
      context.pushReplacement('/lives/broadcast/${live.id}', extra: live);
    } on DioException catch (e) {
      if (!mounted) return;
      final data = e.response?.data;
      _snack(data is Map && data['message'] != null ? data['message'].toString() : 'Création impossible', Colors.red);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _snack(String msg, Color color) =>
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: color));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Démarrer un live')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _loadError != null
              ? _errorState()
              : _form(),
      bottomNavigationBar: (_loading || _loadError != null)
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: SizedBox(
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _submitting ? null : _submit,
                    child: _submitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Créer et continuer'),
                  ),
                ),
              ),
            ),
    );
  }

  Widget _errorState() => Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.wifi_off, size: 48, color: AppColors.textSecondary),
          const SizedBox(height: 10),
          Text(_loadError!, style: const TextStyle(color: AppColors.textSecondary)),
          const SizedBox(height: 8),
          TextButton(onPressed: _load, child: const Text('Réessayer')),
        ]),
      );

  Widget _form() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Cover
        GestureDetector(
          onTap: _pickCover,
          child: AspectRatio(
            aspectRatio: 16 / 9,
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.inputFill,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              clipBehavior: Clip.antiAlias,
              child: _cover != null
                  ? Image.file(_cover!, fit: BoxFit.cover)
                  : const Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                      Icon(Icons.add_a_photo_outlined, color: AppColors.textSecondary, size: 30),
                      SizedBox(height: 8),
                      Text('Image de couverture', style: TextStyle(color: AppColors.textSecondary)),
                    ]),
            ),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _titleCtrl,
          maxLength: 100,
          decoration: const InputDecoration(labelText: 'Titre du live', hintText: 'Ex. Vente de printemps'),
        ),
        const SizedBox(height: 8),
        const Text('Articles à mettre en vente', style: TextStyle(fontWeight: FontWeight.w700)),
        const SizedBox(height: 4),
        const Text('Sélectionnez vos articles et fixez la mise de départ (pré-enchère).',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
        const SizedBox(height: 12),
        if (_products.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 24),
            child: Text('Aucun article approuvé à vendre. Publiez d’abord un article.',
                textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary)),
          )
        else
          ..._products.map(_productTile),
      ],
    );
  }

  Widget _productTile(ApiSellerProduct p) {
    final selected = _selected.contains(p.id);
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: selected ? AppColors.primary.withValues(alpha: 0.06) : AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: selected ? AppColors.primary : AppColors.border),
      ),
      child: Row(
        children: [
          Checkbox(
            value: selected,
            activeColor: AppColors.primary,
            onChanged: (v) => setState(() => v == true ? _selected.add(p.id) : _selected.remove(p.id)),
          ),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: p.image != null
                ? CachedNetworkImage(imageUrl: p.image!, width: 48, height: 48, fit: BoxFit.cover)
                : Container(width: 48, height: 48, color: AppColors.inputFill, child: const Icon(Icons.image_outlined, color: AppColors.textSecondary)),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(p.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
              Text('${p.price.toStringAsFixed(0)} MAD', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
            ]),
          ),
          if (selected)
            SizedBox(
              width: 92,
              child: TextField(
                controller: _preBidCtrl(p),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                textAlign: TextAlign.center,
                decoration: const InputDecoration(
                  labelText: 'Mise',
                  isDense: true,
                  contentPadding: EdgeInsets.symmetric(horizontal: 8, vertical: 10),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
