# Animated Bottom Navigation Bar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the static image-based bottom navigation on HomeScreen with a Flutter-native animated sliding pill bottom navigation bar.

**Architecture:** Create a self-contained `QuizBottomNav` widget with an internal animated sliding pill selector and crisp vector icons. Integrate it directly into `HomeScreen`, preserving exact semantic tap labels for test compatibility.

**Tech Stack:** Flutter, Dart, Material Icons / Custom Vectors.

## Global Constraints
- Width: 384, Height: 58, positioned at `x: 14, y: 841`.
- Navigation actions: `'Home'`, `'Browse categories'`, `'Profile'`.
- All `flutter test` and `flutter analyze` checks must pass with zero issues.

---

### Task 1: Create `QuizBottomNav` Component

**Files:**
- Create: `lib/widgets/quiz_bottom_nav.dart`

**Interfaces:**
- Produces: `QuizBottomNav({super.key, this.initialIndex = 0, this.onTabSelected})`

- [ ] **Step 1: Write `QuizBottomNav` implementation**
Implement `QuizBottomNav` as a `StatefulWidget` with:
- Container styling matching the pill dock (white background, border radius 29, subtle border and drop shadow).
- An animated sliding pill background (`Color(0xFF6703BF)`, height 39, radius 20) using `AnimatedAlign`.
- Three navigation items (Home, Categories, Profile) using Flutter vector icons.
- Expand/cross-fade label when active.
- `DesignAction` or `Semantics` wrapper with labels `'Home'`, `'Browse categories'`, and `'Profile'`.

- [ ] **Step 2: Verify `quiz_bottom_nav.dart` compiles cleanly**
Run: `flutter analyze`
Expected: `No issues found!`

---

### Task 2: Integrate `QuizBottomNav` into `HomeScreen`

**Files:**
- Modify: `lib/screens/home_screen.dart`

**Interfaces:**
- Consumes: `QuizBottomNav` from `lib/widgets/quiz_bottom_nav.dart`

- [ ] **Step 1: Replace static navigation elements on `HomeScreen`**
In `lib/screens/home_screen.dart`:
- Import `../widgets/quiz_bottom_nav.dart`.
- Replace the static container, active panel, PNG assets (`1-2_imgHome1.png`, `1-2_imgDashboard21.png`, `1-2_imgUser21.png`), and static tap rects with:
```dart
at(
  14,
  841,
  384,
  58,
  QuizBottomNav(
    onTabSelected: (index) {
      if (index == 1) {
        Navigator.pushNamed(context, '/categories');
      } else if (index == 2) {
        Navigator.pushNamed(context, '/achievements');
      }
    },
  ),
),
```

- [ ] **Step 2: Run analyzer to verify clean code**
Run: `flutter analyze`
Expected: `No issues found!`

---

### Task 3: Verification & Test Execution

**Files:**
- Test: `test/app_test.dart`

- [ ] **Step 1: Run complete test suite**
Run: `flutter test`
Expected: All tests pass, including:
- `'Static navigation reaches the supplied screens'` (testing `'Browse categories'` navigation)
- `'home renders at Figma size'`
- `'Narrow phones can scroll to navigation without overflow'`

- [ ] **Step 2: Visually verify generated implementation screenshot**
Inspect: `design/implementation/home.png`
Confirm the animated bottom navigation bar renders cleanly with vector icons and active purple pill indicator.
