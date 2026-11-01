import 'package:flutter/foundation.dart';
import 'api_client.dart';
import 'product_service.dart';

/// App-wide liked state, so a heart toggled on one screen is right on every
/// other screen without reloading. The server's `is_favorited` is the default;
/// toggles made in this session override it.
class FavoritesStore extends ChangeNotifier {
  FavoritesStore._() {
    // A different account must not inherit the previous user's hearts.
    ApiClient.authState.addListener(() {
      _overrides.clear();
      notifyListeners();
    });
  }

  static final FavoritesStore instance = FavoritesStore._();

  final Map<int, bool> _overrides = {};
  final Set<int> _pending = {};

  bool isFavorited(ApiProduct product) => _overrides[product.id] ?? product.isFavorited;

  /// Optimistic toggle; reverts and rethrows if the server refuses
  /// (e.g. 422 on the seller's own item, or offline).
  Future<void> toggle(ApiProduct product) async {
    if (_pending.contains(product.id)) return;
    final before = isFavorited(product);
    _overrides[product.id] = !before;
    _pending.add(product.id);
    notifyListeners();
    try {
      final result = await ProductService().toggleFavorite(product.id);
      _overrides[product.id] = result.favorited;
    } catch (_) {
      _overrides[product.id] = before;
      rethrow;
    } finally {
      _pending.remove(product.id);
      notifyListeners();
    }
  }
}
