import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Five stars (half-star precision) followed by the review count, e.g. ★★★★☆ 12.
class RatingStars extends StatelessWidget {
  final double average;
  final int count;
  final double size;

  const RatingStars({super.key, required this.average, required this.count, this.size = 14});

  @override
  Widget build(BuildContext context) {
    final stars = <Widget>[];
    for (var i = 1; i <= 5; i++) {
      final icon = average >= i
          ? Icons.star_rounded
          : (average >= i - 0.5 ? Icons.star_half_rounded : Icons.star_border_rounded);
      stars.add(Icon(icon, size: size, color: count > 0 ? Colors.amber : AppColors.border));
    }
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        ...stars,
        const SizedBox(width: 4),
        Text(
          '$count',
          style: TextStyle(fontSize: size - 2, color: AppColors.textSecondary),
        ),
      ],
    );
  }
}
