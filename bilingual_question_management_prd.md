# PRD — Bilingual Question Management (English + Hindi)

## 1. Purpose

The application already supports switching between **English** and **Hindi**, but the Admin Panel currently allows question content to be entered only in English.

The Question Management system must be upgraded so that **one logical question has two language versions**:

- English (`en`)
- Hindi (`hi`)

These must **not** be treated as two independent questions. They are two translations/versions of the **same Question ID**.

The Flutter application must request/render the appropriate version according to the user's currently selected application language.

---

## 2. Core Requirement

Example:

**Question ID:** `Q-1001`

### English Version
- Question: What is the capital of India?
- Option A: Mumbai
- Option B: New Delhi
- Option C: Kolkata
- Option D: Chennai
- Correct Answer: Option B
- Explanation: New Delhi is the capital of India.

### Hindi Version
- Question: भारत की राजधानी क्या है?
- Option A: मुंबई
- Option B: नई दिल्ली
- Option C: कोलकाता
- Option D: चेन्नई
- Correct Answer: Option B
- Explanation: नई दिल्ली भारत की राजधानी है।

Both versions belong to **Q-1001**.

The answer mapping must remain identical. If `option_b` is correct in English, `option_b` must also represent the correct answer in Hindi.

---

## 3. Important Architecture Rule

Do **not** create separate independent English and Hindi question records that can drift apart.

Recommended conceptual structure:

```text
questions
    id
    exam_id
    paper_id
    subject_id
    topic_id
    category_id
    difficulty
    correct_option_key
    status
    created_at
    updated_at

question_translations
    id
    question_id
    language_code
    question_text
    option_a
    option_b
    option_c
    option_d
    explanation
    image / media fields if required
    created_at
    updated_at
```

Example:

```text
questions
Q-1001 -> correct_option_key = option_b

question_translations
Q-1001 -> en -> English content
Q-1001 -> hi -> Hindi content
```

This keeps quiz logic language-independent while text remains multilingual.

---

## 4. Admin Panel — Create Question

The existing **Add Question** form must be modified.

Admin should create one question and enter both translations from the same screen.

Recommended UI:

```text
Add Question

[ English ] [ हिन्दी ]

--------------------------------

English
Question:
[____________________________]

Option A:
[____________________________]

Option B:
[____________________________]

Option C:
[____________________________]

Option D:
[____________________________]

Explanation:
[____________________________]

--------------------------------

Hindi
Question:
[____________________________]

Option A:
[____________________________]

Option B:
[____________________________]

Option C:
[____________________________]

Option D:
[____________________________]

Explanation:
[____________________________]

--------------------------------

Correct Answer:
[ Option B ]

Difficulty:
[ Medium ]

Category / Subject / Topic / Exam:
[...]

[ Save Question ]
```

The correct-answer selector should preferably be **shared**, rather than independently selected in both language tabs.

---

## 5. Admin Panel — Editing

When admin edits a question, both versions must be available.

Example:

```text
Edit Q-1001

English: Complete
Hindi: Complete
```

Admin can edit only the Hindi translation without accidentally changing English, or edit English without replacing Hindi.

Shared metadata such as:

- Correct answer
- Difficulty
- Exam
- Paper
- Subject
- Topic
- Category
- Status

should remain attached to the parent question.

---

## 6. Translation Status

Question tables should show whether translations are available.

Example:

| ID | Question | English | Hindi | Status |
|---|---|---|---|---|
| Q-1001 | Capital of India | ✓ | ✓ | Published |
| Q-1002 | Newton's Law | ✓ | Missing | Draft |

Useful filters:

- All Questions
- English Available
- Hindi Available
- Hindi Missing
- Translation Incomplete
- Draft
- Published

This will help admins identify old English-only questions that still require Hindi content.

---

## 7. Validation

Before publishing a bilingual question, validate the required content.

For each enabled language:

- Question text required
- Required answer options must exist
- Correct option key must point to a valid option
- Translation must belong to the same parent Question ID

If both languages are mandatory for production, prevent publication until both are complete.

Example:

```text
Cannot Publish

Hindi translation is incomplete:
- Option C is missing
- Explanation is missing (if explanation is mandatory)
```

Draft saving may still be allowed while translation work is incomplete.

---

## 8. Existing English Questions

Existing database questions must **not** be deleted.

Migration should preserve them as English translations.

Conceptually:

```text
Existing Question #100
        ↓
Parent Question #100
        ↓
English Translation = existing content
Hindi Translation = pending
```

Admin can later add Hindi content.

This prevents loss of existing quiz/question history.

---

## 9. Flutter API Behaviour

The Flutter app already knows the user's selected language.

The API should support a language parameter or equivalent locale handling.

Example:

```http
GET /api/quizzes/15/questions?lang=hi
```

For Hindi:

```json
{
  "id": 1001,
  "question": "भारत की राजधानी क्या है?",
  "options": {
    "a": "मुंबई",
    "b": "नई दिल्ली",
    "c": "कोलकाता",
    "d": "चेन्नई"
  }
}
```

For English:

```http
GET /api/quizzes/15/questions?lang=en
```

The same Question ID is returned, but with English text.

---

## 10. Language Switching During a Quiz

If the existing application allows changing language while a quiz is open, the current question should be re-rendered in the newly selected language **without resetting the quiz attempt**.

Example:

```text
Q-1001 English
User selects Option B
        ↓
Switch Language → Hindi
        ↓
Q-1001 Hindi
Previously selected Option B remains selected
```

This works because answers are stored using stable keys such as:

```text
option_a
option_b
option_c
option_d
```

and not by comparing translated answer text.

---

## 11. Quiz Attempt Storage

Quiz attempt records should remain language-independent.

Store:

```text
user_id
quiz_id
question_id
selected_option_key
correct_option_key
is_correct
attempt/session ID
answered_at
```

Optionally also store:

```text
language_used = en / hi
```

for analytics.

Do not determine correctness from translated text.

---

## 12. Admin Activity / Attempt Details

The existing requirement to show user quiz attempts in the Admin Panel must work with bilingual questions.

Admin should be able to inspect:

```text
User: Rahul
Question ID: Q-1001
Language Used: Hindi
Selected: Option B
Correct: Option B
Result: Correct
```

Admin may optionally switch between English and Hindi while reviewing the question because both translations refer to the same Question ID.

---

## 13. Images and Media

If a question uses the same image in both languages, keep that media at the parent-question level.

If an image itself contains translated text and therefore differs by language, allow language-specific media:

```text
English Image
Hindi Image
```

Do not duplicate media unnecessarily.

---

## 14. Search

Admin search should work with both languages.

Searching:

```text
capital
```

should locate the English translation.

Searching:

```text
राजधानी
```

should locate the Hindi translation of the same question.

The result should still open one parent question record.

---

## 15. Bulk Import

If the system supports or later adds Excel/CSV question imports, the import structure should support both languages in one logical row.

Example columns:

```text
question_en
option_a_en
option_b_en
option_c_en
option_d_en
explanation_en

question_hi
option_a_hi
option_b_hi
option_c_hi
option_d_hi
explanation_hi

correct_option
difficulty
exam
paper
subject
topic
category
```

The importer must create **one question**, not two independent questions.

---

## 16. API Fallback Behaviour

Preferred production behaviour:

```text
User Language = Hindi
        ↓
Hindi translation exists?
        ↓
YES → Return Hindi
NO  → Apply configured fallback
```

A sensible fallback is English so the quiz does not completely fail because one translation is missing.

However, the Admin Panel should clearly flag missing Hindi translations so fallback does not become a permanent substitute for translation.

The API may additionally return:

```json
{
  "requested_language": "hi",
  "served_language": "en",
  "translation_fallback": true
}
```

when useful for debugging.

---

## 17. Backend Must Remain Source of Truth

Question text, translations, options, explanations, categories and other quiz content must come from the Laravel backend/API.

Do **not** hardcode English/Hindi question content or question/category slugs in Flutter when those values are managed by the server.

Flutter should primarily:

```text
Read selected language
        ↓
Request server data
        ↓
Render returned localized content
        ↓
Submit answer using Question ID + stable Option Key
```

---

## 18. Recommended Data Flow

```text
ADMIN PANEL
     │
     ├── Create Question
     │
     ├── Enter English Version
     │
     ├── Enter Hindi Version
     │
     └── Select One Shared Correct Option
              │
              ▼
        LARAVEL BACKEND
              │
     ┌────────┴─────────┐
     │                  │
Parent Question    Translations
Metadata/Logic      EN + HI
     │                  │
     └────────┬─────────┘
              ▼
             API
              │
       Receives lang=en/hi
              │
              ▼
        FLUTTER APPLICATION
              │
       Read App Language
              │
       ┌──────┴───────┐
       │              │
    English          Hindi
       │              │
       └──────┬───────┘
              ▼
       Same Question ID
       Same Answer Logic
```

---

# Acceptance Criteria

The feature is complete when:

1. Admin can add English and Hindi versions of one question from the Admin Panel.
2. Both versions share one Question ID.
3. Correct-answer logic is shared and uses stable option keys.
4. Admin can independently edit either translation.
5. Existing English questions remain intact after migration.
6. Admin can identify questions with missing Hindi translations.
7. Flutter receives English content when English is selected.
8. Flutter receives Hindi content when Hindi is selected.
9. Changing language does not create a new quiz attempt or lose selected answers.
10. User answer tracking works identically in both languages.
11. Admin attempt history can identify which language the user used.
12. Quiz statistics, rankings, correct/wrong counts and daily targets do not double-count a question because two translations exist.
13. No question content needs to be hardcoded in Flutter.
14. APIs and database remain the source of truth for question content.
15. Hindi Unicode content is correctly saved, returned and rendered.
16. Existing quiz relationships and historical attempt records remain valid after the multilingual migration.

---

# Critical Rule for AI/Development Agent

> **English and Hindi are translations of one question, not two different questions.**

Never implement bilingual support by duplicating quiz logic.

The stable entities must remain:

```text
Question ID
Correct Option Key
Quiz Relationship
User Attempt
Score
Rank
Statistics
```

Only the display content changes according to language:

```text
Question Text
Options Text
Explanation
Language-specific Media (when required)
```

This rule is necessary so that quiz scores, leaderboard rankings, user progress, correct/wrong statistics, targets and historical records remain accurate regardless of whether a user takes the quiz in English or Hindi.
