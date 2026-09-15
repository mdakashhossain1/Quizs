import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class Illustration extends StatefulWidget {
  const Illustration(
    this.asset, {
    super.key,
    this.innerShadows = const [],
    this.shadows = const [],
  });
  final String asset;
  final List<BoxShadow> innerShadows;
  final List<BoxShadow> shadows;

  @override
  State<Illustration> createState() => _IllustrationState();
}

class _IllustrationState extends State<Illustration> {
  ui.Image? _image;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final data = await rootBundle.load('assets/figma/${widget.asset}');
    final codec = await ui.instantiateImageCodec(data.buffer.asUint8List());
    final frame = await codec.getNextFrame();
    codec.dispose();
    if (!mounted) {
      frame.image.dispose();
      return;
    }
    setState(() => _image = frame.image);
  }

  @override
  void dispose() {
    _image?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => _image == null
      ? const SizedBox.expand()
      : CustomPaint(
          painter: _IllustrationPainter(
            _image!,
            widget.innerShadows,
            widget.shadows,
          ),
        );
}

class _IllustrationPainter extends CustomPainter {
  _IllustrationPainter(this.image, this.innerShadows, this.shadows);
  final ui.Image image;
  final List<BoxShadow> innerShadows;
  final List<BoxShadow> shadows;

  @override
  void paint(Canvas canvas, Size size) {
    final source = Rect.fromLTWH(
      0,
      0,
      image.width.toDouble(),
      image.height.toDouble(),
    );
    final bounds = Offset.zero & size;
    final imagePaint = Paint()..filterQuality = FilterQuality.medium;
    for (final shadow in shadows) {
      canvas.drawImageRect(
        image,
        source,
        bounds.shift(shadow.offset),
        Paint()
          ..filterQuality = FilterQuality.medium
          ..colorFilter = ColorFilter.mode(shadow.color, BlendMode.srcIn)
          ..imageFilter = ui.ImageFilter.blur(
            sigmaX: shadow.blurSigma,
            sigmaY: shadow.blurSigma,
          ),
      );
    }
    canvas.saveLayer(bounds, Paint());
    canvas.drawImageRect(image, source, bounds, imagePaint);
    for (final shadow in innerShadows) {
      canvas.saveLayer(bounds, Paint()..blendMode = BlendMode.srcATop);
      canvas.drawRect(bounds, Paint()..color = shadow.color);
      canvas.saveLayer(
        bounds.inflate(shadow.blurRadius * 2),
        Paint()
          ..blendMode = BlendMode.dstOut
          ..imageFilter = ui.ImageFilter.blur(
            sigmaX: shadow.blurSigma,
            sigmaY: shadow.blurSigma,
          ),
      );
      canvas.drawImageRect(
        image,
        source,
        bounds.shift(shadow.offset),
        imagePaint,
      );
      canvas.restore();
      canvas.restore();
    }
    canvas.restore();
  }

  @override
  bool shouldRepaint(_IllustrationPainter oldDelegate) =>
      image != oldDelegate.image ||
      innerShadows != oldDelegate.innerShadows ||
      shadows != oldDelegate.shadows;
}
