# Google Sheets Integration - Implementation Notes

## Status: In Progress (Paused)

## Files Created/Modified

### New Files
- `includes/admin/class-google-sheets-token.php` - Auto token refresh every 3 days

### Modified Files
- `includes/admin/class-integrations.php` - OAuth callback, REST endpoints
- `includes/admin/class-admin-assets.php` - Localized script with google_sheets data
- `includes/class-boot.php` - Include token refresh class
- `src/admin/views/integrations-configs/GoogleSheets.vue` - Connection UI
- `src/admin/stores/integrationModal.js` - Registered GoogleSheets component
- `src/admin/stores/integrations.js` - Added checkGoogleSheetsConnection()
- `src/admin/components/IntegrationCard.vue` - API connection badge

## Features Implemented

1. **OAuth Flow**
   - Auth URL: `https://auth-staging.wppool.dev/auth/google/formychat-sheet-access`
   - Callback params: `formychat-action=authenticated&integration=googlesheets`
   - Returns: access_token, refresh_token, expires_in, email

2. **Token Storage**
   - Single option: `formychat_google_sheets`
   - Contains: access_token, refresh_token, token_expires, email, picture, connected, revoked

3. **User Info Fetch**
   - Endpoint: `POST https://auth-staging.wppool.dev/userinfo/google`
   - Body: `{ access_token: "..." }`
   - Returns: `{ email, picture }`

4. **Token Refresh**
   - Endpoint: `POST https://auth-staging.wppool.dev/refresh/google/formychat-sheet-access`
   - Body: `{ refresh_token: "..." }`
   - Auto-refresh every 3 days via transient
   - Prevents refresh_token revocation (unused 6 months = revoked)

5. **Revoked State**
   - Yellow warning UI when token expires/revoked
   - "Reconnect with Google" button
   - Clears data and starts new OAuth flow

6. **Profile Display**
   - Shows Google account email and profile picture
   - Fetches from auth server if not cached
   - Saves to DB for subsequent loads

## REST Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/integrations/google-sheets/status` | GET | Get connection status, email, picture, tokens |
| `/integrations/google-sheets/disconnect` | POST | Remove all tokens, disable integration |
| `/integrations/google-sheets/userinfo` | POST | Save email/picture from auth server |
| `/integrations/google-sheets/token` | POST | Save new access_token after refresh |

## Localized Script Data

```php
'google_sheets' => [
    'is_enabled'     => bool,
    'just_connected' => bool,
    'connected'      => bool,
    'revoked'        => bool,
    'email'          => string,
    'picture'        => string,
    'access_token'   => string,
    'refresh_token'  => string,
]
```

## UI States

1. **Loading** - Spinner while checking status
2. **Not Connected** - "Continue with Google" button
3. **Connected** - Profile picture, email, green border, disconnect button
4. **Fetching User Info** - Spinner in profile area while loading from auth server
5. **Revoked** - Yellow warning, email (faded), "Reconnect with Google" button

## Pending Tasks

- [ ] Remove debug console.log statements before production
- [ ] Remove PHP error_log debug statements before production
- [ ] Test full OAuth flow end-to-end
- [ ] Test token refresh mechanism
- [ ] Test revoked state handling
- [ ] Build assets and verify UI

## Auth Server URLs

- Auth: `https://auth-staging.wppool.dev/auth/google/formychat-sheet-access`
- User Info: `https://auth-staging.wppool.dev/userinfo/google`
- Refresh: `https://auth-staging.wppool.dev/refresh/google/formychat-sheet-access`
