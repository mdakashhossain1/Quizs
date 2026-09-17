# Quiz Application — Final Feature Implementation Roadmap

## 1. Project Objective

The goal of this update is to convert the existing quiz application into a fully backend-driven system where user activity, quiz attempts, daily targets, attendance, profile statistics, levels, accuracy, online/offline status, and quiz popularity/progress are calculated from real data instead of static frontend values.

The Admin Panel and User App must use the same backend data so that all statistics remain consistent.

---

# 2. User Account & Login Management

## 2.1 Admin-Created User Accounts

Users will primarily be created from the Admin Panel.

Admin should be able to enter:

- User Name
- Email Address
- Login/User ID
- Temporary Password
- Other required profile information

After account creation, the system should send an email to the registered email address with account information and instructions to set/change the password.

## 2.2 First Login Password Change

When a user logs in for the first time using credentials created by the admin:

1. Validate User ID/Email and temporary password.
2. Detect that this is the first login.
3. Force the user to change the temporary password.
4. Save the new password securely.
5. Allow the user to enter the application.

The user must not be allowed to skip the mandatory first-login password change.

## 2.3 Persistent Login

The user should **not be required to enter the username/password every time the app opens**.

After successful authentication:

- Backend generates a secure authentication/session token.
- The app securely stores the token on the device.
- Raw passwords must never be stored locally.
- On future app launches, the app reads the stored token.
- The token is validated with the backend.
- If valid, the user is automatically logged in.
- If expired/revoked/invalid, the user is sent to the login screen.

Recommended flow:

`App Open -> Read Secure Token -> Validate Token -> Valid -> Open App`

`App Open -> Read Secure Token -> Validate Token -> Invalid -> Login Screen`

## 2.4 Password Security

- Passwords must never be stored as plain text.
- Admin must not be able to view an existing user's password.
- Passwords must be securely hashed on the backend.
- Admin may initiate a password reset or create a temporary password.
- Authentication tokens should be stored using secure device storage.

---

# 3. User Session, Activity & Online Status Tracking

## 3.1 Important Requirement

Because a user can remain logged in for a long time through persistent login, **login/logout buttons alone cannot be used to determine whether the user is currently using the app**.

The system should therefore use a combination of:

- Authentication session
- App lifecycle events
- Heartbeat/activity updates
- `last_active_at`
- Backend timeout logic

## 3.2 Login Time

When a user authenticates successfully, save information such as:

- User ID
- Login timestamp
- Device/session identifier
- Session status

Persistent authentication and app activity sessions should be treated separately. A user may remain authenticated for weeks while having many individual app usage sessions.

## 3.3 Heartbeat / Live Activity

While the application is actively being used, it should periodically send a heartbeat/activity update to the backend.

Example concept:

`User Active in App -> Heartbeat -> Backend updates last_active_at`

A practical interval can be selected during implementation, for example approximately every 30–60 seconds while the app is active. This value should be configurable rather than hard-coded throughout the project.

## 3.4 Online / Offline Calculation

Backend should calculate online status using the latest heartbeat/activity timestamp.

Example logic:

- Recent heartbeat within configured timeout -> **Online**
- Heartbeat missing beyond timeout -> **Offline**

For example, a timeout such as 2 minutes may be used initially and made configurable.

Admin Panel can display:

- Online
- Offline
- Last Active
- Last authenticated login
- Current/last app session

## 3.5 Logout / Session End

An exact logout timestamp is available when the user explicitly logs out.

However, users may:

- Close the app
- Kill the app
- Lose internet
- Switch off the phone
- Leave the app in the background

Therefore, the system must not falsely claim an exact manual logout time in these cases.

Instead store/use:

- Explicit logout time, when available
- Last active time
- Session-ended/inactivity timestamp derived from heartbeat timeout

This provides more accurate activity reporting.

## 3.6 Background Limitation

The implementation must account for Android/iOS background execution restrictions. The application should not assume that an unrestricted cron job can continuously run on the phone after the app is backgrounded or killed.

Heartbeat should primarily represent active application usage, while app lifecycle events and backend timeout logic determine when the user becomes inactive/offline.

---

# 4. Admin Panel — User Activity Tracking

Admin should be able to open a user's profile/activity page and inspect their application usage and quiz activity.

## 4.1 User Activity Information

Display:

- User Name
- User ID
- Email
- Online/Offline status
- Last Active Time
- Login/session history
- Explicit Logout Time, where available
- Session End / Inactivity Time
- Total Quiz Attempts
- Completed Quizzes
- Daily Quiz Progress

## 4.2 Filters

Admin should be able to filter activity by:

- User
- Date
- Date Range
- Quiz
- Category
- Topic
- Online/Offline status
- Target status

---

# 5. Quiz Attempt & Question-Level Tracking

Every meaningful quiz attempt must be recorded in the backend.

## 5.1 Quiz Attempt Record

Store information such as:

- Attempt ID
- User ID
- Quiz ID
- Category/Topic where applicable
- Started At
- Submitted/Completed At
- Attempt Status
- Total Questions
- Attempted Questions
- Correct Answers
- Wrong Answers
- Unanswered Questions, if supported
- Score
- Accuracy

## 5.2 Question-Level Attempt Data

For every answered question, save:

- Question ID
- User-selected option
- Correct option / answer reference
- Whether answer was correct or wrong
- Answer timestamp if required

Admin should be able to inspect:

`Question -> Selected Answer -> Correct Answer -> Correct/Wrong`

## 5.3 Important Counting Rule

A quiz should count toward the user's completed quiz statistics and daily target only after the quiz satisfies the application's defined **successful completion/submission** condition.

Simply opening or starting a quiz must not automatically increase the completed quiz count.

Started/abandoned attempts can still be stored separately for analytics.

---

# 6. Daily Quiz Target System

The application needs a configurable daily quiz target system.

There are **two target levels**.

## 6.1 Global Daily Target

Add a setting in:

`Admin Panel -> Settings -> Daily Quiz Target`

Admin can define one default target for all users.

Example:

`Global Daily Target = 20 quizzes`

Users without a custom target automatically use this value.

## 6.2 Individual User Target

Inside:

`Admin Panel -> Users -> Select User -> Target Settings`

Admin can assign a custom target to a particular user.

Example:

- Global Target = 20
- User A Custom Target = 10
- User B Custom Target = 30
- User C = Use Global Target -> 20

## 6.3 Target Priority

Use this rule:

`If User Custom Target Exists -> Use Custom Target`

`Else -> Use Global Target`

Admin should also have an option:

**Use Global Target**

This removes/disables the custom override for that user.

## 6.4 What Counts Toward the Target

Unless a future feature explicitly assigns specific quizzes, users can complete quizzes from **any topic/category**.

The target only checks the number of successfully completed quizzes.

Example:

- Daily Target: 20
- Completed: 14
- Remaining: 6
- Progress: 70%
- Status: In Progress

After completion:

- Daily Target: 20
- Completed: 20
- Progress: 100%
- Status: Completed

## 6.5 Daily Target Status

Possible states:

- Not Started
- In Progress
- Completed
- Not Completed / Missed after the daily period closes

## 6.6 Daily Reset & History

Daily progress resets for the new day, but historical records must remain stored.

Important: store the **effective target for that date** in the user's daily target/progress record.

Example:

If September 16 target was 20 and admin changes the global target to 30 on September 17, September 16 history should still show target = 20.

Changing today's target should follow a clearly defined backend rule and should not rewrite previous days.

## 6.7 Admin Target Monitoring

Admin should be able to view/filter:

- User
- Date
- Effective Target
- Completed Count
- Remaining Count
- Progress Percentage
- Completed
- In Progress
- Not Completed

---

# 7. Attendance Management

## 7.1 Admin Attendance Page

Create a dedicated page:

`Admin Panel -> Attendance`

Admin can mark users as:

- Present
- Absent
- Leave

Attendance should be date-based.

## 7.2 Attendance History

Store:

- User ID
- Attendance Date
- Attendance Status
- Marked By
- Created/Updated Time
- Optional note/reason if required later

## 7.3 User App Attendance Page

Add an **Attendance** item to the application's bottom navigation.

Users should be able to see:

- Today's Attendance
- Attendance History
- Present Days
- Absent Days
- Leave Days
- Attendance Percentage

## 7.4 Attendance Notification

When admin marks or changes attendance, send a push notification.

Example:

**Attendance Updated**

`Your attendance for today has been marked as Present.`

Tapping the notification should deep-link/open the Attendance page where supported.

## 7.5 Attendance vs Quiz Target

Attendance and quiz target are separate systems.

Completing the daily quiz target must **not automatically mark attendance**, unless this business rule is explicitly added later.

Admin controls attendance.

---

# 8. Profile Progress System

The existing Profile UI contains a profile image, circular progress indicator, Level, Accuracy and quiz statistics. These values should become backend-driven.

## 8.1 Circular Daily Progress

The circular ring around/near the user's profile should represent **today's daily quiz target progress**.

Formula:

`Daily Progress % = Completed Qualifying Quizzes Today / Effective Daily Target * 100`

Cap the visual progress at 100% unless the UI intentionally supports over-target progress.

Example:

Target = 20

Completed = 5

Progress = 25%

As quizzes are successfully completed, the ring fills progressively until it reaches a full circle at 100%.

## 8.2 Level System

The Level must no longer be a static frontend value.

Level should be calculated using accumulated daily-target performance.

The exact XP/level thresholds should be stored centrally/configurably rather than scattered through frontend code.

Recommended conceptual structure:

- Completing daily targets contributes to Level/XP progression.
- Historical completed targets contribute to accumulated progression.
- Missing a target should not arbitrarily corrupt historical XP.
- Backend is the source of truth for Level/XP.

The exact level thresholds can be finalized as a separate configurable algorithm without changing the rest of the architecture.

## 8.3 Accuracy

Accuracy must remain separate from daily progress.

Based on the agreed requirement, the Profile Accuracy should represent the user's question-answer accuracy for the relevant accumulated level/progression period, using actual backend attempt data.

Base formula:

`Accuracy = Correct Answers / Answered Questions * 100`

Do not include unanswered questions in the denominator unless the product later explicitly defines them as wrong.

The backend should expose the correct aggregated value so frontend does not recalculate inconsistent statistics.

## 8.4 Important Separation

These three values represent different concepts:

- **Circular Progress** -> Today's daily target completion
- **Level** -> Accumulated progression from daily target performance
- **Accuracy** -> Correct-answer percentage calculated from real question-attempt data for the defined progression scope

They must not be treated as the same percentage.

---

# 9. Profile Quiz Statistics

The existing Profile cards such as **Quiz Played, Right, Wrong, This Month** must be connected to backend data.

## 9.1 Quiz Played

Show the total number of successfully completed quiz attempts by the logged-in user according to the application's counting rule.

## 9.2 Right

Show the total number of correct answers from the user's counted quiz attempts.

## 9.3 Wrong

Show the total number of wrong answers from the user's counted quiz attempts.

## 9.4 This Month

Show the number of successfully completed quizzes by the user during the current calendar month.

The backend must calculate the month using the application's configured business timezone/date boundaries consistently.

## 9.5 Single Source of Truth

These values must come from the same attempt/question records used by the Admin Panel.

Do not maintain unrelated frontend counters.

Therefore:

`Profile Stats = Backend Quiz Attempt Data`

and

`Admin Stats = Same Backend Quiz Attempt Data`

---

# 10. Quiz List / Leaderboard Card — Played Count

The existing quiz/list card currently displays a static **Played** value. It must become dynamic.

## 10.1 Played Definition

For the current requirement, **Played should represent unique users who successfully completed that quiz**.

Example:

- User A completes quiz 3 times
- User B completes quiz 2 times
- User C completes quiz 1 time

Displayed:

`Played: 3`

not 6.

Recommended backend calculation concept:

`COUNT(DISTINCT user_id)` for qualifying completed attempts of that quiz.

If total attempt count is needed later, expose it as a separate metric instead of changing the meaning of Played.

---

# 11. Quiz Card Progress Bar

The progress bar below each quiz should also use real backend data.

## 11.1 Progress Meaning

For the agreed design, the progress bar represents **quiz completion rate among unique users who started that quiz**.

Formula:

`Completion Rate = Unique Users Who Completed Quiz / Unique Users Who Started Quiz * 100`

Example:

- 100 unique users started
- 75 unique users completed

Progress Bar = 75%

## 11.2 Edge Cases

If nobody has started the quiz:

`Completion Rate = 0%`

The backend should prevent division-by-zero errors.

Multiple attempts by the same user should not artificially inflate the unique-user completion rate.

## 11.3 Real-Time / Near-Real-Time Update

As users start and complete quizzes, the backend statistics should update and the frontend should fetch/refresh the latest value.

"Real-time" does not require continuously querying the database every second. The implementation may use API refresh, event-based updates, WebSocket/realtime infrastructure, or another efficient approach depending on the existing stack.

---

# 12. Ranking Data

Any ranking position displayed in the UI, such as:

- #1
- #2
- #3

must not remain hard-coded.

Ranking should be calculated by the backend using an explicitly defined ranking metric.

Because the final ranking metric has not yet been specified, implement the ranking service so the rule can be configured/finalized separately (for example score, accuracy, completions, XP, or another approved metric).

Do not invent a ranking rule in frontend code.

---

# 13. Suggested Backend Data Model

The exact schema can be adapted to the existing backend, but the system will likely require entities/tables equivalent to the following.

## users

- id
- name
- email
- login_id
- password_hash
- must_change_password
- custom_daily_target nullable
- status
- created_at
- updated_at

## user_sessions

- id
- user_id
- session/device identifier
- authenticated_at
- last_active_at
- explicit_logout_at nullable
- session_ended_at nullable
- status
- created_at
- updated_at

## quizzes

- id
- title
- category_id
- topic_id
- status
- other existing fields

## quiz_attempts

- id
- user_id
- quiz_id
- started_at
- completed_at nullable
- status
- total_questions
- attempted_questions
- correct_count
- wrong_count
- unanswered_count
- score
- accuracy
- created_at
- updated_at

## quiz_attempt_answers

- id
- attempt_id
- question_id
- selected_option_id / selected_answer
- correct_option_id / correct_answer reference
- is_correct
- answered_at

## app_settings

Possible settings:

- global_daily_quiz_target
- heartbeat_interval_seconds
- online_timeout_seconds
- timezone/business-day configuration
- level configuration/reference

## user_daily_progress

- id
- user_id
- date
- effective_target
- completed_quizzes
- progress_percentage
- target_status
- target_completed_at nullable

## attendance

- id
- user_id
- date
- status
- marked_by
- note nullable
- created_at
- updated_at

## user_progression / level records

Depending on implementation:

- user_id
- current_level
- xp/points
- completed_target_days
- progression metadata
- updated_at

---

# 14. Backend as Source of Truth

The following must **not** be hard-coded in the frontend:

- Quiz Played
- Correct Answers
- Wrong Answers
- This Month quiz count
- Played count on quiz cards
- Quiz completion progress
- Daily target progress
- Level
- Accuracy
- Online/Offline status
- Attendance
- Ranking

Frontend should display values returned/calculated by the backend.

---

# 15. API / Service Responsibilities

The AI/development agent should design APIs/services for at least these responsibilities:

### Authentication

- Admin creates user
- Login
- First-login password change
- Token validation/refresh as applicable
- Logout
- Password reset

### User Activity

- Heartbeat/update activity
- Current online status
- Session history
- Last active

### Quiz Attempts

- Start attempt
- Save/update answers
- Submit/complete attempt
- Retrieve attempt details
- Retrieve user attempt history
- Admin attempt inspection

### Targets

- Get global target
- Update global target
- Get/set/remove individual target override
- Get today's effective target
- Get daily progress
- Get historical progress

### Attendance

- Mark/update attendance
- Get attendance history
- Calculate attendance summary
- Send attendance notification

### Profile

Provide a consolidated profile/statistics response containing values such as:

- Current Level
- Level progression information
- Today's Target
- Today's Completed Count
- Today's Progress Percentage
- Accuracy
- Quiz Played
- Right
- Wrong
- This Month
- Attendance summary where needed

### Quiz Statistics

- Unique users played/completed
- Unique users started
- Completion rate
- Ranking information when ranking rules are finalized

---

# 16. Data Flow

## Login & Activity

`Admin Creates User`

-> `Email / Account Setup`

-> `User First Login`

-> `Mandatory Password Change`

-> `Secure Token Stored`

-> `Future App Open Uses Token`

-> `Backend Token Validation`

-> `App Session Starts/Resumes`

-> `Heartbeat / Activity Updates`

-> `last_active_at Updated`

-> `Backend Calculates Online/Offline`

## Quiz Flow

`User Opens Quiz`

-> `Attempt Created`

-> `Questions Answered`

-> `Selected Answers Saved`

-> `Quiz Submitted`

-> `Correct/Wrong Calculated`

-> `Attempt Marked Completed`

-> `Daily Completed Count Updated`

-> `Profile Statistics Updated`

-> `Quiz Played/Completion Statistics Updated`

-> `Admin Panel Reflects Same Data`

## Target Flow

`Check User Custom Target`

-> If available: `Use Custom Target`

-> Otherwise: `Use Global Target`

-> Save effective target for the date

-> Count qualifying completed quizzes

-> Calculate progress

-> Update target status

-> Feed Profile circular progress + progression logic

## Attendance Flow

`Admin Marks Attendance`

-> `Attendance Saved`

-> `Push Notification Sent`

-> `User Opens Attendance Page`

-> `Attendance History/Summary Updated`

---

# 17. Admin Panel UI Changes

The Admin Panel should include/update:

## Dashboard / User Monitoring

- Online users
- Offline users
- User activity information
- Target completion summaries where appropriate

## Users Page

Each user should expose:

- Profile details
- Online status
- Last active
- Session/activity history
- Quiz statistics
- Quiz attempt details
- Daily target progress
- Custom Target setting
- Attendance history
- Password reset/account controls

## Settings

Add:

### Daily Quiz Target

- Global target value
- Save/update action

Potential technical settings such as heartbeat timeout should normally remain developer/system configuration unless the product explicitly wants them editable by administrators.

## Attendance

Dedicated attendance management interface with date and user filters.

---

# 18. User App UI Changes

## Profile Screen

Connect existing UI to backend:

- Profile circular ring -> Today's target progress
- Level -> Backend progression algorithm
- Accuracy -> Backend question accuracy
- Quiz Played -> Backend count
- Right -> Backend correct answers
- Wrong -> Backend wrong answers
- This Month -> Backend monthly completed quiz count

## Attendance Screen

Add to bottom navigation:

- Today's status
- History
- Present/Absent/Leave totals
- Attendance percentage

## Quiz Cards / List

Replace static data with:

- Played -> Unique users who completed
- Progress bar -> Unique-user completion rate
- Ranking -> Backend-calculated ranking once metric is finalized

---

# 19. Important Technical Rules

1. **Never store passwords in plain text.**
2. **Never store raw passwords locally for auto-login.** Use secure tokens.
3. **Backend is the source of truth** for statistics and progress.
4. Persistent login does not mean the user is permanently online.
5. Online status should be based on recent activity/heartbeat plus timeout.
6. Do not depend on an exact logout event because mobile apps can be killed unexpectedly.
7. Quiz completion count should increase only when the defined completion condition is met.
8. Preserve historical daily target values.
9. Individual target overrides global target.
10. Removing individual override returns the user to the global target.
11. Attendance remains separate from quiz target completion.
12. Use unique-user counting for the agreed quiz-card Played metric.
13. Use unique starters vs unique completers for the agreed quiz completion-rate bar.
14. Avoid frontend-only counters or calculations that can become inconsistent with backend records.
15. Use transactions/idempotency where needed so retries do not double-count quiz completions or answers.
16. Use a consistent application/business timezone for daily and monthly calculations.
17. Protect admin APIs with proper authorization/roles.
18. Users must only be able to access their own private activity/attendance data unless authorized otherwise.

---

# 20. Implementation Order

The development/AI agent should implement this upgrade in dependency order.

### Phase 1 — Audit Existing System

- Inspect current database
- Inspect authentication
- Inspect quiz start/submit flow
- Inspect existing Profile UI
- Inspect Admin Users page
- Identify currently static values
- Reuse existing functionality instead of creating duplicate systems

### Phase 2 — Authentication & Persistent Session

- Admin-created accounts
- Password security
- First-login password change
- Secure persistent login
- Session records

### Phase 3 — Activity Tracking

- App lifecycle integration
- Heartbeat
- Last active
- Online/offline calculation
- Session history

### Phase 4 — Quiz Attempt Tracking

- Attempt records
- Question-level answers
- Correct/wrong calculation
- Completion rules
- Admin attempt inspection

### Phase 5 — Target System

- Global target setting
- User-specific override
- Effective target resolution
- Daily progress
- Historical target records

### Phase 6 — Profile Integration

- Circular daily progress
- Level/progression backend integration
- Accuracy
- Quiz Played
- Right
- Wrong
- This Month

### Phase 7 — Attendance

- Admin attendance page
- Attendance records
- App attendance page
- Push notifications

### Phase 8 — Quiz Card Statistics

- Unique Played count
- Unique start count
- Completion rate
- Dynamic progress bar
- Backend ranking architecture

### Phase 9 — Admin Filters & Reporting

- User filters
- Date/date-range filters
- Quiz/category/topic filters
- Target filters
- Online/offline filters
- Attendance filters

### Phase 10 — Testing

Test at minimum:

- First login
- Auto-login after app restart
- Invalid/expired token
- Explicit logout
- App killed without logout
- Internet disconnected
- Heartbeat timeout
- Online -> Offline transition
- Quiz started but abandoned
- Quiz successfully completed
- Multiple attempts of same quiz
- Correct/wrong answer storage
- Global target
- Custom user target
- Custom target removal
- Target history after settings change
- Daily reset
- Month change
- Attendance mark/update
- Push notification
- Profile counters
- Played unique-user count
- Completion rate
- Permission/security checks

---

# 21. Acceptance Criteria

The implementation is considered complete when:

- A user can remain securely logged in without entering credentials on every app launch.
- Admin can determine user activity using backend session/heartbeat information.
- Online/offline status is calculated from recent activity instead of only login/logout buttons.
- Every quiz attempt and relevant question answer is stored correctly.
- Admin can inspect which answers were correct and wrong for a user.
- Global daily target can be set for all users.
- A particular user can receive a custom target that overrides the global value.
- Daily target progress is calculated from real completed quizzes.
- Historical target data remains correct after future target changes.
- Profile circular progress reflects today's target.
- Level uses backend progression rather than a static value.
- Accuracy uses actual question-answer data.
- Quiz Played, Right, Wrong and This Month display actual backend statistics.
- Attendance can be marked from Admin Panel and viewed in the app.
- Attendance updates trigger push notifications.
- Quiz-card Played count reflects unique users who completed the quiz.
- Quiz-card progress reflects unique-user completion rate.
- Ranking is backend-driven once its business metric is finalized.
- Admin Panel and User App display consistent statistics from the same source of truth.

---

# 22. Items That Must Remain Configurable / Finalized

The AI agent must **not invent business rules** for the following if they are not already present in the existing project:

- Exact XP required for each Level
- How much XP/level credit is awarded for completing a daily target
- Exact ranking metric
- Whether over-target quizzes provide additional XP
- Whether partially completed quizzes contribute to any separate metric
- Exact heartbeat interval and offline timeout after technical testing

These should be implemented as configurable rules or clearly isolated services so they can be changed later without rebuilding the application.

---

# Final Development Principle

**Do not redesign the existing application unnecessarily.**

First inspect the existing project, database, APIs, Admin Panel and UI. Keep the current design wherever possible and replace static/mock values with backend-driven values.

The final architecture should follow:

`User Actions -> Backend Records -> Backend Calculations -> API -> Admin Panel + User App`

Both interfaces must consume the same backend data so that activity, quiz statistics, targets, attendance, progress, levels and quiz popularity remain synchronized and auditable.
