# Product Requirements Document (PRD)
## Offerwall Integration for Existing Flutter Application

**Document Version:** 1.0  
**Project Type:** Existing Flutter Application Enhancement  
**Platforms:** Flutter App + Existing Backend + Existing Admin Panel  
**Feature:** Offerwall Integration & User-Level Tracking  

---

## 1. Objective

Integrate an Offerwall into the existing Flutter application so users can access and complete available offers directly from the app.

The implementation **will not include any coin, points, wallet, cashback, or user reward system**. The primary purpose is to provide Offerwall access while allowing the client/admin to track which users have completed offers.

The feature must be integrated into the existing application, backend, database, and admin panel without unnecessarily rebuilding existing modules.

---

## 2. Scope of Work

The implementation includes:

1. Offerwall integration in the existing Flutter application.
2. Offerwall access from the application's Home Page.
3. Identification of the logged-in user when they access/complete an offer.
4. User-level offer completion tracking.
5. Backend integration for receiving and storing Offerwall activity/completion data.
6. Required database changes.
7. A dedicated **Offerwall** section in the existing Admin Panel.
8. User-wise activity visibility.
9. Date-wise activity visibility.
10. Integration testing and verification.

---

## 3. Flutter Application Requirements

### 3.1 Home Page Integration

Add an Offerwall entry/card/button/section to the existing Home Page.

When a logged-in user selects the Offerwall option:

- The Offerwall should open through the supported integration method.
- The currently authenticated user's unique identifier must be associated with the Offerwall session wherever supported by the provider.
- Existing authentication/session behavior must remain unchanged.

The UI should follow the existing application's design system rather than introducing an unrelated visual style.

### 3.2 User Identification

Every Offerwall interaction that needs attribution must be linked to the application's existing user account.

Use an existing stable internal identifier such as:

- User ID
- UUID
- Another existing unique account identifier

Do not rely only on display name or other non-unique values for attribution.

### 3.3 No Coin/Reward System

This version specifically excludes:

- Coins
- Points
- Wallet balance
- Cashback
- Redeem functionality
- Reward balance
- User-facing earnings from completed offers

Completing an Offerwall offer should therefore **not increase any balance or provide an in-app coin benefit**.

---

## 4. Offer Completion Tracking

The system must track completed offers at the user level.

Where supported by the selected Offerwall provider, completion/status information should be received through the provider's official callback/postback/server-side mechanism rather than relying only on client-side confirmation.

For each completion/activity record, store the relevant data available from the provider, such as:

| Field | Description |
|---|---|
| User ID | Internal application user identifier |
| Offer/Transaction ID | Provider's unique offer or transaction reference |
| Offer Name/ID | Offer information when provided |
| Status | Completion/status received from provider |
| Provider | Offerwall/provider identifier if required |
| Completion Date | Date of completion |
| Completion Time | Time of completion |
| Created At | Database record creation timestamp |

Additional provider-specific fields may be stored where necessary for reliable reconciliation or debugging.

### Duplicate Protection

If the provider retries the same completion callback/postback, the backend should not create duplicate completion records.

Where available, the provider's transaction/event ID should be used for idempotency.

---

## 5. Backend Requirements

The existing backend must be extended to support Offerwall tracking.

Required backend work includes:

- Offerwall callback/postback endpoint(s), where applicable
- User mapping
- Completion/activity processing
- Database storage
- Duplicate-event protection
- Validation of incoming provider data where supported
- Admin Panel data retrieval
- Date-based filtering
- User-based filtering

The implementation should follow the existing project's architecture and coding conventions.

Sensitive provider credentials, secrets, or verification keys must remain on the server/configuration layer and must not be hardcoded into the Flutter client.

---

## 6. Database Requirements

Create or extend the necessary database structure for Offerwall records.

A typical structure may include:

```text
offerwall_transactions

id
user_id
provider
offer_id
offer_name
transaction_id
status
completed_at
provider_payload
created_at
updated_at
```

The final schema may be adjusted according to the existing backend and the actual data supplied by the Offerwall provider.

The implementation should preserve existing project data and avoid breaking current database relationships.

---

## 7. Admin Panel – Offerwall Module

Add a new menu/module to the existing Admin Panel:

**Offerwall**

This section will allow administrators to review Offerwall activity and completion records.

### 7.1 Offerwall Records

The administrator should be able to view relevant information such as:

- User
- User ID
- Offer/Offer ID
- Transaction ID
- Completion status
- Completion date
- Completion time

Only fields actually available from the provider need to be displayed.

### 7.2 User-Wise Tracking

The administrator must be able to identify:

- Which user completed an offer
- The user's recorded Offerwall activity/completions
- Relevant completion details

A user filter/search should be provided using existing searchable user information supported by the project.

### 7.3 Date-Wise Tracking

Provide date filtering so the administrator can review Offerwall records for a selected period.

At minimum:

- Single date or date-range filtering

Example:

```text
From: 20 Sep 2026
To:   25 Sep 2026
```

The resulting list should show only records falling within the selected period.

### 7.4 Combined Filtering

Where practical within the existing admin architecture, filters should work together.

Example:

```text
User: User #1024
From: 20 Sep 2026
To:   25 Sep 2026
```

This allows the client to check the selected user's Offerwall records for that specific period.

---

## 8. User Flow

```text
User Logs In
      ↓
Home Page
      ↓
Selects Offerwall
      ↓
Offerwall Opens
      ↓
User Browses/Completes an Offer
      ↓
Offerwall Provider Confirms Completion
      ↓
Backend Receives Completion Data
      ↓
Completion Mapped to User
      ↓
Record Stored in Database
      ↓
Admin Panel → Offerwall
      ↓
Admin Reviews User-Wise / Date-Wise Records
```

---

## 9. Existing Project Integration

This is an enhancement to the **existing project**, not a separate application.

The development team must:

- Reuse the existing authentication system.
- Reuse existing users and user IDs.
- Reuse the existing backend architecture.
- Extend the existing admin panel.
- Follow existing database conventions where appropriate.
- Avoid unnecessary duplication of modules.
- Ensure existing application features continue to work after integration.

---

## 10. Error Handling

The implementation should gracefully handle scenarios such as:

- Offerwall failing to load
- Network unavailable
- Invalid/missing user session
- Provider callback failure
- Invalid callback data
- Duplicate callback/event
- Backend/database failure

The user should not receive a false completion confirmation merely because an Offerwall screen was opened or an offer was clicked.

---

## 11. Testing Requirements

Before delivery, verify:

- Offerwall opens correctly from the Home Page.
- Logged-in user identification is passed/mapped correctly where supported.
- Offer completion data reaches the backend.
- Completion is assigned to the correct user.
- Duplicate provider callbacks do not create duplicate transactions.
- Records appear correctly in the Admin Panel.
- User-wise filtering works.
- Date-wise filtering works.
- Existing app functionality remains operational.
- No coins/rewards are credited to users.
- Basic failure/network scenarios are handled properly.

---

## 12. Out of Scope

The following features are **not included in this version** unless separately requested:

- Coin system
- Points system
- Wallet
- Cashback
- User reward distribution
- Withdrawal/redeem functionality
- Referral rewards
- Gamification based on Offerwall completion
- New authentication system
- Complete redesign of the existing application/admin panel
- Analytics beyond the defined Offerwall tracking requirements

These can be added as separate enhancements in future versions.

---

## 13. Acceptance Criteria

The feature will be considered successfully implemented when:

1. Users can access the Offerwall from the existing Flutter application's Home Page.
2. Offerwall activity/completions can be attributed to the correct logged-in user where supported by the provider.
3. Valid completion data is stored in the backend/database.
4. The Admin Panel contains a dedicated Offerwall section.
5. The administrator can review user-wise Offerwall records.
6. The administrator can review/filter records date-wise.
7. No coin/reward system is introduced.
8. Duplicate completion callbacks are handled safely.
9. Existing major application functionality is not broken by the integration.
10. The completed implementation passes integration testing.

---

## 14. Delivery

**Estimated Implementation Timeline:** 2 days, subject to availability of valid Offerwall provider credentials/API or SDK access and the existing project's required source/backend access.

After implementation, the required application build/files will be provided for review and testing.

---

## 15. Commercial Reference

**Development Cost:** ₹3,000  
**GST (18%):** ₹540  
**Total:** ₹3,540

This commercial reference covers the scope defined in this PRD. Any additional functionality outside this scope can be treated as a separate enhancement.
