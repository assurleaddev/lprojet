import 'package:flutter/material.dart';
import '../../theme/app_colors.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notifications = true;
  bool _emailUpdates = false;
  bool _darkMode = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Paramètres')),
      body: ListView(
        children: [
          _SectionHeader('Compte'),
          _SettingsTile(title: 'Modifier le profil', icon: Icons.person_outline, onTap: () {}),
          _SettingsTile(title: 'Changer le mot de passe', icon: Icons.lock_outline, onTap: () {}),
          _SettingsTile(title: 'Email', icon: Icons.email_outlined, subtitle: 'benouijemmed@gmail.com', onTap: () {}),
          _SettingsTile(title: 'Téléphone', icon: Icons.phone_outlined, subtitle: 'Non renseigné', onTap: () {}),
          const SizedBox(height: 8),
          _SectionHeader('Préférences'),
          SwitchListTile(
            secondary: const Icon(Icons.notifications_outlined, color: AppColors.textPrimary),
            title: const Text('Notifications push'),
            value: _notifications,
            onChanged: (v) => setState(() => _notifications = v),
            activeColor: AppColors.primary,
          ),
          SwitchListTile(
            secondary: const Icon(Icons.email_outlined, color: AppColors.textPrimary),
            title: const Text('Mises à jour par email'),
            value: _emailUpdates,
            onChanged: (v) => setState(() => _emailUpdates = v),
            activeColor: AppColors.primary,
          ),
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
