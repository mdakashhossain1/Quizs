import 'package:flutter/material.dart';

/// A smooth, battery-efficient marquee text widget.
///
/// Automatically scrolls text horizontally in a seamless loop when it overflows
/// the available width. When the text fits within the available width, it behaves
/// as a standard static [Text] widget with zero animation overhead.
class MarqueeText extends StatefulWidget {
  const MarqueeText({
    super.key,
    required this.text,
    this.style,
    this.velocity = 32.0,
    this.blankSpace = 40.0,
    this.pauseDuration = const Duration(milliseconds: 1500),
    this.enableFadingEdges = true,
  });

  /// The string to display and scroll if overflowing.
  final String text;

  /// The text style to apply.
  final TextStyle? style;

  /// Scroll speed in logical pixels per second.
  final double velocity;

  /// Gap between repetitions of the text in the loop.
  final double blankSpace;

  /// Pause duration before each scroll cycle begins.
  final Duration pauseDuration;

  /// Whether to apply subtle fading gradients at the edges.
  final bool enableFadingEdges;

  @override
  State<MarqueeText> createState() => _MarqueeTextState();
}

class _MarqueeTextState extends State<MarqueeText>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  bool _isDisposed = false;
  bool _isLoopRunning = false;
  double _lastMeasuredTextWidth = 0.0;
  double _lastAvailableWidth = 0.0;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this);
  }

  @override
  void didUpdateWidget(covariant MarqueeText oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.text != widget.text ||
        oldWidget.style != widget.style ||
        oldWidget.velocity != widget.velocity ||
        oldWidget.blankSpace != widget.blankSpace) {
      _stopLoop();
      // Reset measurements so next build recalculates and restarts
      _lastMeasuredTextWidth = 0.0;
      _lastAvailableWidth = 0.0;
    }
  }

  void _stopLoop() {
    _isLoopRunning = false;
    _controller.stop();
    _controller.reset();
  }

  @override
  void dispose() {
    _isDisposed = true;
    _isLoopRunning = false;
    _controller.dispose();
    super.dispose();
  }

  void _checkAndStartLoop(double textWidth, double availableWidth) {
    if (_isDisposed || !mounted) return;

    if (textWidth > availableWidth) {
      if (!_isLoopRunning ||
          _lastMeasuredTextWidth != textWidth ||
          _lastAvailableWidth != availableWidth) {
        _lastMeasuredTextWidth = textWidth;
        _lastAvailableWidth = availableWidth;
        _startLoop(textWidth);
      }
    } else {
      if (_isLoopRunning) {
        _stopLoop();
      }
      _lastMeasuredTextWidth = textWidth;
      _lastAvailableWidth = availableWidth;
    }
  }

  Future<void> _startLoop(double textWidth) async {
    _isLoopRunning = true;

    while (!_isDisposed && mounted && _isLoopRunning) {
      // 1. Pause at the starting position so the reader can easily read the start
      await Future.delayed(widget.pauseDuration);
      if (_isDisposed || !mounted || !_isLoopRunning) break;

      final cycleDistance = textWidth + widget.blankSpace;
      final durationMs = ((cycleDistance / widget.velocity) * 1000).round();
      _controller.duration = Duration(milliseconds: durationMs);

      try {
        // 2. Smoothly scroll to 1.0
        await _controller.forward(from: 0.0);
      } catch (_) {
        break;
      }

      if (_isDisposed || !mounted || !_isLoopRunning) break;

      // 3. Reset seamlessly (at 1.0, the repeated text is at offset 0, matching value 0.0)
      _controller.reset();
    }

    _isLoopRunning = false;
  }

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: LayoutBuilder(
        builder: (context, constraints) {
          final availableWidth = constraints.maxWidth;
          final textScaler =
              MediaQuery.maybeTextScalerOf(context) ?? TextScaler.noScaling;
          final textDirection =
              Directionality.maybeOf(context) ?? TextDirection.ltr;

          final textPainter = TextPainter(
            text: TextSpan(text: widget.text, style: widget.style),
            textDirection: textDirection,
            textScaler: textScaler,
            maxLines: 1,
          )..layout();

          final textWidth = textPainter.width;

          // Schedule marquee loop check after current frame renders
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _checkAndStartLoop(textWidth, availableWidth);
          });

          // If text fits in available width, render static text with zero overhead
          if (textWidth <= availableWidth) {
            return Align(
              alignment: Alignment.centerLeft,
              child: Text(
                widget.text,
                style: widget.style,
                maxLines: 1,
                softWrap: false,
                overflow: TextOverflow.clip,
              ),
            );
          }

          final cycleDistance = textWidth + widget.blankSpace;

          Widget marqueeContent = AnimatedBuilder(
            animation: _controller,
            builder: (context, _) {
              final showLeftFade =
                  _controller.value > 0.02 && _controller.value < 0.98;

              Widget row = OverflowBox(
                alignment: Alignment.centerLeft,
                minWidth: 0.0,
                maxWidth: double.infinity,
                child: Transform.translate(
                  offset: Offset(-_controller.value * cycleDistance, 0),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        widget.text,
                        style: widget.style,
                        maxLines: 1,
                        softWrap: false,
                      ),
                      SizedBox(width: widget.blankSpace),
                      Text(
                        widget.text,
                        style: widget.style,
                        maxLines: 1,
                        softWrap: false,
                      ),
                    ],
                  ),
                ),
              );

              if (!widget.enableFadingEdges) {
                return row;
              }

              return ShaderMask(
                shaderCallback: (Rect bounds) {
                  return LinearGradient(
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                    colors: [
                      showLeftFade ? Colors.transparent : Colors.black,
                      Colors.black,
                      Colors.black,
                      Colors.transparent,
                    ],
                    stops: [
                      0.0,
                      showLeftFade ? 0.05 : 0.0,
                      0.94,
                      1.0,
                    ],
                  ).createShader(bounds);
                },
                blendMode: BlendMode.dstIn,
                child: row,
              );
            },
          );

          return ClipRect(
            child: marqueeContent,
          );
        },
      ),
    );
  }
}
