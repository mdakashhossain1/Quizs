import 'package:firebase_auth/firebase_auth.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/foundation.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_client.dart';

/// Thrown by [AuthService.login] when the account exists and the password is
/// correct, but the email hasn't been OTP-verified yet.
class AuthVerificationRequiredException extends ApiException {
  AuthVerificationRequiredException(super.message, {required this.email});

  final String email;
}

class AuthService extends ChangeNotifier {
  AuthService._();
  static final AuthService instance = AuthService._();

  static const String _keyIsLoggedIn = 'auth_is_logged_in';
  static const String _keyUserEmail = 'auth_user_email';
  static const String _keyUserName = 'auth_user_name';
  static const String _keyUserPhoto = 'auth_user_photo';
  static const String _keyAuthToken = 'auth_token';

  SharedPreferences? _prefs;
  bool _isLoggedIn = false;
  String? _userEmail;
  String? _userName;
  String? _userPhoto;
  String? _authToken;
  bool _isInitialized = false;

  bool get isInitialized => _isInitialized;
  bool get isLoggedIn => _isLoggedIn;
  String get userEmail => _userEmail ?? '';
  String get userName => _userName ?? '';
  String? get userPhoto => _userPhoto;
  String? get authToken => _authToken;

  final GoogleSignIn _googleSignIn = GoogleSignIn(
    serverClientId:
        '317616672617-3soecg4cp1q1i1vs7al9ifqfqs5r2dfs.apps.googleusercontent.com',
  );

  Future<void> initialize() async {
    if (_isInitialized) return;
    try {
      _prefs = await SharedPreferences.getInstance();
      _isLoggedIn = _prefs?.getBool(_keyIsLoggedIn) ?? false;
      _userEmail = _prefs?.getString(_keyUserEmail);
      _userName = _prefs?.getString(_keyUserName);
      _userPhoto = _prefs?.getString(_keyUserPhoto);
      _authToken = _prefs?.getString(_keyAuthToken);
      if (_authToken != null) {
        ApiClient.instance.setToken(_authToken);
      }

      if (Firebase.apps.isNotEmpty) {
        try {
          final currentFirebaseUser = FirebaseAuth.instance.currentUser;
          if (currentFirebaseUser != null) {
            _isLoggedIn = true;
            _userEmail ??= currentFirebaseUser.email;
            _userName ??= currentFirebaseUser.displayName;
            _userPhoto ??= currentFirebaseUser.photoURL;
          }
        } catch (_) {}
      }
    } catch (_) {
      _isLoggedIn = false;
    }
    _isInitialized = true;
    notifyListeners();
  }

  /// Logs in against the Laravel API. Throws [ApiException] on failure
  /// (invalid credentials, deactivated account, unreachable server, ...), or
  /// [AuthVerificationRequiredException] if the email hasn't been OTP-verified.
  Future<void> login({
    required String email,
    required String password,
  }) async {
    try {
      final data = await ApiClient.instance.post('/auth/login', body: {
        'email': email,
        'password': password,
      });
      await _applySession(data);
    } on ApiException catch (e) {
      if (e.body?['requires_verification'] == true) {
        throw AuthVerificationRequiredException(
          e.message,
          email: e.body?['email'] as String? ?? email,
        );
      }
      rethrow;
    }
  }

  /// Registers a new account against the Laravel API and triggers an email
  /// OTP; no session is created until [verifyOtp] succeeds. Throws
  /// [ApiException] on failure (validation errors such as a taken email, ...).
  Future<void> register({
    required String name,
    required String email,
    required String password,
  }) async {
    await ApiClient.instance.post('/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
    });
  }

  /// Confirms an OTP code and, on success, logs the user in.
  Future<void> verifyOtp({
    required String email,
    required String code,
  }) async {
    final data = await ApiClient.instance.post('/auth/verify-otp', body: {
      'email': email,
      'code': code,
    });
    await _applySession(data);
  }

  /// Requests a fresh OTP code for an unverified account.
  Future<void> resendOtp({required String email}) async {
    await ApiClient.instance.post('/auth/send-otp', body: {'email': email});
  }

  Future<void> _applySession(Map<String, dynamic> data) async {
    final token = data['token'] as String?;
    final user = data['user'] as Map<String, dynamic>?;
    if (token == null || user == null) {
      throw ApiException('Unexpected response from server.');
    }

    _authToken = token;
    _isLoggedIn = true;
    _userEmail = user['email'] as String?;
    _userName = user['name'] as String?;
    _userPhoto = user['avatar'] as String?;
    ApiClient.instance.setToken(token);

    try {
      _prefs ??= await SharedPreferences.getInstance();
      await _prefs?.setBool(_keyIsLoggedIn, true);
      await _prefs?.setString(_keyAuthToken, token);
      if (_userEmail != null) {
        await _prefs?.setString(_keyUserEmail, _userEmail!);
      }
      if (_userName != null) {
        await _prefs?.setString(_keyUserName, _userName!);
      }
      if (_userPhoto != null) {
        await _prefs?.setString(_keyUserPhoto, _userPhoto!);
      }
    } catch (_) {}
    notifyListeners();
  }

  /// Local-only session (no backend call): used as a fallback when Google
  /// sign-in succeeds with Firebase but the Laravel backend is unreachable,
  /// and by tests that need to seed a logged-in state without a server.
  Future<void> applyLocalSession({
    required String email,
    String? name,
    String? photoUrl,
  }) async {
    _isLoggedIn = true;
    _userEmail = email;
    if (name != null && name.trim().isNotEmpty) {
      _userName = name;
    }
    if (photoUrl != null && photoUrl.trim().isNotEmpty) {
      _userPhoto = photoUrl;
    }
    try {
      _prefs ??= await SharedPreferences.getInstance();
      await _prefs?.setBool(_keyIsLoggedIn, true);
      await _prefs?.setString(_keyUserEmail, email);
      if (name != null && name.trim().isNotEmpty) {
        await _prefs?.setString(_keyUserName, name);
      }
      if (photoUrl != null && photoUrl.trim().isNotEmpty) {
        await _prefs?.setString(_keyUserPhoto, photoUrl);
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<bool> signInWithGoogle() async {
    try {
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      if (googleUser == null) {
        return false;
      }

      if (Firebase.apps.isEmpty) {
        // Without Firebase there is no ID token to verify server-side, so we
        // cannot safely establish a backend session for this sign-in.
        return false;
      }

      String email = googleUser.email;
      String name = googleUser.displayName ?? 'Google User';
      String? photo = googleUser.photoUrl;
      String? firebaseIdToken;

      try {
        final GoogleSignInAuthentication googleAuth =
            await googleUser.authentication;
        final AuthCredential credential = GoogleAuthProvider.credential(
          accessToken: googleAuth.accessToken,
          idToken: googleAuth.idToken,
        );
        final UserCredential userCredential =
            await FirebaseAuth.instance.signInWithCredential(credential);
        if (userCredential.user != null) {
          email = userCredential.user?.email ?? email;
          name = userCredential.user?.displayName ?? name;
          photo = userCredential.user?.photoURL ?? photo;
          firebaseIdToken = await userCredential.user!.getIdToken();
        }
      } catch (e) {
        debugPrint('FirebaseAuth credential sign-in note: $e');
      }

      if (firebaseIdToken == null) {
        return false;
      }

      try {
        final data = await ApiClient.instance.post('/auth/google-login', body: {
          'id_token': firebaseIdToken,
        });
        await _applySession(data);
      } catch (e) {
        debugPrint('Backend google-login note: $e');
        await applyLocalSession(email: email, name: name, photoUrl: photo);
      }
      return true;
    } catch (e) {
      debugPrint('Google Sign-In note: $e');
      return false;
    }
  }

  /// Persists a display-name (and optionally avatar) change to the Laravel
  /// API. Email is not editable here — the backend has no endpoint for it,
  /// since changing it would require re-verifying the new address. Throws
  /// [ApiException] on failure.
  Future<void> updateProfile({
    required String name,
    String? photoUrl,
  }) async {
    final data = await ApiClient.instance.post('/auth/update-profile', body: {
      'name': name,
      if (photoUrl != null && photoUrl.trim().isNotEmpty) 'avatar': photoUrl,
    });

    final user = data['user'] as Map<String, dynamic>?;
    _userName = user?['name'] as String? ?? name;
    _userPhoto = user?['avatar'] as String? ?? _userPhoto;

    try {
      if (Firebase.apps.isNotEmpty) {
        final currentFirebaseUser = FirebaseAuth.instance.currentUser;
        if (currentFirebaseUser != null) {
          await currentFirebaseUser.updateDisplayName(name);
        }
      }
    } catch (_) {}
    try {
      _prefs ??= await SharedPreferences.getInstance();
      await _prefs?.setString(_keyUserName, _userName!);
      if (_userPhoto != null) {
        await _prefs?.setString(_keyUserPhoto, _userPhoto!);
      }
    } catch (_) {}
    notifyListeners();
  }

  /// Changes the account's password via the Laravel API. Throws
  /// [ApiException] on failure.
  Future<void> changePassword({
    required String newPassword,
  }) async {
    await ApiClient.instance.post('/auth/change-password', body: {
      'new_password': newPassword,
    });
    try {
      if (Firebase.apps.isNotEmpty) {
        final currentFirebaseUser = FirebaseAuth.instance.currentUser;
        if (currentFirebaseUser != null) {
          await currentFirebaseUser.updatePassword(newPassword);
        }
      }
    } catch (e) {
      debugPrint('Firebase password sync note: $e');
    }
  }

  Future<void> deleteAccount() async {
    try {
      if (Firebase.apps.isNotEmpty) {
        final currentFirebaseUser = FirebaseAuth.instance.currentUser;
        if (currentFirebaseUser != null) {
          await currentFirebaseUser.delete();
        }
      }
    } catch (_) {}
    await logout();
  }

  Future<void> logout() async {
    if (_authToken != null) {
      try {
        await ApiClient.instance.post('/auth/logout');
      } catch (_) {}
    }
    _isLoggedIn = false;
    _userEmail = null;
    _userName = null;
    _userPhoto = null;
    _authToken = null;
    ApiClient.instance.setToken(null);
    if (_googleSignIn.currentUser != null) {
      try {
        await _googleSignIn.signOut();
      } catch (_) {}
    }
    if (Firebase.apps.isNotEmpty) {
      try {
        await FirebaseAuth.instance.signOut();
      } catch (_) {}
    }
    try {
      _prefs ??= await SharedPreferences.getInstance();
      await _prefs?.setBool(_keyIsLoggedIn, false);
      await _prefs?.remove(_keyUserEmail);
      await _prefs?.remove(_keyUserName);
      await _prefs?.remove(_keyUserPhoto);
      await _prefs?.remove(_keyAuthToken);
    } catch (_) {}
    notifyListeners();
  }
}
