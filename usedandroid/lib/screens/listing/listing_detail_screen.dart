import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import '../../services/api_client.dart';
import '../../services/inbox_service.dart';
import '../../services/product_service.dart';
import '../../theme/app_colors.dart';
import '../checkout/checkout_screen.dart';
import '../inbox/conversation_screen.dart';
import '../sell/sell_screen.dart';

class ListingDetailScreen extends StatefulWidget {
  final ApiProduct product;

  const ListingDetailScreen({super.key, required this.product});

  @override
  State<ListingDetailScreen> createState() => _ListingDetailScreenState();
}

class _ListingDetailScreenState extends State<ListingDetailScreen> {
  final _pageController = PageController();
  late final List<String> _images;
  late String? _status;
  late ApiProduct _product;
  bool _loggedIn = false;
  List<ApiProduct> _vendorProducts = [];
  List<ApiProduct> _similarProducts = [];

  // Only an approved item can be bought / offered on.
  bool get _available => _status == null || _status == 'approved';

  @override
  void initState() {
    super.initState();
    _product = widget.product;
    _status = widget.product.status;
    _images = widget.product.images.isNotEmpty
        ? widget.product.images
        : (widget.product.featuredImage != null ? [widget.product.featuredImage!] : []);
    final vid = widget.product.vendor?['id'];
    if (vid is int) _loadVendorProducts(vid);
    _loadSimilar();
    _checkAuth();
    _refresh();
  }

  Future<void> _checkAuth() async {
    final loggedIn = await ApiClient.isLoggedIn;
    if (mounted) setState(() => _loggedIn = loggedIn);
  }

  Future<void> _login() async {
    await context.push('/login');
    await _checkAuth();
    _refresh();
  }

  // Refresh with the full product (favorites count, fresh status, etc.).
  Future<void> _refresh() async {
    try {
      final fresh = await ProductService().getProduct(widget.product.id);
      if (mounted) setState(() { _product = fresh; _status = fresh.status; });
    } catch (_) {/* keep the passed product */}
  }

  Future<void> _loadVendorProducts(int vendorId) async {
    try {
      final list = await ProductService().getVendorProducts(vendorId, exclude: widget.product.id);
      if (mounted) setState(() => _vendorProducts = list);
    } catch (_) {/* tab shows empty */}
  }

  Future<void> _loadSimilar() async {
    try {
      final list = await ProductService().getSimilarProducts(widget.product.id);
      if (mounted) setState(() => _similarProducts = list);
    } catch (_) {/* tab shows empty */}
  }

  Future<void> _message() async {
    if (!await ApiClient.isLoggedIn) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connectez-vous pour envoyer un message.')),
      );
      return;
    }
    try {
      final conv = await InboxService().startConversation(widget.product.id);
      if (mounted) {
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ConversationScreen(conversation: conv)),
        );
      }
    } on DioException catch (e) {
      final msg = (e.response?.data as Map?)?['message'] ?? 'Impossible d\'ouvrir la conversation';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
      }
    }
  }

  // ── owner actions ──────────────────────────────────────────────────────────
  Future<void> _edit() async {
    final updated = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => SellScreen(product: _product)),
    );
    if (updated == true) _refresh();
  }

  Future<void> _setStatus(String target) async {
    try {
      final p = await ProductService().updateStatus(widget.product.id, target);
      if (mounted) {
        setState(() { _product = p; _status = p.status; });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Annonce mise à jour'), backgroundColor: AppColors.primary),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Erreur lors de la mise à jour')),
        );
      }
    }
  }

  void _manage() {
    final s = _status ?? 'approved';
    final actions = <(String, String, IconData)>[
      if (s != 'sold') ('sold', 'Marquer comme vendu', Icons.check_circle_outline),
      if (s != 'reserved' && s != 'sold') ('reserved', 'Marquer comme réservé', Icons.lock_clock_outlined),
      if (s == 'approved') ('hidden', 'Masquer l\'annonce', Icons.visibility_off_outlined),
      if (s == 'hidden' || s == 'sold' || s == 'reserved') ('approved', 'Remettre en vente', Icons.sell_outlined),
    ];

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (sheetCtx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text('Gérer l\'annonce', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
            for (final a in actions)
              ListTile(
                leading: Icon(a.$3, color: AppColors.textPrimary),
                title: Text(a.$2),
                onTap: () {
                  Navigator.pop(sheetCtx);
                  _setStatus(a.$1);
                },
              ),
            const Divider(height: 1),
            ListTile(
              leading: const Icon(Icons.delete_outline, color: Colors.red),
              title: const Text('Supprimer', style: TextStyle(color: Colors.red)),
              onTap: () {
                Navigator.pop(sheetCtx);
                _confirmDelete();
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _confirmDelete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Supprimer l\'annonce'),
        content: const Text('Cette action est irréversible. Supprimer cet article ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          TextButton(
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ProductService().deleteProduct(widget.product.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Annonce supprimée')),
        );
        Navigator.of(context).pop();
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Erreur lors de la suppression')),
        );
      }
    }
  }

  Future<void> _report() async {
    const reasons = ['Contrefaçon', 'Article interdit', 'Contenu inapproprié', 'Prix suspect', 'Autre'];
    final reason = await showModalBottomSheet<String>(
      context: context,
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text('Signaler cet article', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ),
            ...reasons.map((r) => ListTile(title: Text(r), onTap: () => Navigator.pop(context, r))),
          ],
        ),
      ),
    );
    if (reason == null || !mounted) return;
    if (!await ApiClient.isLoggedIn) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Connectez-vous pour signaler.')),
        );
      }
      return;
    }
    try {
      await ProductService().reportProduct(widget.product.id, reason);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Merci, votre signalement a été envoyé.')),
        );
      }
    } catch (_) {/* ignore */}
  }

  String _timeAgo(String? iso) {
    if (iso == null) return '';
    final d = DateTime.tryParse(iso)?.toLocal();
    if (d == null) return '';
    final diff = DateTime.now().difference(d);
    if (diff.inMinutes < 1) return 'à l\'instant';
    if (diff.inMinutes < 60) return 'il y a ${diff.inMinutes} min';
    if (diff.inHours < 24) return 'il y a ${diff.inHours} h';
    if (diff.inDays < 30) return 'il y a ${diff.inDays} j';
    return 'il y a ${(diff.inDays / 30).floor()} mois';
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final product = _product;

    return Scaffold(
      backgroundColor: AppColors.surface,
      body: CustomScrollView(
        slivers: [
          _ImageSliver(images: _images, pageController: _pageController, favoritesCount: product.favoritesCount),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${product.price.toStringAsFixed(0)} MAD',
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      _FavoriteButton(initial: product.isFavorited),
                    ],
                  ),
                  if (product.priceInclProtection != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.shield_outlined, size: 15, color: AppColors.primary),
                        const SizedBox(width: 4),
                        Text(
                          '${product.priceInclProtection!.toStringAsFixed(2)} MAD Protection acheteurs incluse',
                          style: const TextStyle(fontSize: 13, color: AppColors.primary, fontWeight: FontWeight.w500),
                        ),
                      ],
                    ),
                  ],
                  const SizedBox(height: 12),
                  Text(
                    product.title,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    [
                      if (product.condition.isNotEmpty) _conditionLabel(product.condition),
                      if (product.brand != null) product.brand!,
                      if (product.createdAt != null) 'Ajouté ${_timeAgo(product.createdAt)}',
                    ].join(' · '),
                    style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
                  ),
                  const SizedBox(height: 16),
                  if (product.size != null) _DetailRow(label: 'Taille', value: product.size!),
                  if (product.shippingFrom != null)
                    _DetailRow(
                      label: 'Frais de port',
                      value: 'à partir de ${product.shippingFrom!.toStringAsFixed(2)} MAD',
                    ),
                  if (product.description != null && product.description!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    const Text('Description', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 6),
                    Text(product.description!, style: const TextStyle(color: AppColors.textSecondary, height: 1.5)),
                  ],
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 16),
                  _SellerCard(vendor: product.vendor, onMessage: (_loggedIn && !product.isOwner) ? _message : null),
                  const SizedBox(height: 16),
                  const _BuyerProtectionCard(),
                  const SizedBox(height: 20),
                  _ProductTabs(dressing: _vendorProducts, similar: _similarProducts),
                  const SizedBox(height: 12),
                  _SignalerRow(onTap: _report),
                  const SizedBox(height: 100),
                ],
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: !_loggedIn
          ? _LoginBar(onLogin: _login)
          : (product.isOwner
              ? _OwnerBar(status: _status ?? 'approved', onEdit: _edit, onManage: _manage)
              : (_available
                  ? _BuyBar(
                      price: product.price,
                      onMakeOffer: _showOfferSheet,
                      onBuyNow: _buyNow,
                    )
                  : _StatusBar(status: _status ?? 'sold'))),
    );
  }

  Future<void> _showOfferSheet() async {
    if (!await ApiClient.isLoggedIn) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connectez-vous pour faire une offre.')),
      );
      return;
    }
    if (!mounted) return;

    final sent = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => _OfferSheet(product: widget.product),
    );

    if (sent == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Offre envoyée ! Retrouvez-la dans vos messages.')),
      );
    }
  }

  Future<void> _buyNow() async {
    if (!await ApiClient.isLoggedIn) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Connectez-vous pour acheter.')),
      );
      return;
    }
    if (!mounted) return;
    final purchased = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => CheckoutScreen(productId: widget.product.id)),
    );
    if (purchased == true && mounted) {
      setState(() => _status = 'sold'); // hide buy/offer buttons, like the web
    }
  }

  String _conditionLabel(String condition) => switch (condition) {
        'new_with_tags' => 'Neuf avec étiquette',
        'new_without_tags' => 'Neuf sans étiquette',
        'very_good' => 'Très bon état',
        'good' => 'Bon état',
        'satisfactory' => 'État satisfaisant',
        'heavily_worn' => 'Très usé',
        _ => condition,
      };
}

class _ImageSliver extends StatefulWidget {
  final List<String> images;
  final PageController pageController;
  final int favoritesCount;

  const _ImageSliver({required this.images, required this.pageController, this.favoritesCount = 0});

  @override
  State<_ImageSliver> createState() => _ImageSliverState();
}

class _ImageSliverState extends State<_ImageSliver> {
  @override
  Widget build(BuildContext context) {
    return SliverAppBar(
      expandedHeight: 420,
      pinned: true,
      backgroundColor: AppColors.surface,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () => Navigator.of(context).pop(),
      ),
      actions: [
        IconButton(icon: const Icon(Icons.share_outlined), onPressed: () {}),
      ],
      flexibleSpace: FlexibleSpaceBar(
        background: widget.images.isEmpty
            ? Container(color: AppColors.inputFill, child: const Icon(Icons.image_not_supported, size: 64, color: AppColors.textSecondary))
            : Stack(
                children: [
                  PageView.builder(
                    controller: widget.pageController,
                    itemCount: widget.images.length,
                    onPageChanged: (_) => setState(() {}),
                    itemBuilder: (_, index) => CachedNetworkImage(
                      imageUrl: widget.images[index],
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: AppColors.inputFill),
                      errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                    ),
                  ),
                  if (widget.images.length > 1)
                    Positioned(
                      bottom: 16,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: SmoothPageIndicator(
                          controller: widget.pageController,
                          count: widget.images.length,
                          effect: const ExpandingDotsEffect(
                            dotHeight: 6,
                            dotWidth: 6,
                            activeDotColor: AppColors.primary,
                            dotColor: Colors.white54,
                          ),
                        ),
                      ),
                    ),
                  if (widget.favoritesCount > 0)
                    Positioned(
                      bottom: 12,
                      right: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.black54,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.favorite, color: Colors.white, size: 14),
                            const SizedBox(width: 4),
                            Text('${widget.favoritesCount}',
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 14)),
          ),
          Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

class _SellerCard extends StatelessWidget {
  final Map<String, dynamic>? vendor;
  final VoidCallback? onMessage;

  const _SellerCard({this.vendor, this.onMessage});

  @override
  Widget build(BuildContext context) {
    final name = vendor?['name'] ?? 'Vendeur';
    final since = vendor?['member_since']?.toString();
    final avatarUrl = vendor?['avatar_url'] as String?;

    return Row(
      children: [
        Container(
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.border),
          ),
          child: CircleAvatar(
            radius: 22,
            backgroundColor: AppColors.inputFill,
            backgroundImage: avatarUrl != null ? CachedNetworkImageProvider(avatarUrl) : null,
            child: avatarUrl == null ? const Icon(Icons.person, color: AppColors.textSecondary) : null,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              if (since != null)
                Text('Membre depuis $since', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
            ],
          ),
        ),
        if (onMessage != null)
          OutlinedButton.icon(
            onPressed: onMessage,
            icon: const Icon(Icons.chat_bubble_outline, size: 16),
            label: const Text('Message'),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.primary),
              foregroundColor: AppColors.primary,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
      ],
    );
  }
}

// ── buyer protection info card ─────────────────────────────────────────────────

class _BuyerProtectionCard extends StatelessWidget {
  const _BuyerProtectionCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.inputFill,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.verified_user_outlined, color: AppColors.primary, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text('Achète et vends en toute sécurité',
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                SizedBox(height: 4),
                Text(
                  'Pour chaque achat, tu bénéficies de la Protection acheteurs : '
                  'remboursement, transactions sécurisées et assistance de notre équipe.',
                  style: TextStyle(fontSize: 12, color: AppColors.textSecondary, height: 1.4),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── tabs: "Dressing du membre" / "Articles similaires" ──────────────────────────

class _ProductTabs extends StatefulWidget {
  final List<ApiProduct> dressing;
  final List<ApiProduct> similar;

  const _ProductTabs({required this.dressing, required this.similar});

  @override
  State<_ProductTabs> createState() => _ProductTabsState();
}

class _ProductTabsState extends State<_ProductTabs> with SingleTickerProviderStateMixin {
  late final TabController _tab = TabController(length: 2, vsync: this);

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TabBar(
          controller: _tab,
          isScrollable: true,
          tabAlignment: TabAlignment.start,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textSecondary,
          indicatorColor: AppColors.primary,
          labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
          tabs: const [Tab(text: 'Dressing du membre'), Tab(text: 'Articles similaires')],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 196,
          child: TabBarView(
            controller: _tab,
            children: [
              _grid(widget.dressing, 'Aucun autre article de ce membre'),
              _grid(widget.similar, 'Aucun article similaire'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _grid(List<ApiProduct> items, String emptyLabel) {
    if (items.isEmpty) {
      return Center(child: Text(emptyLabel, style: const TextStyle(color: AppColors.textSecondary)));
    }
    return ListView.separated(
      scrollDirection: Axis.horizontal,
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(width: 10),
      itemBuilder: (_, i) => _MiniProductCard(product: items[i]),
    );
  }
}

class _MiniProductCard extends StatelessWidget {
  final ApiProduct product;
  const _MiniProductCard({required this.product});

  @override
  Widget build(BuildContext context) {
    final img = product.displayImageUrl;
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => ListingDetailScreen(product: product)),
      ),
      child: SizedBox(
        width: 130,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: SizedBox(
                width: 130,
                height: 140,
                child: img.isEmpty
                    ? Container(color: AppColors.inputFill)
                    : CachedNetworkImage(
                        imageUrl: img,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: AppColors.inputFill),
                        errorWidget: (_, __, ___) => Container(color: AppColors.inputFill),
                      ),
              ),
            ),
            const SizedBox(height: 4),
            Text('${product.price.toStringAsFixed(0)} MAD',
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
            if (product.priceInclProtection != null)
              Text('${product.priceInclProtection!.toStringAsFixed(2)} MAD incl.',
                  style: const TextStyle(fontSize: 11, color: AppColors.primary)),
          ],
        ),
      ),
    );
  }
}

// ── report row ──────────────────────────────────────────────────────────────────

class _SignalerRow extends StatelessWidget {
  final VoidCallback onTap;
  const _SignalerRow({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Row(
          children: const [
            Icon(Icons.flag_outlined, size: 20, color: AppColors.textSecondary),
            SizedBox(width: 12),
            Text('Signaler', style: TextStyle(fontSize: 15, color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}

class _FavoriteButton extends StatefulWidget {
  final bool initial;
  const _FavoriteButton({this.initial = false});

  @override
  State<_FavoriteButton> createState() => _FavoriteButtonState();
}

class _FavoriteButtonState extends State<_FavoriteButton> {
  late bool _favorited;

  @override
  void initState() {
    super.initState();
    _favorited = widget.initial;
  }

  @override
  Widget build(BuildContext context) {
    return IconButton(
      icon: Icon(_favorited ? Icons.favorite : Icons.favorite_border),
      color: _favorited ? AppColors.pink : AppColors.textSecondary,
      onPressed: () => setState(() => _favorited = !_favorited),
    );
  }
}

class _StatusBar extends StatelessWidget {
  final String status;
  const _StatusBar({required this.status});

  @override
  Widget build(BuildContext context) {
    final (label, color, icon) = switch (status) {
      'sold' => ('Article vendu', AppColors.greenBadge, Icons.check_circle_outline),
      'reserved' => ('Article réservé', Colors.orange, Icons.lock_clock_outlined),
      'pending' => ('En attente de validation', AppColors.textSecondary, Icons.hourglass_empty),
      'hidden' => ('Article masqué', AppColors.textSecondary, Icons.visibility_off_outlined),
      _ => ('Indisponible', AppColors.textSecondary, Icons.block),
    };
    return Container(
      padding: EdgeInsets.fromLTRB(16, 14, 16, 14 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 8),
          Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 15)),
        ],
      ),
    );
  }
}

class _LoginBar extends StatelessWidget {
  final VoidCallback onLogin;
  const _LoginBar({required this.onLogin});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: onLogin,
          style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
          child: const Text('Se connecter', style: TextStyle(fontWeight: FontWeight.w700)),
        ),
      ),
    );
  }
}

class _OwnerBar extends StatelessWidget {
  final String status;
  final VoidCallback onEdit;
  final VoidCallback onManage;

  const _OwnerBar({required this.status, required this.onEdit, required this.onManage});

  @override
  Widget build(BuildContext context) {
    final (label, color) = switch (status) {
      'approved' => ('En ligne', AppColors.greenBadge),
      'sold' => ('Vendu', AppColors.textSecondary),
      'reserved' => ('Réservé', Colors.orange),
      'hidden' => ('Masqué', AppColors.textSecondary),
      'pending' => ('En attente de validation', Colors.orange),
      _ => (status, AppColors.textSecondary),
    };
    return Container(
      padding: EdgeInsets.fromLTRB(16, 10, 16, 10 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('Votre annonce · ', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              Text(label, style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: onEdit,
                  icon: const Icon(Icons.edit_outlined, size: 18),
                  label: const Text('Modifier'),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: AppColors.darkPurple),
                    foregroundColor: AppColors.darkPurple,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: onManage,
                  icon: const Icon(Icons.tune, size: 18),
                  label: const Text('Gérer'),
                  style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _BuyBar extends StatelessWidget {
  final double price;
  final VoidCallback onMakeOffer;
  final VoidCallback onBuyNow;

  const _BuyBar({
    required this.price,
    required this.onMakeOffer,
    required this.onBuyNow,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton(
              onPressed: onMakeOffer,
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
                side: const BorderSide(color: AppColors.darkPurple),
                foregroundColor: AppColors.darkPurple,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Faire une offre', style: TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: ElevatedButton(
              onPressed: onBuyNow,
              style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
              child: const Text('Acheter maintenant'),
            ),
          ),
        ],
      ),
    );
  }
}

class _OfferSheet extends StatefulWidget {
  final ApiProduct product;

  const _OfferSheet({required this.product});

  @override
  State<_OfferSheet> createState() => _OfferSheetState();
}

class _OfferSheetState extends State<_OfferSheet> {
  final _controller = TextEditingController();
  final _inbox = InboxService();
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final raw = _controller.text.trim().replaceAll(',', '.');
    final price = double.tryParse(raw);

    if (price == null || price <= 0) {
      setState(() => _error = 'Entrez un montant valide.');
      return;
    }
    if (price > widget.product.price) {
      setState(() => _error = 'L\'offre ne peut pas dépasser le prix affiché.');
      return;
    }

    setState(() {
      _submitting = true;
      _error = null;
    });

    try {
      await _inbox.createOffer(widget.product.id, price);
      if (mounted) Navigator.of(context).pop(true);
    } on DioException catch (e) {
      final message = e.response?.data is Map ? e.response?.data['message'] as String? : null;
      setState(() {
        _submitting = false;
        _error = message ?? 'Impossible d\'envoyer l\'offre. Réessayez.';
      });
    } catch (_) {
      setState(() {
        _submitting = false;
        _error = 'Une erreur est survenue. Réessayez.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 20,
        bottom: 20 + MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Faire une offre',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(
            'Prix affiché : ${widget.product.price.toStringAsFixed(0)} MAD',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _controller,
            autofocus: true,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            inputFormatters: [FilteringTextInputFormatter.allow(RegExp(r'[0-9.,]'))],
            onChanged: (_) {
              if (_error != null) setState(() => _error = null);
            },
            decoration: InputDecoration(
              hintText: 'Votre offre',
              suffixText: 'MAD',
              errorText: _error,
              filled: true,
              fillColor: AppColors.inputFill,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _submitting ? null : _submit,
              style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
              child: _submitting
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Envoyer l\'offre'),
            ),
          ),
        ],
      ),
    );
  }
}
