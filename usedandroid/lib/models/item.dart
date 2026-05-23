class Item {
  final String id;
  final String brand;
  final double price;
  final String condition;
  final String imageUrl;
  final String title;
  final String? size;
  final bool isFavorited;

  const Item({
    required this.id,
    required this.brand,
    required this.price,
    required this.condition,
    required this.imageUrl,
    required this.title,
    this.size,
    this.isFavorited = false,
  });
}
