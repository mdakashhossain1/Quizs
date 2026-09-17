# Leaderboard & Achievement Pages — Dynamic Backend Integration Roadmap

## 1. Purpose

This document defines the required modifications for the existing **Leaderboard / Quiz Result** and **Achievement / Global Ranking** pages.

The existing UI/design can remain largely unchanged. The main requirement is to remove static/demo values and connect the complete page to real backend data generated from users' quiz activity, profile statistics, daily targets, levels, accuracy, and global performance.

> **Core Rule:** No ranking, score, user statistic, leaderboard position, achievement value, message, or performance number should remain hard-coded/static. All such data must be calculated from backend records.

---

# PART A — Quiz Result / Leaderboard Page

## 2. Existing Page Behaviour to Modify

The existing result screen currently contains elements such as:

- Congratulations / result message
- Score / points
- Questions solved
- Right answers
- Wrong answers
- Top user podium/ranking
- User names and profile images
- Additional ranked users
- Result-related illustrations/images

These values must become completely dynamic.

---

## 3. Save Complete Quiz Attempt Data

Whenever a user starts and performs a quiz, create a quiz-attempt/session record in the backend.

Store at minimum:

- User ID
- Quiz ID
- Quiz/category/topic information
- Attempt/session ID
- Quiz start time
- Quiz submission/completion time
- Total time taken
- Total questions
- Total attempted questions
- Correct answers
- Wrong answers
- Unanswered/skipped questions, if supported
- Score/points
- Accuracy percentage
- Completion status
- Date and time

For every question, also store:

- Question ID
- User-selected option
- Correct option
- Correct/Wrong status
- Answer time, if available

This becomes the source of truth for result calculation and ranking.

---

## 4. Dynamic Result Summary

After quiz submission, the result card must be generated from the submitted attempt.

Example:

```text
Questions: 70
Attempted: 68
Correct: 55
Wrong: 13
Time Taken: 24m 18s
Accuracy: 80.88%
Score: calculated score
```

The app must not display fixed values such as `10 Questions`, `8 Right`, `2 Wrong`, or `100 Points` unless those are the actual calculated results.

---

## 5. Dynamic Result Message

The current `Congratulations!` message must not always appear regardless of performance.

The heading, supporting text, illustration/image, and celebration state should change according to the user's actual performance.

For example, the application can define configurable performance ranges such as:

```text
Excellent performance  -> Celebration message + achievement artwork
Good performance       -> Positive result message
Average performance    -> Improvement-oriented message
Low performance        -> Practice/retry-oriented message
```

The exact wording and threshold values should be configurable rather than deeply hard-coded into the UI.

The backend/result logic should provide a performance state, and the frontend should render the appropriate text/image/state.

---

## 6. Ranking Must Be Dynamic

The podium/ranking section must be generated from real user performance.

There must be no static users such as demo names permanently occupying Rank 1, Rank 2, or Rank 3.

Whenever relevant quiz results change, the ranking should be recalculated and the latest ranking displayed.

The system should use a clearly defined ranking algorithm.

A recommended ordering is:

1. Higher score / more correct answers
2. Higher accuracy
3. Lower completion time as a tie-breaker
4. Earlier completion time as a final deterministic tie-breaker if required

The exact scoring formula can follow the application's existing quiz scoring rules, but it must be consistent for every user.

### Important

If negative marking or custom question points already exist, ranking should use the final calculated quiz score rather than simply counting correct answers.

---

## 7. Quiz-Specific Ranking

For an individual quiz, the result page can show the user's position against other users who performed that same quiz.

Example:

```text
Quiz: Classical Mechanics

1st — User A — Score 65 — Accuracy 92% — 18m 20s
2nd — User B — Score 62 — Accuracy 90% — 17m 40s
3rd — Current User — Score 60 — Accuracy 88% — 20m 11s
```

When the current user submits a new result, their rank should be recalculated using current backend data.

---

## 8. Multiple Attempts

The backend must explicitly define how repeated attempts affect ranking.

Recommended behaviour:

- Store **every attempt** for history and admin analytics.
- For leaderboard ranking, use the user's **best valid attempt** for that quiz unless the product later specifies another rule.
- Never delete previous attempt history simply because a better attempt was made.

This prevents one user from occupying several leaderboard positions for the same quiz.

---

## 9. Podium and Remaining Ranking List

The UI should dynamically populate:

- Rank 1
- Rank 2
- Rank 3
- Rank 4 onward

Each ranking item can use backend data such as:

- Rank
- User ID
- Display name
- Profile image
- Score
- Correct answers
- Wrong answers
- Accuracy
- Time taken
- Quiz attempt/session information where required

The current logged-in user's row should be identifiable/highlighted in the UI.

---

# PART B — Achievement / Global Ranking Page

## 10. Purpose of Achievement Page

The Achievement page is **not only a ranking for one specific quiz**.

It represents the user's broader performance/profile data compared with all eligible users in the application.

It should combine the same backend statistics already being generated for the user's Profile page with a **global ranking system**.

---

## 11. Profile Information on Achievement Page

The top section should dynamically show the current user's:

- Profile image
- Name
- Current level
- Accuracy
- Progress ring
- Global rank
- Active user count

No demo value should remain static.

---

## 12. Daily Progress Ring

The circular progress around the profile image should represent the current day's target completion.

Example:

```text
Daily Quiz Target: 20
Completed Today: 15
Progress: 75%
```

Therefore, the circular ring should be filled to approximately 75%.

Formula:

```text
Daily Progress % = (Completed Valid Quizzes Today / Effective Daily Target) × 100
```

Cap the visual progress at 100% unless the UI intentionally supports showing overachievement separately.

The **Effective Daily Target** must follow the target system defined in the main roadmap:

- Use a user's individual/custom target when one exists.
- Otherwise use the global target configured from Admin Settings.

---

## 13. Dynamic Level System

The Level value must not be static.

The level algorithm should be linked to accumulated user progress/performance, particularly successful completion of daily targets.

Conceptually:

```text
Daily Target Completion
        ↓
Historical Target Performance
        ↓
Progress / XP Calculation
        ↓
Level Calculation
        ↓
Achievement/Profile Level
```

The exact XP/level thresholds should be stored in configurable backend logic so they can be changed later without redesigning the page.

A user's level must be consistent between:

- Profile page
- Achievement page
- Leaderboard/user cards wherever level is displayed

---

## 14. Accuracy

Accuracy must be calculated from actual question-level data.

It should not be a static percentage.

Basic formula:

```text
Accuracy % = Correct Answers / Attempted Answered Questions × 100
```

For the Achievement page, use the same defined overall/level-based accuracy source used by the Profile page so the user never sees conflicting percentages on different pages.

---

## 15. Global User Ranking

`Your Rank` should represent the current user's position among all eligible users in the application's global ranking.

Example:

```text
Your Rank: 13th
Total Eligible Users: 1,465
```

This must come from backend calculations.

The rank should change when user performance changes or when other users' qualifying performance changes.

### Global Ranking Inputs

Global ranking should be based on a consistent aggregate performance model using data already collected by the system, such as:

- Level / accumulated progress
- Daily target completion performance
- Quiz performance / points
- Accuracy
- Completed quizzes
- Tie-break conditions

The backend should expose one authoritative calculated ranking value instead of letting different frontend screens independently calculate rank.

### Recommended Priority

A practical initial hierarchy is:

1. Higher level / accumulated progression
2. Higher accumulated ranking points or XP
3. Higher overall accuracy
4. More valid completed quizzes
5. Deterministic tie-breaker

The ranking weights/XP formula should be configurable so it can be refined later without changing the UI.

---

## 16. Active Users

The `Active Users` card must also be backend-driven.

Do not use a static value such as `1465`.

Two values may be shown if required by the existing design:

```text
Active Users: 1,465
Active This Month: 1,006
```

Define them clearly:

- **Active Users:** users considered active according to the application's chosen activity window/business definition.
- **Active This Month:** unique users who generated qualifying activity during the current calendar month.

For true current online status, use the heartbeat/session system from the main roadmap rather than confusing `Active Users` with `Online Now`.

---

## 17. Achievement Ranking Cards

The list/cards below the rank summary must also use real backend user data.

Each row can show information such as:

```text
Rank
Profile Image
User Name
Level
Quiz/Performance Information
Total/Relevant Score
Correct
Wrong
Accuracy
```

The exact visible fields can follow the existing design, but every value must come from backend records.

The list should represent actual globally ranked users rather than duplicated placeholder/demo users.

---

## 18. Current User Position

The page should always make it easy for the logged-in user to understand their own position.

If the current user is Rank 13, the page should display:

```text
Your Rank
13th
```

The ranking list may show top-ranked users while also ensuring the current user's own row/position can be located or highlighted even when they are outside the first few results.

---

# PART C — Relationship Between the Two Ranking Systems

## 19. Do Not Mix Quiz Ranking and Global Ranking

The system has two related but different ranking contexts.

### A. Quiz Result Ranking

Used after performing a particular quiz.

It compares the user's performance with other users for that relevant quiz/ranking context.

Typical data:

- Score
- Correct/Wrong
- Accuracy
- Time taken
- Rank for that quiz

### B. Achievement / Global Ranking

Used to show the user's broader position across the application.

It uses accumulated profile/performance data such as:

- Level
- Overall progress
- Daily target history
- Overall/defined accuracy
- Completed quizzes
- Ranking points/XP
- Global rank

These two ranks must be stored/calculated separately and must not overwrite each other.

---

# PART D — Backend Data Dependencies

## 20. Required Backend Data Sources

These pages should reuse the backend systems already required in the main application roadmap.

Relevant entities/modules include:

```text
Users
User Profiles
User Sessions
Quiz Definitions
Quiz Attempts
Quiz Attempt Answers
Daily Targets
Daily Target Progress
User Statistics
Level / XP Data
Leaderboard / Ranking Data
Attendance (separate system; not automatically a ranking score unless explicitly added later)
```

Avoid duplicating statistics in multiple places unless cached/aggregated values are needed for performance.

---

## 21. Suggested Aggregated User Statistics

For performance, the backend can maintain or calculate a user statistics object containing values such as:

```text
user_id
total_quizzes_completed
total_questions_attempted
total_correct_answers
total_wrong_answers
overall_accuracy
quizzes_completed_this_month
today_quizzes_completed
effective_daily_target
today_target_progress
completed_target_days
current_level
current_xp
global_rank
last_activity_at
```

These values must ultimately be derived from authoritative attempt/target records.

---

# PART E — Real-Time / Refresh Behaviour

## 22. Updating the UI

Ranking and performance data should refresh whenever relevant data changes.

Important refresh events include:

- Quiz submitted
- New quiz attempt completed
- User's score changes
- Daily target progress changes
- Level changes
- Ranking recalculation occurs
- Another user's qualifying performance changes

The implementation may use WebSocket/realtime events where appropriate, or API refresh/polling for screens where second-by-second updates are unnecessary.

The requirement is that the user should see current backend data without stale hard-coded values.

---

## 23. Quiz Submission Flow

```text
User Starts Quiz
        ↓
Create Quiz Attempt
        ↓
User Answers Questions
        ↓
Store/submit answer data
        ↓
User Completes Quiz
        ↓
Backend validates submission
        ↓
Calculate Correct / Wrong / Score / Accuracy / Time
        ↓
Save final Quiz Attempt
        ↓
Update user's aggregate statistics
        ↓
Update daily target progress
        ↓
Recalculate XP / Level if required
        ↓
Recalculate Quiz Ranking
        ↓
Recalculate Global Ranking if affected
        ↓
Return Result + Ranking Data
        ↓
Render Dynamic Result / Leaderboard Page
```

---

## 24. Achievement Page Flow

```text
User Opens Achievement Page
        ↓
Fetch Current User Profile Statistics
        ↓
Fetch Daily Target Progress
        ↓
Fetch Level + Accuracy
        ↓
Fetch Global Rank
        ↓
Fetch Active User Statistics
        ↓
Fetch Global Ranking List
        ↓
Render Existing Achievement UI with Dynamic Data
```

---

# PART F — Admin Panel Visibility

## 25. Ranking Data for Admin

Because all leaderboard values originate from backend records, the Admin Panel should be able to inspect relevant performance data when needed.

For a user, admin should be able to see:

- Total quizzes completed
- Quiz attempt history
- Correct/Wrong statistics
- Accuracy
- Daily target progress/history
- Current level
- Global rank
- Quiz-specific result/rank where applicable

This must remain consistent with the data displayed in the user application.

---

# PART G — Important Business Rules

## 26. Data Consistency

The same backend statistic must produce the same value everywhere.

For example:

```text
Profile Accuracy = Achievement Accuracy
Profile Level = Achievement Level
Profile Quiz Played = backend total completed quiz count
Achievement Global Rank = backend global ranking value
Quiz Result Rank = relevant quiz leaderboard rank
```

Do not independently calculate the same statistic differently on multiple Flutter screens.

---

## 27. Do Not Count Invalid Quiz Activity

Simply opening or starting a quiz should not automatically count it as a completed quiz.

Maintain separate states such as:

```text
Started
In Progress
Submitted / Completed
Abandoned / Incomplete
```

Only a valid completed/submitted quiz should affect completion-based statistics unless a particular metric explicitly measures starts/attempts.

---

## 28. Server-Side Calculation

Important values should be validated/calculated on the backend, including:

- Final score
- Correct/Wrong counts
- Accuracy
- Valid completion
- Daily target completion
- XP/level changes
- Quiz ranking
- Global ranking

Do not trust values calculated only by the Flutter client because they can be manipulated.

---

# PART H — Final Expected Result

After implementation, both pages should behave as a connected dynamic system.

### Quiz Result / Leaderboard

When a user completes a quiz, the system immediately uses the real attempt data to display their score, correct/wrong answers, accuracy, time, performance message, and updated relevant quiz ranking.

### Achievement / Global Ranking

The Achievement page uses accumulated backend profile/performance data to display the user's current level, daily progress, accuracy, global rank, active-user statistics, and the global ranking list.

### Final Principle

> **The existing design is the presentation layer. The backend must become the source of truth for every changing number, ranking, progress value, user statistic, performance message, and leaderboard entry.**

