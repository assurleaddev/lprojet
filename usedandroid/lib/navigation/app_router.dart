import 'package:go_router/go_router.dart';
import '../screens/auth/login_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/inbox/conversation_screen.dart';
import '../screens/inbox/inbox_screen.dart';
import '../screens/listing/listing_detail_screen.dart';
import '../screens/profile/balance_screen.dart';
import '../screens/profile/edit_profile_screen.dart';
import '../screens/profile/orders_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/profile/settings_screen.dart';
import '../screens/search/category_screen.dart';
import '../screens/search/search_screen.dart';
import '../screens/sell/sell_screen.dart';
import '../services/category_service.dart';
import '../services/inbox_service.dart';
import '../services/product_service.dart';
import 'main_nav_shell.dart';

final appRouter = GoRouter(
  initialLocation: '/',
  routes: [
    StatefulShellRoute.indexedStack(
      builder: (context, state, shell) => MainNavShell(shell: shell),
      branches: [
        StatefulShellBranch(routes: [
          GoRoute(path: '/', builder: (_, __) => const HomeScreen()),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(
            path: '/search',
            builder: (_, __) => const SearchScreen(),
            routes: [
              GoRoute(
                path: 'categories/:id',
                builder: (_, state) =>
                    CategoryScreen(category: state.extra as ApiCategory),
              ),
            ],
          ),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(path: '/sell', builder: (_, __) => const SellScreen()),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(
            path: '/inbox',
            builder: (_, __) => const InboxScreen(),
            routes: [
              GoRoute(
                path: 'conversation/:id',
                builder: (_, state) => ConversationScreen(
                  conversation: state.extra as ApiConversation,
                ),
              ),
            ],
          ),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(
            path: '/profile',
            builder: (_, __) => const ProfileScreen(),
            routes: [
              GoRoute(path: 'edit', builder: (_, __) => const EditProfileScreen()),
              GoRoute(path: 'settings', builder: (_, __) => const SettingsScreen()),
              GoRoute(path: 'balance', builder: (_, __) => const BalanceScreen()),
              GoRoute(path: 'orders', builder: (_, __) => const OrdersScreen()),
            ],
          ),
        ]),
      ],
    ),
    GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
    GoRoute(
      path: '/listing/:id',
      builder: (_, state) =>
          ListingDetailScreen(product: state.extra as ApiProduct),
    ),
    GoRoute(
      path: '/categories/:id',
      builder: (_, state) =>
          CategoryScreen(category: state.extra as ApiCategory),
    ),
  ],
);
