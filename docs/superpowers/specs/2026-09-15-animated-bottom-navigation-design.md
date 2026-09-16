# Animated Bottom Navigation Bar Design

## Overview
Replace the static image-based bottom navigation bar on the Quizs app with a fully native Flutter animated navigation bar that preserves the existing visual aesthetic while providing fluid sliding pill animations and crisp vector icons.

## Current Problem
The existing bottom navigation on `HomeScreen` is composed of:
- Fixed-coordinate layout slices (`panel`, `asset`, `label`, `action`).
- Sliced raster PNG images (`1-2_imgHome1.png`, `1-2_imgDashboard21.png`, `1-2_imgUser21.png`).
- Static, non-interactive active states with no animations or state feedback.

## Proposed Solution

### 1. `QuizBottomNav` Widget
A standalone, reusable Flutter widget located in `lib/widgets/quiz_bottom_nav.dart`.

#### Container Specifications:
- **Dimensions**: Width 384, Height 58.
- **Decoration**:
  - Background color: `Colors.white`
  - Border radius: `BorderRadius.circular(29)`
  - Border: `Border.all(color: Color(0x0F000000), width: 0.3)`
  - Drop Shadow: `BoxShadow(color: Color(0x40000000), offset: Offset(1, 2), blurRadius: 4)`

#### Tabs:
1. **Home**:
   - Icon: `Icons.home_rounded` (or vector shape)
   - Label: `'Home'`
   - Semantics / Action Label: `'Home'`
2. **Categories**:
   - Icon: `Icons.grid_view_rounded`
   - Label: `'Categories'`
   - Semantics / Action Label: `'Browse categories'`
   - Destination: Route `'/categories'`
3. **Profile**:
   - Icon: `Icons.person_outline_rounded`
   - Label: `'Profile'`
   - Semantics / Action Label: `'Profile'`
   - Destination: Route `'/achievements'`

### 2. Animation & Interaction
- **Sliding Pill**:
  - Pill dimensions: Height 39, radius 20, color `Color(0xFF6703BF)`.
  - Animates its position smoothly using `AnimatedAlign` / `AnimatedPositioned` with curve `Curves.easeInOutCubicEmphasized` (~250-300ms).
- **Active / Inactive Transitions**:
  - Active item shows both white icon and white text label.
  - Inactive items show icons tinted with `Color(0x996900C5)`.
  - Micro-scale and cross-fade effects on selection change.
- **Gesture / Semantics**:
  - Each item wraps a `DesignAction` or `Semantics` + `GestureDetector` / `InkResponse` to ensure exact test discovery for `'Home'`, `'Browse categories'`, and `'Profile'`.

## Verification Plan
1. `flutter analyze` runs with 0 errors or warnings.
2. `flutter test` passes all tests:
   - Navigation from Home -> Categories via `'Browse categories'`.
   - Navigation from Home -> Achievements via `'Profile'`.
   - Layout rendering without overflow across all screen sizes.
3. Screenshots updated in `design/implementation/` and visually verified.
