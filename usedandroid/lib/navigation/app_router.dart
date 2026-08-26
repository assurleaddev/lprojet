import 'package:go_router/go_router.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/verify_email_screen.dart';
import '../screens/auth/verify_phone_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/inbox/conversation_screen.dart';
import '../screens/inbox/inbox_screen.dart';
import '../screens/listing/listing_detail_screen.dart';
import '../screens/live/live_broadcast_screen.dart';
import '../screens/live/live_create_screen.dart';
import '../screens/live/lives_screen.dart';
import '../screens/live/live_watch_screen.dart';
import '../screens/profile/balance_screen.dart';
import '../screens/profile/edit_profile_screen.dart';
import '../screens/profile/my_listings_screen.dart';
import '../screens/profile/orders_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/profile/settings_screen.dart';
import '../screens/search/category_screen.dart';
import '../screens/search/search_screen.dart';
import '../screens/sell/sell_screen.dart';
import '../services/auth_state.dart';
import '../services/category_service.dart';
import '../services/inbox_service.dart';
import '../services/live_service.dart';
import '../services/product_service.dart';
import 'main_nav_shell.dart';

final appRouter = GoRouter(
  initialLocation: '/',
  // Re-evaluate redirects whenever auth/verification state changes.
  refreshListenable: AuthState.instance,
  // Mandatory verification gate: a logged-in user must verify email then phone
  // before using the app. Guests (not logged in) browse freely.
  redirect: (context, state) {
    final s = AuthState.instance;
    if (!s.loggedIn) return null;
    final loc = state.matchedLocation;
    if (s.needsEmail && loc != '/verify-email') return '/verify-email';
    if (s.needsPhone && loc != '/verify-phone') return '/verify-phone';
    if (s.verified && (loc == '/verify-email' || loc == '/verify-phone')) return '/';
    return null;
  },
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
              GoRoute(
                path: 'my-listings',
                builder: (_, __) => const MyListingsScreen(),
                routes: [
                  GoRoute(
                    path: 'edit',
                    builder: (_, state) => SellScreen(product: state.extra as ApiProduct),
                  ),
                ],
              ),
            ],
          ),
        ]),
      ],
    ),
    GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
    GoRoute(path: '/lives', builder: (_, __) => const LivesScreen()),
    GoRoute(path: '/lives/create', builder: (_, __) => const LiveCreateScreen()),
    GoRoute(
      path: '/lives/broadcast/:id',
      builder: (_, state) => LiveBroadcastScreen(live: state.extra as ApiLive),
    ),
    GoRoute(
      path: '/lives/watch/:id',
      builder: (_, state) => LiveWatchScreen(live: state.extra as ApiLive),
    ),
    GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
    GoRoute(path: '/verify-email', builder: (_, __) => const VerifyEmailScreen()),
    GoRoute(path: '/verify-phone', builder: (_, __) => const VerifyPhoneScreen()),
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
