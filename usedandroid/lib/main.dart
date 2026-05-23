import 'package:flutter/material.dart';
import 'navigation/app_router.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const UsedApp());
}

class UsedApp extends StatelessWidget {
  const UsedApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Used',
      theme: AppTheme.light,
      routerConfig: appRouter,
      debugShowCheckedModeBanner: false,
    );
  }
}
