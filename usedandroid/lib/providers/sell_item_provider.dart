import 'dart:io';
import 'package:flutter/foundation.dart';
import '../services/category_service.dart';

class SellItemProvider extends ChangeNotifier {
  List<File> images = [];
  String title = '';
  String description = '';
  int? categoryId;
  String? categoryPath;
  double? price;
  String condition = '';
  String? size;

  List<ApiAttribute> attributes = [];
  bool loadingAttributes = false;
  // attribute_id → selected option_id (single select per attribute)
  Map<int, int> selectedOptionIds = {};

  bool get canSubmit =>
      images.isNotEmpty &&
      title.isNotEmpty &&
      categoryId != null &&
      price != null &&
      condition.isNotEmpty;

  List<int> get allOptionIds => selectedOptionIds.values.toList();

  void addImage(File file) {
    images = [...images, file];
    notifyListeners();
  }

  void removeImage(int index) {
    images = [...images]..removeAt(index);
    notifyListeners();
  }

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

  void setSize(String? v) {
    size = v;
    notifyListeners();
  }

  void setCategory(int id, String path) {
    categoryId = id;
    categoryPath = path;
    selectedOptionIds = {};
    attributes = [];
    notifyListeners();
    _loadAttributes(id);
  }

  void setOption(int attributeId, int optionId) {
    selectedOptionIds = {...selectedOptionIds, attributeId: optionId};
    notifyListeners();
  }

  void clearOption(int attributeId) {
    final updated = Map<int, int>.from(selectedOptionIds);
    updated.remove(attributeId);
    selectedOptionIds = updated;
    notifyListeners();
  }

  Future<void> _loadAttributes(int catId) async {
    loadingAttributes = true;
    notifyListeners();
    try {
      final attrs = await CategoryService().getCategoryAttributes(catId);
      attributes = attrs.where((a) => a.options.isNotEmpty).toList();
    } catch (_) {
      attributes = [];
    }
    loadingAttributes = false;
    notifyListeners();
  }

  void reset() {
    images = [];
    title = '';
    description = '';
    categoryId = null;
    categoryPath = null;
    price = null;
    condition = '';
    size = null;
    attributes = [];
    selectedOptionIds = {};
    loadingAttributes = false;
    notifyListeners();
  }
}
