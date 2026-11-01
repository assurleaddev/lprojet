import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:usedandroid/services/inbox_service.dart';
import 'package:usedandroid/services/product_service.dart';
import 'package:usedandroid/services/profile_service.dart';
import 'package:usedandroid/widgets/favorite_heart.dart';
import 'package:usedandroid/widgets/rating_stars.dart';

ApiProduct _product({bool isOwner = false, Map<String, dynamic>? vendor}) => ApiProduct.fromJson({
      'id': 7,
      'title': 'Nike vomero',
      'price': 600,
      'condition': 'new_with_tags',
      'is_owner': isOwner,
      'vendor': vendor,
    });

void main() {
  group('API parsing', () {
    test('notification carries its tap target', () {
      final n = ApiNotification.fromJson({
        'id': 'a',
        'type': 'offer_received',
        'message': 'New offer of 144.00 MAD on sac',
        'read': false,
        'created_at': 'il y a 3 minutes',
        'target': {'type': 'product', 'id': 42},
      });
      expect(n.targetType, 'product');
      expect(n.targetId, 42);

      final legacy = ApiNotification.fromJson({'id': 'b', 'message': 'x'});
      expect(legacy.targetType, isNull);
      expect(legacy.targetId, isNull);
    });

    test('unread counts compare by value', () {
      expect(const UnreadCounts(messages: 2, notifications: 1),
          const UnreadCounts(messages: 2, notifications: 1));
      expect(const UnreadCounts(messages: 2, notifications: 1).total, 3);
      expect(const UnreadCounts(messages: 1) == const UnreadCounts(messages: 2), isFalse);
    });

    test('product exposes the seller id and rating when sent', () {
      final p = _product(vendor: {'id': 5, 'name': 'REDA', 'rating_avg': 4.5, 'rating_count': 12});
      expect(p.vendorId, 5);
      expect(p.vendorRatingAvg, 4.5);
      expect(p.vendorRatingCount, 12);

      final noRating = _product(vendor: {'id': 5, 'name': 'REDA'});
      expect(noRating.vendorRatingAvg, isNull);
    });

    test('member parses profile fields and falls back to the username', () {
      final m = ApiMember.fromJson({
        'id': 3,
        'username': 'saradeals',
        'full_name': ' ',
        'rating_avg': 4,
        'rating_count': 1,
        'followers_count': 10,
        'following_count': 2,
        'products_count': 7,
        'is_following': true,
      });
      expect(m.displayName, 'saradeals');
      expect(m.ratingAvg, 4.0);
      expect(m.followersCount, 10);
      expect(m.isFollowing, isTrue);
    });

    test('conversation keeps the other user id', () {
      final c = ApiConversation.fromJson({
        'id': 1,
        'other_user': {'id': 9, 'name': 'REDA'},
      });
      expect(c.otherUserId, 9);
    });

    test('profile stats include followers, following and rating', () {
      final p = MobileProfile.fromJson({
        'id': 1,
        'stats': {'products_count': 7, 'followers_count': 3, 'following_count': 4, 'rating_avg': 4.5, 'rating_count': 2},
      });
      expect(p.followersCount, 3);
      expect(p.followingCount, 4);
      expect(p.ratingAvg, 4.5);
      expect(p.ratingCount, 2);
    });
  });

  group('widgets', () {
    Widget wrap(Widget child) => MaterialApp(home: Scaffold(body: Center(child: child)));

    testWidgets('no heart on the seller\'s own item', (tester) async {
      await tester.pumpWidget(wrap(FavoriteHeart(product: _product(isOwner: true))));
      expect(find.byIcon(Icons.favorite_border), findsNothing);
      expect(find.byIcon(Icons.favorite), findsNothing);
    });

    testWidgets('heart shows on other sellers\' items', (tester) async {
      await tester.pumpWidget(wrap(FavoriteHeart(product: _product())));
      expect(find.byIcon(Icons.favorite_border), findsOneWidget);
    });

    testWidgets('rating stars show half stars and the review count', (tester) async {
      await tester.pumpWidget(wrap(const RatingStars(average: 3.5, count: 12)));
      expect(find.byIcon(Icons.star_rounded), findsNWidgets(3));
      expect(find.byIcon(Icons.star_half_rounded), findsOneWidget);
      expect(find.byIcon(Icons.star_border_rounded), findsOneWidget);
      expect(find.text('12'), findsOneWidget);
    });
  });
}
