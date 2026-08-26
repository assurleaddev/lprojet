import 'api_client.dart';

double _d(dynamic v) => v is num ? v.toDouble() : double.tryParse('${v ?? ''}') ?? 0;
double? _dn(dynamic v) => v == null ? null : (v is num ? v.toDouble() : double.tryParse('$v'));
int _i(dynamic v) => v is int ? v : int.tryParse('${v ?? ''}') ?? 0;

class ApiLiveUser {
  final int id;
  final String name;
  final String? avatarUrl;
  const ApiLiveUser({required this.id, required this.name, this.avatarUrl});
  factory ApiLiveUser.fromJson(Map<String, dynamic> j) => ApiLiveUser(
        id: _i(j['id']),
        name: j['name'] ?? '',
        avatarUrl: j['avatar_url'] != null ? ApiClient.fixImageUrl(j['avatar_url']) : null,
      );
}

class ApiLiveProduct {
  final int id;
  final String title;
  final String? image;
  final double preBidMin;
  const ApiLiveProduct({required this.id, required this.title, this.image, this.preBidMin = 0});
  factory ApiLiveProduct.fromJson(Map<String, dynamic> j) => ApiLiveProduct(
        id: _i(j['id']),
        title: j['title'] ?? '',
        image: j['image'] != null ? ApiClient.fixImageUrl(j['image']) : null,
        preBidMin: _d(j['pre_bid_min']),
      );
}

class ApiLiveComment {
  final int id;
  final String content;
  final String username;
  final String? avatarUrl;
  const ApiLiveComment({required this.id, required this.content, required this.username, this.avatarUrl});
  factory ApiLiveComment.fromJson(Map<String, dynamic> j) => ApiLiveComment(
        id: _i(j['id']),
        content: j['content'] ?? '',
        username: j['username'] ?? '',
        avatarUrl: j['avatar_url'] != null ? ApiClient.fixImageUrl(j['avatar_url']) : null,
      );
}

class ApiLive {
  final int id;
  final String title;
  final String status; // scheduled | live | ended
  final String auctionStatus; // idle | active
  final String? thumbnailUrl;
  final int likesCount;
  final double startingBid;
  final double? currentBid;
  final double minNextBid;
  final DateTime? countdownEndsAt;
  final String? agoraChannel;
  final ApiLiveUser? seller;
  final ApiLiveProduct? currentProduct;
  final String? currentBidderName;
  final List<ApiLiveProduct> products;

  const ApiLive({
    required this.id,
    required this.title,
    required this.status,
    required this.auctionStatus,
    this.thumbnailUrl,
    this.likesCount = 0,
    this.startingBid = 0,
    this.currentBid,
    this.minNextBid = 0,
    this.countdownEndsAt,
    this.agoraChannel,
    this.seller,
    this.currentProduct,
    this.currentBidderName,
    this.products = const [],
  });

  bool get isLive => status == 'live';
  bool get isScheduled => status == 'scheduled';
  bool get isEnded => status == 'ended';
  bool get auctionActive => auctionStatus == 'active';

  factory ApiLive.fromJson(Map<String, dynamic> j) => ApiLive(
        id: _i(j['id']),
        title: j['title'] ?? '',
        status: j['status'] ?? '',
        auctionStatus: j['auction_status'] ?? 'idle',
        thumbnailUrl: j['thumbnail_url'] != null ? ApiClient.fixImageUrl(j['thumbnail_url']) : null,
        likesCount: _i(j['likes_count']),
        startingBid: _d(j['starting_bid']),
        currentBid: _dn(j['current_bid']),
        minNextBid: _d(j['min_next_bid']),
        countdownEndsAt: j['countdown_ends_at'] != null ? DateTime.tryParse(j['countdown_ends_at']) : null,
        agoraChannel: j['agora_channel'],
        seller: j['seller'] != null ? ApiLiveUser.fromJson(Map<String, dynamic>.from(j['seller'])) : null,
        currentProduct: j['current_product'] != null ? ApiLiveProduct.fromJson(Map<String, dynamic>.from(j['current_product'])) : null,
        currentBidderName: j['current_bidder'] != null ? j['current_bidder']['name'] : null,
        products: (j['products'] as List?)?.map((p) => ApiLiveProduct.fromJson(Map<String, dynamic>.from(p))).toList() ?? [],
      );
}

class LiveService {
  final _client = ApiClient();

  Future<List<ApiLive>> getLives() async {
    final r = await _client.dio.get('/mobile/lives');
    return (r.data['data'] as List).map((j) => ApiLive.fromJson(Map<String, dynamic>.from(j))).toList();
  }

  Future<ApiLive> getLive(int id) async {
    final r = await _client.dio.get('/mobile/lives/$id');
    return ApiLive.fromJson(Map<String, dynamic>.from(r.data));
  }

  Future<List<ApiLiveComment>> getComments(int id) async {
    final r = await _client.dio.get('/mobile/lives/$id/comments');
    return (r.data['data'] as List).map((j) => ApiLiveComment.fromJson(Map<String, dynamic>.from(j))).toList();
  }

  Future<void> like(int id) async {
    await _client.dio.post('/mobile/lives/$id/like');
  }

  Future<ApiLiveComment> comment(int id, String content) async {
    final r = await _client.dio.post('/mobile/lives/$id/comment', data: {'content': content});
    return ApiLiveComment.fromJson(Map<String, dynamic>.from(r.data));
  }

  /// Returns the raw response: {ok, current_bid, countdown_ends_at, balance} or
  /// {ok:false, insufficient_balance:true, shortfall, ...}.
  Future<Map<String, dynamic>> placeBid(int id, double amount) async {
    final r = await _client.dio.post('/mobile/lives/$id/bid', data: {'amount': amount});
    return Map<String, dynamic>.from(r.data);
  }

  Future<double> balance() async {
    final r = await _client.dio.get('/mobile/live-balance');
    return _d(r.data['balance']);
  }
}
