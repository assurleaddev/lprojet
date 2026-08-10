import '../models/item.dart';
import '../models/category.dart';

final List<Item> mockItems = List.generate(
  20,
  (i) => Item(
    id: 'item_$i',
    brand: ['Zara', 'H&M', 'Nike', 'Adidas', 'Levi\'s', 'Mango', 'Pull&Bear'][i % 7],
    price: (10 + (i * 7.5)).roundToDouble(),
    condition: ['Neuf avec étiquette', 'Très bon état', 'Bon état', 'État satisfaisant'][i % 4],
    imageUrl: 'https://picsum.photos/seed/item$i/300/400',
    title: ['Robe fleurie', 'Jean slim', 'Sneakers blanches', 'Veste en jean', 'Pull oversize',
        'Chemise rayée', 'Manteau camel', 'Jupe midi', 'Sweat à capuche', 'Blazer'][i % 10],
    size: ['XS', 'S', 'M', 'L', 'XL', '36', '38', '40'][i % 8],
  ),
);

final List<Category> rootCategories = const [
  Category(id: '1', name: 'Femme', icon: '👗'),
  Category(id: '2', name: 'Homme', icon: '👔'),
  Category(id: '3', name: 'Enfants', icon: '🧸'),
  Category(id: '4', name: 'Maison', icon: '🏠'),
  Category(id: '5', name: 'Électronique', icon: '📱'),
  Category(id: '6', name: 'Sport', icon: '⚽'),
  Category(id: '7', name: 'Beauté', icon: '💄'),
  Category(id: '8', name: 'Livres', icon: '📚'),
];

final Map<String, List<Category>> subCategories = {
  '1': [
    Category(id: '1-1', name: 'Robes', parentId: '1'),
    Category(id: '1-2', name: 'Tops', parentId: '1'),
    Category(id: '1-3', name: 'Pantalons', parentId: '1'),
    Category(id: '1-4', name: 'Vestes', parentId: '1'),
    Category(id: '1-5', name: 'Jupes', parentId: '1'),
    Category(id: '1-6', name: 'Chaussures', parentId: '1'),
    Category(id: '1-7', name: 'Sacs', parentId: '1'),
    Category(id: '1-8', name: 'Accessoires', parentId: '1'),
  ],
  '2': [
    Category(id: '2-1', name: 'T-shirts', parentId: '2'),
    Category(id: '2-2', name: 'Chemises', parentId: '2'),
    Category(id: '2-3', name: 'Pantalons', parentId: '2'),
    Category(id: '2-4', name: 'Vestes', parentId: '2'),
    Category(id: '2-5', name: 'Chaussures', parentId: '2'),
    Category(id: '2-6', name: 'Accessoires', parentId: '2'),
  ],
  '3': [
    Category(id: '3-1', name: 'Bébé (0-2 ans)', parentId: '3'),
    Category(id: '3-2', name: 'Petite enfance (2-6 ans)', parentId: '3'),
    Category(id: '3-3', name: 'Enfant (6-14 ans)', parentId: '3'),
  ],
};
