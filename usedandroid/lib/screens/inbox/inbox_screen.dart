import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../services/api_client.dart';
import '../../services/inbox_service.dart';
import '../../theme/app_colors.dart';

class InboxScreen extends StatefulWidget {
  const InboxScreen({super.key});

  @override
  State<InboxScreen> createState() => _InboxScreenState();
}

class _InboxScreenState extends State<InboxScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabController;
  final _service = InboxService();

  List<ApiConversation> _conversations = [];
  List<ApiNotification> _notifications = [];
  bool _loading = true;
  bool _loggedIn = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _init();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    final loggedIn = await ApiClient.isLoggedIn;
    if (!loggedIn) {
      if (mounted) setState(() { _loggedIn = false; _loading = false; });
      return;
    }
    setState(() => _loggedIn = true);
    await _load();
  }

  Future<void> _load() async {
    try {
      final results = await Future.wait([
        _service.getConversations(),
        _service.getNotifications(),
      ]);
      if (mounted) {
        setState(() {
          _conversations = results[0] as List<ApiConversation>;
          _notifications = results[1] as List<ApiNotification>;
          _loading = false;
        });
      }
    } on DioException {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.primary,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textSecondary,
          tabs: const [Tab(text: 'Messages'), Tab(text: 'Notifications')],
        ),
      ),
      body: !_loggedIn
          ? _LoginPrompt()
          : _loading
              ? const Center(child: CircularProgressIndicator())
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _ConversationsList(conversations: _conversations, onRefresh: _load),
                    _NotificationsList(notifications: _notifications, onRefresh: _load),
                  ],
                ),
    );
  }
}

class _ConversationsList extends StatelessWidget {
  final List<ApiConversation> conversations;
  final Future<void> Function() onRefresh;

  const _ConversationsList({required this.conversations, required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    if (conversations.isEmpty) {
      return const _EmptyState(icon: Icons.chat_bubble_outline, message: 'Pas encore de messages');
    }
    return RefreshIndicator(
      onRefresh: onRefresh,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 4),
        itemCount: conversations.length,
        separatorBuilder: (_, __) =>
            const Divider(height: 1, thickness: 1, indent: 84, color: AppColors.border),
        itemBuilder: (_, i) => _ConversationTile(conv: conversations[i]),
      ),
    );
  }
}

class _ConversationTile extends StatelessWidget {
  final ApiConversation conv;

  const _ConversationTile({required this.conv});

  @override
  Widget build(BuildContext context) {
    final unread = conv.unreadCount > 0;
    final preview = conv.lastMessageBody == null
        ? null
        : (conv.lastMessageIsMine ? 'Vous : ${conv.lastMessageBody}' : conv.lastMessageBody!);

    return InkWell(
      onTap: () => context.push('/inbox/conversation/${conv.id}', extra: conv),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
        child: Row(
          children: [
            Container(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.border),
              ),
              child: CircleAvatar(
                radius: 26,
                backgroundColor: AppColors.inputFill,
                backgroundImage:
                    conv.otherUserAvatar != null ? CachedNetworkImageProvider(conv.otherUserAvatar!) : null,
                child: conv.otherUserAvatar == null
                    ? Text(
                        conv.otherUserName.isNotEmpty ? conv.otherUserName[0].toUpperCase() : '?',
                        style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary),
                      )
                    : null,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          conv.otherUserName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: unread ? FontWeight.w800 : FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ),
                      if (conv.lastMessageTime != null)
                        Text(
                          conv.lastMessageTime!,
                          style: TextStyle(
                            fontSize: 11,
                            color: unread ? AppColors.primary : AppColors.textSecondary,
                            fontWeight: unread ? FontWeight.w600 : FontWeight.w400,
                          ),
                        ),
                    ],
                  ),
                  if (conv.productTitle != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      conv.productTitle!,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w500),
                    ),
                  ],
                  if (preview != null) ...[
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            preview,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 13,
                              height: 1.2,
                              color: unread ? AppColors.textPrimary : AppColors.textSecondary,
                              fontWeight: unread ? FontWeight.w600 : FontWeight.w400,
                            ),
                          ),
                        ),
                        if (unread)
                          Container(
                            margin: const EdgeInsets.only(left: 6),
                            width: 9,
                            height: 9,
                            decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                          ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            if (conv.productImage != null) ...[
              const SizedBox(width: 12),
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: CachedNetworkImage(
                  imageUrl: conv.productImage!,
                  width: 52,
                  height: 52,
                  fit: BoxFit.cover,
                  placeholder: (_, __) => Container(width: 52, height: 52, color: AppColors.inputFill),
                  errorWidget: (_, __, ___) => Container(width: 52, height: 52, color: AppColors.inputFill),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _NotificationsList extends StatelessWidget {
  final List<ApiNotification> notifications;
  final Future<void> Function() onRefresh;

  const _NotificationsList({required this.notifications, required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    if (notifications.isEmpty) {
      return const _EmptyState(icon: Icons.notifications_none, message: 'Pas encore de notifications');
    }
    return RefreshIndicator(
      onRefresh: onRefresh,
      child: ListView.separated(
        itemCount: notifications.length,
        separatorBuilder: (_, __) => const Divider(height: 1, indent: 60),
        itemBuilder: (_, i) => _NotifTile(notif: notifications[i]),
      ),
    );
  }
}

class _NotifTile extends StatelessWidget {
  final ApiNotification notif;

  const _NotifTile({required this.notif});

  @override
  Widget build(BuildContext context) {
    final (icon, color) = _iconFor(notif.type);
    return ListTile(
      tileColor: notif.read ? null : AppColors.primary.withOpacity(0.05),
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(color: color.withOpacity(0.12), shape: BoxShape.circle),
        child: Icon(icon, color: color, size: 20),
      ),
      title: Text(notif.message, style: const TextStyle(fontSize: 14)),
      trailing: Text(notif.createdAt, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
    );
  }

  (IconData, Color) _iconFor(String type) => switch (type) {
        'offer_received' => (Icons.local_offer, AppColors.primary),
        'offer_accepted' => (Icons.check_circle, AppColors.greenBadge),
        'offer_rejected' => (Icons.cancel, Colors.red),
        'item_sold' => (Icons.sell, AppColors.primary),
        'item_shipped' => (Icons.local_shipping, AppColors.darkPurple),
        'order_completed' => (Icons.star, Colors.amber),
        'new_message' => (Icons.chat_bubble, AppColors.primary),
        _ => (Icons.notifications, AppColors.textSecondary),
      };
}

class _LoginPrompt extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.lock_outline, size: 64, color: AppColors.textSecondary),
          const SizedBox(height: 16),
          const Text('Connectez-vous pour voir vos messages', style: TextStyle(color: AppColors.textSecondary)),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () => context.push('/login'),
            child: const Text('Se connecter'),
          ),
        ],
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String message;

  const _EmptyState({required this.icon, required this.message});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 64, color: AppColors.textSecondary),
          const SizedBox(height: 16),
          Text(message, style: const TextStyle(color: AppColors.textSecondary, fontSize: 16)),
        ],
      ),
    );
  }
}
