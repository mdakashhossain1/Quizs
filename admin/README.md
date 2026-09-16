# Quizs Backend & Admin Panel (Laravel 12 + Filament v3 + Tailwind CSS)

Welcome to the backend management panel and REST API for the **Quizs** application.

---

## 🚀 Quick Access

- **Admin Panel URL:** [http://localhost/Quizs/admin/public/admin](http://localhost/Quizs/admin/public/admin)
- **Base API URL:** `http://localhost/Quizs/admin/public/api`

### 🔑 Default Credentials

| Role | Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@quizs.com` | `password123` | Filament Admin Panel + API |
| **Test Mobile User** | `user@quizs.com` | `password123` | Mobile REST API |

---

## 🎨 Admin Panel Features (Filament v3 & Tailwind CSS)

1. **Modern Tailwind UI:**
   - Dark/Light mode support.
   - Brand color palette (Vibrant Purple).
   - Real-time widgets & responsive data tables.
2. **Dashboard Overview:**
   - Live metrics for Total Users, Active Categories, Quizzes, Questions, and Quiz Attempts.
3. **Categories Management (`/admin/categories`):**
   - Create, edit, toggle active status, color picker, sort ordering.
4. **Quizzes Management (`/admin/quizzes`):**
   - Difficulty levels (`easy`, `medium`, `hard`), category binding, passing percentage, timer configuration.
5. **Questions Bank (`/admin/questions`):**
   - Inline repeater for question choices/options.
   - Toggle to mark the correct choice directly.
   - Question explanations and custom point weighting.
6. **Quiz Attempts Log (`/admin/quiz-attempts`):**
   - View completed quiz runs, player accuracy, and total scores.
7. **User Management (`/admin/users`):**
   - Manage user roles (`admin`, `user`), view streaks, total scores, and activate/deactivate accounts.

---

## 📡 REST API Documentation

All responses are formatted as JSON.

### 🔐 1. Authentication Endpoints

#### **Register New User**
`POST /api/auth/register`
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "securepassword"
}
```
**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful.",
  "token": "2|...",
  "user": { "id": 3, "name": "Jane Doe", "email": "jane@example.com", ... }
}
```

#### **Login User**
`POST /api/auth/login`
```json
{
  "email": "user@quizs.com",
  "password": "password123"
}
```
**Response (200):**
```json
{
  "success": true,
  "message": "Login successful.",
  "token": "1|...",
  "user": { "id": 2, "name": "Alex Johnson", "streak": 5, "score": 380, ... }
}
```

#### **Google Sign-In Integration**
`POST /api/auth/google-login`
```json
{
  "email": "alex@gmail.com",
  "name": "Alex J",
  "google_id": "109283091823901",
  "avatar": "https://lh3.googleusercontent.com/..."
}
```
**Response (200):**
```json
{
  "success": true,
  "token": "3|...",
  "user": { ... }
}
```

#### **Current Authenticated Profile**
`GET /api/auth/me` *(Requires Bearer Token)*

#### **Logout**
`POST /api/auth/logout` *(Requires Bearer Token)*

---

### 🧠 2. Quiz & Content Endpoints

#### **List Categories**
`GET /api/categories`

#### **List Quizzes by Category**
`GET /api/categories/{categoryId}/quizzes`

#### **Get Quiz Details (Questions & Choices)**
`GET /api/quizzes/{id}`
*(Note: Correct answers are safely masked during gameplay)*

#### **Submit Quiz Answers**
`POST /api/quizzes/{id}/submit` *(Requires Bearer Token)*
```json
{
  "answers": {
    "1": 2,
    "2": 7,
    "3": 10
  }
}
```
**Response (200):**
```json
{
  "success": true,
  "result": {
    "attempt_id": 2,
    "score": 30,
    "total_possible_score": 30,
    "correct_answers": 3,
    "total_questions": 3,
    "percentage": 100,
    "passed": true,
    "updated_streak": 5,
    "updated_total_score": 380,
    "review": [
      {
        "question_id": 1,
        "question_text": "Which planet is known as the Red Planet?",
        "explanation": "Mars appears red because of iron oxide...",
        "selected_option_id": 2,
        "correct_option_id": 2,
        "is_correct": true
      }
    ]
  }
}
```

#### **Leaderboard**
`GET /api/leaderboard`

#### **User Play History**
`GET /api/user/history` *(Requires Bearer Token)*

---

## 🛠 Database & Setup

- **Database:** `quizs_db` (MySQL on `127.0.0.1:3306`)
- **Commands:**
  ```bash
  # Re-run migrations
  php artisan migrate

  # Re-run seeder
  php artisan db:seed
  ```
