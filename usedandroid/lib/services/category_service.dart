import 'api_client.dart';

class ApiOption {
  final int id;
  final String value;

  const ApiOption({required this.id, required this.value});

  factory ApiOption.fromJson(Map<String, dynamic> json) =>
      ApiOption(id: json['id'], value: json['value'] ?? '');
}

class ApiAttribute {
  final int id;
  final String name;
  final String? code;
  final String? type;
  final List<ApiOption> options;

  const ApiAttribute({
    required this.id,
    required this.name,
    this.code,
    this.type,
    this.options = const [],
  });

  factory ApiAttribute.fromJson(Map<String, dynamic> json) => ApiAttribute(
        id: json['id'],
        name: json['name'] ?? '',
        code: json['code'],
        type: json['type'],
        options: (json['options'] as List?)
                ?.map((o) => ApiOption.fromJson(o))
                .toList() ??
            [],
      );
}

class ApiCategory {
  final int id;
  final String name;
  final String? slug;
  final int? parentId;
  final String? imageUrl;
  final List<ApiCategory> children;

  const ApiCategory({
    required this.id,
    required this.name,
    this.slug,
    this.parentId,
    this.imageUrl,
    this.children = const [],
  });

  factory ApiCategory.fromJson(Map<String, dynamic> json) => ApiCategory(
        id: json['id'],
        name: json['name'] ?? '',
        slug: json['slug'],
        parentId: json['parent_id'],
        imageUrl: json['image_url'],
        children: (json['children'] as List?)
                ?.map((c) => ApiCategory.fromJson(c))
                .toList() ??
            [],
      );
}

class CategoryService {
  final _client = ApiClient();

  Future<List<ApiCategory>> getRootCategories() async {
    final response = await _client.dio.get('/mobile/categories');
    return (response.data['data'] as List)
        .map((j) => ApiCategory.fromJson(j))
        .toList();
  }

  Future<ApiCategory> getCategory(int id) async {
    final response = await _client.dio.get('/mobile/categories/$id');
    return ApiCategory.fromJson(response.data['data']);
  }

  Future<List<ApiAttribute>> getCategoryAttributes(int id) async {
    final response =
        await _client.dio.get('/mobile/categories/$id/attributes');
    return (response.data as List)
        .map((j) => ApiAttribute.fromJson(j))
        .toList();
  }
}
