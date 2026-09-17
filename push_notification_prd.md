# PRD — Server-Driven Push Notification System

## 1. Feature Overview

The application must support **server-driven push notifications** that can be created and sent from the Laravel Admin Panel/backend to users of the Flutter application.

The existing in-app/system notifications should continue working as they currently do. This feature is an **additional notification channel** and must not remove or break the existing notification functionality.

The main purpose is to allow the admin/server to send rich push notifications containing:

- Notification title
- Notification message/body
- Thumbnail/image
- Optional destination/deep-link information
- Target audience information
- Created/sent timestamp

When a user taps the push notification, the Flutter application should open and navigate directly to the relevant screen whenever a destination has been configured.

---

## 2. Main Requirement

Admin must be able to send push notifications from the server/Admin Panel instead of depending only on notifications generated locally inside the Flutter application.

Example flow:

`Admin Panel → Create Notification → Laravel Backend → Push Notification Provider → User Device → User Taps Notification → Flutter App Opens → Relevant App Page Opens`

No notification title, body, thumbnail URL, or destination should be hardcoded in Flutter when the notification is created from the server.

---

## 3. Admin Panel — Push Notification Management

Create a dedicated **Push Notifications** section in the Laravel Admin Panel.

The admin should be able to enter/select:

- Notification Title
- Notification Description / Message
- Thumbnail Image
- Target users/audience
- Destination Type
- Destination ID or route parameters when required
- Send action

The architecture should be extensible so scheduling, notification history, resend functionality, or additional audience filters can be added later without redesigning the complete notification system.

---

## 4. Rich Notification With Thumbnail

Push notifications must support an image/thumbnail.

Example visual structure:

**Title:** New Quiz Available

**Message:** A new Mathematics quiz has been added. Start now and improve your rank.

**Thumbnail:** Quiz/banner image uploaded from the Admin Panel.

The backend must send a **publicly accessible absolute HTTPS URL** for the thumbnail in the push payload. The Flutter notification implementation should use this URL to display a rich notification where the target platform/provider supports notification images.

If the image cannot be downloaded, the notification must still be displayed using its title and message instead of failing completely.

---

## 5. IMPORTANT — Laravel Image Storage Requirement for Shared Hosting

This project is deployed on **shared hosting**. Therefore, notification thumbnails must be stored in a location that is directly accessible from the public web.

For this project, notification thumbnail uploads should be saved under a dedicated folder inside Laravel's web-accessible `public` directory, for example:

`public/uploads/notifications/`

Example server file:

`public/uploads/notifications/notification_123.jpg`

The backend must generate a public absolute URL similar to:

`https://<application-domain>/uploads/notifications/notification_123.jpg`

The push notification payload must contain the **public URL**, not a local filesystem path.

### Do not assume private Laravel storage is publicly accessible

Do not save a thumbnail only to a private/non-public storage location and then send its filesystem path to the mobile application.

For example, a value similar to:

`storage/app/.../image.jpg`

must not be sent directly as the notification image URL unless that deployment has explicitly exposed it through a working public URL/symlink configuration.

Because this project uses shared hosting and the required deployment approach is direct public access, the implementation should use the dedicated `public/uploads/notifications/` location for notification thumbnails.

---

## 6. Thumbnail Upload Flow

Required flow:

1. Admin selects a thumbnail from the Push Notification form.
2. Laravel validates the uploaded image.
3. Laravel generates a safe/unique filename.
4. Image is stored in `public/uploads/notifications/`.
5. Laravel generates the absolute HTTPS URL of the uploaded image.
6. Public URL is saved with the notification record.
7. The same public URL is included in the push notification payload.
8. Flutter receives the payload and displays the image in the notification when supported.

The implementation should validate file type and file size and should not trust the original uploaded filename.

---

## 7. Notification Targeting

The system should support server-side audience targeting.

At minimum, architecture should support:

- All Users
- Single User
- Selected Users

The system should be designed so additional groups can be added later, such as users who have not completed today's target, active users, inactive users, or category-specific users.

For an individual user notification, the backend should resolve that user's currently registered device/push token(s).

---

## 8. Device Token Registration

After the user logs into the Flutter application, the application must register its push notification token with the Laravel backend.

Recommended data relationship:

`User → Device(s) → Push Token(s)`

Do not assume one user always has exactly one permanent token. A user may log in from multiple devices and notification providers can refresh/rotate device tokens.

The app/backend should therefore support:

- Creating a device token record
- Updating/refeshing an existing token
- Associating tokens with the authenticated user
- Deactivating/removing invalid tokens
- Supporting multiple devices for one user if required

Persistent login does not remove this requirement. When an existing authenticated session opens the app, the current push token should still be validated/synchronized with the backend when appropriate.

---

## 9. Notification Payload

The server should send structured data rather than only a title and message.

Conceptual payload fields:

```text
notification_id
notification_type
title
body
image_url
target_type
destination_type
destination_id
destination_route
additional_data
```

The exact provider-specific payload can be implemented by the backend developer, but Flutter must receive enough information to determine what should happen after the user taps the notification.

---

## 10. Click Action / Deep Linking

A push notification must not simply disappear after being tapped.

When the user clicks it:

1. Flutter app should open.
2. Notification payload should be read.
3. Authentication/session state should be checked.
4. Destination should be resolved.
5. User should be navigated to the correct screen.

Examples:

`New Quiz Notification → Open Quiz Details`

`Attendance Notification → Open Attendance Page`

`Daily Target Notification → Open Target/Progress Screen`

`Achievement Notification → Open Achievement Page`

`General Announcement → Open Notification Details or configured destination`

This must work when the application is:

- Already open (foreground)
- Running in background
- Closed/terminated and launched by tapping the notification

If a notification points to protected content and the user's authentication session is no longer valid, the app should complete authentication/login first and then continue to the intended destination where practical.

---

## 11. Notification Database Record

Server-generated notifications should be stored in the database so they can be tracked and extended later.

Suggested information:

```text
id
title
body
thumbnail_url
target_type
target_user_id / audience information
destination_type
destination_id
payload_data
status
created_by
created_at
sent_at
```

Recipient/delivery records may be stored separately when per-user delivery/read tracking is required.

---

## 12. Existing Notifications vs Push Notifications

The system must clearly separate these concepts:

### Existing Application Notifications

Current notifications already implemented in the application should continue working without regression.

### Server Push Notifications

These are notifications initiated by Laravel/Admin Panel and delivered to the device using the configured push notification service.

Both systems can coexist.

Where appropriate, the same event can create an in-app notification record and also send a device push notification, but duplicate notifications should be avoided.

---

## 13. Example — Attendance Push Notification

When admin marks attendance:

```text
Title: Attendance Updated
Message: Your attendance for today has been marked as Present.
Thumbnail: <public HTTPS image URL>
Destination: attendance
```

On tap:

`Push Notification → App Opens → Attendance Page`

This should integrate with the Attendance feature already defined in the main application roadmap.

---

## 14. Example — New Quiz Push Notification

Admin creates a new quiz and sends an announcement:

```text
Title: New Quiz Available
Message: A new Science quiz is now available. Start playing now.
Thumbnail: <public HTTPS thumbnail URL>
Destination: quiz_details
Destination ID: <quiz_id>
```

On tap:

`Push Notification → App Opens → Quiz Details → Selected Quiz`

The quiz ID must come from the backend payload and must not be hardcoded in Flutter.

---

## 15. Security and Validation

Implementation must include the following safeguards:

- Only authorized admin roles can send push notifications.
- Uploaded thumbnail files must be validated.
- Generate safe unique filenames instead of trusting uploaded names.
- Store only required public assets in the public upload directory.
- Use HTTPS image URLs in production.
- Validate destination type and destination ID before navigation.
- Do not put passwords, authentication tokens, or sensitive private data inside push notification title/body/image URL.
- Invalid/expired device tokens should be handled and disabled/removed appropriately.

---

## 16. Failure Handling

The feature should fail gracefully.

### Thumbnail fails

Display text notification without the image.

### Invalid destination

Open the application normally or open a safe default notification/details screen instead of crashing.

### Invalid device token

Record/handle the provider failure and mark the token invalid where appropriate.

### User is logged out

Open authentication first and preserve the intended destination when possible.

### Push provider temporarily fails

The notification database record should remain available so failure can be logged and retry functionality can be added later.

---

## 17. Acceptance Criteria

The feature is complete when:

- Admin can create a push notification from Laravel Admin Panel.
- Admin can upload a notification thumbnail.
- Thumbnail is saved under the project's publicly accessible notification upload directory.
- Backend produces an absolute public HTTPS thumbnail URL.
- Server can send the notification to the intended user(s).
- Flutter receives and displays the notification.
- Thumbnail appears in rich notifications on supported devices/platforms.
- Notification still works if the thumbnail fails.
- Clicking a notification opens the Flutter application.
- Clicking can navigate to the configured internal page.
- Navigation works from foreground, background, and terminated states as supported by the platform.
- Existing notification functionality remains intact.
- Push/device tokens are associated with users and can be refreshed.
- Notification content and navigation data are backend-driven rather than hardcoded.

---

## 18. Final Implementation Principle

**The Laravel backend is the source of truth for server-generated push notification content and routing metadata.**

Flutter is responsible for:

`Receive Payload → Render Notification → Handle Tap → Validate Session → Navigate`

Laravel is responsible for:

`Admin Input → Validate → Store Thumbnail in Public Directory → Generate Public URL → Build Payload → Select Recipients → Send Push → Store/Log Notification`

This separation must be maintained so future notification types can be added from the server without repeatedly hardcoding notification content into the Flutter application.
