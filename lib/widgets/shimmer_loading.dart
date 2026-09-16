import 'package:flutter/material.dart';

class Shimmer extends StatefulWidget {
  const Shimmer({
    super.key,
    required this.child,
    this.baseColor = const Color(0xFFF3E8FF),
    this.highlightColor = const Color(0xFFFFFFFF),
    this.duration = const Duration(milliseconds: 1400),
  });

  final Widget child;
  final Color baseColor;
  final Color highlightColor;
  final Duration duration;

  @override
  State<Shimmer> createState() => _ShimmerState();
}

class _ShimmerState extends State<Shimmer>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: widget.duration,
    );
    if (!WidgetsBinding.instance.platformDispatcher.accessibilityFeatures
        .disableAnimations) {
      _controller.repeat();
    } else {
      _controller.value = 0.5;
    }
  }


  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
        animation: _controller,
        builder: (context, child) => ShaderMask(
          blendMode: BlendMode.srcATop,
          shaderCallback: (bounds) => LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              widget.baseColor,
              widget.highlightColor,
              widget.baseColor,
            ],
            stops: const [0.0, 0.5, 1.0],
            transform:
                _SlidingGradientTransform(slidePercent: _controller.value),
          ).createShader(bounds),
          child: widget.child,
        ),
      );

}

class _SlidingGradientTransform extends GradientTransform {
  const _SlidingGradientTransform({required this.slidePercent});
  final double slidePercent;

  @override
  Matrix4? transform(Rect bounds, {TextDirection? textDirection}) =>
      Matrix4.translationValues(
        (bounds.width * 2 * slidePercent) - bounds.width,
        0.0,
        0.0,
      );
}

class ShimmerBox extends StatelessWidget {
  const ShimmerBox({
    super.key,
    required this.width,
    required this.height,
    this.radius = 8.0,
    this.color,
  });

  final double width;
  final double height;
  final double radius;
  final Color? color;

  @override
  Widget build(BuildContext context) => Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: color ?? const Color(0x33BF00FF),
          borderRadius: BorderRadius.circular(radius),
        ),
      );
}

class QuizCardShimmer extends StatelessWidget {
  const QuizCardShimmer({super.key});

  @override
  Widget build(BuildContext context) => Shimmer(
        baseColor: const Color(0x22BF00FF),
        highlightColor: const Color(0x55FFFFFF),
        child: Container(
          width: 367,
          height: 80,
          decoration: BoxDecoration(
            color: const Color(0x26BF00FF),
            borderRadius: BorderRadius.circular(9),
          ),
          child: Stack(
            children: [
              // Circle index shimmer placeholder
              Positioned(
                left: 10,
                top: 11,
                child: Container(
                  width: 58,
                  height: 58,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Color(0x446703BF),
                  ),
                ),
              ),
              // Title shimmer bar
              Positioned(
                left: 78,
                top: 16,
                child: Container(
                  width: 175,
                  height: 16,
                  decoration: BoxDecoration(
                    color: const Color(0x446703BF),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              // Subtitle / Questions count shimmer bar
              Positioned(
                left: 78,
                top: 38,
                child: Container(
                  width: 85,
                  height: 11,
                  decoration: BoxDecoration(
                    color: const Color(0x336703BF),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              // Played count shimmer on right
              Positioned(
                right: 18,
                top: 26,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 32,
                      height: 12,
                      decoration: BoxDecoration(
                        color: const Color(0x446703BF),
                        borderRadius: BorderRadius.circular(3),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Container(
                      width: 26,
                      height: 8,
                      decoration: BoxDecoration(
                        color: const Color(0x226703BF),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ],
                ),
              ),
              // Progress bar shimmer background
              Positioned(
                left: 78,
                top: 61,
                child: Container(
                  width: 228,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(22),
                  ),
                ),
              ),
              // Progress bar active shimmer line
              Positioned(
                left: 78,
                top: 61,
                child: Container(
                  width: 95,
                  height: 4,
                  decoration: BoxDecoration(
                    color: const Color(0x666703BF),
                    borderRadius: BorderRadius.circular(22),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
}
