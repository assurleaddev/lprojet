import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

/// A [CachedNetworkImage] that retries a few times on failure.
///
/// The Android emulator drops some concurrent cleartext-HTTP image connections
/// when the feed mounts (~30 images load at once), and CachedNetworkImage
/// otherwise shows its error state permanently. This re-attempts the load a few
/// times with a short backoff, using a cache-busting query param so the failed
/// entry isn't served from cache.
class RetryNetworkImage extends StatefulWidget {
  const RetryNetworkImage({
    super.key,
    required this.imageUrl,
    required this.placeholder,
    required this.errorWidget,
    this.fit = BoxFit.cover,
    this.width,
    this.height,
    this.maxRetries = 6,
  });

  final String imageUrl;
  final Widget placeholder;
  final Widget errorWidget;
  final BoxFit fit;
  final double? width;
  final double? height;
  final int maxRetries;

  @override
  State<RetryNetworkImage> createState() => _RetryNetworkImageState();
}

class _RetryNetworkImageState extends State<RetryNetworkImage> {
  int _attempt = 0;

  String get _url => _attempt == 0
      ? widget.imageUrl
      : '${widget.imageUrl}${widget.imageUrl.contains('?') ? '&' : '?'}retry=$_attempt';

  @override
  Widget build(BuildContext context) {
    return CachedNetworkImage(
      key: ValueKey(_url),
      imageUrl: _url,
      width: widget.width ?? double.infinity,
      height: widget.height,
      fit: widget.fit,
      placeholder: (_, __) => widget.placeholder,
      errorWidget: (_, __, ___) {
        if (_attempt < widget.maxRetries) {
          // Backoff grows with each attempt to spread load across the
          // single-threaded `php artisan serve`, which serves images serially.
          Future.delayed(Duration(milliseconds: 500 * (_attempt + 1)), () {
            if (mounted) setState(() => _attempt++);
          });
          return widget.placeholder;
        }
        return widget.errorWidget;
      },
    );
  }
}
