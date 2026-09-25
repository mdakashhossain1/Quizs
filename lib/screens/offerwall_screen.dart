import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/api_client.dart';
import '../services/offerwall_service.dart';
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
    } catch (_) {
      if (!mounted) return;
      _errorKey = 'offerwall_failed';
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
                  const Icon(
                    Icons.explore_outlined,
                    size: 64,
                    color: QuizColors.purple,
                  ),
                  const SizedBox(height: 24),
                  Text(
                    AppStrings.t(
                      _loading
                          ? 'offerwall_opening'
                          : _errorKey ?? 'offerwall_browse',
                    ),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 18, height: 1.5),
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
