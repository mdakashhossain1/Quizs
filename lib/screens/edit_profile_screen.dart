import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../widgets/auth_widgets.dart';
import '../widgets/design_widgets.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: AuthService.instance.userName);
    _emailController = TextEditingController(text: AuthService.instance.userEmail);
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _handleSaveChanges() async {
    final name = _nameController.text.trim();
    final newPassword = _newPasswordController.text.trim();
    final confirmPassword = _confirmPasswordController.text.trim();

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

    if (newPassword.isNotEmpty) {
      if (newPassword.length < 6) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Password must be at least 6 characters'),
            backgroundColor: QuizColors.purple,
            duration: Duration(seconds: 2),
          ),
        );
        return;
      }
      if (newPassword != confirmPassword) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Passwords do not match'),
            backgroundColor: QuizColors.purple,
            duration: Duration(seconds: 2),
          ),
        );
        return;
      }
    }

    setState(() => _isSaving = true);
    try {
      await AuthService.instance.updateProfile(name: name);

      if (newPassword.isNotEmpty) {
        await AuthService.instance.changePassword(newPassword: newPassword);
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppStrings.t('profile_updated_success')),
          backgroundColor: const Color(0xFF10BA65),
          duration: const Duration(seconds: 2),
        ),
      );
      Navigator.of(context).pop();
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
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _handleDeleteAccount() {
    showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.warning_amber_rounded, color: Color(0xFFE53935), size: 26),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                AppStrings.t('delete_account_confirm_title'),
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
        content: Text(
          AppStrings.t('delete_account_confirm_msg'),
          style: const TextStyle(
            fontFamily: 'Poppins',
            fontSize: 13,
            height: 1.45,
            color: Color(0xFF555555),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(dialogContext).pop();
              await AuthService.instance.deleteAccount();
              if (mounted) {
                Navigator.of(context).pushNamedAndRemoveUntil('/signin', (route) => false);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE53935),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.delete_forever_rounded, size: 16, color: Colors.white),
                const SizedBox(width: 6),
                Text(AppStrings.t('delete_btn')),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top App Bar
                Row(
                  children: [
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
                    const SizedBox(width: 16),
                    Text(
                      AppStrings.t('edit_profile'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1E1E1E),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 28),

                // Avatar with Edit Badge
                Center(
                  child: Stack(
                    children: [
                      Container(
                        width: 90,
                        height: 90,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: const Color(0xFFF3EEF8),
                          border: Border.all(
                            color: QuizColors.purple.withValues(alpha: 0.2),
                            width: 2.5,
                          ),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.person_rounded,
                            size: 54,
                            color: QuizColors.purple,
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: Container(
                          width: 30,
                          height: 30,
                          decoration: const BoxDecoration(
                            color: QuizColors.purple,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.camera_alt_rounded,
                            size: 16,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),

                // Name Input
                AuthInputField(
                  label: AppStrings.t('name_label'),
                  hintText: AppStrings.t('name_placeholder'),
                  controller: _nameController,
                  prefixIcon: Icons.person_outline_rounded,
                  textInputAction: TextInputAction.next,
                ),
                const SizedBox(height: 18),

                // Email / ID Input (read-only: the backend has no endpoint to change it)
                AuthInputField(
                  label: AppStrings.t('email_label'),
                  hintText: AppStrings.t('email_placeholder'),
                  controller: _emailController,
                  prefixIcon: Icons.mail_outline_rounded,
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  enabled: false,
                ),
                const SizedBox(height: 28),

                // Change Password Section
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAFAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFECE5F2)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.lock_outline_rounded,
                            color: QuizColors.purple,
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            AppStrings.t('change_password'),
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 14.5,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E1E1E),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
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
                  ),
                ),
                const SizedBox(height: 32),

                // Save Changes Button
                AuthPrimaryButton(
                  text: AppStrings.t('save_changes'),
                  onPressed: _handleSaveChanges,
                  isLoading: _isSaving,
                ),
                const SizedBox(height: 24),

                // Delete Account Section (Danger Zone)
                Center(
                  child: OutlinedButton.icon(
                    onPressed: _handleDeleteAccount,
                    icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFE53935)),
                    label: Text(
                      AppStrings.t('delete_account'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13.5,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFFE53935),
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFFFCDD2), width: 1.2),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      backgroundColor: const Color(0xFFFFF8F8),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        ),
      );
}
