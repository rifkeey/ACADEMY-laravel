# Implementation status

Last updated: 19 October 2026. "Tested" means covered by the automated suite (PHPUnit on MySQL 8.4, PHP 8.3) and/or checked by hand in a browser on a local install. Nothing has been deployed to GO54 yet.

## Automated tests

`php artisan test`: **40 tests, 676 assertions, all passing** (unit: KQL interpreter, firewall, grading rules, colour contrast; feature: content integrity, full learner journey, every page over HTTP, the admin area).

Specific checks the migration brief asked for:

| Check | Result |
| --- | --- |
| 74% fails, 75% passes (29/40 fails, 30/40 passes; integer arithmetic) | Passing (`LearnerJourneyTest`, `LabEngineTest`) |
| Duplicate webhook enrols once and records one payment | Passing (service level and over HTTP) |
| Forged webhook signature rejected | Passing |
| Payment redirect alone grants nothing; amount mismatch rejected | Passing |
| One learner cannot reach another's course, labs, attempts or certificates (404) | Passing |
| Exam page never contains correct answers; lab pages never contain answer keys | Passing |
| One open exam attempt per learner (database-enforced) | Passing |
| QR code decodes to the real `/verify/{id}`; valid / revoked / not-found states | Passing |
| Learners get 404 on `/admin`; instructors get 403 on admin-only areas; nobody can change their own role | Passing |
| Every lab is solvable with the reference answers, an empty submission fails, and the starter KQL query solves nothing | Passing |
| Text colours meet WCAG AA (4.5:1) on all surfaces | Passing |

## By area

| Area | Status |
| --- | --- |
| Public site: home, programmes (search, filter, sort), programme pages with JSON-LD, about, corporate, contact with enquiry form, resources and articles, legal pages, sitemap, robots | Implemented, tested |
| Accounts: registration, email verification, login, logout, password reset, password confirmation, profile and password change, TOTP 2FA with recovery codes, suspension | Implemented, tested over HTTP. **Sending real email is untested** (dev uses the log mailer) |
| Learner area: dashboard, payment history, account, course overview, lesson player with self-checks, references, mark complete, prev/next | Implemented, tested |
| Labs: evidence explorer (search, filter, sort, paging), server-side KQL console, documents, task types text/number/choice/multi/kql/firewall/grid, hints, local draft, server grading, attempt history | Implemented, tested; checked in a browser |
| Final exam: eligibility, balanced random selection, shuffling, snapshot, server timer with auto-submit, one-at-a-time navigation, flags, draft restore, idempotent submit, retakes and cooldown, results with topic breakdown and explanations | Implemented, tested; checked in a browser |
| Credentials: badge and certificate issued once, certificate PDF with QR, public verification, revoke and reinstate | Implemented, tested |
| Commerce: prices in kobo, discount codes, orders, sandbox checkout, signed idempotent webhooks, refunds, complimentary enrolments | Implemented, tested with the sandbox provider |
| Paystack | Implemented to Paystack's documented API (initialise, verify, HMAC-SHA512 webhook) but **not tested against Paystack itself**. Do one low-value live purchase and refund before launch |
| Admin: dashboard, users and roles, programmes (details, publication, versions, modules, lessons), labs (JSON editor with validation), question bank, exam settings, badges, certificates, pricing and discounts, orders and enrolments, enquiries, articles, sources, settings, audit log | Implemented, every page tested over HTTP; spot-checked in a browser |
| GO54 deployment | Documented in [DEPLOYMENT_GO54.md](DEPLOYMENT_GO54.md); **not yet performed**. PHP 8.3 availability, SSH/Composer and outbound HTTPS must be confirmed with GO54 |

## Content status (unchanged from the original)

| Programme | State |
| --- | --- |
| SOC Analyst | Complete: lessons, 6 labs, 46 questions, published exam. Enrolment open |
| Governance, Risk and Compliance | Complete: lessons, 5 labs, 57 questions, published exam. Enrolment open |
| Network Security | **In development**: 9 starter lessons, 2 labs, no questions, so its exam cannot be published. Enrolment closed |
| Cloud Security | **In development**: 10 starter lessons, 2 labs, no questions, exam unpublished. Enrolment closed |
| Penetration Testing and Ethical Hacking | **Outline only**: module structure, no lessons, labs or questions. Enrolment closed |

## Placeholders to fill before launch

- Admin → Settings: contact email, refund window (days) and certificate signatory are empty on a fresh install.
- Legal pages are drafts carried over from the original and need review by a lawyer.
- No real testimonials, partners, accreditations or statistics exist, and none are shown.

## Deliberate differences from the Next.js version

- The KQL interpreter runs only on the server (the console calls an endpoint), so there is one implementation.
- Lab task `spray-ips` now needs an aggregated answer (see `database/content/README.md`).
- Retiring a badge now actually stops new awards (the original ignored the status).
- "Forgot password" gives the same answer for known and unknown emails, and sign-up and reset requests are rate-limited per IP.
