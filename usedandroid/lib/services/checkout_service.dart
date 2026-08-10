import 'api_client.dart';

class CheckoutItem {
  final int? id;
  final String title;
  final double price;
  final String? image;
  final String? brand;
  final String? size;

  const CheckoutItem({required this.id, required this.title, required this.price, this.image, this.brand, this.size});

  factory CheckoutItem.fromJson(Map<String, dynamic> j) => CheckoutItem(
        id: j['id'],
        title: j['title'] ?? '',
        price: (j['price'] as num).toDouble(),
        image: j['image'] != null ? ApiClient.fixImageUrl(j['image']) : null,
        brand: j['brand'],
        size: j['size'],
      );
}

class CheckoutAddress {
  final int id;
  final String fullName;
  final String addressLine1;
  final String? addressLine2;
  final String city;
  final String postcode;
  final String country;

  const CheckoutAddress({
    required this.id,
    required this.fullName,
    required this.addressLine1,
    this.addressLine2,
    required this.city,
    required this.postcode,
    required this.country,
  });

  String get oneLine {
    final l2 = (addressLine2 != null && addressLine2!.isNotEmpty) ? ', $addressLine2' : '';
    return '$addressLine1$l2, $city $postcode, $country';
  }

  factory CheckoutAddress.fromJson(Map<String, dynamic> j) => CheckoutAddress(
        id: j['id'],
        fullName: j['full_name'] ?? '',
        addressLine1: j['address_line_1'] ?? '',
        addressLine2: j['address_line_2'],
        city: j['city'] ?? '',
        postcode: j['postcode'] ?? '',
        country: j['country'] ?? '',
      );
}

class CheckoutShippingOption {
  final int id;
  final String key;
  final String label;
  final String type; // drop_off | home_pickup
  final double price;
  final String? description;

  const CheckoutShippingOption({
    required this.id,
    required this.key,
    required this.label,
    required this.type,
    required this.price,
    this.description,
  });

  factory CheckoutShippingOption.fromJson(Map<String, dynamic> j) => CheckoutShippingOption(
        id: j['id'],
        key: j['key'] ?? '',
        label: j['label'] ?? '',
        type: j['type'] ?? 'drop_off',
        price: (j['price'] as num).toDouble(),
        description: j['description'],
      );
}

class CheckoutFees {
  final double buyerProtectionPercentage;
  final double buyerProtectionFixed;
  final double verificationFee;
  final double verificationThreshold;
  final double defaultShipping;

  const CheckoutFees({
    required this.buyerProtectionPercentage,
    required this.buyerProtectionFixed,
    required this.verificationFee,
    required this.verificationThreshold,
    required this.defaultShipping,
  });

  double protectionFor(double itemPrice) => itemPrice * buyerProtectionPercentage / 100 + buyerProtectionFixed;

  factory CheckoutFees.fromJson(Map<String, dynamic> j) => CheckoutFees(
        buyerProtectionPercentage: (j['buyer_protection_percentage'] as num).toDouble(),
        buyerProtectionFixed: (j['buyer_protection_fixed'] as num).toDouble(),
        verificationFee: (j['verification_fee'] as num).toDouble(),
        verificationThreshold: (j['verification_threshold'] as num).toDouble(),
        defaultShipping: (j['default_shipping'] as num).toDouble(),
      );
}

class CheckoutInit {
  final CheckoutItem item;
  final List<CheckoutAddress> addresses;
  final List<CheckoutShippingOption> shippingOptions;
  final double walletBalance;
  final CheckoutFees fees;

  const CheckoutInit({
    required this.item,
    required this.addresses,
    required this.shippingOptions,
    required this.walletBalance,
    required this.fees,
  });

  factory CheckoutInit.fromJson(Map<String, dynamic> j) => CheckoutInit(
        item: CheckoutItem.fromJson(j['item']),
        addresses: (j['addresses'] as List).map((a) => CheckoutAddress.fromJson(a)).toList(),
        shippingOptions: (j['shipping_options'] as List).map((s) => CheckoutShippingOption.fromJson(s)).toList(),
        walletBalance: (j['wallet_balance'] as num).toDouble(),
        fees: CheckoutFees.fromJson(j['fees']),
      );
}

class CheckoutService {
  final _client = ApiClient();

  Future<CheckoutInit> getInit({int? productId, int? offerId}) async {
    final response = await _client.dio.get('/mobile/checkout/init', queryParameters: {
      if (productId != null) 'product_id': productId,
      if (offerId != null) 'offer_id': offerId,
    });
    return CheckoutInit.fromJson(response.data);
  }

  Future<CheckoutAddress> addAddress({
    required String country,
    required String fullName,
    required String addressLine1,
    String? addressLine2,
    required String city,
    required String postcode,
  }) async {
    final response = await _client.dio.post('/mobile/addresses', data: {
      'country': country,
      'full_name': fullName,
      'address_line_1': addressLine1,
      if (addressLine2 != null && addressLine2.isNotEmpty) 'address_line_2': addressLine2,
      'city': city,
      'postcode': postcode,
    });
    return CheckoutAddress.fromJson(response.data);
  }

  Future<void> submit({
    int? productId,
    int? offerId,
    required int addressId,
    required int shippingOptionId,
    required String paymentMethod,
    required bool wantsVerification,
  }) async {
    await _client.dio.post('/mobile/checkout', data: {
      if (productId != null) 'product_id': productId,
      if (offerId != null) 'offer_id': offerId,
      'address_id': addressId,
      'shipping_option_id': shippingOptionId,
      'payment_method': paymentMethod,
      'wants_verification': wantsVerification,
    });
  }
}
