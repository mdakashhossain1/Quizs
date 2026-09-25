# Offerwall integration

The app uses Unity Web Offerwall (the Tapjoy-backed Unity Offerwall product), independently of its existing LevelPlay banner and interstitial setup. It opens Unity's official link in an in-app browser on supported mobile platforms, with the platform browser as fallback. No new native SDK dependency is required.

## Activate

1. Create a Web Offerwall app, placement and content card in the Unity/Tapjoy publisher dashboard. The existing LevelPlay app key is **not** an Offerwall SDK key.
2. Obtain the Web Offerwall SDK key and a separate callback verification secret. Use a secret unique to this Offerwall app.
3. Configure the callback URL as `https://quizs.in/api/offerwall/callback/unity` (adjust the hostname for other deployments).
4. Select the callback format below and configure the matching server environment values.
5. Run `php artisan migrate --force` from `admin`, then `php artisan config:cache` on the deployed server. The migration adds a table; it does not alter existing quiz/user records.
6. Install the updated Flutter build and complete the live checks below.

```dotenv
OFFERWALL_ENABLED=true
OFFERWALL_SDK_KEY="your-web-offerwall-sdk-key"
OFFERWALL_PLACEMENT="#WebOfferwall"
OFFERWALL_CALLBACK_SECRET="your-app-specific-callback-secret"
OFFERWALL_CALLBACK_MODE=hmac_sha256
```

Keep the verification secret exclusively on the server. The SDK key is a public identifier in Unity's launch URL; the app retrieves it from the authenticated backend instead of embedding it in the build. Never reuse the Laravel application key or the LevelPlay app key as a callback secret.

The feature stays unavailable until enabled with both keys. Setting `OFFERWALL_ENABLED=false` stops new launches while still accepting correctly signed callbacks for offers already in progress.

## Callback formats

**Recommended: `hmac_sha256`.** Ask Unity/Tapjoy support to enable its improved POST callback for the account. The backend verifies `X-Tapjoy-Signature` against HMAC-SHA256 of the exact request body. It reads `id`, `user.id`, `timestamp` and optional offer/task/placement details. The header is a lowercase hexadecimal digest. This mode rejects GET callbacks.

**Standard GET integration: `legacy_md5`.** If the account uses standard callbacks, explicitly set this mode. Unity sends `id`, `snuid`, `currency` and `verifier`. Verification uses Unity's documented MD5 of `id:snuid:currency:secret`. The amount is used only for signature verification and is never credited or stored as a balance. This format does not authenticate offer metadata or supply a completion timestamp, so the admin shows the first receipt time with a “Time first received” label. This mode rejects POST callbacks.

Only the configured format is accepted; there is no automatic downgrade. Invalid signatures, invalid data, unknown users and conflicting attribution receive 403. Valid new events and duplicates receive 200. Database failures receive a server error so the provider can retry. Callbacks are processed synchronously without a queue or cron dependency.

The account ID comes from Sanctum authentication on `POST /api/offerwall/launch`, not from Flutter request parameters. Canonical numeric account IDs are required on callbacks (for example, `0123` is rejected rather than mapped to `123`). A database unique constraint on provider plus a SHA-256 transaction key protects retries, including concurrent callbacks, while preserving case-sensitive provider references on MySQL installations with case-insensitive text collations.

## Tracking and admin

The **Offerwall** sidebar entry shows confirmed transactions, user identity, available offer details, status, completion time and receipt time. Search accepts name, email and login ID. Exact user ID and inclusive date-range filters can be combined. Dates use the displayed Laravel application timezone; pagination preserves filters.

For multi-step offers, each provider-confirmed transaction is recorded separately. A callback confirms the associated offer task; it does not necessarily mean every task in a multi-step offer is finished. Opening or closing the browser creates no completion record. User deletion cascades to that user's Offerwall records.

There are no coins, wallet, balance, score, XP, daily-target or reward updates in this integration. Unity's standard Offerwall product is reward-oriented: before enabling a live placement, confirm with the provider that its configuration supports this PRD's tracking-only arrangement and does not promise rewards the app will not deliver. Provider-hosted content and account eligibility cannot be verified using local tests.

## Verification

```powershell
# From admin
php artisan test tests/Feature

# From the repository root
flutter analyze
flutter test test/offerwall_test.dart
flutter build apk --debug
```

Automated tests cover authenticated launch, canonical user mapping, signed and tampered callbacks, duplicate/conflicting events, database failure, admin access, combined date/user filtering, no score/progress updates, Flutter failure/retry/session states, and bottom navigation access in English and Hindi on a small phone.

Live verification requires actual provider credentials and an eligible test device:

1. Sign in as a test account, open Offerwall from the bottom navigation, and confirm the correct placement loads.
2. Close it without completing a task; confirm no transaction appears.
3. Complete a provider test task; verify the callback records the same internal user ID in Admin → Offerwall.
4. Redeliver the same signed event; confirm one row remains and the endpoint acknowledges it.
5. Verify the row with user/date filters, then verify that quiz score and progress are unchanged.
6. Test browser closure, offline access, retry and a second signed-in account on Android and iOS.

The application can report API and browser-launch failures. Once the provider page opens, network/page errors are handled by the native browser and provider page; returning to the app allows another launch.

## Provider references

- [Unity Web Offerwall deep links](https://docs.unity.com/en-us/grow/offerwall/web/displaying-offerwall/deep-linking-implementation)
- [Unity callback formats and verification](https://docs.unity.com/en-us/grow/offerwall/monetization/virtual-currency/self-managed-currency)
- [Flutter URL launcher](https://pub.dev/packages/url_launcher)
