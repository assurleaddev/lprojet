import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../services/profile_service.dart';
import '../../theme/app_colors.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _service = ProfileService();
  final _picker = ImagePicker();

  final _firstNameCtrl = TextEditingController();
  final _lastNameCtrl = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _bioCtrl = TextEditingController();
  final _cityCtrl = TextEditingController();
  final _countryCtrl = TextEditingController();

  MobileProfile? _profile;
  File? _newAvatar;
  bool _loading = true;
  bool _saving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _firstNameCtrl.dispose();
    _lastNameCtrl.dispose();
    _usernameCtrl.dispose();
    _bioCtrl.dispose();
    _cityCtrl.dispose();
    _countryCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final p = await _service.getProfile();
      _firstNameCtrl.text = p.firstName;
      _lastNameCtrl.text = p.lastName;
      _usernameCtrl.text = p.username ?? '';
      _bioCtrl.text = p.about ?? '';
      _cityCtrl.text = p.city ?? '';
      _countryCtrl.text = p.country ?? '';
      if (mounted) setState(() { _profile = p; _loading = false; });
    } catch (_) {
      if (mounted) setState(() { _error = 'Erreur de chargement'; _loading = false; });
    }
  }

  Future<void> _pickAvatar() async {
    final file = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (file != null) setState(() => _newAvatar = File(file.path));
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    try {
      await _service.updateProfile(
        firstName: _firstNameCtrl.text.trim(),
        lastName: _lastNameCtrl.text.trim(),
        username: _usernameCtrl.text.trim(),
        about: _bioCtrl.text.trim(),
        city: _cityCtrl.text.trim(),
        country: _countryCtrl.text.trim(),
        avatar: _newAvatar,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profil mis à jour'), backgroundColor: AppColors.primary),
        );
        Navigator.pop(context, true);
      }
    } on Object catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(_errorMessage(e)), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  String _errorMessage(Object e) {
    if (e is DioException) {
      final msg = (e.response?.data as Map?)?['message'];
      if (msg != null) return msg.toString();
    }
    return 'Erreur lors de l\'enregistrement';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Modifier le profil'),
        actions: [
          if (_saving)
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
            )
          else if (_profile != null)
            TextButton(
              onPressed: _save,
              child: const Text('Enregistrer',
                  style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Center(
                      child: GestureDetector(
                        onTap: _pickAvatar,
                        child: Stack(
                          children: [
                            CircleAvatar(
                              radius: 48,
                              backgroundColor: AppColors.inputFill,
                              backgroundImage: _newAvatar != null
                                  ? FileImage(_newAvatar!)
                                  : (_profile?.avatarUrl != null
                                      ? CachedNetworkImageProvider(_profile!.avatarUrl!)
                                      : null) as ImageProvider?,
                              child: (_newAvatar == null && _profile?.avatarUrl == null)
                                  ? const Icon(Icons.person, size: 56, color: AppColors.textSecondary)
                                  : null,
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
                    ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        Expanded(child: _Field(label: 'Prénom', controller: _firstNameCtrl)),
                        const SizedBox(width: 12),
                        Expanded(child: _Field(label: 'Nom', controller: _lastNameCtrl)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    _Field(label: 'Nom d\'utilisateur', controller: _usernameCtrl, hint: 'unique'),
                    const SizedBox(height: 16),
                    _Field(label: 'Bio', controller: _bioCtrl, maxLines: 3, hint: 'Parlez de vous...'),
                    const SizedBox(height: 16),
                    _Field(label: 'Ville', controller: _cityCtrl, hint: 'Ex: Casablanca'),
                    const SizedBox(height: 16),
                    _Field(label: 'Pays', controller: _countryCtrl, hint: 'Ex: Maroc'),
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
