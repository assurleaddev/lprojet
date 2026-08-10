import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/api_client.dart';
import '../../services/auth_service.dart';
import '../../services/product_service.dart';
import '../../services/profile_service.dart';
import '../../theme/app_colors.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _loggedIn = false;
  MobileProfile? _profile;
  bool _loading = true;
  bool _error = false;

  @override
  void initState() {
    super.initState();
    _checkAuth();
    // Re-check whenever auth changes elsewhere (login screen, logout) — the
    // Profile tab is kept alive in the shell so initState won't run again.
    ApiClient.authState.addListener(_onAuthChanged);
  }

  @override
  void dispose() {
    ApiClient.authState.removeListener(_onAuthChanged);
    super.dispose();
  }

  void _onAuthChanged() {
    if (mounted) _checkAuth();
  }

  Future<void> _checkAuth() async {
    final loggedIn = await ApiClient.isLoggedIn;
    if (!loggedIn) {
      if (mounted) setState(() { _loggedIn = false; _loading = false; _error = false; });
      return;
    }
    if (mounted) setState(() { _loading = true; _error = false; });

    // Retry transient failures (the single-threaded dev server drops requests
    // under concurrency). Only a real 401 means the session is invalid.
    for (var attempt = 0; attempt < 3; attempt++) {
      try {
        final profile = await ProfileService().getProfile();
        if (mounted) setState(() { _loggedIn = true; _profile = profile; _loading = false; });
        return;
      } on DioException catch (e) {
        if (e.response?.statusCode == 401) {
          await ApiClient.clearToken();
          if (mounted) setState(() { _loggedIn = false; _loading = false; });
          return;
        }
      } catch (_) {
        // fall through to retry
      }
      await Future.delayed(Duration(milliseconds: 400 * (attempt + 1)));
    }
    // Keep the session (token still valid) but surface a retry.
    if (mounted) setState(() { _loggedIn = true; _loading = false; _error = true; });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (!_loggedIn) return _LoginGate(onLogin: _checkAuth);
    if (_error || _profile == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Profil')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.wifi_off, size: 48, color: AppColors.textSecondary),
              const SizedBox(height: 8),
              const Text('Impossible de charger le profil'),
              TextButton(onPressed: _checkAuth, child: const Text('Réessayer')),
            ],
          ),
        ),
      );
    }
    return _ProfileContent(profile: _profile!, onChanged: _checkAuth);
  }
}

// ── Not logged in ──────────────────────────────────────────────────────────────

class _LoginGate extends StatelessWidget {
  final VoidCallback onLogin;

  const _LoginGate({required this.onLogin});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            children: [
              const Spacer(),
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: AppColors.inputFill,
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.border, width: 2),
                ),
                child: const Icon(Icons.person_outline, size: 44, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 20),
              const Text(
                'Connectez-vous à votre compte',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              const Text(
                'Achetez, vendez et discutez avec des milliers de membres.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.textSecondary, height: 1.5),
              ),
              const SizedBox(height: 32),
              // Email login
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.email_outlined, size: 20),
                  label: const Text('Continuer avec Email'),
                  onPressed: () async {
                    await context.push('/login');
                    onLogin();
                  },
                ),
              ),
              const SizedBox(height: 12),
              // Google login
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: AppColors.border),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {},
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _GoogleIcon(),
                      const SizedBox(width: 10),
                      const Text('Continuer avec Google',
                          style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              // Facebook login
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: AppColors.border),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {},
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.facebook, color: Color(0xFF1877F2), size: 22),
                      SizedBox(width: 10),
                      Text('Continuer avec Facebook',
                          style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  const Expanded(child: Divider()),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    child: Text('ou', style: TextStyle(color: AppColors.textSecondary)),
                  ),
                  const Expanded(child: Divider()),
                ],
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: () async {
                  await context.push('/register');
                  onLogin();
                },
                child: const Text.rich(TextSpan(children: [
                  TextSpan(text: 'Pas encore de compte ? ', style: TextStyle(color: AppColors.textSecondary)),
                  TextSpan(
                      text: 'S\'inscrire',
                      style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
                ])),
              ),
              const Spacer(),
            ],
          ),
        ),
      ),
    );
  }
}

class _GoogleIcon extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 20,
      height: 20,
      child: Stack(
        children: [
          Container(
            decoration: const BoxDecoration(shape: BoxShape.circle, color: Colors.white),
          ),
          const Center(
            child: Text('G',
                style: TextStyle(
                    color: Color(0xFF4285F4), fontWeight: FontWeight.w700, fontSize: 14)),
          ),
        ],
      ),
    );
  }
}

// ── Logged in ──────────────────────────────────────────────────────────────────

class _ProfileContent extends StatelessWidget {
  final MobileProfile profile;
  final VoidCallback onChanged;

  const _ProfileContent({required this.profile, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            onPressed: () async {
              await context.push('/profile/settings');
              onChanged();
            },
          ),
        ],
      ),
      body: ListView(
        children: [
          _ProfileHeader(profile: profile, onChanged: onChanged),
          const SizedBox(height: 8),
          _StatsRow(profile: profile),
          const SizedBox(height: 8),
          _MyListingsSection(),
          const SizedBox(height: 8),
          _MenuSection(onLoggedOut: onChanged),
        ],
      ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  final MobileProfile profile;
  final VoidCallback onChanged;

  const _ProfileHeader({required this.profile, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.all(20),
      child: Row(
        children: [
          CircleAvatar(
            radius: 36,
            backgroundColor: AppColors.inputFill,
            backgroundImage:
                profile.avatarUrl != null ? CachedNetworkImageProvider(profile.avatarUrl!) : null,
            child: profile.avatarUrl == null
                ? Text(
                    profile.fullName.isNotEmpty ? profile.fullName[0].toUpperCase() : '?',
                    style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w700, color: AppColors.primary),
                  )
                : null,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(profile.fullName,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(
                  profile.location.isNotEmpty ? profile.location : profile.email,
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
                const SizedBox(height: 8),
                OutlinedButton(
                  onPressed: () async {
                    await context.push('/profile/edit');
                    onChanged();
                  },
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                    side: const BorderSide(color: AppColors.border),
                    foregroundColor: AppColors.textPrimary,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Modifier le profil', style: TextStyle(fontSize: 13)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatsRow extends StatelessWidget {
  final MobileProfile profile;

  const _StatsRow({required this.profile});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          _StatItem(label: 'Articles', value: '${profile.productsCount}'),
          _VertDivider(),
          _StatItem(label: 'Abonnés', value: '—'),
          _VertDivider(),
          _StatItem(label: 'Abonnements', value: '—'),
        ],
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;

  const _StatItem({required this.label, required this.value});

  @override
  Widget build(BuildContext context) => Expanded(
        child: Column(
          children: [
            Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
            Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          ],
        ),
      );
}

class _VertDivider extends StatelessWidget {
  @override
  Widget build(BuildContext context) =>
      Container(width: 1, height: 32, color: AppColors.border);
}

class _MyListingsSection extends StatefulWidget {
  @override
  State<_MyListingsSection> createState() => _MyListingsSectionState();
}

class _MyListingsSectionState extends State<_MyListingsSection> {
  List<ApiProduct> _products = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final products = await ProductService().getMyProducts();
      if (mounted) setState(() { _products = products; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openAll() async {
    await context.push('/profile/my-listings');
    _load(); // refresh in case something changed
  }

  Future<void> _edit(ApiProduct p) async {
    final updated = await context.push<bool>('/profile/my-listings/edit', extra: p);
    if (updated == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Mes articles', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
              if (_products.isNotEmpty)
                TextButton(
                  onPressed: _openAll,
                  child: const Text('Voir tout', style: TextStyle(color: AppColors.primary)),
                ),
            ],
          ),
          if (_loading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_products.isEmpty)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: Column(
                  children: [
                    Icon(Icons.inventory_2_outlined, size: 48, color: AppColors.textSecondary),
                    SizedBox(height: 8),
                    Text('Aucun article en vente', style: TextStyle(color: AppColors.textSecondary)),
                  ],
                ),
              ),
            )
          else
            SizedBox(
              height: 120,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _products.length,
                separatorBuilder: (_, __) => const SizedBox(width: 10),
                itemBuilder: (_, i) {
                  final p = _products[i];
                  final img = p.displayImageUrl;
                  return GestureDetector(
                    onTap: () => _edit(p),
                    child: SizedBox(
                      width: 100,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: SizedBox(
                              width: 100,
                              height: 90,
                              child: img.isEmpty
                                  ? Container(color: AppColors.inputFill)
                                  : CachedNetworkImage(
                                      imageUrl: img,
                                      fit: BoxFit.cover,
                                      placeholder: (_, __) => Container(color: AppColors.inputFill),
                                      errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                                    ),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text('${p.price.toStringAsFixed(0)} MAD',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}

class _MenuSection extends StatelessWidget {
  final VoidCallback onLoggedOut;

  const _MenuSection({required this.onLoggedOut});

  @override
  Widget build(BuildContext context) {
    final items = [
      _MenuItem(icon: Icons.account_balance_wallet_outlined, title: 'Mon solde', onTap: () => context.push('/profile/balance')),
      _MenuItem(icon: Icons.shopping_bag_outlined, title: 'Mes achats', onTap: () => context.push('/profile/orders')),
      _MenuItem(icon: Icons.favorite_outline, title: 'Mes favoris', onTap: () {}),
      _MenuItem(icon: Icons.settings_outlined, title: 'Paramètres', onTap: () => context.push('/profile/settings')),
      _MenuItem(icon: Icons.help_outline, title: 'Aide & support', onTap: () {}),
      _MenuItem(
        icon: Icons.logout,
        title: 'Se déconnecter',
        color: Colors.red,
        onTap: () => _logout(context),
      ),
    ];

    return Container(
      color: AppColors.surface,
      child: ListView.separated(
        physics: const NeverScrollableScrollPhysics(),
        shrinkWrap: true,
        itemCount: items.length,
        separatorBuilder: (_, __) => const Divider(height: 1, indent: 56),
        itemBuilder: (_, i) {
          final item = items[i];
          return ListTile(
            leading: Icon(item.icon, color: item.color ?? AppColors.textPrimary, size: 22),
            title: Text(item.title, style: TextStyle(color: item.color ?? AppColors.textPrimary)),
            trailing: item.color == null ? const Icon(Icons.chevron_right, color: AppColors.textSecondary) : null,
            onTap: item.onTap,
          );
        },
      ),
    );
  }

  void _logout(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        title: const Text('Se déconnecter'),
        content: const Text('Voulez-vous vraiment vous déconnecter ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () async {
              Navigator.pop(dialogCtx);
              await AuthService().logout();
              onLoggedOut();
            },
            child: const Text('Se déconnecter'),
          ),
        ],
      ),
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final Color? color;

  const _MenuItem({required this.icon, required this.title, required this.onTap, this.color});
}
