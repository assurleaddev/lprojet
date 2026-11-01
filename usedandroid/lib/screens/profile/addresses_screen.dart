import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../services/checkout_service.dart';
import '../../theme/app_colors.dart';

/// Settings → Livraison: the user's delivery addresses (add / delete).
class AddressesScreen extends StatefulWidget {
  const AddressesScreen({super.key});

  @override
  State<AddressesScreen> createState() => _AddressesScreenState();
}

class _AddressesScreenState extends State<AddressesScreen> {
  final _service = CheckoutService();
  List<CheckoutAddress> _addresses = [];
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _failed = false; });
    try {
      final list = await _service.getAddresses();
      if (mounted) setState(() { _addresses = list; _loading = false; });
    } catch (_) {
      if (mounted) setState(() { _loading = false; _failed = true; });
    }
  }

  Future<void> _add() async {
    final added = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => const _AddressForm(),
    );
    if (added == true) _load();
  }

  Future<void> _delete(CheckoutAddress a) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        title: const Text("Supprimer l'adresse"),
        content: Text(a.oneLine),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx, false), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () => Navigator.pop(dialogCtx, true),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await _service.deleteAddress(a.id);
      _load();
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Suppression impossible')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Livraison')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _add,
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Ajouter une adresse'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: (_failed || _addresses.isEmpty)
                  ? ListView(
                      children: [
                        const SizedBox(height: 140),
                        Icon(_failed ? Icons.wifi_off : Icons.location_off_outlined,
                            size: 48, color: AppColors.textSecondary),
                        const SizedBox(height: 12),
                        Text(
                          _failed ? 'Impossible de charger vos adresses' : 'Aucune adresse enregistrée',
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: AppColors.textSecondary),
                        ),
                        if (_failed) Center(child: TextButton(onPressed: _load, child: const Text('Réessayer'))),
                      ],
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.only(bottom: 96),
                      itemCount: _addresses.length,
                      separatorBuilder: (_, __) => const Divider(height: 1),
                      itemBuilder: (_, i) {
                        final a = _addresses[i];
                        return ListTile(
                          leading: const Icon(Icons.location_on_outlined, color: AppColors.textPrimary),
                          title: Text(a.fullName, style: const TextStyle(fontWeight: FontWeight.w600)),
                          subtitle: Text(a.oneLine, style: const TextStyle(color: AppColors.textSecondary)),
                          trailing: IconButton(
                            icon: const Icon(Icons.delete_outline, color: Colors.red),
                            tooltip: 'Supprimer',
                            onPressed: () => _delete(a),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}

class _AddressForm extends StatefulWidget {
  const _AddressForm();

  @override
  State<_AddressForm> createState() => _AddressFormState();
}

class _AddressFormState extends State<_AddressForm> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _line1 = TextEditingController();
  final _line2 = TextEditingController();
  final _city = TextEditingController();
  final _postcode = TextEditingController();
  final _country = TextEditingController(text: 'Maroc');
  bool _saving = false;

  @override
  void dispose() {
    for (final c in [_name, _line1, _line2, _city, _postcode, _country]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      await CheckoutService().addAddress(
        country: _country.text.trim(),
        fullName: _name.text.trim(),
        addressLine1: _line1.text.trim(),
        addressLine2: _line2.text.trim(),
        city: _city.text.trim(),
        postcode: _postcode.text.trim(),
      );
      if (mounted) Navigator.pop(context, true);
    } on DioException catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      final msg = (e.response?.data as Map?)?['message'] ?? "Impossible d'enregistrer l'adresse";
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
    }
  }

  String? _required(String? v) => (v == null || v.trim().isEmpty) ? 'Champ requis' : null;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(16, 16, 16, 16 + MediaQuery.of(context).viewInsets.bottom),
      child: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Nouvelle adresse', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              TextFormField(controller: _name, decoration: const InputDecoration(labelText: 'Nom complet'), validator: _required),
              const SizedBox(height: 8),
              TextFormField(controller: _line1, decoration: const InputDecoration(labelText: 'Adresse'), validator: _required),
              const SizedBox(height: 8),
              TextFormField(controller: _line2, decoration: const InputDecoration(labelText: "Complément d'adresse (optionnel)")),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(controller: _city, decoration: const InputDecoration(labelText: 'Ville'), validator: _required),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextFormField(
                      controller: _postcode,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Code postal'),
                      validator: _required,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              TextFormField(controller: _country, decoration: const InputDecoration(labelText: 'Pays'), validator: _required),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _saving ? null : _save,
                child: _saving
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Enregistrer'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
