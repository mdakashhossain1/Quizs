import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../l10n/app_strings.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../widgets/auth_widgets.dart';
import '../widgets/design_widgets.dart';

class SignUpScreen extends StatefulWidget {
  const SignUpScreen({super.key});

  @override
  State<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends State<SignUpScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _agreeTerms = true;
  bool _isLoading = false;
  bool _isGoogleLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _openTerms() async {
    final uri = Uri.parse('https://policies.google.com/terms');
    try {
      await launchUrl(uri, mode: LaunchMode.inAppBrowserView);
    } catch (_) {}
  }

  void _handleSignUp() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your name'),
          backgroundColor: QuizColors.purple,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    if (email.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your email address'),
          backgroundColor: QuizColors.purple,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    if (password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your password'),
          backgroundColor: QuizColors.purple,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    if (!_agreeTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please agree to Terms & Conditions'),
          backgroundColor: QuizColors.purple,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    setState(() => _isLoading = true);
    try {
      await AuthService.instance.register(
        name: name,
        email: email,
        password: password,
      );
      if (!mounted) return;
      Navigator.of(context).pushNamed('/verify-code', arguments: email);
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.message),
          backgroundColor: QuizColors.purple,
          duration: const Duration(seconds: 2),
        ),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _handleGoogleSignUp() async {
    setState(() => _isGoogleLoading = true);
    final success = await AuthService.instance.signInWithGoogle();
    if (success && mounted) {
      Navigator.of(context).pushReplacementNamed('/');
    } else if (mounted) {
      setState(() => _isGoogleLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Google Sign-In was not completed'),
          backgroundColor: QuizColors.purple,
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minHeight: constraints.maxHeight - 40,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const SizedBox(height: 10),

                          // Top Quiz Badge
                          Container(
                            width: 58,
                            height: 58,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: LinearGradient(
                                colors: [Color(0xFF5800A4), Color(0xFF8C26E8)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                            ),
                            child: const Center(
                              child: Icon(
                                Icons.person_add_alt_1_rounded,
                                color: Colors.white,
                                size: 28,
                              ),
                            ),
                          ),
                          const SizedBox(height: 18),

                          Text(
                            AppStrings.t('create_account'),
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 24,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E1E1E),
                              letterSpacing: -0.3,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            AppStrings.t('signup_subtitle'),
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 12.5,
                              fontWeight: FontWeight.w400,
                              color: Color(0xFF757575),
                              height: 1.35,
                            ),
                          ),
                          const SizedBox(height: 24),

                          // Name Field
                          AuthInputField(
                            label: AppStrings.t('name_label'),
                            hintText: AppStrings.t('name_placeholder'),
                            controller: _nameController,
                            prefixIcon: Icons.person_outline_rounded,
                            textInputAction: TextInputAction.next,
                          ),
                          const SizedBox(height: 14),

                          // Email Field
                          AuthInputField(
                            label: AppStrings.t('email_label'),
                            hintText: AppStrings.t('email_placeholder'),
                            controller: _emailController,
                            prefixIcon: Icons.mail_outline_rounded,
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                          ),
                          const SizedBox(height: 14),

                          // Password Field
                          AuthInputField(
                            label: AppStrings.t('password_label'),
                            hintText: '••••••••••••••••',
                            controller: _passwordController,
                            prefixIcon: Icons.lock_outline_rounded,
                            isPassword: true,
                            textInputAction: TextInputAction.done,
                          ),
                          const SizedBox(height: 12),

                          // Agree with Terms & Condition
                          Row(
                            children: [
                              SizedBox(
                                width: 22,
                                height: 22,
                                child: Checkbox(
                                  value: _agreeTerms,
                                  onChanged: (val) =>
                                      setState(() => _agreeTerms = val ?? true),
                                  activeColor: QuizColors.purple,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text.rich(
                                  TextSpan(
                                    style: const TextStyle(
                                      fontFamily: 'Poppins',
                                      fontSize: 12,
                                      fontWeight: FontWeight.w500,
                                      color: Color(0xFF222222),
                                    ),
                                    children: [
                                      TextSpan(
                                        text: '${AppStrings.t('agree_terms')} ',
                                      ),
                                      TextSpan(
                                        text: AppStrings.t('terms_condition_link'),
                                        style: const TextStyle(
                                          color: QuizColors.purple,
                                          fontWeight: FontWeight.w600,
                                          decoration: TextDecoration.underline,
                                        ),
                                        recognizer: TapGestureRecognizer()
                                          ..onTap = _openTerms,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 22),

                          // Sign Up Primary Button
                          AuthPrimaryButton(
                            text: AppStrings.t('sign_up_btn'),
                            onPressed: _handleSignUp,
                            isLoading: _isLoading,
                          ),
                          const SizedBox(height: 22),

                          // Divider
                          AuthDivider(text: AppStrings.t('or_signup_with')),
                          const SizedBox(height: 18),

                          // Wide Google Login Button
                          GoogleWideButton(
                            text: AppStrings.t('continue_with_google'),
                            onPressed: _handleGoogleSignUp,
                            isLoading: _isGoogleLoading,
                          ),
                        ],
                      ),

                      // Footer: Already have an account? Sign In
                      Padding(
                        padding: const EdgeInsets.only(top: 20, bottom: 8),
                        child: Wrap(
                          alignment: WrapAlignment.center,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(
                              AppStrings.t('already_have_account'),
                              style: const TextStyle(
                                fontFamily: 'Poppins',
                                fontSize: 12.5,
                                fontWeight: FontWeight.w400,
                                color: Color(0xFF666666),
                              ),
                            ),
                            const SizedBox(width: 5),
                            GestureDetector(
                              onTap: () {
                                Navigator.of(context)
                                    .pushReplacementNamed('/signin');
                              },
                              child: Text(
                                AppStrings.t('sign_in_link'),
                                style: const TextStyle(
                                  fontFamily: 'Poppins',
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w700,
                                  color: QuizColors.purple,
                                  decoration: TextDecoration.underline,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      );
}
