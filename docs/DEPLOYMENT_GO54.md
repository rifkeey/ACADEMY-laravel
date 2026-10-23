# Deploying to GO54 (cPanel shared hosting)

This guide assumes a GO54 Linux web-hosting plan with cPanel. **Confirm the items marked "Ask GO54" with their support before you buy or deploy**: the public GO54 documentation confirms cPanel, "Select PHP Version" (PHP versions and extensions), MySQL databases and cron jobs, but not every detail below (SSH access, Composer, exact PHP versions on your plan).

## 1. Requirements checklist

| Requirement | Needed | How to check |
| --- | --- | --- |
| PHP | **8.3 or newer** (Laravel 13 minimum) | cPanel → Select PHP Version. **Ask GO54** whether 8.3/8.4 is available on your plan |
| PHP extensions | `pdo_mysql`, `mbstring`, `intl`, `gd`, `fileinfo`, `openssl`, `zip`, `bcmath`, `curl`, `dom`, `xml`, `tokenizer`, `ctype` | Select PHP Version → Extensions |
| PHP settings | `memory_limit` ≥ 256M, `max_execution_time` ≥ 60, `upload_max_filesize` ≥ 8M | Select PHP Version → Options |
| MySQL | 8.0 or newer (MariaDB 10.6+ usually works but is not what the tests run on) | cPanel → MySQL Databases / phpMyAdmin shows the version. **Ask GO54** |
| Document root | Must point at the project's `public/` folder | Domains → manage the domain/subdomain |
| HTTPS | Free AutoSSL / Let's Encrypt certificate | cPanel → SSL/TLS Status |
| Cron | One job every minute | cPanel → Cron Jobs |
| Email | An SMTP mailbox (e.g. academy@amaniqgroup.com) | cPanel → Email Accounts → Connect Devices |
| SSH + Composer | Recommended, not required | **Ask GO54** whether SSH (Terminal) and Composer are enabled |
| Outbound HTTPS | To `api.paystack.co` (payments) and `api.pwnedpasswords.com` (password check) | **Ask GO54** whether outbound connections are allowed |

## 2. Prepare the release on your computer

```bash
npm ci && npm run build          # only if you changed CSS/JS; commit public/build afterwards
php artisan test                 # recommended, needs a local MySQL test database
bash scripts/build-release.sh    # writes release/amaniq-academy-<commit>.tar.gz
```

The build script packages **only what is committed** (uncommitted drafts never ship), installs production dependencies into a temporary copy (your local `vendor/` is untouched), removes tests, `.env`, `node_modules` and other development files, and checks that `public/build` exists. The archive includes `vendor/`, so the server does not need Composer. Set `PHP=` or `COMPOSER=` if your tools are not on the default paths.

The archive does not run Composer's package discovery, because the app (correctly) refuses to boot in production without a real `.env`; Laravel rebuilds that cache automatically on the first request.

## 3. Upload and folder layout

Keep the application **outside** `public_html`, so `.env`, source code and storage are never web-accessible:

```
/home/USER/academy/            ← whole project (app, bootstrap, config, database, vendor, …)
/home/USER/academy/public/     ← the ONLY web-visible folder
```

Then, in cPanel → Domains, create `academy.amaniqgroup.com` (or edit the domain) and set its **document root to `academy/public`**.

If your plan does not let you change the document root of the main domain, use a subdomain (recommended) or ask GO54 support to set it. Do not move `index.php` into `public_html` and edit paths unless you have no other option; it is easy to expose `.env` that way.

Upload the `.tar.gz` from step 2 with cPanel File Manager (upload into `/home/USER/academy/`, then Extract) or with SFTP.

## 4. Database

1. cPanel → **MySQL Databases**: create a database and a user with a long random password, then add the user to the database with **ALL PRIVILEGES**.
2. Note the full names (cPanel prefixes them, e.g. `user_academy`).

## 5. Environment file

Copy `.env.example` to `.env` in `/home/USER/academy/` and fill in:

- `APP_KEY`: run `php artisan key:generate` (SSH), or generate locally with `php artisan key:generate --show` and paste the value.
- `APP_URL` and `ACADEMY_SITE_URL`: `https://academy.amaniqgroup.com` (https is required; the app refuses to boot in production otherwise).
- `DB_*`: the values from step 4. `DB_HOST=localhost` on cPanel.
- `MAIL_*`: SMTP host, port 465 (SSL) and mailbox credentials from cPanel → Email Accounts.
- `PAYMENT_PROVIDER=paystack` and `PAYSTACK_SECRET_KEY=sk_live_…` (live keys only; test keys are refused in production). Use `PAYMENT_PROVIDER=none` to launch without online payments and grant enrolments from the admin area.

Set the file permission of `.env` to **600** (or 640).

## 6. Install

With SSH (Terminal in cPanel):

```bash
cd ~/academy
php -v                                   # must show 8.3+; if not, use the full path, e.g. /opt/cpanel/ea-php83/root/usr/bin/php
php artisan migrate --force              # creates tables
php artisan db:seed --class=CatalogueSeeder --force   # loads the curriculum (safe to re-run)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Without SSH: create a temporary cron job (step 8) that runs `php artisan migrate --force` once, then `db:seed --class=CatalogueSeeder --force` once, then delete it. Check `storage/logs` for the result. Or import a SQL dump made from a fresh local install with phpMyAdmin.

**Never run `DevAccountsSeeder` in production** (it refuses to). Create the first administrator by registering normally on the site, then promote that account once with SSH:

```bash
php artisan tinker --execute="App\Models\User::where('email','you@amaniqgroup.com')->first()->forceFill(['role'=>'admin'])->save();"
```

(Without SSH, run the equivalent `UPDATE users SET role='admin' WHERE email='…';` in phpMyAdmin.) Then sign in, open **Account → Security** and turn on two-factor authentication. Administrators cannot open `/admin` in production until they do.

## 7. Permissions

```bash
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

Folders 755 and files 644 elsewhere is fine. The web server user must be able to write to `storage/` and `bootstrap/cache/`.

## 8. Cron job

cPanel → Cron Jobs → "Once per minute":

```
cd /home/USER/academy && php artisan schedule:run >> /dev/null 2>&1
```

(Use the full PHP 8.3 path if `php` is an older version.) The scheduler only cleans expired password-reset tokens; the site works without it.

## 9. HTTPS and Paystack

1. Check the SSL certificate is active, then open the site over https.
2. In the Paystack dashboard set the **webhook URL** to `https://academy.amaniqgroup.com/webhooks/payments` and the callback URL to `https://academy.amaniqgroup.com/checkout/complete`.
3. Make a real low-value test purchase (e.g. with a 99% discount code) and refund it, to confirm payments end to end. **This has not yet been done: the Paystack integration is untested against the live API.**

## 10. Updating the site

1. Put the site in maintenance mode: `php artisan down`.
2. Upload the changed files (or `git pull` if Git is available).
3. `composer install --no-dev --optimize-autoloader` (or upload `vendor/`).
4. `php artisan migrate --force` then `php artisan config:cache route:cache view:cache` (run `optimize:clear` first if something looks stale).
5. `php artisan up`.

## Limitations of shared hosting (honest list)

- **No background workers.** Queues run synchronously (`QUEUE_CONNECTION=sync`), so verification and reset emails are sent during the request. If the SMTP server is slow, sign-up feels slow. Nothing else depends on queues.
- **PDF generation uses PHP memory.** Rendering a certificate PDF with embedded fonts and images uses a noticeable amount of memory; keep `memory_limit` at 256M or more.
- **Single server, shared CPU.** Fine for a launch cohort (hundreds of learners). Many simultaneous exam submissions or KQL queries will be slower than on a VPS. Move to a VPS when traffic grows; the code needs no changes.
- **Database sessions and cache.** No Redis is needed, but every request touches MySQL. This is normal for shared hosting.
- **IP-based rate limits and proxies.** The app does not trust `X-Forwarded-For`. If GO54 or a CDN (e.g. Cloudflare) sits in front of the site, add its proxy IP ranges in `bootstrap/app.php`, or rate limits will see one IP for everyone.
- **Unconfirmed items.** PHP 8.3 availability, SSH/Composer access and outbound HTTPS on your specific GO54 plan must be confirmed with GO54.
