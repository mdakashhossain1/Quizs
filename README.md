# Quizs Backend & Admin Panel (Laravel 12 + Tailwind CSS)

Welcome to the backend management panel and REST API for the **Quizs** application.
Built with standard **Laravel 12 MVC**, **Blade Templates**, and **Tailwind CSS** (100% free of Livewire or heavy dynamic panel packages).

---

## 🚀 Quick Access

- **Admin Panel URL:** [http://localhost/Quizs/admin/public/admin/login](http://localhost/Quizs/admin/public/admin/login)
- **Base REST API URL:** `http://localhost/Quizs/admin/public/api`

### 🔑 Default Credentials

| Role | Email | Password | Allowed Access |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@quizs.com` | `password123` | Web Admin Panel + API |
| **Test Mobile User** | `user@quizs.com` | `password123` | Mobile REST API |

---

## 🎨 Admin Panel Architecture (Pure Blade & Tailwind CSS)

1. **Clean Laravel MVC Design:**
   - Standard Blade templates located in [`resources/views/admin/`](file:///c:/xampp_8.2/htdocs/Quizs/admin/resources/views/admin/).
   - Standard Controllers located in [`app/Http/Controllers/Admin/`](file:///c:/xampp_8.2/htdocs/Quizs/admin/app/Http/Controllers/Admin/).
   - Standard RESTful web routes in [`routes/web.php`](file:///c:/xampp_8.2/htdocs/Quizs/admin/routes/web.php).
   - Zero Livewire scripts or dynamic websocket dependencies.

2. **Dashboard (`/admin/dashboard`):**
   - Real-time stat cards (Total Users, Categories, Quizzes, Question Bank, Quiz Attempts).
   - Recent quiz attempts table with scores and accuracy.
   - Quick action shortcuts.

3. **Categories (`/admin/categories`):**
   - List, Create, Edit, Delete with color preview and active status toggles.

4. **Quizzes (`/admin/quizzes`):**
   - Filter by category and search.
   - Difficulty level badges (`Easy`, `Medium`, `Hard`), durations, passing score %.

5. **Questions Bank (`/admin/questions`):**
   - Question text, points, and explanation fields.
   - 4 standard option fields with a radio button to select the correct answer.

6. **Quiz Attempts (`/admin/quiz-attempts`):**
   - View complete play logs, scores, and player accuracy percentages.

7. **Users Management (`/admin/users`):**
   - Role management (`admin`, `user`), score, streak, and account active status.

---

## 📡 REST API Documentation

All responses are formatted as JSON.

### 🔐 1. Authentication Endpoints

- `POST /api/auth/register` — Register player
- `POST /api/auth/login` — Login player (email & password)
- `POST /api/auth/google-login` — Firebase Google sign-in synchronization
- `GET /api/auth/me` — Authenticated profile & stats *(Bearer Token)*
- `POST /api/auth/update-profile` — Update name or avatar *(Bearer Token)*
- `POST /api/auth/logout` — Revoke token *(Bearer Token)*

### 🧠 2. Quiz & Content Endpoints

- `GET /api/categories` — Active categories with quizzes count
- `GET /api/categories/{id}/quizzes` — Quizzes for a category
- `GET /api/quizzes/{id}` — Quiz questions and masked answer choices
- `POST /api/quizzes/{id}/submit` — Submit answers, calculate score, update streak *(Bearer Token)*
- `GET /api/leaderboard` — Global leaderboard by score & streak
- `GET /api/user/history` — User's quiz attempts history *(Bearer Token)*

---

## 🛠 Database & Commands

- **Database:** `quizs_db` (MySQL on `127.0.0.1:3306`)
- **Run Seeder:**
  ```bash
  php artisan db:seed
  ```
