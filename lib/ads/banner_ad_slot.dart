import 'dart:async';

import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

import 'test_ads.dart';

class BannerAdSlot extends StatefulWidget {
  const BannerAdSlot({super.key});

  static const double width = 320;
  static const double height = 50;

  @override
  State<BannerAdSlot> createState() => _BannerAdSlotState();
}

class _BannerAdSlotState extends State<BannerAdSlot> {
  BannerAd? _ad;
  bool _loaded = false;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    if (TestAds.enabled) unawaited(_load());
  }

  Future<void> _load() async {
    setState(() {
      _failed = false;
      _loaded = false;
    });
    try {
      await TestAds.ensureInitialized();
      if (!mounted) return;
      final banner = BannerAd(
        adUnitId: TestAds.bannerId,
        size: AdSize.banner,
        request: const AdRequest(nonPersonalizedAds: true),
        listener: BannerAdListener(
          onAdLoaded: (ad) {
            if (!mounted || !identical(_ad, ad)) return;
            setState(() => _loaded = true);
            debugPrint(
              'AdMob test banner loaded: ${ModalRoute.of(context)?.settings.name}',
            );
          },
          onAdFailedToLoad: (ad, error) {
            if (!mounted || !identical(_ad, ad)) return;
            unawaited(ad.dispose());
            setState(() {
              _ad = null;
              _loaded = false;
              _failed = true;
            });
            debugPrint(
              'AdMob test banner failed (${error.code}): ${error.message}',
            );
          },
          onAdImpression: (ad) {
            if (!mounted) return;
            debugPrint(
              'AdMob test banner displayed: ${ModalRoute.of(context)?.settings.name}',
            );
          },
        ),
      );
      _ad = banner;
      await banner.load();
    } catch (error) {
      if (!mounted) return;
      final ad = _ad;
      _ad = null;
      if (ad != null) unawaited(ad.dispose());
      setState(() {
        _failed = true;
        _loaded = false;
      });
      debugPrint('AdMob test banner unavailable: $error');
    }
  }

  @override
  void dispose() {
    final ad = _ad;
    _ad = null;
    if (ad != null) unawaited(ad.dispose());
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, constraints) {
      if (constraints.maxWidth < BannerAdSlot.width) {
        return const _AdStatus('Advertisement');
      }
      return Center(
        child: SizedBox(
          width: BannerAdSlot.width,
          height: BannerAdSlot.height,
          child: _loaded && _ad != null
              ? AdWidget(ad: _ad!)
              : !TestAds.enabled
              ? const _AdStatus('Ad preview available on Android and iOS')
              : _failed
              ? _AdStatus('Ad unavailable', onRetry: () => unawaited(_load()))
              : const _AdStatus('Loading ad…'),
        ),
      );
    },
  );
}

class _AdStatus extends StatelessWidget {
  const _AdStatus(this.message, {this.onRetry});
  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) => Container(
    alignment: Alignment.center,
    color: Colors.white,
    child: Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Flexible(
          child: Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 10, color: Color(0xFF777777)),
          ),
        ),
        if (onRetry != null)
          TextButton(onPressed: onRetry, child: const Text('Retry')),
      ],
    ),
  );
}
