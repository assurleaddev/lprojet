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
  final int? brandId;
  final int? categoryId;
  final String? categoryName;
  final String? featuredImage;
  final List<String> images;
  final List<String> fabric;
  final List<int> optionIds;
  final String? status;
  final bool isFavorited;
  final Map<String, dynamic>? vendor;
  final double? priceInclProtection;
  final double? buyerProtection;
  final double? shippingFrom;
  final int favoritesCount;
  final String? createdAt;
  final bool isOwner;

  const ApiProduct({
    required this.id,
    required this.title,
    this.description,
    required this.price,
    required this.condition,
    this.size,
    this.brand,
    this.brandId,
    this.categoryId,
    this.categoryName,
    this.featuredImage,
    this.images = const [],
    this.fabric = const [],
    this.optionIds = const [],
    this.status,
    this.isFavorited = false,
    this.vendor,
    this.priceInclProtection,
    this.buyerProtection,
    this.shippingFrom,
    this.favoritesCount = 0,
    this.createdAt,
    this.isOwner = false,
  });

  factory ApiProduct.fromJson(Map<String, dynamic> json) => ApiProduct(
        id: json['id'],
        title: json['title'] ?? '',
        description: json['description'],
        price: (json['price'] as num).toDouble(),
        condition: json['condition'] ?? '',
        size: json['size'],
        brand: json['brand'],
        brandId: json['brand_id'],
        categoryId: json['category_id'],
        categoryName: json['category'] is Map ? json['category']['name'] : null,
        featuredImage: json['featured_image'] != null
            ? ApiClient.fixImageUrl(json['featured_image'])
            : null,
        images: (json['images'] as List?)
                ?.map((i) => ApiClient.fixImageUrl(i['preview_url'] as String))
                .toList() ??
            [],
        fabric: (json['fabric'] as List?)?.map((f) => f.toString()).toList() ?? [],
        optionIds: (json['option_ids'] as List?)?.map((o) => o as int).toList() ?? [],
        status: json['status'],
        isFavorited: json['is_favorited'] ?? false,
        vendor: json['vendor'],
        priceInclProtection: (json['price_incl_protection'] as num?)?.toDouble(),
        buyerProtection: (json['buyer_protection'] as num?)?.toDouble(),
        shippingFrom: (json['shipping_from'] as num?)?.toDouble(),
        favoritesCount: json['favorites_count'] ?? 0,
        createdAt: json['created_at'],
        isOwner: json['is_owner'] ?? false,
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
    List<String> fabric = const [],
    List<int> optionIds = const [],
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

    for (final f in fabric) {
      formData.fields.add(MapEntry('fabric[]', f));
    }
    for (final id in optionIds) {
      formData.fields.add(MapEntry('options[]', id.toString()));
    }
    for (final image in images) {
      formData.files.add(MapEntry(
        'images[]',
        await MultipartFile.fromFile(image.path),
      ));
    }

    final response = await _client.dio.post('/mobile/products', data: formData);
    return ApiProduct.fromJson(response.data);
  }

  /// Update one of the seller's own products. New [images] are appended
  /// (existing ones are preserved server-side). Sent as multipart POST because
  /// PHP does not parse multipart PUT bodies.
  Future<ApiProduct> updateProduct({
    required int id,
    String? name,
    double? price,
    int? categoryId,
    String? condition,
    String? description,
    int? brandId,
    List<String> fabric = const [],
    List<int> optionIds = const [],
    List<File> images = const [],
  }) async {
    final formData = FormData.fromMap({
      if (name != null) 'name': name,
      if (price != null) 'price': price,
      if (categoryId != null) 'category_id': categoryId,
      if (condition != null) 'condition': condition,
      if (description != null) 'description': description,
      if (brandId != null) 'brand_id': brandId,
    });

    for (final f in fabric) {
      formData.fields.add(MapEntry('fabric[]', f));
    }
    for (final oid in optionIds) {
      formData.fields.add(MapEntry('options[]', oid.toString()));
    }
    for (final image in images) {
      formData.files.add(MapEntry(
        'images[]',
        await MultipartFile.fromFile(image.path),
      ));
    }

    final response = await _client.dio.post('/mobile/products/$id', data: formData);
    return ApiProduct.fromJson(response.data);
  }

  /// The seller's own listings (any status).
  Future<List<ApiProduct>> getMyProducts() async {
    final response = await _client.dio.get('/mobile/my-products');
    return (response.data['data'] as List).map((j) => ApiProduct.fromJson(j)).toList();
  }

  /// Another seller's other approved listings ("Dressing du membre").
  Future<List<ApiProduct>> getVendorProducts(int vendorId, {int? exclude}) async {
    final response = await _client.dio.get(
      '/mobile/vendors/$vendorId/products',
      queryParameters: {if (exclude != null) 'exclude': exclude},
    );
    return (response.data['data'] as List).map((j) => ApiProduct.fromJson(j)).toList();
  }

  /// Similar approved products ("Articles similaires").
  Future<List<ApiProduct>> getSimilarProducts(int productId) async {
    final response = await _client.dio.get('/mobile/products/$productId/similar');
    return (response.data['data'] as List).map((j) => ApiProduct.fromJson(j)).toList();
  }

  Future<void> reportProduct(int productId, String reason) async {
    await _client.dio.post('/mobile/products/$productId/report', data: {'reason': reason});
  }

  /// Owner: change listing status (approved / sold / reserved / hidden).
  Future<ApiProduct> updateStatus(int productId, String status) async {
    final response = await _client.dio.post('/mobile/products/$productId/status', data: {'status': status});
    return ApiProduct.fromJson(response.data);
  }

  /// Owner: delete a listing.
  Future<void> deleteProduct(int productId) async {
    await _client.dio.delete('/mobile/products/$productId');
  }

  /// Brands for the searchable picker (optionally filtered by [query]).
  Future<List<ApiBrand>> getBrands({String? query}) async {
    final response = await _client.dio.get(
      '/mobile/brands',
      queryParameters: {if (query != null && query.isNotEmpty) 'q': query},
    );
    return (response.data['data'] as List).map((j) => ApiBrand.fromJson(j)).toList();
  }
}

class ApiBrand {
  final int id;
  final String name;

  const ApiBrand({required this.id, required this.name});

  factory ApiBrand.fromJson(Map<String, dynamic> json) =>
      ApiBrand(id: json['id'], name: json['name'] ?? '');
}
