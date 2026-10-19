# AMANIQ Academy (Laravel + MySQL)

Practice-oriented cybersecurity learning platform for **AMANIQ Academy**, a division of AMANIQ Projects Limited. Learners enrol in a specialization, study structured lessons, complete simulated hands-on labs, sit a server-graded final examination and receive a verifiable certificate and badge.

This is the PHP / Laravel / MySQL rebuild of the original Next.js application ([rifkeey/ACADEMY](https://github.com/rifkeey/ACADEMY)). It keeps the curriculum, rules, design and features of the original and is built to run on Linux shared hosting with cPanel (target: GO54).

| | |
| --- | --- |
| Language / framework | PHP 8.3+, Laravel 13 |
| Database | MySQL 8.0+ (InnoDB, utf8mb4) |
| Authentication | Laravel Fortify: sessions, email verification, password reset, TOTP two-factor |
| Front end | Blade components, Tailwind CSS 4, Alpine.js (CSP build), built with Vite. Compiled assets are committed in `public/build`, so the server does **not** need Node.js |
| PDF / QR | dompdf and chillerlan/php-qrcode (pure PHP) |
| Payments | Paystack (server-verified, signed webhooks) and a development-only sandbox |
| Tests | PHPUnit against a real MySQL test database |

## Programmes

| Programme | Price | Content |
| --- | --- | --- |
| SOC Analyst | ₦180,000 | Complete: 10 modules, 23 lessons, 6 labs, 46-question bank, exam |
| Governance, Risk and Compliance | ₦150,000 | Complete: 9 modules, 27 lessons, 5 labs, 57-question bank, exam |
| Network Security | ₦180,000 | In development: starter lessons and 2 labs, no exam yet |
| Cloud Security | ₦220,000 | In development: starter lessons and 2 labs, no exam yet |
| Penetration Testing and Ethical Hacking | ₦220,000 | Outline only |

Prices are stored in kobo and can be changed in **Admin → Pricing**. The pass mark is **75%** (30 of 40), checked with integer arithmetic. Certificates are issued only when every required lesson and lab is complete and the examination is passed.

## Run it locally

Requirements: PHP 8.3+ with `pdo_mysql`, `mbstring`, `intl`, `gd`, `fileinfo`, `openssl`, `zip`, `bcmath`; Composer 2; MySQL 8; Node.js 20+ (only to rebuild CSS/JS).

```bash
composer install
cp .env.example .env        # then set APP_ENV=local, APP_DEBUG=true, PAYMENT_PROVIDER=sandbox and your DB details
php artisan key:generate
php artisan migrate --seed  # creates tables, loads the curriculum and three dev accounts
npm install && npm run build
php artisan serve
```

Open <http://127.0.0.1:8000>. The development accounts (admin, instructor, learner) and their passwords are listed in `database/seeders/DevAccountsSeeder.php`; that seeder refuses to run in production. With `PAYMENT_PROVIDER=sandbox`, checkout shows a simulated payment page.

Tests use a separate database (`amaniq_academy_test`, see `phpunit.xml`):

```bash
php artisan test
```

## Documentation

- [docs/DEPLOYMENT_GO54.md](docs/DEPLOYMENT_GO54.md): step-by-step deployment to GO54 cPanel hosting, and its limitations
- [docs/IMPLEMENTATION_STATUS.md](docs/IMPLEMENTATION_STATUS.md): what is implemented, tested, untested or placeholder
- [docs/DATABASE.md](docs/DATABASE.md): schema, constraints, indexes and transactions
- [docs/SECURITY.md](docs/SECURITY.md): authentication, authorisation, exam integrity, payments, headers
- [docs/MIGRATION_INVENTORY.md](docs/MIGRATION_INVENTORY.md): how each part of the Next.js app maps to Laravel
- [database/content/README.md](database/content/README.md): curriculum source files

## Project layout

```
app/Assessment     Pass-mark rule and balanced question selection
app/Labs           KQL interpreter, lab grader and firewall evaluator (server-only)
app/Payments       Payment provider interface, Paystack and sandbox
app/Services       Business logic: learning, labs, exams, credentials, checkout, versions
app/Http           Controllers (public, learner, admin), middleware, form requests
app/Support        Permission matrix, money, identifiers
database/content   The curriculum (JSON + Markdown), loaded by CatalogueSeeder
resources/views    Blade pages and components
resources/js       Alpine components: lab runner, exam taker, dialogs
```

## Honesty notes

No accreditation, partnerships, testimonials or statistics are claimed anywhere on the site. AMANIQ certificates confirm completion of an AMANIQ programme; they are not vendor certifications. All lab data is synthetic.
