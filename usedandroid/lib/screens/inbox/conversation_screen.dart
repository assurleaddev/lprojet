import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../services/inbox_service.dart';
import '../../theme/app_colors.dart';

// ── carriers ──────────────────────────────────────────────────────────────────

const _carriers = {
  'tawsil': 'Tawsil',
  'ozon_express': 'Ozon Express',
  'sendit': 'Sendit',
  'ortalog': 'Ortalog',
  'nearya': 'Nearya',
  'ameex': 'Ameex',
  'cathedis': 'Cathedis',
  'speedaf': 'Speedaf',
  'amana': 'Amana',
  'olivraison': 'Olivraison',
  'coliaty': 'Coliaty',
  'digylog': 'Digylog',
};

// ── screen ────────────────────────────────────────────────────────────────────

class ConversationScreen extends StatefulWidget {
  final ApiConversation conversation;
  const ConversationScreen({super.key, required this.conversation});

  @override
  State<ConversationScreen> createState() => _ConversationScreenState();
}

class _ConversationScreenState extends State<ConversationScreen> {
  final _service = InboxService();
  final _scrollController = ScrollController();
  final _inputController = TextEditingController();
  final _focusNode = FocusNode();

  List<ApiMessage> _messages = [];
  bool _loading = true;
  bool _sending = false;
  bool _actionLoading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _inputController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final msgs = await _service.getMessages(widget.conversation.id);
      // ignore: avoid_print
      debugPrint('[Chat] Loaded ${msgs.length} messages, types: ${msgs.map((m) => m.type).toSet()}');
      if (mounted) {
        setState(() {
          _messages = msgs;
          _loading = false;
          _error = null;
        });
        _scrollToBottom();
      }
    } on DioException catch (e) {
      if (mounted) setState(() { _error = e.message; _loading = false; });
    }
  }

  void _scrollToBottom({bool animate = false}) {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        if (animate) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOut,
          );
        } else {
          _scrollController.jumpTo(_scrollController.position.maxScrollExtent);
        }
      }
    });
  }

  Future<void> _send() async {
    final text = _inputController.text.trim();
    if (text.isEmpty || _sending) return;
    _inputController.clear();
    setState(() => _sending = true);
    try {
      final msg = await _service.sendMessage(widget.conversation.id, text);
      if (mounted) {
        setState(() { _messages.add(msg); _sending = false; });
        _scrollToBottom(animate: true);
      }
    } on DioException {
      if (mounted) {
        setState(() => _sending = false);
        _showError('Erreur lors de l\'envoi');
        _inputController.text = text;
      }
    }
  }

  Future<void> _runAction(Future<void> Function() action) async {
    if (_actionLoading) return;
    setState(() => _actionLoading = true);
    try {
      await action();
      await _load();
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Une erreur est survenue';
      if (mounted) _showError(msg.toString());
    } finally {
      if (mounted) setState(() => _actionLoading = false);
    }
  }

  void _showError(String msg) =>
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));

  // ── offer actions ────────────────────────────────────────────────────────────

  void _acceptOffer(int offerId) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Accepter l\'offre'),
        content: const Text('Voulez-vous accepter cette offre ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _runAction(() => _service.acceptOffer(offerId));
            },
            child: const Text('Accepter'),
          ),
        ],
      ),
    );
  }

  void _rejectOffer(int offerId) {
    final reasonCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Refuser l\'offre'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Voulez-vous refuser cette offre ?'),
            const SizedBox(height: 12),
            TextField(
              controller: reasonCtrl,
              decoration: const InputDecoration(
                hintText: 'Raison (optionnelle)',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () {
              final reason = reasonCtrl.text.trim().isEmpty ? null : reasonCtrl.text.trim();
              Navigator.pop(context);
              _runAction(() => _service.rejectOffer(offerId, reason: reason));
            },
            child: const Text('Refuser'),
          ),
        ],
      ),
    );
  }

  void _withdrawOffer(int offerId) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Retirer l\'offre'),
        content: const Text('Voulez-vous retirer votre offre ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () {
              Navigator.pop(context);
              _runAction(() => _service.withdrawOffer(offerId));
            },
            child: const Text('Retirer'),
          ),
        ],
      ),
    );
  }

  void _shipOrder(int orderId) {
    String? selectedCarrier;
    final trackingCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheet) => Padding(
          padding: EdgeInsets.only(
            left: 20, right: 20, top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Marquer comme expédié',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(
                  labelText: 'Transporteur',
                  border: OutlineInputBorder(),
                ),
                value: selectedCarrier,
                items: _carriers.entries
                    .map((e) => DropdownMenuItem(value: e.key, child: Text(e.value)))
                    .toList(),
                onChanged: (v) => setSheet(() => selectedCarrier = v),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: trackingCtrl,
                decoration: const InputDecoration(
                  labelText: 'Numéro de suivi (optionnel)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: selectedCarrier == null
                      ? null
                      : () {
                          final carrier = selectedCarrier!;
                          final tracking = trackingCtrl.text.trim().isEmpty
                              ? null
                              : trackingCtrl.text.trim();
                          Navigator.pop(ctx);
                          _runAction(() => _service.shipOrder(
                                orderId,
                                carrier: carrier,
                                trackingCode: tracking,
                              ));
                        },
                  child: const Text('Confirmer l\'expédition'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _receiveOrder(int orderId) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Confirmer la réception'),
        content: const Text(
            'Confirmez-vous avoir reçu votre commande ? Les fonds seront libérés au vendeur.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _runAction(() => _service.receiveOrder(orderId));
            },
            child: const Text('Confirmer'),
          ),
        ],
      ),
    );
  }

  // ── build ─────────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final conv = widget.conversation;
    return Scaffold(
      backgroundColor: const Color(0xFFF0F2F5),
      appBar: AppBar(
        titleSpacing: 0,
        title: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: AppColors.inputFill,
              backgroundImage: conv.otherUserAvatar != null
                  ? NetworkImage(conv.otherUserAvatar!)
                  : null,
              child: conv.otherUserAvatar == null
                  ? Text(
                      conv.otherUserName.isNotEmpty ? conv.otherUserName[0].toUpperCase() : '?',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                    )
                  : null,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(conv.otherUserName,
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                      overflow: TextOverflow.ellipsis),
                  if (conv.productTitle != null)
                    Text(conv.productTitle!,
                        style: const TextStyle(fontSize: 11, color: AppColors.primary),
                        overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
          ],
        ),
        actions: [
          if (_actionLoading)
            const Padding(
              padding: EdgeInsets.all(12),
              child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
            ),
        ],
      ),
      body: Column(
        children: [
          Expanded(child: _buildMessageList()),
          _buildInputBar(),
        ],
      ),
    );
  }

  Widget _buildMessageList() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppColors.textSecondary),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 12),
            ElevatedButton(onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (_messages.isEmpty) {
      return const Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Icon(Icons.chat_bubble_outline, size: 56, color: AppColors.textSecondary),
          SizedBox(height: 12),
          Text('Commencez la conversation',
              style: TextStyle(color: AppColors.textSecondary, fontSize: 15)),
        ]),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        itemCount: _messages.length,
        itemBuilder: (_, i) {
          final msg = _messages[i];
          final showTime = i == 0 ||
              _messages[i].createdAt.substring(0, 16) !=
                  _messages[i - 1].createdAt.substring(0, 16);
          return Column(
            children: [
              if (showTime && i > 0) _TimeLabel(label: msg.sentAt),
              _buildMessage(msg),
            ],
          );
        },
      ),
    );
  }

  Widget _buildMessage(ApiMessage msg) {
    debugPrint('[MSG] type="${msg.type}" offer=${msg.offer?.id} order=${msg.order?.id}');
    switch (msg.type) {
      case 'offer_made':
      case 'offer_countered':
        return _OfferCard(
          msg: msg,
          onAccept: msg.offer != null && msg.offer!.isSeller &&
                  (msg.offer!.isPending || msg.offer!.isAwaitingBuyer)
              ? () => _acceptOffer(msg.offer!.id)
              : null,
          onReject: msg.offer != null &&
                  ((msg.offer!.isSeller && msg.offer!.isPending) ||
                      (msg.offer!.isBuyer && msg.offer!.isAwaitingBuyer))
              ? () => _rejectOffer(msg.offer!.id)
              : null,
          onWithdraw: msg.offer != null && msg.offer!.isBuyer && msg.offer!.isPending
              ? () => _withdrawOffer(msg.offer!.id)
              : null,
        );

      case 'offer_accepted':
        return _StatusCard(
          icon: Icons.check_circle_outline,
          color: AppColors.greenBadge,
          title: 'Offre acceptée',
          subtitle: msg.offer != null ? '${msg.offer!.price.toStringAsFixed(2)} MAD' : msg.body,
          sentAt: msg.sentAt,
        );

      case 'offer_rejected':
        return _StatusCard(
          icon: Icons.cancel_outlined,
          color: Colors.red,
          title: 'Offre refusée',
          subtitle: msg.offer?.rejectionReason ?? msg.body,
          sentAt: msg.sentAt,
        );

      case 'offer_withdrawn':
        return _StatusCard(
          icon: Icons.undo,
          color: AppColors.textSecondary,
          title: 'Offre retirée',
          subtitle: msg.body,
          sentAt: msg.sentAt,
        );

      case 'offer_checkout_prompt':
        return _CheckoutPromptCard(msg: msg);

      case 'product_reserved':
        return _StatusCard(
          icon: Icons.lock_outline,
          color: AppColors.darkPurple,
          title: 'Article réservé',
          subtitle: msg.body,
          sentAt: msg.sentAt,
        );

      case 'item_sold':
        return _ItemSoldCard(
          msg: msg,
          onShip: msg.order != null && msg.order!.isSeller && msg.order!.isPending
              ? () => _shipOrder(msg.order!.id)
              : null,
        );

      case 'order_placed':
        return _OrderPlacedCard(msg: msg);

      case 'item_shipped':
        return _ItemShippedCard(
          msg: msg,
          onReceive: msg.order != null && msg.order!.isBuyer && msg.order!.isShipped
              ? () => _receiveOrder(msg.order!.id)
              : null,
        );

      case 'order_completed':
        return _StatusCard(
          icon: Icons.star_outline,
          color: Colors.amber,
          title: 'Transaction terminée',
          subtitle: 'L\'acheteur a confirmé la réception. Les fonds ont été libérés.',
          sentAt: msg.sentAt,
        );

      case 'order_cancelled':
        return _OrderCancelledCard(msg: msg);

      default:
        return _MessageBubble(message: msg);
    }
  }

  Widget _buildInputBar() {
    return Container(
      color: AppColors.surface,
      padding: EdgeInsets.only(
        left: 12,
        right: 8,
        top: 8,
        bottom: MediaQuery.of(context).viewInsets.bottom + 8,
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _inputController,
                focusNode: _focusNode,
                maxLines: null,
                textCapitalization: TextCapitalization.sentences,
                decoration: InputDecoration(
                  hintText: 'Message…',
                  hintStyle: const TextStyle(color: AppColors.textSecondary),
                  filled: true,
                  fillColor: AppColors.inputFill,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                ),
                onSubmitted: (_) => _send(),
              ),
            ),
            const SizedBox(width: 8),
            _sending
                ? const Padding(
                    padding: EdgeInsets.all(10),
                    child: SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(strokeWidth: 2)),
                  )
                : IconButton(
                    onPressed: _send,
                    icon: const Icon(Icons.send_rounded),
                    color: AppColors.primary,
                    iconSize: 28,
                  ),
          ],
        ),
      ),
    );
  }
}

// ── plain text bubble ─────────────────────────────────────────────────────────

class _MessageBubble extends StatelessWidget {
  final ApiMessage message;
  const _MessageBubble({required this.message});

  @override
  Widget build(BuildContext context) {
    final isMine = message.isMine;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: isMine ? MainAxisAlignment.end : MainAxisAlignment.start,
        children: [
          Flexible(
            child: Container(
              constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.72),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isMine ? AppColors.primary : AppColors.surface,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(18),
                  topRight: const Radius.circular(18),
                  bottomLeft: Radius.circular(isMine ? 18 : 4),
                  bottomRight: Radius.circular(isMine ? 4 : 18),
                ),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4)],
              ),
              child: Column(
                crossAxisAlignment:
                    isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                children: [
                  Text(
                    message.body,
                    style: TextStyle(
                      color: isMine ? Colors.white : AppColors.textPrimary,
                      fontSize: 14.5,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    message.sentAt,
                    style: TextStyle(
                      fontSize: 10,
                      color: isMine ? Colors.white.withOpacity(0.7) : AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── offer card ────────────────────────────────────────────────────────────────

class _OfferCard extends StatelessWidget {
  final ApiMessage msg;
  final VoidCallback? onAccept;
  final VoidCallback? onReject;
  final VoidCallback? onWithdraw;

  const _OfferCard({required this.msg, this.onAccept, this.onReject, this.onWithdraw});

  @override
  Widget build(BuildContext context) {
    final offer = msg.offer;
    final isCounter = msg.type == 'offer_countered';
    final statusLabel = _statusLabel(offer?.status);
    final statusColor = _statusColor(offer?.status);

    return _CardShell(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // header
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isCounter ? Icons.swap_horiz : Icons.local_offer_outlined,
                  color: AppColors.primary,
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isCounter ? 'Contre-offre' : 'Offre reçue',
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                ),
              ),
              if (statusLabel != null)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(statusLabel,
                      style: TextStyle(fontSize: 11, color: statusColor, fontWeight: FontWeight.w600)),
                ),
            ],
          ),
          const SizedBox(height: 12),
          // product row
          if (offer != null) ...[
            Row(
              children: [
                if (offer.productImage != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: CachedNetworkImage(
                      imageUrl: offer.productImage!,
                      width: 52,
                      height: 52,
                      fit: BoxFit.cover,
                      errorWidget: (_, __, ___) =>
                          Container(width: 52, height: 52, color: AppColors.inputFill),
                    ),
                  ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (offer.productTitle != null)
                        Text(offer.productTitle!,
                            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis),
                      Text(
                        '${offer.price.toStringAsFixed(2)} MAD',
                        style: const TextStyle(
                            fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.primary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            // action buttons — only when offer is still active
            if (onAccept != null || onReject != null || onWithdraw != null) ...[
              const SizedBox(height: 14),
              const Divider(height: 1),
              const SizedBox(height: 10),
              if (onAccept != null || onReject != null)
                Row(
                  children: [
                    if (onReject != null)
                      Expanded(
                        child: OutlinedButton(
                          onPressed: onReject,
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.red,
                            side: const BorderSide(color: Colors.red),
                          ),
                          child: const Text('Refuser'),
                        ),
                      ),
                    if (onReject != null && onAccept != null) const SizedBox(width: 10),
                    if (onAccept != null)
                      Expanded(
                        child: ElevatedButton(
                          onPressed: onAccept,
                          child: const Text('Accepter'),
                        ),
                      ),
                  ],
                ),
              if (onWithdraw != null)
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: onWithdraw,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                    ),
                    child: const Text('Retirer l\'offre'),
                  ),
                ),
            ],
          ],
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }

  String? _statusLabel(String? status) => switch (status) {
        'pending' => 'En attente',
        'awaiting_buyer' => 'Contre-offre',
        'accepted' => 'Acceptée',
        'rejected' => 'Refusée',
        'withdrawn' => 'Retirée',
        'expired' => 'Expirée',
        _ => null,
      };

  Color _statusColor(String? status) => switch (status) {
        'accepted' => AppColors.greenBadge,
        'rejected' || 'withdrawn' || 'expired' => Colors.red,
        _ => AppColors.primary,
      };
}

// ── checkout prompt card ──────────────────────────────────────────────────────

class _CheckoutPromptCard extends StatelessWidget {
  final ApiMessage msg;
  const _CheckoutPromptCard({required this.msg});

  @override
  Widget build(BuildContext context) {
    final offer = msg.offer;
    final isBuyer = offer?.isBuyer ?? true;

    return _CardShell(
      accentColor: AppColors.greenBadge,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.greenBadge.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle, color: AppColors.greenBadge, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isBuyer ? 'Offre acceptée !' : 'Lien de paiement envoyé',
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (offer != null) ...[
            Text('${offer.price.toStringAsFixed(2)} MAD',
                style: const TextStyle(
                    fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primary)),
            if (offer.productTitle != null)
              Text(offer.productTitle!,
                  style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          ],
          const SizedBox(height: 10),
          Text(
            isBuyer
                ? 'Finalisez votre achat sur le site web.'
                : 'L\'acheteur doit finaliser le paiement sur le site.',
            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
          ),
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }
}

// ── item sold card ────────────────────────────────────────────────────────────

class _ItemSoldCard extends StatelessWidget {
  final ApiMessage msg;
  final VoidCallback? onShip;
  const _ItemSoldCard({required this.msg, this.onShip});

  @override
  Widget build(BuildContext context) {
    final order = msg.order;
    return _CardShell(
      accentColor: AppColors.primary,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.sell, color: AppColors.primary, size: 20),
              ),
              const SizedBox(width: 10),
              const Text('Article vendu !',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 10),
          if (order != null) ...[
            Text('Vous gagnerez : ${order.payoutAmount.toStringAsFixed(2)} MAD',
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
            const SizedBox(height: 4),
            const Text(
              'Les fonds seront libérés dès que l\'acheteur confirme la réception.',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ],
          if (onShip != null) ...[
            const SizedBox(height: 14),
            const Divider(height: 1),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: onShip,
                icon: const Icon(Icons.local_shipping_outlined, size: 18),
                label: const Text('Marquer comme expédié'),
              ),
            ),
          ],
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }
}

// ── order placed card ─────────────────────────────────────────────────────────

class _OrderPlacedCard extends StatelessWidget {
  final ApiMessage msg;
  const _OrderPlacedCard({required this.msg});

  @override
  Widget build(BuildContext context) {
    return _CardShell(
      accentColor: AppColors.darkPurple,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.darkPurple.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.inventory_2_outlined, color: AppColors.darkPurple, size: 20),
              ),
              const SizedBox(width: 10),
              const Text('Commande passée !',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 10),
          if (msg.order != null) ...[
            Text('Montant : ${msg.order!.amount.toStringAsFixed(2)} MAD',
                style: const TextStyle(fontWeight: FontWeight.w600)),
            const SizedBox(height: 4),
          ],
          const Text(
            'Le vendeur va préparer votre commande. Vous serez notifié dès l\'expédition.',
            style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
          ),
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }
}

// ── item shipped card ─────────────────────────────────────────────────────────

class _ItemShippedCard extends StatelessWidget {
  final ApiMessage msg;
  final VoidCallback? onReceive;
  const _ItemShippedCard({required this.msg, this.onReceive});

  @override
  Widget build(BuildContext context) {
    final order = msg.order;
    final carrierLabel = order?.carrier != null ? (_carriers[order!.carrier] ?? order.carrier) : null;

    return _CardShell(
      accentColor: AppColors.darkPurple,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.darkPurple.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child:
                    const Icon(Icons.local_shipping, color: AppColors.darkPurple, size: 20),
              ),
              const SizedBox(width: 10),
              const Text('Article expédié !',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 10),
          if (carrierLabel != null) ...[
            _InfoRow(label: 'Transporteur', value: carrierLabel!),
            const SizedBox(height: 4),
          ],
          if (order?.trackingCode != null) ...[
            _InfoRow(label: 'Suivi', value: order!.trackingCode!),
            const SizedBox(height: 4),
          ],
          if (carrierLabel == null && order?.trackingCode == null)
            const Text('Le colis est en route.',
                style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          if (onReceive != null) ...[
            const SizedBox(height: 14),
            const Divider(height: 1),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: onReceive,
                icon: const Icon(Icons.check_circle_outline, size: 18),
                label: const Text('J\'ai bien reçu ma commande'),
              ),
            ),
          ],
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }
}

// ── order cancelled card ──────────────────────────────────────────────────────

class _OrderCancelledCard extends StatelessWidget {
  final ApiMessage msg;
  const _OrderCancelledCard({required this.msg});

  @override
  Widget build(BuildContext context) {
    return _CardShell(
      accentColor: Colors.red,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.cancel_outlined, color: Colors.red, size: 22),
              SizedBox(width: 8),
              Text('Commande annulée',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Colors.red)),
            ],
          ),
          const SizedBox(height: 8),
          Text(msg.body, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          _Timestamp(sentAt: msg.sentAt),
        ],
      ),
    );
  }
}

// ── generic status card ───────────────────────────────────────────────────────

class _StatusCard extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final String sentAt;

  const _StatusCard({
    required this.icon,
    required this.color,
    required this.title,
    required this.subtitle,
    required this.sentAt,
  });

  @override
  Widget build(BuildContext context) {
    return _CardShell(
      accentColor: color,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(title,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
              ),
            ],
          ),
          if (subtitle.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(subtitle, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          ],
          _Timestamp(sentAt: sentAt),
        ],
      ),
    );
  }
}

// ── shared card shell ─────────────────────────────────────────────────────────

class _CardShell extends StatelessWidget {
  final Widget child;
  final Color? accentColor;

  const _CardShell({required this.child, this.accentColor});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border(
            left: BorderSide(color: accentColor ?? AppColors.primary, width: 3),
          ),
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6, offset: const Offset(0, 2)),
          ],
        ),
        padding: const EdgeInsets.all(14),
        child: child,
      ),
    );
  }
}

// ── helpers ───────────────────────────────────────────────────────────────────

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  const _InfoRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text('$label : ', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        Expanded(
          child: Text(value,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
              overflow: TextOverflow.ellipsis),
        ),
      ],
    );
  }
}

class _Timestamp extends StatelessWidget {
  final String sentAt;
  const _Timestamp({required this.sentAt});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Text(sentAt, style: const TextStyle(fontSize: 10, color: AppColors.textSecondary)),
    );
  }
}

class _TimeLabel extends StatelessWidget {
  final String label;
  const _TimeLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Center(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.black.withOpacity(0.08),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(label,
              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        ),
      ),
    );
  }
}
