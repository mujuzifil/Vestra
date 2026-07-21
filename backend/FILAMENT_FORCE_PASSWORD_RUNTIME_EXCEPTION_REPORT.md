# Stage 7.3.6 — Filament Force Password Change Runtime Exception: Audit & Remediation Report

## 1. Executive Summary

**PASS**

`GET /admin/force-password-change` returned HTTP 500 due to two separate, sequential Filament runtime defects in `app/Filament/Pages/ForcePasswordChange.php` and its Blade view. Both were identified from `storage/logs/laravel.log` stack traces and confirmed live against the running `vestra-backend-dev` container, then fixed with the smallest correct change in each case. The complete administrator onboarding flow (login → exchange token → force-password-change page → password update → Filament dashboard → subsequent logins bypass the page) now works end-to-end, verified via direct HTTP/Livewire-protocol requests against the live app and the full automated test suite (31/31 passing).

---

## 2. Exception Details

### Exception 1 (pre-existing, found in log)

```
[2026-07-19 21:40:15] local.ERROR: Undefined array key "changePassword" (View: /var/www/html/resources/views/filament/pages/force-password-change.blade.php) {"userId":37,"exception":"[object] (Illuminate\View\ViewException(code: 0): Undefined array key \"changePassword\" (View: /var/www/html/resources/views/filament/pages/force-password-change.blade.php) at /var/www/html/storage/framework/views/06dbeb73fff102ae7d26936e6173d6f0.php:53)
[stacktrace]
#0 vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php(58): Illuminate\View\Engines\CompilerEngine->handleViewException(...)
...
#9 vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php(274): ...->trackInRenderStack(Object(App\Filament\Pages\ForcePasswordChange), Object(Closure))
...
#20 app/Http/Middleware/EnsureAdminPasswordChanged.php(21): Illuminate\Pipeline\Pipeline->{closure}(...)
```
Previous exception (root cause):
```
[previous exception] [object] (ErrorException(code: 0): Undefined array key "changePassword" at /var/www/html/storage/framework/views/06dbeb73fff102ae7d26936e6173d6f0.php:53)
#0 HandleExceptions->handleError(2, 'Undefined array key "changePassword"', ..., 53)
#1 storage/framework/views/06dbeb73fff102ae7d26936e6173d6f0.php(53): include(...)
...
```

### Exception 2 (surfaced live, during remediation, after fixing Exception 1)

```
[2026-07-19 22:04:56] local.ERROR: Method App\Filament\Pages\ForcePasswordChange::getCachedFormActions does not exist. (View: /var/www/html/resources/views/filament/pages/force-password-change.blade.php) {"userId":37,"exception":"[object] (Illuminate\View\ViewException(code: 0): Method App\Filament\Pages\ForcePasswordChange::getCachedFormActions does not exist. (View: ...) at vendor/livewire/livewire/src/Component.php:138)"}
```

### Regression 3 (surfaced live, during verification — not a 500, but violated acceptance criteria)

Not an exception — a silent session-state bug. After the two view/page fixes, submitting the password-change form succeeded (HTTP 200, redirect effect to `/admin`), but the *following* `GET /admin` request 302-redirected back to `/admin/login`, logging the administrator out instead of showing the dashboard. Root cause: Laravel's `AuthenticateSession` middleware (`Filament\Http\Middleware\AuthenticateSession`, wired into the admin panel) invalidates the session whenever the authenticated user's password hash no longer matches the hash cached in the session (`password_hash_web`). `ForcePasswordChange::changePassword()` updated the password directly via Eloquent without refreshing that session value, so the very next request logged the admin out.

---

## 3. Root Cause

**PASS** (fully diagnosed and fixed)

- **Exception 1**: `resources/views/filament/pages/force-password-change.blade.php` indexed `$this->getFormActions()['changePassword']` by string key. `ForcePasswordChange::getFormActions()` returns a plain sequential array (`[0 => Action]`) — this is how Filament's `cacheFormActions()` always builds it (`$cachedFormActions[] = $action`, never keyed by name). The key `'changePassword'` never existed, so every render threw `Undefined array key`.
- **Exception 2**: The idiomatic fix for Exception 1 is Filament's own `<x-filament-panels::form.actions :actions="$this->getCachedFormActions()" />` component (verified against Filament's own built-in pages: `vendor/filament/filament/resources/views/pages/auth/edit-profile.blade.php`, `login.blade.php`, `resources/pages/edit-record.blade.php`, all use this exact pattern). But `getCachedFormActions()` and `hasFullWidthFormActions()` are provided by the `Filament\Pages\Concerns\InteractsWithFormActions` trait, and `Filament\Pages\Page` (the base class `ForcePasswordChange` extends) does **not** include that trait by default — only pages that explicitly `use` it (like Filament's built-in `EditProfile`) have it. `ForcePasswordChange` only used `InteractsWithForms`, so the method genuinely did not exist on the class.
- **Regression 3**: Filament's own `EditProfile::save()` (`vendor/filament/filament/src/Pages/Auth/EditProfile.php:175-179`) shows the correct idiom for a self-service password change: after saving, manually write `request()->session()->put(['password_hash_' . Filament::getAuthGuard() => $newHash])`. This is necessary because the `AuthenticateSession` middleware only auto-refreshes that session key via its own post-response hook — which does **not** fire for the `/livewire/update` AJAX endpoint that Livewire form submissions actually POST to (that route isn't wrapped by the Filament panel's middleware group). `ForcePasswordChange::changePassword()` was missing this step entirely.

---

## 4. ForcePasswordChange Audit

**PASS** (after remediation)

- Namespace, inheritance (`Filament\Pages\Page`), `HasForms` contract: correct, unchanged.
- `mount()`: correct, unchanged — redirects away if password change isn't required, fills the form, logs `password_change.required`.
- `form()`: correct, unchanged — 3 fields with proper validation rules.
- `getFormActions()`: correct, unchanged — returns the `changePassword` submit action.
- `changePassword()`: **modified** — added the session password-hash refresh (see §9) so the admin stays authenticated after the password update.
- Trait usage: **modified** — added `use Filament\Pages\Concerns\InteractsWithFormActions;` so `getCachedFormActions()`/`hasFullWidthFormActions()` exist, matching Filament's own `EditProfile` page.

## 5. Livewire Audit

**PASS**

Lifecycle traced end-to-end via live requests: `mount()` → form fill → render (previously failing here) → `wire:submit="changePassword"` → `changePassword()` → redirect effect. No Livewire-specific defect; the failures were in the Blade view (missing/incorrect action-array access) and in the page class (missing trait). Verified the actual Livewire AJAX endpoint (`POST /livewire/update`) directly with a hand-built snapshot/updates/calls payload — confirmed correct `effects.redirect` in the JSON response after the fix.

## 6. Blade Audit

**PASS** (after remediation)

`resources/views/filament/pages/force-password-change.blade.php` — the only defect was the `getFormActions()['changePassword']` array access (§9). Replaced with Filament's standard `<x-filament-panels::form.actions>` component. All other Blade syntax, slots, and components in the view were already correct.

## 7. Middleware Audit

**PASS**

- `EnsureAdminPasswordChanged` (`app/Http/Middleware/EnsureAdminPasswordChanged.php`): correctly allows the request through once the current path equals the force-password-change route; no redirect loop observed in any test run.
- `Filament\Http\Middleware\AuthenticateSession`: not defective itself — it enforces a standard, intentional Laravel security control (logging out sessions whose cached password hash goes stale). The defect was that `ForcePasswordChange::changePassword()` didn't keep that cache in sync, which is now fixed (§9). No middleware code was modified, per the task's constraint.

## 8. Route Audit

**PASS**

`/admin/force-password-change` is a Filament auto-registered page route (`filament.admin.pages.force-password-change`), correctly discovered via `AdminPanelProvider::pages()`/`discoverPages()`. No routing defect found; no route files modified.

---

## 9. Remediation

### File 1: `app/Filament/Pages/ForcePasswordChange.php`

**Change A** — added the trait that supplies `getCachedFormActions()`/`hasFullWidthFormActions()`:
```diff
 use App\Services\AuditService;
 use Filament\Actions\Action;
+use Filament\Facades\Filament;
 use Filament\Forms\Components\TextInput;
 use Filament\Forms\Concerns\InteractsWithForms;
 use Filament\Forms\Contracts\HasForms;
 use Filament\Forms\Form;
 use Filament\Notifications\Notification;
+use Filament\Pages\Concerns\InteractsWithFormActions;
 use Filament\Pages\Page;
 use Illuminate\Support\Facades\Hash;
 use Illuminate\Validation\Rules\Password;

 class ForcePasswordChange extends Page implements HasForms
 {
     use InteractsWithForms;
+    use InteractsWithFormActions;
```

**Change B** — refresh the session's cached password hash after updating the password, so `AuthenticateSession` middleware doesn't log the admin out on the very next request (matching the exact idiom used by Filament's own `Pages\Auth\EditProfile::save()`):
```diff
         $user->clearPasswordChangeRequired();

+        if (request()->hasSession()) {
+            request()->session()->put([
+                'password_hash_' . Filament::getAuthGuard() => $user->getAuthPassword(),
+            ]);
+        }
+
         AuditService::log(
```

### File 2: `resources/views/filament/pages/force-password-change.blade.php`

```diff
     <x-filament-panels::form wire:submit="changePassword">
         {{ $this->form }}

-        <div class="fi-ac gap-3 flex flex-wrap items-center justify-start">
-            {{ $this->getFormActions()['changePassword'] }}
-        </div>
+        <x-filament-panels::form.actions
+            :actions="$this->getCachedFormActions()"
+            :full-width="$this->hasFullWidthFormActions()"
+        />
     </x-filament-panels::form>
```

No changes were made to authentication, exchange-token logic, RBAC, general middleware, CSP, or session-creation code, per the task's explicit constraints — the `AuthenticateSession` middleware itself was left untouched; only the page class was updated to cooperate correctly with it.

---

## 10. Regression Testing

### Backend (automated)
`php artisan test` — full suite: **31 passed, 138 assertions**, 0 failures, including all 24 tests in `ApiEndpointsTest` (unified login, exchange token, admin/customer flows, password-change API, privilege-escalation rejection).

### Manual / live HTTP verification (against running `vestra-backend-dev` container, MySQL-backed)
Performed as raw HTTP + Livewire-protocol requests (browser extension was unavailable in this environment) against the seeded admin `admin@vestra.com`:
1. `POST /api/v1/auth/login` → 200, `must_change_password: true`, exchange token issued.
2. `POST /api/v1/auth/exchange` → 302 to `/admin/force-password-change`.
3. `GET /admin/force-password-change` → **200** (previously 500), page contains "Change Password Required" heading, all 3 form fields, and a working submit button.
4. `POST /livewire/update` (simulating the form's `wire:submit="changePassword"`) → 200, `effects.redirect = http://localhost:8000/admin`.
5. DB check: `force_password_change_at` cleared to `null`; new password hash verifies via `Hash::check()`.
6. Audit log: `password_changed` event recorded (id 94, timestamped).
7. `GET /admin` (same session, following the redirect) → **200**, page contains Filament dashboard markers (title "VESTRA Dashboard", `fi-body` class) — confirms the session-hash fix (§9, Change B); without it this step 302-redirected to `/admin/login`.
8. Re-login with the new password → `must_change_password: false`, exchange redirects straight to `/admin`, which loads (200) without hitting the force-password-change page again.
9. `storage/logs/laravel.log` checked after every step — no new exceptions logged post-fix.

### Frontend / Browser
Not performed — the Chrome browser automation extension was not connected in this environment, and the frontend dev server (port 3000) was not running. The above manual verification instead drove the real backend endpoints directly (HTTP + the actual Livewire AJAX protocol used by the browser), which exercises the identical server-side code path a browser would trigger. **This is a gap relative to the "manual browser testing" acceptance criterion** — recommend a follow-up manual pass in an actual browser (via the unified login UI on port 3000, or directly at `/admin/login`) before considering this fully closed for production sign-off.

### Customer authentication
Not modified, not touched by either fix. `test_unified_login_returns_customer_role_and_dashboard_redirect` and `test_public_registration_creates_customer_with_customer_role` both pass unchanged.

---

## 11. Files Modified

1. `backend/app/Filament/Pages/ForcePasswordChange.php`
2. `backend/resources/views/filament/pages/force-password-change.blade.php`

No other files were modified. No migrations, config, routes, or middleware were changed.

---

## 12. Commands Executed

```
docker ps
docker exec vestra-backend-dev php artisan view:clear
docker exec vestra-backend-dev php -l app/Filament/Pages/ForcePasswordChange.php
docker exec vestra-backend-dev php artisan route:list --path=auth
docker exec vestra-backend-dev php artisan route:list --path=livewire
docker exec vestra-backend-dev php artisan tinker --execute="..."   (state inspection/reset only, see below)
docker exec vestra-backend-dev php artisan test --filter=ApiEndpointsTest
docker exec vestra-backend-dev php artisan test
curl (multiple) against http://localhost:8000/{sanctum/csrf-cookie, api/v1/auth/login, api/v1/auth/exchange, admin/force-password-change, livewire/update, admin}
node -e "..."  (used only to decode HTML entities / build JSON payloads for the Livewire protocol test, no app code involved)
```

`tinker` was used only for read-only inspection (`User::find(37)`, `AuditLog::where(...)`) and to reset the seeded admin test account's password/`force_password_change_at`/sessions table back to its original pre-test state after verification — no application logic was changed via tinker.

---

## 13. Screenshots

Not available — the Chrome browser automation extension was not connected in this environment (`tabs_context_mcp` returned "Browser extension is not connected"). All verification in §10 was performed via direct HTTP requests reproducing the exact same server-side code path (including the real Livewire AJAX protocol), with raw response bodies/headers captured as text instead of screenshots. Recommend capturing screenshots in a follow-up pass once browser access is available.

---

## 14. Final Recommendation

**PASS**, with one caveat noted above: browser-based visual verification (screenshots, real click-through) was not possible in this environment and should be done as a follow-up before final production sign-off. All server-side behavior — the original 500, the two Filament defects that caused it, and a third session-state regression discovered during verification — has been root-caused, fixed with minimal changes confined to the `ForcePasswordChange` page and its view, and confirmed via live HTTP/Livewire-protocol testing plus the full automated test suite (31/31 passing).

### Acceptance criteria checklist
- ✓ `/admin/force-password-change` returns HTTP 200
- ✓ No runtime exceptions (confirmed via log tail after each step)
- ✓ Password form renders correctly
- ✓ Password validation works (existing rules unchanged, exercised by `test_weak_password_change_is_rejected`)
- ✓ Password is updated successfully
- ✓ Password hash is persisted (`Hash::check()` verified)
- ✓ `must_change_password` flag is cleared
- ✓ Audit log records the password change
- ✓ Administrator is redirected to `/admin`
- ✓ Filament Dashboard loads successfully (confirmed after session-hash fix)
- ✓ Subsequent administrator logins bypass the force-password page
- ✓ Customer authentication remains unaffected (unchanged, tests pass)
- ✓ All automated tests pass (31/31)
- ✗ Manual **browser** testing — not possible in this environment (browser extension unavailable); equivalent live HTTP/Livewire-protocol testing was performed instead. Recommend a follow-up browser pass.
