import 'package:flutter/material.dart';
import '../../theme/app_colors.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _usernameCtrl = TextEditingController(text: 'Mohammed B.');
  final _bioCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();

  @override
  void dispose() {
    _usernameCtrl.dispose();
    _bioCtrl.dispose();
    _locationCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Modifier le profil'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Enregistrer', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Center(
            child: Stack(
              children: [
                const CircleAvatar(
                  radius: 48,
                  backgroundColor: AppColors.inputFill,
                  child: Icon(Icons.person, size: 56, color: AppColors.textSecondary),
                ),
                Positioned(
                  bottom: 0,
                  right: 0,
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                    child: const Icon(Icons.camera_alt, size: 18, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          _Field(label: 'Nom d\'utilisateur', controller: _usernameCtrl),
          const SizedBox(height: 16),
          _Field(label: 'Bio', controller: _bioCtrl, maxLines: 3, hint: 'Parlez de vous...'),
          const SizedBox(height: 16),
          _Field(label: 'Localisation', controller: _locationCtrl, hint: 'Ex: Alger'),
        ],
      ),
    );
  }
}

class _Field extends StatelessWidget {
  final String label;
  final TextEditingController controller;
  final int maxLines;
  final String? hint;

  const _Field({required this.label, required this.controller, this.maxLines = 1, this.hint});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          maxLines: maxLines,
          decoration: InputDecoration(hintText: hint),
        ),
      ],
    );
  }
}
