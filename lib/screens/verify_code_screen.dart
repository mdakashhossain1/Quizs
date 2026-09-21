import 'dart:async';
import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../widgets/auth_widgets.dart';
import '../widgets/design_widgets.dart';

/// Route arguments for [VerifyCodeScreen], distinguishing the signup email
/// verification flow from the forgot-password reset flow.
class VerifyCodeArgs {
  const VerifyCodeArgs({required this.email, this.isPasswordReset = false});

  final String email;
  final bool isPasswordReset;
}

class VerifyCodeScreen extends StatefulWidget {
  const VerifyCodeScreen({super.key, this.email, this.isPasswordReset = false});

  final String? email;
  final bool isPasswordReset;

  @override
  State<VerifyCodeScreen> createState() => _VerifyCodeScreenState();
}

class _VerifyCodeScreenState extends State<VerifyCodeScreen> {
  final List<TextEditingController> _controllers =
      List.generate(4, (_) => TextEditingController());
  final List<FocusNode> _focusNodes = List.generate(4, (_) => FocusNode());
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isVerifying = false;
  bool _isResending = false;
  Timer? _resendTimer;
  int _resendCountdown = 300;

  String _formatTimer(int totalSeconds) {
    final m = (totalSeconds ~/ 60).toString().padLeft(2, '0');
    final s = (totalSeconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  @override
  void initState() {
    super.initState();
    _startResendTimer();
  }

  void _startResendTimer() {
    _resendTimer?.cancel();
    setState(() => _resendCountdown = 300);
    _resendTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      if (_resendCountdown <= 1) {
        timer.cancel();
        setState(() => _resendCountdown = 0);
      } else {
        setState(() => _resendCountdown--);
      }
    });
  }

  @override
  void dispose() {
    _resendTimer?.cancel();
    for (final c in _controllers) {
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: QuizColors.purple,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _handleVerify(String email, bool isPasswordReset) async {
    final code = _controllers.map((c) => c.text).join();
    if (code.length < 4) {
      _showMessage('Please enter the 4-digit code');
      return;
    }

    String? newPassword;
    if (isPasswordReset) {
      newPassword = _newPasswordController.text.trim();
      final confirmPassword = _confirmPasswordController.text.trim();
      if (newPassword.length < 6) {
        _showMessage('Password must be at least 6 characters');
        return;
      }
      if (newPassword != confirmPassword) {
        _showMessage('Passwords do not match');
        return;
      }
    }

    setState(() => _isVerifying = true);
    try {
      if (isPasswordReset) {
        await AuthService.instance.resetPassword(
          email: email,
          code: code,
          newPassword: newPassword!,
        );
      } else {
        await AuthService.instance.verifyOtp(email: email, code: code);
      }
      if (!mounted) return;
      Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
    } on ApiException catch (e) {
      _showMessage(e.message);
    } finally {
      if (mounted) setState(() => _isVerifying = false);
    }
  }

  void _handleResend(String email, bool isPasswordReset) async {
    if (_isResending || _resendCountdown > 0) return;
    setState(() => _isResending = true);
    try {
      if (isPasswordReset) {
        await AuthService.instance.forgotPassword(email: email);
      } else {
        await AuthService.instance.resendOtp(email: email);
      }
      _showMessage('A fresh 4-digit verification code has been sent!');
      _startResendTimer();
    } on ApiException catch (e) {
      _showMessage(e.message);
    } finally {
      if (mounted) setState(() => _isResending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final routeArgs = ModalRoute.of(context)?.settings.arguments;
    final emailArg = widget.email ??
        (routeArgs is VerifyCodeArgs
            ? routeArgs.email
            : (routeArgs is String ? routeArgs : null)) ??
        '';
    final isPasswordReset = widget.isPasswordReset ||
        (routeArgs is VerifyCodeArgs && routeArgs.isPasswordReset);

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Back Button
              GestureDetector(
                onTap: () => Navigator.of(context).pop(),
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: const BoxDecoration(
                    color: QuizColors.purple,
                    shape: BoxShape.circle,
                  ),
                  child: const Center(
                    child: Icon(
                      Icons.chevron_left,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 40),

              // Centered Title & Subtitle
              Center(
                child: Column(
                  children: [
                    Text(
                      AppStrings.t('verify_code'),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1E1E1E),
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Text(
                      AppStrings.t('verify_code_subtitle'),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 12.5,
                        fontWeight: FontWeight.w400,
                        color: Color(0xFF757575),
                        height: 1.35,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      emailArg,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: QuizColors.purple,
                      ),
                    ),
                    const SizedBox(height: 36),

                    // 4-Box PIN Input
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(4, (index) {
                        return Container(
                          width: 50,
                          height: 50,
                          margin: const EdgeInsets.symmetric(horizontal: 6),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1EBF7),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: _controllers[index].text.isNotEmpty
                                  ? const Color(0x306703BF)
                                  : Colors.transparent,
                              width: 1.5,
                            ),
                          ),
                          alignment: Alignment.center,
                          child: TextField(
                            controller: _controllers[index],
                            focusNode: _focusNodes[index],
                            textAlign: TextAlign.center,
                            keyboardType: TextInputType.number,
                            maxLength: 1,
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 19,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF222222),
                            ),
                            decoration: const InputDecoration(
                              counterText: '',
                              border: InputBorder.none,
                              isDense: true,
                              contentPadding: EdgeInsets.zero,
                              hintText: '-',
                              hintStyle: TextStyle(
                                fontFamily: 'Poppins',
                                fontSize: 18,
                                fontWeight: FontWeight.w400,
                                color: Color(0xFFB3A8C2),
                              ),
                            ),
                            onChanged: (val) {
                              if (val.isNotEmpty && index < 3) {
                                _focusNodes[index + 1].requestFocus();
                              } else if (val.isEmpty && index > 0) {
                                _focusNodes[index - 1].requestFocus();
                              }
                              setState(() {});
                            },
                          ),
                        );
                      }),
                    ),
                    const SizedBox(height: 28),

                    // Resend Code Link (Wrap prevents overflow)
                    Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          AppStrings.t('dont_receive_otp'),
                          style: const TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 12.5,
                            fontWeight: FontWeight.w400,
                            color: Color(0xFF666666),
                          ),
                        ),
                        const SizedBox(width: 5),
                        if (_resendCountdown > 0)
                          Text(
                            'Resend in ${_formatTimer(_resendCountdown)}',
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 12.5,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF9E92AB),
                            ),
                          )
                        else
                          GestureDetector(
                            onTap: () => _handleResend(emailArg, isPasswordReset),
                            child: Text(
                              AppStrings.t('resend_code'),
                              style: const TextStyle(
                                fontFamily: 'Poppins',
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF1E1E1E),
                                decoration: TextDecoration.underline,
                              ),
                            ),
                          ),
                      ],
                    ),

                    if (isPasswordReset) ...[
                      const SizedBox(height: 28),
                      AuthInputField(
                        label: AppStrings.t('new_password'),
                        hintText: 'Enter new password',
                        controller: _newPasswordController,
                        isPassword: true,
                        prefixIcon: Icons.lock_reset_rounded,
                        textInputAction: TextInputAction.next,
                      ),
                      const SizedBox(height: 14),
                      AuthInputField(
                        label: AppStrings.t('confirm_password'),
                        hintText: 'Confirm new password',
                        controller: _confirmPasswordController,
                        isPassword: true,
                        prefixIcon: Icons.check_circle_outline_rounded,
                        textInputAction: TextInputAction.done,
                      ),
                    ],
                    const SizedBox(height: 36),

                    // Verify / Reset Button
                    AuthPrimaryButton(
                      text: isPasswordReset
                          ? AppStrings.t('reset_password_btn')
                          : AppStrings.t('verify_btn'),
                      onPressed: () => _handleVerify(emailArg, isPasswordReset),
                      isLoading: _isVerifying,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
