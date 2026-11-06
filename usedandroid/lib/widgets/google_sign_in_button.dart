import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../theme/app_colors.dart';

/// "Continuer avec Google": runs Google Sign-In and opens a USED session.
/// [onSignedIn] gets the signed-in user (to route to verification if needed).
class GoogleSignInButton extends StatefulWidget {
  final ValueChanged<AuthUser> onSignedIn;

  const GoogleSignInButton({super.key, required this.onSignedIn});

  @override
  State<GoogleSignInButton> createState() => _GoogleSignInButtonState();
}

class _GoogleSignInButtonState extends State<GoogleSignInButton> {
  bool _busy = false;

  Future<void> _signIn() async {
    setState(() => _busy = true);
    try {
      final user = await AuthService().loginWithGoogle();
      if (user != null && mounted) widget.onSignedIn(user);
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Connexion avec Google impossible';
      _error(msg.toString());
    } catch (e) {
      debugPrint('Google sign-in failed: $e');
      _error('Connexion avec Google impossible. Réessayez.');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _error(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: Colors.red));
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton(
        style: OutlinedButton.styleFrom(
          padding: const EdgeInsets.symmetric(vertical: 14),
          side: const BorderSide(color: AppColors.border),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
        onPressed: _busy ? null : _signIn,
        child: _busy
            ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
            : const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _GoogleIcon(),
                  SizedBox(width: 10),
                  Text('Continuer avec Google',
                      style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600)),
                ],
              ),
      ),
    );
  }
}

class _GoogleIcon extends StatelessWidget {
  const _GoogleIcon();

  @override
  Widget build(BuildContext context) {
    return const SizedBox(
      width: 20,
      height: 20,
      child: Center(
        child: Text('G', style: TextStyle(color: Color(0xFF4285F4), fontWeight: FontWeight.w700, fontSize: 16)),
      ),
    );
  }
}
