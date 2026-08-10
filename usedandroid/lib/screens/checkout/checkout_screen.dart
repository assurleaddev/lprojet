import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../services/checkout_service.dart';
import '../../theme/app_colors.dart';

class CheckoutScreen extends StatefulWidget {
  final int? productId;
  final int? offerId;

  const CheckoutScreen({super.key, this.productId, this.offerId});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _service = CheckoutService();

  CheckoutInit? _data;
  bool _loading = true;
  String? _error;
  bool _submitting = false;

  int? _addressId;
  int? _shippingId;
  bool _wantsVerification = false;
  String _payment = 'cod'; // matches web default

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await _service.getInit(productId: widget.productId, offerId: widget.offerId);
      setState(() {
        _data = data;
        _addressId = data.addresses.isNotEmpty ? data.addresses.first.id : null;
        _shippingId = data.shippingOptions.isNotEmpty ? data.shippingOptions.first.id : null;
        _loading = false;
      });
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Erreur de chargement';
      setState(() { _error = msg.toString(); _loading = false; });
    } catch (_) {
      setState(() { _error = 'Erreur de chargement'; _loading = false; });
    }
  }

  bool get _verificationApplies =>
      _data != null && _data!.item.price >= _data!.fees.verificationThreshold;

  double get _shippingCost {
    final opt = _data?.shippingOptions.where((o) => o.id == _shippingId).firstOrNull;
    return opt?.price ?? (_data?.fees.defaultShipping ?? 0);
  }

  double get _protection => _data == null ? 0 : _data!.fees.protectionFor(_data!.item.price);
  double get _verification =>
      (_wantsVerification && _verificationApplies) ? (_data?.fees.verificationFee ?? 0) : 0;
  double get _total => _data == null ? 0 : _data!.item.price + _protection + _shippingCost + _verification;

  bool get _walletOk => (_data?.walletBalance ?? 0) >= _total;
  bool get _canPlace =>
      _addressId != null &&
      _shippingId != null &&
      (_payment != 'wallet' || _walletOk);

  Future<void> _placeOrder() async {
    setState(() => _submitting = true);
    try {
      await _service.submit(
        productId: widget.productId,
        offerId: widget.offerId,
        addressId: _addressId!,
        shippingOptionId: _shippingId!,
        paymentMethod: _payment,
        wantsVerification: _wantsVerification && _verificationApplies,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Commande passée ! Retrouvez-la dans vos messages.'),
            backgroundColor: AppColors.primary,
          ),
        );
        Navigator.of(context).pop(true);
      }
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Erreur lors du paiement';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg.toString()), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF7F8FA),
      appBar: AppBar(title: const Text('Commande')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _ErrorState(message: _error!, onRetry: _load)
              : _content(),
      bottomNavigationBar: (_loading || _error != null) ? null : _bottomBar(),
    );
  }

  Widget _content() {
    final d = _data!;
    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        _ItemCard(item: d.item),
        const SizedBox(height: 12),
        _Section(
          title: 'Adresse de livraison',
          child: d.addresses.isEmpty
              ? _AddAddressPrompt(onAdd: _addAddress)
              : Column(
                  children: [
                    ...d.addresses.map((a) => RadioListTile<int>(
                          value: a.id,
                          groupValue: _addressId,
                          onChanged: (v) => setState(() => _addressId = v),
                          activeColor: AppColors.primary,
                          contentPadding: EdgeInsets.zero,
                          title: Text(a.fullName, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                          subtitle: Text(a.oneLine, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                        )),
                    Align(
                      alignment: Alignment.centerLeft,
                      child: TextButton.icon(
                        onPressed: _addAddress,
                        icon: const Icon(Icons.add, size: 18),
                        label: const Text('Ajouter une adresse'),
                      ),
                    ),
                  ],
                ),
        ),
        const SizedBox(height: 12),
        _Section(
          title: 'Mode de livraison',
          child: Column(
            children: [
              _shippingGroup('Point relais', 'drop_off'),
              _shippingGroup('Livraison à domicile', 'home_pickup'),
            ],
          ),
        ),
        if (_verificationApplies) ...[
          const SizedBox(height: 12),
          _Section(
            title: 'Vérification',
            child: CheckboxListTile(
              value: _wantsVerification,
              onChanged: (v) => setState(() => _wantsVerification = v ?? false),
              activeColor: AppColors.primary,
              contentPadding: EdgeInsets.zero,
              title: Text('Vérifier l\'article (+${d.fees.verificationFee.toStringAsFixed(2)} MAD)',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
              subtitle: const Text(
                'Un agent inspecte l\'article et confirme son état avant l\'envoi.',
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ),
          ),
        ],
        const SizedBox(height: 12),
        _Section(
          title: 'Moyen de paiement',
          child: Column(
            children: [
              _paymentTile(
                value: 'cod',
                icon: Icons.payments_outlined,
                title: 'Paiement à la livraison',
              ),
              _paymentTile(
                value: 'wallet',
                icon: Icons.account_balance_wallet_outlined,
                title: 'Portefeuille',
                subtitle: 'Solde : ${d.walletBalance.toStringAsFixed(2)} MAD'
                    '${!_walletOk ? '  (Solde insuffisant)' : ''}',
                disabled: !_walletOk,
                subtitleColor: !_walletOk ? Colors.red : AppColors.textSecondary,
              ),
              _paymentTile(
                value: 'card',
                icon: Icons.credit_card,
                title: 'Carte bancaire',
                subtitle: 'Bientôt disponible',
                disabled: true,
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        _Section(title: 'Récapitulatif', child: _summary()),
        const SizedBox(height: 12),
      ],
    );
  }

  Widget _shippingGroup(String label, String type) {
    final opts = _data!.shippingOptions.where((o) => o.type == type).toList();
    if (opts.isEmpty) return const SizedBox.shrink();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(top: 6, bottom: 2),
          child: Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
        ),
        ...opts.map((o) => RadioListTile<int>(
              value: o.id,
              groupValue: _shippingId,
              onChanged: (v) => setState(() => _shippingId = v),
              activeColor: AppColors.primary,
              contentPadding: EdgeInsets.zero,
              title: Text(o.label, style: const TextStyle(fontSize: 14)),
              secondary: Text('${o.price.toStringAsFixed(2)} MAD', style: const TextStyle(fontWeight: FontWeight.w600)),
            )),
      ],
    );
  }

  Widget _paymentTile({
    required String value,
    required IconData icon,
    required String title,
    String? subtitle,
    bool disabled = false,
    Color? subtitleColor,
  }) {
    return RadioListTile<String>(
      value: value,
      groupValue: _payment,
      onChanged: disabled ? null : (v) => setState(() => _payment = v!),
      activeColor: AppColors.primary,
      contentPadding: EdgeInsets.zero,
      secondary: Icon(icon, color: disabled ? AppColors.textSecondary : AppColors.textPrimary),
      title: Text(title, style: TextStyle(fontSize: 14, color: disabled ? AppColors.textSecondary : AppColors.textPrimary)),
      subtitle: subtitle != null
          ? Text(subtitle, style: TextStyle(fontSize: 12, color: subtitleColor ?? AppColors.textSecondary))
          : null,
    );
  }

  Widget _summary() {
    return Column(
      children: [
        _summaryRow('Article', _data!.item.price),
        _summaryRow('Protection acheteur', _protection),
        _summaryRow('Livraison', _shippingCost),
        if (_verification > 0) _summaryRow('Vérification', _verification),
        const Divider(height: 20),
        _summaryRow('Total', _total, bold: true),
      ],
    );
  }

  Widget _summaryRow(String label, double value, {bool bold = false}) {
    final style = TextStyle(
      fontSize: bold ? 16 : 14,
      fontWeight: bold ? FontWeight.w800 : FontWeight.w400,
      color: bold ? AppColors.textPrimary : AppColors.textSecondary,
    );
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: style),
          Text('${value.toStringAsFixed(2)} MAD', style: style.copyWith(color: bold ? AppColors.primary : AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _bottomBar() {
    return Container(
      padding: EdgeInsets.fromLTRB(16, 10, 16, 10 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Total', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              Text('${_total.toStringAsFixed(2)} MAD',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.primary)),
            ],
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ElevatedButton(
              onPressed: (_canPlace && !_submitting) ? _placeOrder : null,
              style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
              child: _submitting
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Passer la commande', style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _addAddress() async {
    final added = await showModalBottomSheet<CheckoutAddress>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => _AddAddressSheet(service: _service),
    );
    if (added != null && mounted) {
      setState(() {
        _data = CheckoutInit(
          item: _data!.item,
          addresses: [added, ..._data!.addresses],
          shippingOptions: _data!.shippingOptions,
          walletBalance: _data!.walletBalance,
          fees: _data!.fees,
        );
        _addressId = added.id;
      });
    }
  }
}

// ── item card ─────────────────────────────────────────────────────────────────

class _ItemCard extends StatelessWidget {
  final CheckoutItem item;
  const _ItemCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: SizedBox(
              width: 64,
              height: 64,
              child: item.image == null
                  ? Container(color: AppColors.inputFill, child: const Icon(Icons.image_not_supported, color: AppColors.textSecondary))
                  : CachedNetworkImage(
                      imageUrl: item.image!,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: AppColors.inputFill),
                      errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                    ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.title, maxLines: 1, overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                if (item.brand != null || item.size != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    [if (item.brand != null) item.brand!, if (item.size != null) 'Taille ${item.size}'].join(' · '),
                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                  ),
                ],
                const SizedBox(height: 4),
                Text('${item.price.toStringAsFixed(2)} MAD',
                    style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.primary, fontSize: 15)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _AddAddressPrompt extends StatelessWidget {
  final VoidCallback onAdd;
  const _AddAddressPrompt({required this.onAdd});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Aucune adresse enregistrée.', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: onAdd,
          icon: const Icon(Icons.add, size: 18),
          label: const Text('Ajouter une adresse'),
        ),
      ],
    );
  }
}

class _AddAddressSheet extends StatefulWidget {
  final CheckoutService service;
  const _AddAddressSheet({required this.service});

  @override
  State<_AddAddressSheet> createState() => _AddAddressSheetState();
}

class _AddAddressSheetState extends State<_AddAddressSheet> {
  final _fullName = TextEditingController();
  final _line1 = TextEditingController();
  final _line2 = TextEditingController();
  final _city = TextEditingController();
  final _postcode = TextEditingController();
  final _country = TextEditingController(text: 'Maroc');
  bool _saving = false;

  @override
  void dispose() {
    for (final c in [_fullName, _line1, _line2, _city, _postcode, _country]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    if ([_fullName, _line1, _city, _postcode, _country].any((c) => c.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Veuillez remplir tous les champs obligatoires.')),
      );
      return;
    }
    setState(() => _saving = true);
    try {
      final address = await widget.service.addAddress(
        country: _country.text.trim(),
        fullName: _fullName.text.trim(),
        addressLine1: _line1.text.trim(),
        addressLine2: _line2.text.trim(),
        city: _city.text.trim(),
        postcode: _postcode.text.trim(),
      );
      if (mounted) Navigator.pop(context, address);
    } on DioException catch (e) {
      setState(() => _saving = false);
      final msg = (e.response?.data as Map?)?['message'] ?? 'Erreur';
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 16, right: 16, top: 16,
        bottom: MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Nouvelle adresse', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            const SizedBox(height: 12),
            _field(_fullName, 'Nom complet'),
            _field(_line1, 'Adresse'),
            _field(_line2, 'Complément (optionnel)'),
            Row(children: [
              Expanded(child: _field(_city, 'Ville')),
              const SizedBox(width: 12),
              Expanded(child: _field(_postcode, 'Code postal')),
            ]),
            _field(_country, 'Pays'),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                child: _saving
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Enregistrer'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _field(TextEditingController c, String label) => Padding(
        padding: const EdgeInsets.only(bottom: 10),
        child: TextField(
          controller: c,
          decoration: InputDecoration(labelText: label, isDense: true, border: const OutlineInputBorder()),
        ),
      );
}

// ── shared ────────────────────────────────────────────────────────────────────

class _Section extends StatelessWidget {
  final String title;
  final Widget child;
  const _Section({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
          const SizedBox(height: 6),
          child,
        ],
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorState({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 48, color: AppColors.textSecondary),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(message, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.textSecondary)),
          ),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: onRetry, child: const Text('Réessayer')),
        ],
      ),
    );
  }
}
