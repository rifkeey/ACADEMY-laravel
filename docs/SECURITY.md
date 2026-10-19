# Security

## Authentication (Laravel Fortify)

- Session-based authentication with bcrypt password hashing. Sessions are stored in the database, regenerated on login and can be encrypted (`SESSION_ENCRYPT=true`), with `Secure`, `HttpOnly` and `SameSite=Lax` cookies in production.
- Password policy: 10–128 characters and checked against known breaches (Have I Been Pwned range API, k-anonymity, so the password never leaves the server). If the API is unreachable the check is skipped rather than blocking sign-up.
- Email verification is required before any learner page. Password reset tokens expire and are cleaned daily.
- TOTP two-factor authentication with recovery codes. **Staff must enable it before using `/admin` in production** (`REQUIRE_STAFF_2FA`).
- Rate limits: login per email+IP and per IP, two-factor challenge, registration, password reset, enquiries, checkout, KQL queries, lab submissions, certificate verification and webhooks.
- Suspended accounts cannot sign in, are signed out on their next request, and their database sessions are deleted when suspended.

## Authorisation

- Roles: `learner`, `instructor`, `admin`. Permissions are a code-defined matrix (`app/Support/Permissions.php`) registered as Gates. Every admin action calls `$this->authorize('permission')`; hiding a link is never the protection.
- `role` and `status` are not mass-assignable. Registration can never set a role. Staff cannot change their own role or status, and the last active administrator cannot be demoted or suspended.
- Non-staff get **404** for `/admin/*`, so the area's existence is not confirmed.
- Learner data is always queried by the signed-in user. Other learners' exam attempts and certificates return 404. These rules are covered by feature tests.

## Assessment integrity

- **Labs**: answer keys live in a separate `validator` column that is hidden from serialisation and never rendered for learners. The KQL console runs on the server against the lab's own synthetic tables only. Grading is server-side; the response contains only per-task correctness and feedback.
- **Exams**: questions are drawn and shuffled on the server, and a snapshot is stored per attempt. The exam page receives question text and option ids only, never which options are correct. The deadline is enforced on the server (plus a 30-second grace period for latency). Late submissions score zero; submissions are idempotent; the pass rule is integer arithmetic (`correct × 100 ≥ pass% × total`); attempts and cooldowns are checked on the server.
- Certificates are issued only by the server when every requirement is met, inside a locked transaction.

## Payments

- The return redirect from the provider proves nothing: the server asks Paystack for the transaction status and checks amount and currency before granting access.
- Webhooks are verified with HMAC-SHA512 over the raw body (`x-paystack-signature`) using a constant-time comparison, stored with a unique event key (duplicates are acknowledged and ignored) and processed idempotently.
- The development sandbox provider is refused in production, and so are Paystack test keys.

## Certificates and verification

- Each certificate has a 130-bit random credential id. Its QR code links to `/verify/{id}` on the real site, which reads the live record and shows **valid**, **revoked** (with date) or **not found**. It shows no email, account or exam details; names can be shown as initials only (Admin → Settings).
- Revocation and reinstatement need a reason and are recorded in `certificate_events` and the audit log.

## Web security

- CSRF tokens on every form (webhooks are exempt and authenticated by signature instead).
- Content Security Policy with per-request script nonces; Alpine.js runs in its CSP build, so there is no `eval`. Fonts are self-hosted. `frame-ancestors 'none'`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, a restrictive `Permissions-Policy`, and HSTS in production.
- Markdown is rendered with raw HTML escaped and unsafe links (`javascript:`, `data:`) removed. Source URLs are validated as http(s).
- Production refuses to boot with `APP_DEBUG=true`, a non-https site URL, the sandbox provider or a Paystack test key.
- Forwarded-for headers are not trusted (cPanel hosting has no proxy), so clients cannot spoof their IP for rate limits.
- Sensitive admin actions are written to an append-only audit log.

## Reporting a vulnerability

Email the AMANIQ team (address in Admin → Settings → contact email). Please do not open public issues for security problems.
