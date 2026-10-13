import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../l10n/app_strings.dart';
import '../services/api_client.dart';
import '../services/offerwall_service.dart';
import '../services/pubscale_offerwall_service.dart';
import '../services/unity_offerwall_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';

enum OfferwallProvider { pubscale, unity }

class OfferwallScreen extends StatefulWidget {
  const OfferwallScreen({
    super.key,
    this.service,
    this.unityService,
    this.initialProvider,
  });

  final OfferwallService? service;
  final UnityOfferwallService? unityService;
  final OfferwallProvider? initialProvider;

  @override
  State<OfferwallScreen> createState() => _OfferwallScreenState();
}

class _OfferwallScreenState extends State<OfferwallScreen> {
  OfferwallProvider? _loading;
  String? _errorKey;
  String? _authRoute;

  @override
  void initState() {
    super.initState();
    final provider = widget.initialProvider;
    if (provider != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _open(provider);
      });
    }
  }

  Future<void> _open(OfferwallProvider provider) async {
    if (_loading != null) return;
    setState(() {
      _loading = provider;
      _errorKey = null;
      _authRoute = null;
    });
    try {
      switch (provider) {
        case OfferwallProvider.pubscale:
          await (widget.service ?? OfferwallService.instance).open(
            shouldOpen: () => mounted,
          );
        case OfferwallProvider.unity:
          await (widget.unityService ?? UnityOfferwallService.instance).open(
            shouldOpen: () => mounted,
          );
      }
    } on ApiException catch (error) {
      if (!mounted) return;
      if (error.statusCode == 401) {
        _errorKey = 'offerwall_signin_required';
        _authRoute = '/signin';
      } else if (error.body?['must_change_password'] == true) {
        _errorKey = 'offerwall_password_required';
        _authRoute = '/force-change-password';
      } else {
        _errorKey = error.statusCode == 503
            ? 'offerwall_unavailable'
            : 'offerwall_failed';
      }
    } catch (e) {
      if (!mounted) return;
      final message = e.toString();
      _errorKey =
          message.contains('not initialized') ||
              message.contains('Initialization failed')
          ? 'offerwall_unavailable'
          : 'offerwall_failed';
    } finally {
      if (mounted) setState(() => _loading = null);
    }
  }

  @override
  Widget build(BuildContext context) => ListenableBuilder(
    listenable: Listenable.merge([
      AppLanguage.instance,
      PubscaleOfferwallService.instance,
    ]),
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
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    AppStrings.t(
                      _loading != null
                          ? 'offerwall_opening'
                          : _errorKey ?? 'offerwall_choose',
                    ),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 18, height: 1.5),
                  ),
                  const SizedBox(height: 24),
                  _ProviderButton(
                    label: 'PubScale',
                    leading: SvgPicture.asset(
                      'assets/figma/pubscale_logo.svg',
                      width: 90,
                      height: 42,
                    ),
                    loading:
                        _loading == OfferwallProvider.pubscale ||
                        PubscaleOfferwallService.instance.isInitializing,
                    onPressed: _loading == null
                        ? () => _open(OfferwallProvider.pubscale)
                        : null,
                  ),
                  const SizedBox(height: 14),
                  _ProviderButton(
                    label: 'Tapjoy',
                    leading: Image.asset(
                      'assets/figma/tapjoy_logo.png',
                      width: 90,
                      height: 43,
                    ),
                    loading: _loading == OfferwallProvider.unity,
                    onPressed: _loading == null
                        ? () => _open(OfferwallProvider.unity)
                        : null,
                  ),
                  if (_authRoute != null) ...[
                    const SizedBox(height: 24),
                    FilledButton(
                      onPressed: () =>
                          Navigator.of(context).pushNamed(_authRoute!),
                      child: Text(AppStrings.t('offerwall_continue')),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    ),
  );
}

class _ProviderButton extends StatelessWidget {
  const _ProviderButton({
    required this.label,
    required this.leading,
    required this.loading,
    required this.onPressed,
  });

  final String label;
  final Widget leading;
  final bool loading;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: '${AppStrings.t('offerwall_open')}: $label',
    excludeSemantics: true,
    child: OutlinedButton(
      onPressed: onPressed,
      style: OutlinedButton.styleFrom(
        minimumSize: const Size.fromHeight(72),
        padding: const EdgeInsets.symmetric(horizontal: 20),
        backgroundColor: Colors.white,
        side: const BorderSide(color: Color(0xFFE4D6F5)),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
      child: Row(
        children: [
          leading,
          const Spacer(),
          if (loading)
            const SizedBox.square(
              dimension: 22,
              child: CircularProgressIndicator(strokeWidth: 2.5),
            )
          else
            const Icon(Icons.chevron_right, color: QuizColors.purple),
        ],
      ),
    ),
  );
}
