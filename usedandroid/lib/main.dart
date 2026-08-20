import 'package:flutter/material.dart';
import 'navigation/app_router.dart';
import 'services/auth_service.dart';
import 'theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Load verification state before the first frame so the router can gate
  // an unverified logged-in session immediately (survives app restarts).
  await AuthService().bootstrap();
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
