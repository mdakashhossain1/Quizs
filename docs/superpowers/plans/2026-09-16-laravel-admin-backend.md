# Laravel Backend & Tailwind CSS Admin Panel Implementation Plan

> **Goal:** Create a Laravel backend in the `admin/` folder using Tailwind CSS (via Filament PHP v3), MySQL database `quizs_db`, and an authentication + REST API system for the Quiz platform.

**Architecture:** Laravel 11, Filament v3 TALL stack (Tailwind CSS, Alpine.js, Livewire, Laravel), MySQL on XAMPP, and Laravel Sanctum for API token authentication.

**Tech Stack:** PHP 8.2, Laravel 11, Filament v3, Tailwind CSS, MySQL, Sanctum.

---

### Task 1: Initialize Laravel 11 in `admin/`
**Files:**
- Create: `admin/` directory via `composer create-project`
- Modify: `admin/.env`

- [ ] **Step 1: Run composer create-project laravel/laravel admin**
- [ ] **Step 2: Configure MySQL connection in admin/.env**
- [ ] **Step 3: Verify database connection with php artisan migrate**

---

### Task 2: Install Filament v3 & Setup Tailwind Admin Panel
**Files:**
- Modify: `admin/composer.json`
- Create: `admin/app/Providers/Filament/AdminPanelProvider.php`

- [ ] **Step 1: Install filament/filament:"^3.2"**
- [ ] **Step 2: Run php artisan filament:install --panels**
- [ ] **Step 3: Verify admin panel registration**

---

### Task 3: Database Models & Migrations
**Files:**
- Modify: `admin/database/migrations/*`
- Create: `admin/app/Models/Category.php`
- Create: `admin/app/Models/Quiz.php`
- Create: `admin/app/Models/Question.php`
- Create: `admin/app/Models/Option.php`
- Create: `admin/app/Models/QuizAttempt.php`

- [ ] **Step 1: Create Category, Quiz, Question, Option, QuizAttempt migrations**
- [ ] **Step 2: Define Eloquent relationships and fillable attributes**
- [ ] **Step 3: Run php artisan migrate**

---

### Task 4: Filament Admin Resources (Tailwind UI)
**Files:**
- Create: `admin/app/Filament/Resources/UserResource.php`
- Create: `admin/app/Filament/Resources/CategoryResource.php`
- Create: `admin/app/Filament/Resources/QuizResource.php`
- Create: `admin/app/Filament/Resources/QuestionResource.php`
- Create: `admin/app/Filament/Resources/QuizAttemptResource.php`
- Create: `admin/app/Filament/Widgets/StatsOverviewWidget.php`

- [ ] **Step 1: Generate and configure UserResource**
- [ ] **Step 2: Generate and configure CategoryResource & QuizResource**
- [ ] **Step 3: Generate and configure QuestionResource with choice repeater**
- [ ] **Step 4: Create dashboard stats widget**

---

### Task 5: Database Seeder & Admin User
**Files:**
- Modify: `admin/database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Seed default super admin (admin@quizs.com / password123)**
- [ ] **Step 2: Seed initial categories and quiz questions**
- [ ] **Step 3: Run php artisan db:seed**

---

### Task 6: REST API & Authentication Endpoints
**Files:**
- Create: `admin/app/Http/Controllers/Api/AuthController.php`
- Create: `admin/app/Http/Controllers/Api/QuizController.php`
- Modify: `admin/routes/api.php`

- [ ] **Step 1: Install and configure Laravel Sanctum**
- [ ] **Step 2: Implement registration, login, logout, and me endpoints**
- [ ] **Step 3: Implement category, quiz, and submission endpoints**
- [ ] **Step 4: Verify API endpoints with HTTP requests**
