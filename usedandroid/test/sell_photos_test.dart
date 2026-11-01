import 'dart:io';
import 'package:flutter_test/flutter_test.dart';
import 'package:usedandroid/providers/sell_item_provider.dart';
import 'package:usedandroid/services/product_service.dart';

ApiProduct _withPhotos() => ApiProduct.fromJson({
      'id': 1,
      'title': 'Veste',
      'price': 300,
      'condition': 'good',
      'images': [
        {'id': 10, 'url': 'https://x/a.jpg', 'preview_url': 'https://x/a.jpg'},
        {'id': 11, 'url': 'https://x/b.jpg', 'preview_url': 'https://x/b.jpg'},
      ],
    });

void main() {
  test('edit loads existing photos with their ids, in order', () {
    final p = SellItemProvider()..initForEdit(_withPhotos());
    expect(p.photos.map((x) => x.mediaId), [10, 11]);
    expect(p.photoOrder, ['m:10', 'm:11']);
  });

  test('existing and new photos reorder together; first is the cover', () {
    final p = SellItemProvider()..initForEdit(_withPhotos());
    p.addImage(File('new.jpg'));
    p.reorderPhotos(2, 0); // new photo becomes the cover
    expect(p.photoOrder, ['n:0', 'm:10', 'm:11']);
    expect(p.images.single.path, 'new.jpg');

    p.removePhoto(1); // drop m:10
    expect(p.photoOrder, ['n:0', 'm:11']);
  });

  test('no order is sent when an existing photo has no id (old server)', () {
    final p = SellItemProvider()
      ..initForEdit(ApiProduct.fromJson({
        'id': 1,
        'title': 'Veste',
        'price': 300,
        'condition': 'good',
        'images': [
          {'url': 'https://x/a.jpg', 'preview_url': 'https://x/a.jpg'},
        ],
      }));
    expect(p.photoOrder, isNull);
  });

  test('editing needs at least one photo', () {
    final p = SellItemProvider()..initForEdit(_withPhotos());
    p.removePhoto(0);
    p.removePhoto(0);
    expect(p.totalImageCount, 0);
    expect(p.canSubmit, isFalse);
  });
}
