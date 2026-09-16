# Firebase Google Login Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrate Firebase Authentication with Google Sign-In into the Quiz Flutter application.

**Architecture:** Add `firebase_core`, `firebase_auth`, and `google_sign_in` dependencies, configure Android gradle scripts, integrate Google OAuth sign-in flow inside `AuthService`, and attach to SignIn and SignUp screens.

**Tech Stack:** Flutter, Firebase Auth, Google Sign In, SharedPreferences.

---

### Task 1: Add Dependencies and Configure Android Gradle
**Files:**
- Modify: `pubspec.yaml`
- Modify: `android/build.gradle.kts`
- Modify: `android/app/build.gradle.kts`

- [ ] **Step 1: Update pubspec.yaml with firebase_core, firebase_auth, and google_sign_in**
- [ ] **Step 2: Run flutter pub get**
- [ ] **Step 3: Update android gradle files for google-services**

---

### Task 2: Implement Google Sign-In & Firebase Auth in AuthService
**Files:**
- Modify: `lib/services/auth_service.dart`
- Modify: `lib/main.dart`
- Modify: `test/auth_screens_test.dart`

- [ ] **Step 1: Add signInWithGoogle() and signOut() to AuthService**
- [ ] **Step 2: Update main.dart with Firebase.initializeApp() error-tolerant check**
- [ ] **Step 3: Verify with unit and widget tests**

---

### Task 3: Hook Google Sign-In in Screens & Run Verification
**Files:**
- Modify: `lib/screens/signin_screen.dart`
- Modify: `lib/screens/signup_screen.dart`

- [ ] **Step 1: Connect SignInScreen Google button to AuthService.instance.signInWithGoogle()**
- [ ] **Step 2: Connect SignUpScreen Google button to AuthService.instance.signInWithGoogle()**
- [ ] **Step 3: Run flutter analyze and flutter test**
