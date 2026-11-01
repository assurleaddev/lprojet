import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/profile_service.dart';
import '../../services/web_links.dart';
import '../../theme/app_colors.dart';
import 'addresses_screen.dart';

// App-facing notification toggles → backend meta keys (subset of the web page).
const _notifToggles = [
  ('enable_email_notifications', 'Notifications par email', Icons.email_outlined),
  ('notify_high_priority_messages', 'Nouveaux messages', Icons.chat_bubble_outline),
  ('notify_new_items', 'Nouveaux articles', Icons.new_releases_outlined),
  ('notify_marketing', 'Communications marketing', Icons.campaign_outlined),
];

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final _service = ProfileService();
  MobileProfile? _profile;
  Map<String, bool> _notifs = {};
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final p = await _service.getProfile();
      if (mounted) setState(() { _profile = p; _notifs = Map.of(p.notifications); _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _openPage(String title, List<Widget> Function() children) {
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => Scaffold(
        appBar: AppBar(title: Text(title)),
        body: ListView(children: children()),
      ),
    ));
  }

  /// Same sections as the website's settings menu.
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Paramètres')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              children: [
                _SettingsTile(
                  title: 'Profil',
                  icon: Icons.person_outline,
                  onTap: () async {
                    await context.push('/profile/edit');
                    _load();
                  },
                ),
                _SettingsTile(
                  title: 'Paramètres du compte',
                  icon: Icons.manage_accounts_outlined,
                  onTap: () => _openPage('Paramètres du compte', () => [
                        _InfoTile(title: 'E-mail', icon: Icons.email_outlined, value: _profile?.email ?? '—'),
                        _InfoTile(
                          title: 'Téléphone',
                          icon: Icons.phone_outlined,
                          value: (_profile?.phoneNumber?.isNotEmpty ?? false) ? _profile!.phoneNumber! : 'Non renseigné',
                        ),
                        _InfoTile(
                          title: "Nom d'utilisateur",
                          icon: Icons.alternate_email,
                          value: _profile?.username ?? '—',
                        ),
                      ]),
                ),
                _SettingsTile(
                  title: 'Livraison',
                  icon: Icons.local_shipping_outlined,
                  subtitle: 'Adresses de livraison',
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const AddressesScreen()),
                  ),
                ),
                _SettingsTile(
                  title: 'Paiements',
                  icon: Icons.account_balance_outlined,
                  subtitle: 'Comptes de versement · sur usedis.com',
                  external: true,
                  onTap: () => WebLinks.open(context, WebLinks.paymentsSettings),
                ),
                _SettingsTile(
                  title: 'Remises groupées',
                  icon: Icons.percent,
                  subtitle: 'Réductions par lot · sur usedis.com',
                  external: true,
                  onTap: () => WebLinks.open(context, WebLinks.bundleDiscountsSettings),
                ),
                _SettingsTile(
                  title: 'Notifications',
                  icon: Icons.notifications_outlined,
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(
                    builder: (_) => _NotificationSettingsPage(initial: _notifs, onChanged: (m) => _notifs = m),
                  )),
                ),
                _SettingsTile(
                  title: 'Confidentialité',
                  icon: Icons.privacy_tip_outlined,
                  onTap: () => _openPage('Confidentialité', () => [
                        _SettingsTile(
                          title: 'Politique de confidentialité',
                          icon: Icons.privacy_tip_outlined,
                          external: true,
                          onTap: () => WebLinks.open(context, WebLinks.privacyPolicy),
                        ),
                        _SettingsTile(
                          title: "Conditions générales d'utilisation",
                          icon: Icons.description_outlined,
                          external: true,
                          onTap: () => WebLinks.open(context, WebLinks.terms),
                        ),
                      ]),
                ),
                _SettingsTile(
                  title: 'Sécurité',
                  icon: Icons.lock_outline,
                  onTap: () => _openPage('Sécurité', () => [
                        _SettingsTile(
                          title: 'Changer le mot de passe',
                          icon: Icons.password,
                          onTap: _changePassword,
                        ),
                        _SettingsTile(
                          title: 'Supprimer le compte',
                          icon: Icons.delete_outline,
                          textColor: Colors.red,
                          iconColor: Colors.red,
                          onTap: () => _confirmDelete(context),
                        ),
                      ]),
                ),
              ],
            ),
    );
  }

  void _changePassword() {
    final currentCtrl = TextEditingController();
    final newCtrl = TextEditingController();
    final confirmCtrl = TextEditingController();
    var submitting = false;

    showDialog(
      context: context,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (dialogCtx, setDialog) => AlertDialog(
          title: const Text('Changer le mot de passe'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: currentCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Mot de passe actuel'),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: newCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Nouveau mot de passe (min. 8)'),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: confirmCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Confirmer le mot de passe'),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Annuler')),
            ElevatedButton(
              onPressed: submitting
                  ? null
                  : () async {
                      if (newCtrl.text.length < 8) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Le mot de passe doit faire au moins 8 caractères')),
                        );
                        return;
                      }
                      if (newCtrl.text != confirmCtrl.text) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Les mots de passe ne correspondent pas')),
                        );
                        return;
                      }
                      setDialog(() => submitting = true);
                      try {
                        await _service.changePassword(currentCtrl.text, newCtrl.text);
                        if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Mot de passe mis à jour'), backgroundColor: AppColors.primary),
                          );
                        }
                      } on DioException catch (e) {
                        setDialog(() => submitting = false);
                        final msg = (e.response?.data as Map?)?['message'] ?? 'Erreur';
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
                        }
                      }
                    },
              child: const Text('Enregistrer'),
            ),
          ],
        ),
      ),
    );
  }

  void _confirmDelete(BuildContext context) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Supprimer le compte'),
        content: const Text(
          'Pour supprimer votre compte, écrivez à contact@usedis.com avec pour objet '
          '« Supprimer mon compte Usedis ». Les commandes en cours seront annulées.',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('OK')),
        ],
      ),
    );
  }
}

class _SettingsTile extends StatelessWidget {
  final String title;
  final IconData icon;
  final String? subtitle;
  final VoidCallback onTap;
  final Color? textColor;
  final Color? iconColor;

  /// Opens a page of the website (shown with an "open in browser" icon).
  final bool external;

  const _SettingsTile({
    required this.title,
    required this.icon,
    required this.onTap,
    this.subtitle,
    this.textColor,
    this.iconColor,
    this.external = false,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: iconColor ?? AppColors.textPrimary, size: 22),
      title: Text(title, style: TextStyle(color: textColor ?? AppColors.textPrimary)),
      subtitle: subtitle != null ? Text(subtitle!, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)) : null,
      trailing: Icon(external ? Icons.open_in_new : Icons.chevron_right, color: AppColors.textSecondary, size: external ? 18 : 24),
      onTap: onTap,
    );
  }
}

class _InfoTile extends StatelessWidget {
  final String title;
  final IconData icon;
  final String value;

  const _InfoTile({required this.title, required this.icon, required this.value});

  @override
  Widget build(BuildContext context) => ListTile(
        leading: Icon(icon, color: AppColors.textPrimary, size: 22),
        title: Text(title),
        subtitle: Text(value, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
      );
}

/// Notification preferences (same keys as the web notifications page).
class _NotificationSettingsPage extends StatefulWidget {
  final Map<String, bool> initial;
  final ValueChanged<Map<String, bool>> onChanged;

  const _NotificationSettingsPage({required this.initial, required this.onChanged});

  @override
  State<_NotificationSettingsPage> createState() => _NotificationSettingsPageState();
}

class _NotificationSettingsPageState extends State<_NotificationSettingsPage> {
  final _service = ProfileService();
  late final Map<String, bool> _notifs = Map.of(widget.initial);

  Future<void> _toggle(String key, bool value) async {
    final previous = _notifs[key] ?? false;
    setState(() => _notifs[key] = value); // optimistic
    try {
      await _service.updateNotifications({key: value});
      widget.onChanged(Map.of(_notifs));
    } catch (_) {
      if (!mounted) return;
      setState(() => _notifs[key] = previous); // revert
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Impossible de mettre à jour la préférence')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: ListView(
        children: [
          for (final t in _notifToggles)
            SwitchListTile(
              secondary: Icon(t.$3, color: AppColors.textPrimary),
              title: Text(t.$2),
              value: _notifs[t.$1] ?? false,
              onChanged: (v) => _toggle(t.$1, v),
              activeThumbColor: AppColors.primary,
            ),
        ],
      ),
    );
  }
}
