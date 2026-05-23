import 'dart:io';
import 'package:dio/dio.dart';
import 'api_client.dart';

class ApiProduct {
  final int id;
  final String title;
  final String? description;
  final double price;
  final String condition;
  final String? size;
  final String? brand;
  final int? categoryId;
  final String? featuredImage;
  final List<String> images;
  final bool isFavorited;
  final Map<String, dynamic>? vendor;

  const ApiProduct({
    required this.id,
    required this.title,
    this.description,
    required this.price,
    required this.condition,
    this.size,
    this.brand,
    this.categoryId,
    this.featuredImage,
    this.images = const [],
    this.isFavorited = false,
    this.vendor,
  });

  factory ApiProduct.fromJson(Map<String, dynamic> json) => ApiProduct(
        id: json['id'],
        title: json['title'] ?? '',
        description: json['description'],
        price: (json['price'] as num).toDouble(),
        condition: json['condition'] ?? '',
        size: json['size'],
        brand: json['brand'],
        categoryId: json['category_id'],
        featuredImage: json['featured_image'] != null
            ? ApiClient.fixImageUrl(json['featured_image'])
            : null,
        images: (json['images'] as List?)
                ?.map((i) => ApiClient.fixImageUrl(i['preview_url'] as String))
                .toList() ??
            [],
        isFavorited: json['is_favorited'] ?? false,
        vendor: json['vendor'],
      );

  String get displayImageUrl =>
      featuredImage ?? (images.isNotEmpty ? images.first : '');
}

class ProductPage {
  final List<ApiProduct> items;
  final int currentPage;
  final int lastPage;
  final int total;

  const ProductPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  bool get hasMore => currentPage < lastPage;
}

class ProductService {
  final _client = ApiClient();

  Future<ProductPage> getProducts({
    String? query,
    int? categoryId,
    bool includeSubcategories = false,
    String? condition,
    double? minPrice,
    double? maxPrice,
    List<int> optionIds = const [],
    String sort = 'newest',
    int page = 1,
    int perPage = 20,
  }) async {
    final params = <String, dynamic>{
      'page': page,
      'per_page': perPage,
      'sort': sort,
      if (query != null && query.isNotEmpty) 'q': query,
      if (categoryId != null) 'category_id': categoryId,
      if (categoryId != null && includeSubcategories) 'include_subcategories': 1,
      if (condition != null) 'condition': condition,
      if (minPrice != null) 'min_price': minPrice,
      if (maxPrice != null) 'max_price': maxPrice,
      if (optionIds.isNotEmpty) 'option_ids': optionIds.join(','),
    };

    final response = await _client.dio.get('/mobile/products', queryParameters: params);
    final data = response.data;
    final items = (data['data'] as List)
        .map((j) => ApiProduct.fromJson(j))
        .toList();
    final meta = data['meta'] as Map<String, dynamic>?;

    return ProductPage(
      items: items,
      currentPage: meta?['current_page'] ?? 1,
      lastPage: meta?['last_page'] ?? 1,
      total: meta?['total'] ?? items.length,
    );
  }

  Future<ApiProduct> getProduct(int id) async {
    final response = await _client.dio.get('/mobile/products/$id');
    return ApiProduct.fromJson(response.data);
  }

  Future<ApiProduct> createProduct({
    required String name,
    required double price,
    required int categoryId,
    required String condition,
    String? description,
    String? size,
    int? brandId,
    List<File> images = const [],
  }) async {
    final formData = FormData.fromMap({
      'name': name,
      'price': price,
      'category_id': categoryId,
      'condition': condition,
      if (description != null) 'description': description,
      if (size != null) 'size': size,
      if (brandId != null) 'brand_id': brandId,
    });

    for (int i = 0; i < images.length; i++) {
      formData.files.add(MapEntry(
        'images[]',
        await MultipartFile.fromFile(images[i].path),
      ));
    }

    final response = await _client.dio.post('/mobile/products', data: formData);
    return ApiProduct.fromJson(response.data);
  }
}
