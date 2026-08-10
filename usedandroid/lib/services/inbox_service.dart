import 'api_client.dart';

class ApiConversation {
  final int id;
  final String otherUserName;
  final String? otherUserAvatar;
  final String? productTitle;
  final String? productImage;
  final String? lastMessageBody;
  final String? lastMessageTime;
  final bool lastMessageIsMine;
  final int unreadCount;

  const ApiConversation({
    required this.id,
    required this.otherUserName,
    this.otherUserAvatar,
    this.productTitle,
    this.productImage,
    this.lastMessageBody,
    this.lastMessageTime,
    this.lastMessageIsMine = false,
    this.unreadCount = 0,
  });

  factory ApiConversation.fromJson(Map<String, dynamic> json) {
    final other = json['other_user'] as Map<String, dynamic>;
    final last = json['last_message'] as Map<String, dynamic>?;
    final product = json['product'] as Map<String, dynamic>?;
    return ApiConversation(
      id: json['id'],
      otherUserName: other['name'] ?? '',
      otherUserAvatar: other['avatar_url'] != null ? ApiClient.fixImageUrl(other['avatar_url']) : null,
      productTitle: product?['title'],
      productImage: product?['image'] != null ? ApiClient.fixImageUrl(product!['image']) : null,
      lastMessageBody: last?['body'],
      lastMessageTime: last?['sent_at'],
      lastMessageIsMine: last?['is_mine'] ?? false,
      unreadCount: json['unread_count'] ?? 0,
    );
  }
}

class ApiOffer {
  final int id;
  final double price;
  final String status;
  final bool isBuyer;
  final bool isSeller;
  final String? productTitle;
  final String? productImage;
  final String? rejectionReason;
  final String? expiresAt;

  const ApiOffer({
    required this.id,
    required this.price,
    required this.status,
    required this.isBuyer,
    required this.isSeller,
    this.productTitle,
    this.productImage,
    this.rejectionReason,
    this.expiresAt,
  });

  factory ApiOffer.fromJson(Map<String, dynamic> json) => ApiOffer(
        id: json['id'],
        price: (json['price'] as num).toDouble(),
        status: json['status'] ?? 'pending',
        isBuyer: json['is_buyer'] ?? false,
        isSeller: json['is_seller'] ?? false,
        productTitle: json['product_title'],
        productImage: json['product_image'],
        rejectionReason: json['rejection_reason'],
        expiresAt: json['expires_at'],
      );

  bool get isPending => status == 'pending';
  bool get isAwaitingBuyer => status == 'awaiting_buyer';
  bool get isAccepted => status == 'accepted';
  bool get isRejected => status == 'rejected';
  bool get isWithdrawn => status == 'withdrawn';
}

class ApiOrder {
  final int id;
  final String status;
  final String? carrier;
  final String? trackingCode;
  final double amount;
  final double payoutAmount;
  final bool isBuyer;
  final bool isSeller;

  const ApiOrder({
    required this.id,
    required this.status,
    this.carrier,
    this.trackingCode,
    required this.amount,
    required this.payoutAmount,
    required this.isBuyer,
    required this.isSeller,
  });

  factory ApiOrder.fromJson(Map<String, dynamic> json) => ApiOrder(
        id: json['id'],
        status: json['status'] ?? '',
        carrier: json['carrier'],
        trackingCode: json['tracking_code'],
        amount: (json['amount'] as num).toDouble(),
        payoutAmount: (json['payout_amount'] as num).toDouble(),
        isBuyer: json['is_buyer'] ?? false,
        isSeller: json['is_seller'] ?? false,
      );

  bool get isPending => status == 'pending' || status == 'processing';
  bool get isShipped => status == 'shipped';
  bool get isCompleted => status == 'completed';
  bool get isCancelled => status == 'cancelled';
}

class ApiMessage {
  final int id;
  final String type;
  final String body;
  final bool isMine;
  final String createdAt;
  final String sentAt;
  final Map<String, dynamic>? metadata;
  final ApiOffer? offer;
  final ApiOrder? order;

  const ApiMessage({
    required this.id,
    required this.type,
    required this.body,
    required this.isMine,
    required this.createdAt,
    required this.sentAt,
    this.metadata,
    this.offer,
    this.order,
  });

  factory ApiMessage.fromJson(Map<String, dynamic> json) => ApiMessage(
        id: json['id'],
        type: json['type'] ?? 'text',
        body: json['body'] ?? '',
        isMine: json['is_mine'] ?? false,
        createdAt: json['created_at'] ?? '',
        sentAt: json['sent_at'] ?? '',
        metadata: json['metadata'] as Map<String, dynamic>?,
        offer: json['offer'] != null ? ApiOffer.fromJson(json['offer']) : null,
        order: json['order'] != null ? ApiOrder.fromJson(json['order']) : null,
      );
}

class ApiNotification {
  final String id;
  final String type;
  final String message;
  final bool read;
  final String createdAt;

  const ApiNotification({
    required this.id,
    required this.type,
    required this.message,
    required this.read,
    required this.createdAt,
  });

  factory ApiNotification.fromJson(Map<String, dynamic> json) => ApiNotification(
        id: json['id'],
        type: json['type'] ?? '',
        message: json['message'] ?? '',
        read: json['read'] ?? false,
        createdAt: json['created_at'] ?? '',
      );
}

class InboxService {
  final _client = ApiClient();

  Future<List<ApiConversation>> getConversations() async {
    final response = await _client.dio.get('/mobile/conversations');
    return (response.data['data'] as List).map((j) => ApiConversation.fromJson(j)).toList();
  }

  /// Start (or reuse) a conversation with a product's seller.
  Future<ApiConversation> startConversation(int productId) async {
    final response = await _client.dio.post('/mobile/conversations', data: {'product_id': productId});
    return ApiConversation.fromJson(response.data);
  }

  Future<List<ApiMessage>> getMessages(int conversationId) async {
    final response = await _client.dio.get('/mobile/conversations/$conversationId/messages');
    return (response.data['data'] as List).map((j) => ApiMessage.fromJson(j)).toList();
  }

  Future<ApiMessage> sendMessage(int conversationId, String body) async {
    final response = await _client.dio.post(
      '/mobile/conversations/$conversationId/messages',
      data: {'body': body},
    );
    return ApiMessage.fromJson(response.data['data']);
  }

  /// Buyer makes (or updates) an offer on a product.
  /// Returns the created/updated offer id and the conversation it lives in.
  Future<({int offerId, int conversationId})> createOffer(int productId, double price) async {
    final response = await _client.dio.post('/mobile/offers', data: {
      'product_id': productId,
      'offer_price': price,
    });
    return (
      offerId: response.data['offer_id'] as int,
      conversationId: response.data['conversation_id'] as int,
    );
  }

  Future<void> acceptOffer(int offerId) async {
    await _client.dio.post('/mobile/offers/$offerId/accept');
  }

  /// Seller sends a counter offer against the buyer's pending offer.
  Future<void> counterOffer(int offerId, double price) async {
    await _client.dio.post('/mobile/offers/$offerId/counter', data: {'offer_price': price});
  }

  /// Buyer pays for an accepted offer (wallet escrow) — creates the order.
  Future<void> checkout(int offerId) async {
    await _client.dio.post('/mobile/checkout', data: {
      'offer_id': offerId,
      'payment_method': 'wallet',
    });
  }

  /// Direct "buy now" purchase of a product (no prior offer).
  Future<void> checkoutProduct(int productId) async {
    await _client.dio.post('/mobile/checkout', data: {
      'product_id': productId,
      'payment_method': 'wallet',
    });
  }

  Future<void> rejectOffer(int offerId, {String? reason}) async {
    await _client.dio.post('/mobile/offers/$offerId/reject', data: {'reason': reason});
  }

  Future<void> withdrawOffer(int offerId) async {
    await _client.dio.post('/mobile/offers/$offerId/withdraw');
  }

  Future<void> shipOrder(int orderId, {required String carrier, String? trackingCode}) async {
    await _client.dio.post('/mobile/orders/$orderId/ship', data: {
      'carrier': carrier,
      'tracking_code': trackingCode,
    });
  }

  Future<void> receiveOrder(int orderId) async {
    await _client.dio.post('/mobile/orders/$orderId/receive');
  }

  Future<List<ApiNotification>> getNotifications() async {
    final response = await _client.dio.get('/mobile/notifications');
    return (response.data['data'] as List).map((j) => ApiNotification.fromJson(j)).toList();
  }

  Future<void> markAllRead() async {
    await _client.dio.post('/mobile/notifications/read');
  }
}
