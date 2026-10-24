# AMANIQ Academy (Laravel): notes for contributors and AI assistants

Laravel 13 / PHP 8.3 / MySQL 8 rebuild of the AMANIQ Academy learning platform. It must run on cPanel shared hosting (GO54): no Node, Redis, queues or daemons on the server.

## Commands

- `php artisan serve`: dev server (http://127.0.0.1:8000)
- `php artisan migrate:fresh --seed`: rebuild the dev database (curriculum + dev accounts from `DevAccountsSeeder`)
- `php artisan test`: full suite on the MySQL database `amaniq_academy_test` (see `phpunit.xml`)
- `npm run build`: rebuild CSS/JS. **Commit `public/build`**: the server cannot build assets

## Rules that must not be broken

- **Brand**: "amaniq" is the parent wordmark; the division name ("Academy") is a separate element beside it (`<x-brand.lockup division="Academy">`). Never merge them.
- **Honesty**: no invented accreditation, partnerships, testimonials or statistics. Certificates confirm completion only.
- **Pass mark**: integer rule in `App\Assessment\Grading::isPass` (75% = 30/40). Never use floats for grading or money.
- **Money**: integer minor units (kobo) plus currency.
- **Answer keys never reach the browser**: lab `validator` and `question_options.is_correct` are hidden on the models. The exam page gets option ids and text only.
- **Authorisation**: every admin action calls `$this->authorize('<permission>')` (matrix in `app/Support/Permissions.php`). `role`/`status` are never mass-assignable.
- **Versioning**: structural changes (required items, status, tasks, answer keys) only in draft versions; use `VersionService::assertEditable`.
- **Alpine CSP build**: no inline JS expressions beyond simple calls and property access; put logic in `resources/js/components/*`. Inside component methods `this.$el` is the element that triggered the event, so read `data-*` attributes in `init()`.
- **Blade gotchas**: `@word` in an attribute (e.g. `@use-query`) can be parsed as a directive; use `x-on:` for custom events. A directive directly after a word character (`</x-slot>@endisset`) is not compiled; put it on its own line.
- **Bilingual (EN/FR)**: every piece of interface text goes through `__()` (or `trans_choice()`), with whole sentences and `:placeholders`, never fragments around a variable. Add the French to `lang/fr.json`; `LocalisationTest` fails if a key has no French or its placeholders differ. Link to public pages with `lroute()` so French pages link to `/fr/...`; signed-in pages keep one URL and follow the saved preference. Brand names, product names, KQL/column names and code stay untranslated. Plan: `docs/BILINGUAL_PLAN.md`.
- **Windows dev gotcha**: a single-word translation key that matches a language file name (`Pagination`, `Auth`, `Validation`, `Passwords`) returns an array on Windows; use a longer key.
- Comments: short, plain explanations of why, matching the existing style.

## Layout

`app/Services` holds the business logic (controllers stay thin). `app/Labs` is the KQL interpreter and grader. `database/content` is the curriculum source loaded by `CatalogueSeeder` (insert-if-missing). Docs live in `docs/`.
