import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/profile_service.dart';
import '../../theme/app_colors.dart';

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
  bool _darkMode = false; // local only

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

  Future<void> _toggleNotif(String key, bool value) async {
    final previous = _notifs[key] ?? false;
    setState(() => _notifs[key] = value); // optimistic
    try {
      await _service.updateNotifications({key: value});
    } catch (_) {
      if (mounted) {
        setState(() => _notifs[key] = previous); // revert
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Impossible de mettre à jour la préférence')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Paramètres')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              children: [
                _SectionHeader('Compte'),
                _SettingsTile(
                  title: 'Modifier le profil',
                  icon: Icons.person_outline,
                  onTap: () async {
                    await context.push('/profile/edit');
                    _load();
                  },
                ),
                _SettingsTile(
                  title: 'Changer le mot de passe',
                  icon: Icons.lock_outline,
                  onTap: _changePassword,
                ),
                _SettingsTile(
                  title: 'Email',
                  icon: Icons.email_outlined,
                  subtitle: _profile?.email,
                  onTap: () {},
                ),
                _SettingsTile(
                  title: 'Téléphone',
                  icon: Icons.phone_outlined,
                  subtitle: (_profile?.phoneNumber?.isNotEmpty ?? false)
                      ? _profile!.phoneNumber
                      : 'Non renseigné',
                  onTap: () {},
                ),
                const SizedBox(height: 8),
                _SectionHeader('Notifications'),
                for (final t in _notifToggles)
                  SwitchListTile(
                    secondary: Icon(t.$3, color: AppColors.textPrimary),
                    title: Text(t.$2),
                    value: _notifs[t.$1] ?? false,
                    onChanged: (v) => _toggleNotif(t.$1, v),
                    activeColor: AppColors.primary,
                  ),
                const SizedBox(height: 8),
                _SectionHeader('Préférences'),
                SwitchListTile(
                  secondary: const Icon(Icons.dark_mode_outlined, color: AppColors.textPrimary),
                  title: const Text('Mode sombre'),
                  value: _darkMode,
                  onChanged: (v) => setState(() => _darkMode = v),
                  activeColor: AppColors.primary,
                ),
                const SizedBox(height: 8),
                _SectionHeader('Confidentialité'),
                _SettingsTile(title: 'Politique de confidentialité', icon: Icons.privacy_tip_outlined, onTap: () {}),
                _SettingsTile(title: 'Conditions d\'utilisation', icon: Icons.description_outlined, onTap: () {}),
                const SizedBox(height: 8),
                _SectionHeader('Danger'),
                _SettingsTile(
                  title: 'Supprimer le compte',
                  icon: Icons.delete_outline,
                  textColor: Colors.red,
                  iconColor: Colors.red,
                  onTap: () => _confirmDelete(context),
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
        content: const Text('Cette action est irréversible. Toutes vos données seront supprimées.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Annuler')),
          TextButton(
            onPressed: () => Navigator.pop(context),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;

  const _SectionHeader(this.title);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Text(
        title.toUpperCase(),
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textSecondary, letterSpacing: 0.8),
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

  const _SettingsTile({
    required this.title,
    required this.icon,
    required this.onTap,
    this.subtitle,
    this.textColor,
    this.iconColor,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: iconColor ?? AppColors.textPrimary, size: 22),
      title: Text(title, style: TextStyle(color: textColor ?? AppColors.textPrimary)),
      subtitle: subtitle != null ? Text(subtitle!, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)) : null,
      trailing: const Icon(Icons.chevron_right, color: AppColors.textSecondary),
      onTap: onTap,
    );
  }
}
