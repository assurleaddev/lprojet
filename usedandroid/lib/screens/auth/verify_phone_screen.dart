import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import '../../services/auth_service.dart';
import '../../services/auth_state.dart';
import '../../theme/app_colors.dart';

class VerifyPhoneScreen extends StatefulWidget {
  const VerifyPhoneScreen({super.key});

  @override
  State<VerifyPhoneScreen> createState() => _VerifyPhoneScreenState();
}

class _VerifyPhoneScreenState extends State<VerifyPhoneScreen> {
  static const _codes = ['+212', '+33', '+1', '+44', '+34', '+49'];

  int _step = 0; // 0 = enter number, 1 = enter code
  String _countryCode = '+212';
  final _phoneCtrl = TextEditingController();
  final _codeCtrl = TextEditingController();
  bool _loading = false;

  @override
  void dispose() {
    _phoneCtrl.dispose();
    _codeCtrl.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    final phone = _phoneCtrl.text.replaceAll(RegExp(r'[^0-9]'), '');
    if (phone.length < 8) {
      _showError('Entrez un numéro de téléphone valide.');
      return;
    }
    setState(() => _loading = true);
    try {
      await AuthService().sendPhoneCode(_countryCode, phone);
      if (mounted) setState(() => _step = 1);
    } on DioException catch (e) {
      if (mounted) _showError(_msg(e, 'Impossible d\'envoyer le code.'));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _verify() async {
    final code = _codeCtrl.text.trim();
    if (code.length != 6) {
      _showError('Entrez le code à 6 chiffres.');
      return;
    }
    setState(() => _loading = true);
    try {
      await AuthService().verifyPhoneCode(code);
      AuthState.instance.markPhoneVerified();
      if (mounted) context.go('/');
    } on DioException catch (e) {
      if (mounted) _showError(_msg(e, 'Code invalide ou expiré.'));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _msg(DioException e, String fallback) =>
      (e.response?.data is Map ? (e.response!.data['message'] ?? fallback) : fallback).toString();

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red),
    );
  }

  Future<void> _logout() async {
    await AuthService().logout();
    if (mounted) context.go('/login');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        automaticallyImplyLeading: false,
        actions: [
          TextButton(
            onPressed: _logout,
            child: const Text('Se déconnecter', style: TextStyle(color: AppColors.textSecondary)),
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 12),
              Center(
                child: Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.phone_iphone, color: AppColors.primary, size: 34),
                ),
              ),
              const SizedBox(height: 20),
              Text(_step == 0 ? 'Vérifiez votre numéro' : 'Entrez le code',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              Text(
                _step == 0
                    ? 'Nous vous enverrons un code par WhatsApp.'
                    : 'Code à 6 chiffres envoyé au $_countryCode ${_phoneCtrl.text}.',
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              const SizedBox(height: 28),
              if (_step == 0) ..._entry() else ..._code(),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _entry() => [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              width: 110,
              child: DropdownButtonFormField<String>(
                initialValue: _countryCode,
                decoration: const InputDecoration(labelText: 'Indicatif'),
                items: _codes.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                onChanged: (v) => setState(() => _countryCode = v ?? '+212'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: TextField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: const InputDecoration(labelText: 'Numéro', prefixIcon: Icon(Icons.phone_outlined)),
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
        ElevatedButton(
          onPressed: _loading ? null : _send,
          child: _loading
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Envoyer le code'),
        ),
      ];

  List<Widget> _code() => [
        TextField(
          controller: _codeCtrl,
          keyboardType: TextInputType.number,
          textAlign: TextAlign.center,
          maxLength: 6,
          style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w700, letterSpacing: 10),
          inputFormatters: [FilteringTextInputFormatter.digitsOnly],
          decoration: const InputDecoration(counterText: '', hintText: '––––––'),
        ),
        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: _loading ? null : _verify,
          child: _loading
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Vérifier'),
        ),
        const SizedBox(height: 8),
        TextButton(
          onPressed: _loading ? null : () => setState(() => _step = 0),
          child: const Text('Modifier le numéro'),
        ),
      ];
}
