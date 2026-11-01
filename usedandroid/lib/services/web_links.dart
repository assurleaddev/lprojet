import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/app_config.dart';

/// Website pages the app links to.
class WebLinks {
  const WebLinks._();

  static const privacyPolicy = '/politique-de-confidentialite';
  static const terms = '/cgu';
  static const faq = '/faq';
  static const paymentsSettings = '/settings/payments';
  static const bundleDiscountsSettings = '/settings/bundle-discounts';

  /// Opens [path] on usedis.com in the phone's browser.
  static Future<void> open(BuildContext context, String path) async {
    final messenger = ScaffoldMessenger.of(context);
    final ok = await launchUrl(Uri.parse('${AppConfig.siteUrl}$path'), mode: LaunchMode.externalApplication)
        .catchError((_) => false);
    if (!ok) {
      messenger.showSnackBar(const SnackBar(content: Text("Impossible d'ouvrir la page")));
    }
  }
}
