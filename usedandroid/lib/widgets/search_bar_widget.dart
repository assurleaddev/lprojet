import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class SearchBarWidget extends StatelessWidget {
  final TextEditingController? controller;
  final String hintText;
  final VoidCallback? onTap;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onSubmitted;
  final bool readOnly;

  const SearchBarWidget({
    super.key,
    this.controller,
    this.hintText = 'Rechercher...',
    this.onTap,
    this.onChanged,
    this.onSubmitted,
    this.readOnly = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: readOnly ? onTap : null,
      child: Container(
        height: 44,
        decoration: BoxDecoration(
          color: AppColors.inputFill,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            const SizedBox(width: 12),
            const Icon(Icons.search, color: AppColors.textSecondary, size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: readOnly
                  ? Text(
                      hintText,
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 15),
                    )
                  : TextField(
                      controller: controller,
                      onChanged: onChanged,
                      onSubmitted: onSubmitted,
                      textInputAction: TextInputAction.search,
                      decoration: InputDecoration(
                        hintText: hintText,
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: EdgeInsets.zero,
                        fillColor: Colors.transparent,
                        filled: false,
                        hintStyle: const TextStyle(color: AppColors.textSecondary, fontSize: 15),
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
