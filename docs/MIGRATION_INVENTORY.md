# Migration inventory and plan: Next.js → Laravel 13 + MySQL 8

Source of truth for the existing product: <https://github.com/rifkeey/ACADEMY> (commit `5bb8d0c`, the Next.js 16 / TypeScript / PostgreSQL build). This document records what existed there and how each part maps to this Laravel application.

## 1. Audit of the existing implementation

| Area | What existed (Next.js) | State |
| --- | --- | --- |
| Public pages | Home, catalogue (search/filter/sort), programme detail, about, corporate, contact, resources + article, verify + verify/{id}, 4 policy pages, sitemap, robots | Working |
| Design | Dark navy palette (tokens in `globals.css`, WCAG AA-checked), Inter + JetBrains Mono, amaniq wordmark vector + division lockup, credential badge component | Working |
| Auth | Supabase Auth (prod) / scrypt local auth (dev), sign-up, sign-in, sign-out, password reset, rate limits | Working locally; Supabase untested |
| Roles | learner / instructor / admin, code-defined permission matrix, 404 for non-staff on /admin | Working |
| Learning | Dashboard, course overview, lesson player (sidebar, knowledge checks, references, mark complete, prev/next), payments history, account | Working |
| Labs | Engine: evidence explorer, KQL console (TS interpreter in browser + server), documents; tasks: text, number, choice, multi, KQL, firewall, grid; hints; drafts; server grading | Working |
| Exams | Eligibility, 40 random balanced questions, option shuffle, timer, snapshots, idempotent submit, 75% integer rule, retakes + cooldown, results with explanations | Working |
| Credentials | Idempotent badge + certificate issue, 130-bit credential IDs, PDF (Noto Sans) with QR, verify valid/revoked/not-found, revoke/reinstate with events | Working |
| Commerce | Offers (kobo), discounts, orders, provider abstraction, Paystack, sandbox, signed idempotent webhooks, amount re-check, refunds, complimentary grants | Working with sandbox; Paystack untested |
| Admin | 15 areas: overview, users/roles, programmes + versions + modules, lessons, labs, questions, exams, badges, certificates, pricing, orders/enrolments, enquiries, articles, sources, settings, audit | Working |
| Content | SOC (10 modules, 23 lessons, 6 labs, 46 q), GRC (9, 27, 5, 57), Network (9, 9, 2, 0), Cloud (10, 10, 2, 0), Pentest outline | Exported intact to `database/content/` |
| Data | PostgreSQL schema (34 tables), RLS, partial unique index, check constraints | Rebuilt for MySQL |
| Tests | 68 Vitest tests incl. full journey, RLS, contrast | Rewritten in PHPUnit against MySQL |
| Docs | Product spec, architecture, database, curriculum, security, deployment | Rewritten for Laravel / GO54 |

There was **no production data**: the PostgreSQL database only ever existed locally. Fresh, repeatable MySQL migrations and seeders are therefore correct; no data transfer is needed.

## 2. Mapping

| Next.js / TypeScript | Laravel / PHP | Notes |
| --- | --- | --- |
| App Router pages | `routes/web.php` + controllers + Blade views | Same URLs kept |
| React components | Blade components (`resources/views/components`) | Same names and visual design |
| Interactive client components (labs, exam, dialogs) | Blade + Alpine.js | Small, bundled by Vite |
| Server actions | Controller actions + Form Requests | CSRF via Laravel middleware |
| Zod schemas | Form Request validation rules | |
| `server/services/*` | `app/Services/*` | Same responsibilities and names |
| Drizzle schema + SQL migrations | Laravel migrations (MySQL 8) | See DATABASE.md |
| PostgreSQL RLS | Policies, Gates and user-scoped queries + tests | MySQL has no RLS |
| Partial unique index (one open attempt) | Stored generated column + unique index | `open_marker` |
| Supabase Auth / local auth | Laravel Fortify (session auth, bcrypt/argon, email verification, reset, 2FA) | No Supabase |
| Postgres rate-limit table | Laravel `RateLimiter` on the database cache store | Works on shared hosting |
| KQL interpreter (TS, browser + server) | `App\Labs\Kql\*` (PHP), **server only**; console calls an endpoint | One implementation, no drift |
| Lab grading, firewall evaluator | `App\Labs\LabEvaluator`, `FirewallEvaluator` | Direct port |
| Exam grading helpers | `App\Assessment\Grading` | Same integer pass rule |
| pdf-lib + fontkit | dompdf (Noto Sans TTF) | Pure PHP |
| qrcode + jsQR | chillerlan/php-qrcode (generate + read in tests) | Pure PHP |
| react-markdown | `Str::markdown` (CommonMark/GFM) with raw HTML escaped | |
| Paystack + sandbox providers | `App\Payments\*` behind `PaymentProvider` interface | Same flows |
| Vitest | PHPUnit 12 feature + unit tests on MySQL | |
| Vercel/Supabase deployment docs | GO54 cPanel deployment guide | See DEPLOYMENT_GO54.md |

## 3. Preserved, adapted, rebuilt

- **Preserved as-is:** all curriculum content and lab datasets, prices, business rules (75% pass, completion requirements, versioning), copy, policy drafts, colour palette, brand assets, fonts, URLs.
- **Adapted:** design system (Tailwind tokens ported to Blade components), auth (Fortify replaces Supabase), RLS replaced by authorisation layers, KQL console now runs server-side only.
- **Rebuilt:** all application code in PHP, database schema for MySQL, tests, deployment guide.

## 4. Stages

1. Audit (this document) · 2. Mapping (this document) · 3. Foundation: migrations, auth, design system · 4. Public site, catalogue, dashboard, admin · 5. Lessons, progress, labs · 6. Exams, badges, certificates, QR · 7. Commerce · 8. Tests and browser checks · 9. GO54 guide.

Status per stage is tracked in [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md).
