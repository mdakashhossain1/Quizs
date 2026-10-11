import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../l10n/app_strings.dart';
import '../services/offerwall_service.dart';
import '../services/pubscale_offerwall_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';

class OfferwallScreen extends StatefulWidget {
  const OfferwallScreen({super.key, this.service});

  final OfferwallService? service;

  @override
  State<OfferwallScreen> createState() => _OfferwallScreenState();
}

class _OfferwallScreenState extends State<OfferwallScreen> {
  bool _loading = false;
  String? _errorKey;
  String? _authRoute;

  @override
  void initState() {
    super.initState();
    _open();
  }

  Future<void> _open() async {
    if (_loading) return;
    setState(() {
      _loading = true;
      _errorKey = null;
      _authRoute = null;
    });
    try {
      await (widget.service ?? OfferwallService.instance).open(
        shouldOpen: () => mounted,
      );
    } catch (e) {
      if (!mounted) return;
      final message = e.toString();
      if (message.contains('not initialized') ||
          message.contains('Initialization failed')) {
        _errorKey = 'offerwall_unavailable';
      } else {
        _errorKey = 'offerwall_failed';
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) => ListenableBuilder(
    listenable: AppLanguage.instance,
    builder: (context, _) => Scaffold(
      backgroundColor: const Color(0xFFFCFAFE),
      appBar: AppBar(
        title: Text(AppStrings.t('offerwall')),
        foregroundColor: Colors.white,
        backgroundColor: QuizColors.purple,
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        minimum: const EdgeInsets.fromLTRB(14, 8, 14, 18),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Flexible(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 384),
                child: const QuizBottomNav(initialIndex: 2),
              ),
            ),
          ],
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(28),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 360),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  SvgPicture.asset(
                    'assets/figma/pubscale_logo.svg',
                    width: 90,
                    height: 42,
                  ),
                  const SizedBox(height: 24),
                  // Show SDK initialization status from PubScale
                  ListenableBuilder(
                    listenable: PubscaleOfferwallService.instance,
                    builder: (context, _) {
                      final pubscale = PubscaleOfferwallService.instance;
                      String statusText;
                      if (_loading || pubscale.isInitializing) {
                        statusText = AppStrings.t('offerwall_opening');
                      } else if (_errorKey != null) {
                        statusText = AppStrings.t(_errorKey!);
                      } else if (pubscale.lastError != null) {
                        statusText = AppStrings.t('offerwall_unavailable');
                      } else {
                        statusText = AppStrings.t('offerwall_browse');
                      }
                      return Text(
                        statusText,
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 18, height: 1.5),
                      );
                    },
                  ),
                  const SizedBox(height: 24),
                  if (_loading)
                    CircularProgressIndicator(
                      semanticsLabel: AppStrings.t('offerwall_opening'),
                    )
                  else
                    FilledButton(
                      onPressed: _authRoute != null
                          ? () => Navigator.of(context).pushNamed(_authRoute!)
                          : _open,
                      child: Text(
                        AppStrings.t(
                          _authRoute != null
                              ? 'offerwall_continue'
                              : _errorKey != null
                              ? 'retry'
                              : 'offerwall_open',
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ),
      ),
    ),
  );
}
