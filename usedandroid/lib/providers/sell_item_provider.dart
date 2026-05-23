import 'dart:io';
import 'package:flutter/foundation.dart';

class SellItemProvider extends ChangeNotifier {
  List<File> images = [];
  String title = '';
  String description = '';
  String? categoryId;
  String? categoryName;
  double? price;
  String condition = '';
  String? size;
  String parcelSize = 'S';

  bool get canSubmit =>
      images.isNotEmpty &&
      title.isNotEmpty &&
      categoryId != null &&
      price != null &&
      condition.isNotEmpty;

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

  void setCategory(String id, String name) {
    categoryId = id;
    categoryName = name;
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

  void setSize(String v) {
    size = v;
    notifyListeners();
  }

  void setParcelSize(String v) {
    parcelSize = v;
    notifyListeners();
  }

  void reset() {
    images = [];
    title = '';
    description = '';
    categoryId = null;
    categoryName = null;
    price = null;
    condition = '';
    size = null;
    parcelSize = 'S';
    notifyListeners();
  }
}
