import 'dart:io';
import 'package:flutter/foundation.dart';
import '../services/category_service.dart';
import '../services/product_service.dart';

/// Fabric options — mirrors the web form's hardcoded list.
const kFabricOptions = [
  'Cotton',
  'Polyester',
  'Wool',
  'Silk',
  'Linen',
  'Denim',
  'Leather',
  'Synthetic',
  'Other',
];

/// One photo of the listing: either already on the server ([mediaId] + [url])
/// or newly picked on the phone ([file]).
class SellPhoto {
  final int? mediaId;
  final String? url;
  final File? file;

  const SellPhoto.existing({required this.mediaId, required this.url}) : file = null;
  const SellPhoto.picked(File this.file)
      : mediaId = null,
        url = null;

  bool get isNew => file != null;

  /// Stable key for drag & drop.
  String get key => file?.path ?? 'm:$mediaId:$url';
}

class SellItemProvider extends ChangeNotifier {
  // ── edit mode ────────────────────────────────────────────────────────────
  int? editingProductId;
  bool get isEditing => editingProductId != null;

  // ── form fields ──────────────────────────────────────────────────────────
  /// All photos in display order — existing and new together. First = cover.
  List<SellPhoto> photos = [];

  /// Newly picked files, in display order (what gets uploaded).
  List<File> get images => [for (final p in photos) if (p.file != null) p.file!];
  String title = '';
  String description = '';
  int? categoryId;
  String? categoryPath;
  double? price;
  String condition = '';
  int? brandId;
  String? brandName;
  List<String> fabric = []; // max 2

  List<ApiAttribute> attributes = [];
  bool loadingAttributes = false;
  // attribute_id → set of selected option ids (single- or multi-select)
  Map<int, Set<int>> selectedOptions = {};

  // Flat option ids awaiting distribution once attributes finish loading (edit).
  List<int> _pendingOptionIds = [];

  bool get canSubmit =>
      // At least one photo, when creating and when editing.
      photos.isNotEmpty &&
      title.isNotEmpty &&
      categoryId != null &&
      price != null &&
      condition.isNotEmpty &&
      fabric.isNotEmpty;

  List<int> get allOptionIds =>
      selectedOptions.values.expand((s) => s).toList();

  Set<int> optionsFor(int attributeId) => selectedOptions[attributeId] ?? {};

  // ── init for edit ──────────────────────────────────────────────────────────
  void initForEdit(ApiProduct p) {
    editingProductId = p.id;
    title = p.title;
    description = p.description ?? '';
    price = p.price;
    condition = p.condition;
    categoryId = p.categoryId;
    categoryPath = p.categoryName;
    brandId = p.brandId;
    brandName = p.brand;
    fabric = [...p.fabric];
    photos = [
      for (var i = 0; i < p.images.length; i++)
        SellPhoto.existing(mediaId: i < p.imageIds.length ? p.imageIds[i] : null, url: p.images[i]),
    ];
    _pendingOptionIds = [...p.optionIds];
    notifyListeners();
    if (p.categoryId != null) _loadAttributes(p.categoryId!);
  }

  // ── images ───────────────────────────────────────────────────────────────
  void addImage(File file) {
    photos = [...photos, SellPhoto.picked(file)];
    notifyListeners();
  }

  void removePhoto(int index) {
    photos = [...photos]..removeAt(index);
    notifyListeners();
  }

  /// Reorder photos (drag & drop), existing and new alike. The first photo
  /// becomes the cover (featured) image, matching the website. [newIndex] is
  /// the final position (ReorderableListView.onReorderItem semantics).
  void reorderPhotos(int oldIndex, int newIndex) {
    final next = [...photos];
    next.insert(newIndex, next.removeAt(oldIndex));
    photos = next;
    notifyListeners();
  }

  int get totalImageCount => photos.length;

  /// The final order sent when editing: `m:<media id>` for a photo already on
  /// the product, `n:<i>` for the i-th uploaded file. Null when an existing
  /// photo has no id (old server response) — the server then only appends.
  List<String>? get photoOrder {
    if (photos.any((p) => !p.isNew && p.mediaId == null)) return null;
    var newIndex = 0;
    return [for (final p in photos) p.isNew ? 'n:${newIndex++}' : 'm:${p.mediaId}'];
  }

  // ── simple fields ──────────────────────────────────────────────────────────
  void setTitle(String v) {
    title = v;
    notifyListeners();
  }

  void setDescription(String v) {
    description = v;
    notifyListeners();
  }

  void setPrice(double v) {
    price = v;
    notifyListeners();
  }

  void setCondition(String v) {
    condition = v;
    notifyListeners();
  }

  void setBrand(int id, String name) {
    brandId = id;
    brandName = name;
    notifyListeners();
  }

  void clearBrand() {
    brandId = null;
    brandName = null;
    notifyListeners();
  }

  void toggleFabric(String value) {
    final next = [...fabric];
    if (next.contains(value)) {
      next.remove(value);
    } else if (next.length < 2) {
      next.add(value);
    }
    fabric = next;
    notifyListeners();
  }

  // ── category + attributes ──────────────────────────────────────────────────
  void setCategory(int id, String path) {
    categoryId = id;
    categoryPath = path;
    selectedOptions = {};
    _pendingOptionIds = [];
    attributes = [];
    notifyListeners();
    _loadAttributes(id);
  }

  /// Single-select (radio / dropdown attribute types).
  void setSingleOption(int attributeId, int optionId) {
    selectedOptions = {...selectedOptions, attributeId: {optionId}};
    notifyListeners();
  }

  /// Multi-select with a cap (color type — max 2 on the web).
  void toggleMultiOption(int attributeId, int optionId, {int max = 2}) {
    final current = Set<int>.from(selectedOptions[attributeId] ?? {});
    if (current.contains(optionId)) {
      current.remove(optionId);
    } else if (current.length < max) {
      current.add(optionId);
    }
    selectedOptions = {...selectedOptions, attributeId: current};
    notifyListeners();
  }

  void clearOption(int attributeId) {
    final updated = Map<int, Set<int>>.from(selectedOptions)..remove(attributeId);
    selectedOptions = updated;
    notifyListeners();
  }

  Future<void> _loadAttributes(int catId) async {
    loadingAttributes = true;
    notifyListeners();
    try {
      final attrs = await CategoryService().getCategoryAttributes(catId);
      attributes = attrs.where((a) => a.options.isNotEmpty).toList();
      _distributePendingOptions();
    } catch (_) {
      attributes = [];
    }
    loadingAttributes = false;
    notifyListeners();
  }

  /// Map the flat option-id list (from an edited product) back onto attributes.
  void _distributePendingOptions() {
    if (_pendingOptionIds.isEmpty) return;
    final map = <int, Set<int>>{};
    for (final attr in attributes) {
      for (final opt in attr.options) {
        if (_pendingOptionIds.contains(opt.id)) {
          map.putIfAbsent(attr.id, () => {}).add(opt.id);
        }
      }
    }
    selectedOptions = map;
    _pendingOptionIds = [];
  }

  void reset() {
    editingProductId = null;
    photos = [];
    title = '';
    description = '';
    categoryId = null;
    categoryPath = null;
    price = null;
    condition = '';
    brandId = null;
    brandName = null;
    fabric = [];
    attributes = [];
    selectedOptions = {};
    _pendingOptionIds = [];
    loadingAttributes = false;
    notifyListeners();
  }
}
