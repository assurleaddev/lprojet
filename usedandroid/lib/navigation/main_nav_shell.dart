import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/inbox_service.dart';
import '../services/unread_store.dart';
import '../theme/app_colors.dart';

class MainNavShell extends StatelessWidget {
  final StatefulNavigationShell shell;

  const MainNavShell({super.key, required this.shell});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: shell,
      bottomNavigationBar: ValueListenableBuilder<UnreadCounts>(
        valueListenable: UnreadStore.instance,
        builder: (context, unread, _) => NavigationBar(
        selectedIndex: shell.currentIndex,
        onDestinationSelected: (index) => shell.goBranch(
          index,
          initialLocation: index == shell.currentIndex,
        ),
        backgroundColor: AppColors.surface,
        indicatorColor: AppColors.primary.withOpacity(0.15),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Accueil',
          ),
          const NavigationDestination(
            icon: Icon(Icons.search_outlined),
            selectedIcon: Icon(Icons.search),
            label: 'Explorer',
          ),
          const NavigationDestination(
            icon: _SellIcon(),
            selectedIcon: _SellIcon(selected: true),
            label: 'Vendre',
          ),
          // Unread messages + notifications (both live in the Messages tab).
          NavigationDestination(
            icon: _Badged(count: unread.total, child: const Icon(Icons.chat_bubble_outline)),
            selectedIcon: _Badged(count: unread.total, child: const Icon(Icons.chat_bubble)),
            label: 'Messages',
          ),
          const NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
        ),
      ),
    );
  }
}

class _SellIcon extends StatelessWidget {
  final bool selected;

  const _SellIcon({this.selected = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Icon(Icons.add, color: Colors.white, size: 20),
    );
  }
}

class _Badged extends StatelessWidget {
  final int count;
  final Widget child;

  const _Badged({required this.count, required this.child});

  @override
  Widget build(BuildContext context) => Badge(
        isLabelVisible: count > 0,
        label: Text(count > 99 ? '99+' : '$count'),
        backgroundColor: AppColors.primary,
        child: child,
      );
}
