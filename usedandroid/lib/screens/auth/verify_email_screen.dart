import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import '../../services/auth_service.dart';
import '../../theme/app_colors.dart';

class VerifyEmailScreen extends StatefulWidget {
  const VerifyEmailScreen({super.key});

  @override
  State<VerifyEmailScreen> createState() => _VerifyEmailScreenState();
}

class _VerifyEmailScreenState extends State<VerifyEmailScreen> {
  final _codeCtrl = TextEditingController();
  bool _loading = false;
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _sendCode(initial: true);
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    super.dispose();
  }

  Future<void> _sendCode({bool initial = false}) async {
    setState(() => _sending = true);
    try {
      await AuthService().sendEmailCode();
      if (mounted && !initial) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Un nouveau code a été envoyé.')),
        );
      }
    } on DioException {
      if (mounted) _showError('Impossible d\'envoyer le code. Réessayez.');
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  Future<void> _verify() async {
    final code = _codeCtrl.text.trim();
    if (code.length != 4) {
      _showError('Entrez le code à 4 chiffres.');
      return;
    }
    setState(() => _loading = true);
    try {
      await AuthService().verifyEmailCode(code);
      if (mounted) context.go('/verify-phone');
    } on DioException catch (e) {
      final msg = e.response?.data is Map
          ? (e.response!.data['message'] ?? 'Code invalide ou expiré.')
          : 'Code invalide ou expiré.';
      if (mounted) _showError(msg.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(backgroundColor: AppColors.surface, elevation: 0),
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
                  child: const Icon(Icons.mark_email_read_outlined, color: AppColors.primary, size: 34),
                ),
              ),
              const SizedBox(height: 20),
              const Text('Vérifiez votre e-mail',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              const Text(
                'Entrez le code à 4 chiffres envoyé à votre adresse e-mail.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.textSecondary),
              ),
              const SizedBox(height: 28),
              TextField(
                controller: _codeCtrl,
                keyboardType: TextInputType.number,
                textAlign: TextAlign.center,
                maxLength: 4,
                style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w700, letterSpacing: 12),
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: const InputDecoration(counterText: '', hintText: '––––'),
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
                onPressed: _sending ? null : () => _sendCode(),
                child: Text(_sending ? 'Envoi…' : 'Renvoyer le code'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
