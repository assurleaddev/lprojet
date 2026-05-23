class Category {
  final String id;
  final String name;
  final String? parentId;
  final String? icon;

  const Category({
    required this.id,
    required this.name,
    this.parentId,
    this.icon,
  });
}
