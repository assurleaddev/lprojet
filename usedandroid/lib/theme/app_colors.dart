import 'package:flutter/material.dart';

/// Brand palette matched to usedis.com (brand red #FC0E00).
class AppColors {
  // Brand
  static const primary = Color(0xFFFC0E00); // usedis.com brand red
  static const primaryDark = Color(0xFFCC0B00); // pressed / darker red

  // Accents
  static const pink = Color(0xFFFC0E00); // "like"/heart + notification dot (brand red)
  static const darkPurple = Color(0xFF1F2937); // dark neutral: secondary/outline buttons, icons
  static const greenBadge = Color(0xFF27866D);

  // Neutrals (aligned to usedis.com greys)
  static const background = Color(0xFFF5F6F7);
  static const surface = Colors.white;
  static const textPrimary = Color(0xFF111827);
  static const textSecondary = Color(0xFF6B7280);
  static const border = Color(0xFFE5E7EB);
  static const inputFill = Color(0xFFF3F4F6);
}
