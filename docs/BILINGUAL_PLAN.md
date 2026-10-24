# Bilingual (EN/FR) and XOF: audit and build plan

Working record for making AMANIQ Academy a bilingual English/French platform with NGN and XOF pricing and GeniusPay for XOF payments. Phase 0 (this document) changes no code. Each later phase is committed separately and leaves the site working.

## 1. Decisions agreed with the owner (24 October 2026)

| Question | Decision |
| --- | --- |
| URL structure | French pages at `/fr/...`; every existing English URL stays unchanged |
| French scope | **Everything**: interface, public pages, all programmes, labs, exams, emails, certificates, legal pages |
| Translation quality | Claude drafts the French; a francophone reviewer with security knowledge (the owner's friend) approves it in Admin. Nothing is advertised as French until approved |
| Updates to English | **Not** translated automatically. Changed English marks the French as "out of date" in Admin; learners keep seeing the last approved French. An optional AI "draft translation" button may be added later; it would never publish |
| XOF prices | Set by the owner in Admin. Admin can **suggest** a price from an exchange rate the owner enters (with its date), rounded; the owner confirms. Saved prices never move with exchange rates |
| GeniusPay | Build and test in sandbox with credentials from the owner's contact. **Live payments need AMANIQ's own GeniusPay merchant account**: with someone else's live keys, customers' money would go to that account |
| Certificate | Issued in the learner's language (FR or EN) |

## 2. What exists today (audit)

### Interface and text

| Area | Finding |
| --- | --- |
| Blade views | 87 files (23 admin, 30 components, 14 learner, 7 public, 7 auth, others). About 6,600 visible words, **all hard-coded in English**; no `lang/` directory; `__()` is used once |
| JavaScript | 3 Alpine components with a handful of user-facing messages (lab submission, KQL errors, exam timer) |
| Controller messages | About 33 flash messages and 35 `RuleViolation` messages, all English |
| Validation | Laravel's default (English) messages; one custom request (`EnquiryRequest`) |
| Emails | Only Laravel's built-in verification and password-reset notifications (via Fortify). **No purchase confirmation email exists** |
| Certificate PDF | `CertificatePdfService` with Noto Sans (supports French accents). `certificates.programme_title` is an English snapshot |
| Locale config | `APP_LOCALE=en`, fallback `en`; no locale middleware |

### Content volume to translate (words, approximate)

| Programme | Lessons | Checks | Labs (incl. data that stays as-is) | Questions | Module and catalogue text | Total |
| --- | --- | --- | --- | --- | --- | --- |
| Network Security | 29,050 | 5,545 | 13,308 | 4,771 | 1,335 | ~54,000 |
| Cloud Security | 26,530 | 5,302 | 11,014 | 4,885 | 1,355 | ~49,000 |
| GRC | 11,430 | 1,107 | 3,220 | 2,707 | 573 | ~19,000 |
| SOC Analyst | 9,460 | 766 | 2,438 | 1,693 | 716 | ~15,000 |
| Penetration Testing | outline only | | | | 341 | <400 |
| Legal pages, articles, marketing pages | | | | | | ~3,000 |
| Interface | | | | | | ~6,600 |
| **Total** | | | | | | **~145,000** |

Lab figures include synthetic log tables, IP addresses and KQL data, which are **not** translated.

### Schema: content

Content lives in English columns on: `specializations` (title, summary, description, audience, prerequisites, outcomes, inclusions, faqs), `course_modules`, `lessons` (title, objectives, body_md, knowledge_checks), `labs` (title, summary, `content` JSON, `validator` JSON), `questions` (prompt, explanation, objective) and `question_options` (text), `exams` (title), `badge_definitions`, `articles`, plus `legal.json` and marketing text in Blade. Courses are versioned (`course_versions`); `VersionService::createDraft` replicates modules, lessons and labs into a new draft. Stable keys exist: programme slug, module slug, lesson slug, lab slug, question `external_key`.

### Schema: money and payments (already strong)

| Item | Finding |
| --- | --- |
| Prices | `offers`: per programme and **currency**, `amount_minor` (integer), `active`, `valid_from`, `valid_until`, `created_by` |
| Orders | `orders` snapshot `currency`, `subtotal_minor`, `discount_minor`, `total_minor`, `provider`, `status`, `paid_at`, `offer_id` |
| Payments | `payments` with unique (`provider`, `provider_reference`) |
| Webhooks | `payment_webhook_events` with unique (`provider`, `event_key`): duplicates ignored |
| Verification | `CheckoutService::confirm` verifies server-to-server and rejects amount or currency mismatches (audited) |
| Provider abstraction | `PaymentProvider` interface (`initialize`, `verify`, `parseWebhook`), with Paystack and a non-production sandbox |

**Gaps found:**

1. **XOF display bug waiting to happen.** `Money::format` always divides by 100. XOF has no minor unit (ISO 4217 exponent 0), so 150,000 FCFA stored as `150000` would display as 1,500. Needs a per-currency decimals table used for display, provider amounts and input parsing.
2. **One provider for the whole site.** `PaymentProviderFactory::make()` reads a single `PAYMENT_PROVIDER`; `confirm()` and webhooks use that one provider. XOF via GeniusPay plus NGN via Paystack needs providers chosen **per currency**, with each order resolved by its stored `provider`.
3. **One webhook URL.** `/webhooks/payments` must become `/webhooks/payments/{provider}` (keeping the old path for Paystack so its dashboard setting still works).
4. **NGN hard-coded** in `Admin\PricingController` (create offer, discounts), `CatalogueService::activePrice` default and `SandboxProvider`.
5. Discounts with a fixed amount are single-currency; percentage discounts work for any currency.

## 3. GeniusPay (research, 24 October 2026)

From the official reference at `https://pay.genius.ci/doc` and the official Laravel SDK (`geniuspay/laravel-sdk` v2.0.1, June 2026). **Everything below must be confirmed in sandbox**; the reference has internal inconsistencies, noted.

| Item | Documented behaviour |
| --- | --- |
| Base URL | `https://pay.genius.ci/api/v1/merchant/` for both environments; sandbox or live chosen by key prefix (`pk_sandbox_`/`sk_sandbox_`, `pk_live_`/`sk_live_`) |
| Authentication | Headers `X-API-Key` (public key), `X-API-Secret` (secret, server-side only) |
| Create payment | `POST /payments` with `amount` (whole XOF, minimum 200), `currency` (`XOF`; EUR and USD also listed), `description`, `customer`, `success_url`, `error_url`, `metadata`. Without `payment_method`, returns a hosted `checkout_url` (24-hour expiry) where the learner picks Wave, Orange Money, MTN, Moov or card |
| Verify | `GET /payments/{reference}`: `status`, amount, currency, fees, `completed_at` |
| Webhooks | Header `X-Webhook-Signature` = HMAC-SHA256 of `timestamp + "." + body` with the webhook secret; `X-Webhook-Timestamp` (reject if older than 300 s); events `payment.success`, `payment.failed`, `payment.cancelled`, `payment.expired`, `payment.refunded` |
| Statuses | Listed inconsistently (`pending`, `processing`, `completed`, `failed`, `expired`, and elsewhere `cancelled`, `refunded`). Map only a confirmed "completed" to success |
| Refunds | No refund API documented; record refunds manually in Admin, as with Paystack |
| Integration choice | A small `GeniusPayProvider` against the HTTP API (same pattern as `PaystackProvider`), verifying signatures on the **raw** body. No SDK dependency |

Production needs: AMANIQ's own merchant account and live keys, the webhook URL registered in the GeniusPay dashboard, outbound HTTPS from GO54 to `pay.genius.ci`, and one real low-value test payment with refund.

## 4. Architecture

### Locales and routing

- `config/academy.php` lists supported locales (`en`, `fr`) and each locale's default currency (`en → NGN`, `fr → XOF`).
- **Public pages** (home, programmes, about, corporate, contact, resources, legal, verify) get a `/fr` prefix through a route group defined once and registered for both locales, with names such as `fr.programmes.show`. A `localized_route()` helper picks the right one. English URLs are unchanged.
- **Signed-in and admin pages** (dashboard, learning, checkout, account, admin) keep one URL; their language comes from the user's saved preference. Switching language never touches the session, so the learner stays signed in and progress is untouched.
- `SetLocale` middleware: URL prefix → signed-in user's `locale` column → `locale` cookie (one year) → browser `Accept-Language` on first visit → `en`.
- Header selector `EN | FR`: on a public page it links to the equivalent page; elsewhere it posts to `/locale` (CSRF-protected), which saves the cookie and, for signed-in users, `users.locale`, then returns to the same page.
- Currency is a separate preference: it defaults from the locale but learners can choose (a French speaker in Lagos, an English speaker in Dakar).

### Interface strings

- Laravel JSON language files (`lang/fr.json`) keyed by the English text, plus `lang/fr/*.php` for validation, passwords, pagination and authentication messages.
- All Blade text wrapped in `__()`; JavaScript messages passed in from Blade (Alpine CSP build) rather than hard-coded.
- A test fails if a key used in views has no French entry.

### Content translations

> **Changed when built (Phase 4):** instead of one table per content type, a single `content_translations` table holds every type, with the translated text stored as **segments** (each piece of English prose, by its path, with the English it was translated from and the French). Same rules as below, but one editor, one review queue and one out-of-date check for all content, and a lab translation can never change a task, option id or answer key, because the French is laid over the English structure rather than stored as a copy. See section 7, Phase 4.

The original design (one translation table per content type, never duplicate courses):

| Table | Key | Translated fields |
| --- | --- | --- |
| `specialization_translations` | specialization_id + locale | title, summary, description, audience, prerequisites, outcomes, inclusions, faqs |
| `module_translations` | course_module_id + locale | title, purpose, prerequisite knowledge, objectives, key concepts, assessment criteria |
| `lesson_translations` | lesson_id + locale | title, objectives, body_md, knowledge_checks |
| `lab_translations` | lab_id + locale | title, summary, learner-visible `content` (same task and option IDs), validator **text** only: feedback, debrief, `mustMention` terms per locale |
| `question_translations` | question_id + locale | prompt, explanation, objective, option texts keyed by option id |
| `article_translations`, `badge_translations`, `exam_translations` | id + locale | text fields |

Every translation row carries `status` (`draft`, `in_review`, `approved`), `source_hash` (hash of the English fields it was translated from, so a changed English source is detected as **out of date**), `translated_by`, `reviewed_by`, `reviewed_at`, timestamps, and a unique (id, locale) constraint.

Rules:

- English stays in the existing columns: no destructive migration, no change for English learners.
- Answer keys, option IDs, task IDs, technical values (IPs, KQL, JSON policies, commands), ATT&CK IDs and source references are **shared**; only words are translated. A test checks that each lab translation has the same task and option IDs as the English.
- Labs graded by required terms (`deliverable`) use the learner's locale's term list.
- Exam attempts record the locale, and their snapshots are taken in that language, so a past attempt never changes.
- `VersionService::createDraft` copies translations with the lesson, module and lab copies.
- A French page is shown in French only when its translation is **approved**. Otherwise the English is shown under a clear "Not yet available in French" notice (never a blank page), and the programme is not advertised as available in French until all its parts are approved.
- Translations are authored as files in the repository (`database/content/programmes/<dir>/fr/...`), loaded by the seeder like the English, then managed in Admin after launch.

### Pricing and currencies

- `Currency` support class: code, decimals (NGN 2, XOF 0), symbol and display (`₦180,000`; `150 000 FCFA` in French, `XOF 150,000` in English), provider amount conversion.
- `offers` already supports XOF: no new table. Admin pricing shows NGN and XOF side by side, each enabled or disabled independently; history kept (old offers deactivated, never deleted).
- **Suggested price** helper: the owner enters a rate (for example 1 NGN = x XOF) and its date; Admin proposes a rounded XOF price (for example to the nearest 500 FCFA). Nothing is saved until the owner confirms. The rate used is recorded in the audit log.
- No XOF price configured: the French catalogue shows "Tarif en FCFA bientôt disponible : contactez-nous" with a contact link, and checkout in XOF is refused server-side. Never a silent NGN charge.
- Fixed-amount discounts stay single-currency and only apply to orders in that currency.

### Payments

- Provider chosen per currency: `config('academy.payments.routes') = ['NGN' => 'paystack', 'XOF' => 'geniuspay']`, each provider with its own keys and enabled flag.
- `PaymentProviderFactory::forCurrency()` and `::byId()`; `confirm()` and webhooks resolve the provider from the **order's** stored provider.
- Webhooks at `/webhooks/payments/{provider}`; `/webhooks/payments` kept as Paystack's.
- `GeniusPayProvider`: initialize (hosted checkout), verify (`GET /payments/{reference}`), webhook (HMAC-SHA256 over timestamp and raw body, 300-second window, constant-time compare).
- Sandbox provider extended to XOF for local development and tests.
- Orders gain `locale` (for messages) and nothing else; existing amount, currency and provider snapshots already preserve history.
- Admin payments page shows each order's currency and exact amount, filters for pending and failed, a "check status with provider" reconciliation button, and which providers are configured (never the keys).
- Secrets only in `.env`; never in views, logs or JavaScript.

### SEO and accessibility

- `<html lang>` from the locale; localised titles and descriptions.
- `hreflang="en"`, `hreflang="fr"` and `x-default` only between pages whose French is approved; canonical URL per language.
- Sitemap lists French URLs only for approved content.
- Selector is a real link or button pair with visible focus, `aria-current`, `lang` attributes on the labels, and fits the mobile menu.

## 5. Phases

| Phase | Scope | Done when |
| --- | --- | --- |
| **0** | This audit and plan | Plan committed |
| **1** | Locale infrastructure: config, `users.locale`, `SetLocale` middleware, `/fr` public routes, selector, cookie, `/locale` endpoint, `<html lang>`, all interface strings in `lang/fr.json` and `lang/fr/*.php`, JavaScript messages, emails (verification, reset) | Site fully switchable; course content still English with an honest notice; tests for switching, persistence, progress unchanged |
| **2** | Currencies and pricing: `Currency` class and the XOF formatting fix, XOF offers, admin pricing per currency with suggested price, currency preference, missing-price handling, admin warnings | XOF prices shown correctly in French; checkout refuses missing prices; tests |
| **3** | Payments: per-currency providers, `GeniusPayProvider`, per-provider webhooks, reconciliation in Admin, localised confirmation and failure messages, sandbox tests (success once, failure, pending, duplicate webhook, bad signature, amount or currency mismatch) | Works end to end in the GeniusPay sandbox (once credentials arrive); production steps documented |
| **4** | Content translation model: tables, seeder support for `fr/` files, learner pages using approved translations with fallback notices, labs and exams in French with shared keys, version copying, Admin translation editor, review workflow, completeness dashboard, out-of-date detection | A translated sample lesson, lab and question work end to end; tests |
| **5** | French content in batches, all as `draft`: marketing pages and legal pages first, then Network, Cloud, SOC, GRC; certificate in French | Every batch committed; reviewer works through the Admin queue |
| **6** | SEO: hreflang, canonical, sitemap, localised metadata | Approved French pages discoverable |
| **7** | Full acceptance test of the brief's journeys in both languages | Results reported, including anything not yet verifiable (live payments) |

## 6. Risks and limits

- **Translation quality** depends on the human review; the platform enforces that only approved French is advertised.
- **Volume**: about 145,000 words. Phase 5 spans many sessions.
- **Safety check**: some English security lessons were stopped before; translating them may be stopped too. Anything stopped is listed for a person to translate.
- **Legal pages** in French need a lawyer's approval before launch.
- **GeniusPay** is untested until sandbox credentials are provided; live readiness needs AMANIQ's own merchant account.
- **Exchange-rate suggestions** are a convenience only. Prices are business decisions for the owner.

## 7. Progress

### Phase 1: language infrastructure and interface text (24 October 2026), done

- `config/academy.php` lists the locales; `App\Support\Locales` and the `lroute()` helper map public routes to their `/fr` equivalents.
- Public pages are registered once per language (`routes/web.php`): English URLs and route names unchanged, French under `/fr` with `fr.` names.
- `SetLocale` middleware: URL prefix, then the signed-in user's `locale`, then the `locale` cookie, then `Accept-Language`, then English. An explicit French choice redirects English public pages to their French version; visitors without a choice (including search engines) are never redirected.
- EN | FR switch in the site and admin headers (`/language/{locale}?to=…`): saves the cookie and, for signed-in users, `users.locale`; returns to the same page in the chosen language; refuses outside targets. Also shown on the Account page.
- New accounts keep the language they registered in; `User::preferredLocale()` sends verification and reset emails in that language.
- All interface text in the 87 views, controller and service messages, lab grader feedback and script messages goes through `__()`; `lang/fr.json` has 1,071 French entries; `lang/fr/{auth,passwords,pagination,validation}.php` translate Laravel's own messages, with French field names.
- Dates use `translatedFormat`, so month names follow the language. Role names use `App\Support\Labels::role()`.
- Tests: `LocalisationTest` (8 tests) covers URLs, the switch, cookie and profile persistence, staying signed in with progress unchanged, no redirect for new visitors, refusing outside redirects, French validation and email text, and that every key in the code has French with matching placeholders. Full suite: 54 tests passing.
- Checked in the browser: French home, programme, catalogue, corporate, dashboard and admin pages; switching back and forth while signed in; no horizontal scrolling at 375 px.

**Not yet French:** course and article content, programme titles and the certificate's programme title (Phase 4–5). Prices in FCFA: see Phase 2. French text is a draft for the reviewer; nothing is marked approved.

### Phase 2: XOF as a currency and prices per currency (24 October 2026), done

- `config/academy.currencies` defines NGN (2 decimals, kobo) and XOF (0 decimals, whole francs, ISO 4217). `App\Support\Currency` parses admin input exactly (string arithmetic, no floats; XOF refuses decimals) and formats per language: `₦180,000` in English, `150 000 FCFA` in French (narrow no-break space for thousands). `Money::format` now delegates to it; the old "divide by 100" would have shown XOF prices wrongly.
- Visitor currency: the signed-in user's `users.currency`, then the `currency` cookie, then the language default (English → NGN, French → XOF). NGN | FCFA switch (`/currency/{currency}?to=…`) on the catalogue, programme and checkout pages. It only chooses which configured price is shown and charged; nothing is converted.
- No XOF price: programme cards say "Tarif en FCFA bientôt disponible"; the programme and checkout pages explain it, offer "Contactez-nous pour vous inscrire" and a link to the naira price; checkout refuses with a French message and no order is created. The naira price is never charged silently.
- Admin → Pricing: one price per currency per programme, set independently; a change creates a new offer and closes the old one, so existing orders keep their price. A price can be switched off, except the programme's last live price while enrolment is open. Opening enrolment needs at least one live price in any currency.
- Exchange rate: the owner enters "FCFA for 1 naira", its date and source (stored as platform setting `fx_rate_ngn_xof`). The pricing page then suggests an XOF price rounded to the nearest 500 FCFA and pre-fills it when no XOF price exists. The suggestion is never charged until the admin saves it; the audit entry records the rate it came from.
- Fixed-amount discounts have a currency and only apply to orders in it; percentage codes apply in any currency.
- Providers declare `supportsCurrency()`. Paystack takes the currencies in `PAYSTACK_CURRENCIES` (default `NGN`); if the learner's currency has no provider, checkout shows the price, says online payment in that currency is not available yet and offers contact instead. GeniusPay (XOF) is Phase 3.
- Audit: `price.changed` (with currency and any rate used), `price.disabled`, `fx_rate.saved`, `discount.created` (with currency).
- Tests: `CurrencyTest` (8 tests) covers parsing, formatting, rounding, the switch and its persistence, the missing-price notice and refusal, XOF orders keeping their price after a change, currency-bound discounts, refusal when the provider cannot take XOF, and the admin rate, price, switch-off and discount flows with their audit entries.
- Fixed while testing: French thousands separators were garbled (a multibyte space reversed with the digits), and the currency switch sent visitors without a saved language from a French page to the English one. It now returns to the page in the language it was in.
- Checked in the browser: French programme page without an FCFA price (notice, contact button, link to the naira price); switching currency stays on the French page; admin rate saved and suggestions correct (₦180,000 × 0.38 → 68,500 FCFA); an FCFA price saved and shown as "68 500 FCFA" on the French catalogue and in checkout; switching it off; all three actions in the audit log; no horizontal scrolling at 375 px. The test rate and price were switched off again on the development database; no real FCFA prices or rate are set.
- Full suite: 62 tests passing.

**Owner to do:** enter the real exchange rate and confirm each FCFA price in Admin → Pricing. Until a programme has an FCFA price, French visitors see "Tarif en FCFA bientôt disponible" and can contact AMANIQ or view the naira price. Online FCFA payment needs GeniusPay (Phase 3); until then FCFA checkout shows the price and asks the learner to contact AMANIQ.

### Phase 3: GeniusPay for FCFA, Paystack kept for naira (24 October 2026), built and tested against a faked GeniusPay; not yet run against the real sandbox

- One provider per currency (`academy.payments.currencies`, from `PAYMENT_PROVIDER_NGN` and `PAYMENT_PROVIDER_XOF`; the old `PAYMENT_PROVIDER` still sets NGN). `PaymentProviderFactory` builds them; every status check and webhook goes back to the provider stored on the order, so changing the routing never strands a pending payment.
- `GeniusPayProvider` (no SDK): `POST /payments` for a hosted checkout (Wave, Orange Money, MTN, Moov, card) with the amount in whole francs; `GET /payments/{reference}` for the authoritative status; webhooks checked with HMAC-SHA256 over `timestamp.body` (constant-time compare, 300-second window). Keys travel only in request headers to GeniusPay; they are never shown in Admin, sent to the browser or logged.
- GeniusPay issues its own reference (`MTX-…`), now stored as `orders.provider_reference`; ours goes in `metadata.order_reference`. Orders also record the checkout language (`orders.locale`).
- Webhooks: `/webhooks/payments/geniuspay` (and `/payments/sandbox` in development); `/webhooks/payments` stays Paystack's. A webhook only prompts a status check: access is granted only when GeniusPay itself reports `completed` for exactly the order's amount and currency. Amounts must be exact whole francs (`65000` or `65000.00`); anything else is refused without rounding. `payment.failed`, `cancelled` and `expired` close the order after the same check; refunds are recorded by hand in Admin, as for Paystack.
- Production guards: the app refuses to boot with GeniusPay routed but no `sk_live_` key, and in production a payment GeniusPay reports from its sandbox environment never counts as paid.
- Checkout: the provider is chosen on the server from the currency of the price; below GeniusPay's 200 FCFA minimum, or with no provider for the currency, the learner is asked to contact AMANIQ and nothing is sent. If GeniusPay cannot be reached the order is closed and the learner sees "Nous n'avons pas pu joindre le prestataire de paiement. Aucun montant n'a été débité…" instead of an error page.
- Admin → Orders shows which provider takes each currency (ids only), GeniusPay's reference for each order, and a "Check with provider" button for pending orders (audited as `order.status_checked`).
- Tests: `GeniusPayTest` (12 tests, faked GeniusPay API): checkout request and stored reference, success enrols once, duplicate webhook, forged and stale signatures, webhook not trusted over the status check, failure, amount and currency mismatches, sandbox payments refused in production, exact amounts, provider unreachable (French message), below minimum, naira routed separately, and the admin status check.

- Checked in the browser (development sandbox standing in for GeniusPay): a French learner checks out a programme at 68 500 FCFA, pays on the sandbox page and sees "Paiement confirmé"; Admin → Orders shows the routing and the FCFA order. The test order was then recorded as refunded and the test FCFA price switched off. Full suite: 74 tests passing.

**GeniusPay sandbox checklist** (needs AMANIQ's or the owner's friend's `pk_sandbox_`/`sk_sandbox_`/`whsec_sandbox_` keys in a local or staging `.env` with `PAYMENT_PROVIDER_XOF=geniuspay`; the webhook needs a public https URL, so staging is easiest):

1. Create a payment: does `POST /payments` without `payment_method` return `data.checkout_url` and `data.reference`?
2. Pay in the sandbox: does the status become exactly `completed`, and is `amount` returned as a whole number (`65000` or `65000.00`)?
3. Webhook signature: is it computed over the raw body exactly as received? (The reference's sample re-encodes the JSON.) If webhooks are refused with 401 while payments succeed, this is why; the return page still confirms the payment by status check.
4. Is `data.reference` in the webhook the same `MTX-…` reference returned at creation? (The reference's webhook example shows `TXN-…`.)
5. Failed, cancelled and expired payments: which statuses and events arrive?
6. Do learners return to `success_url`/`error_url` with any query parameters?

Until this is done, nothing here claims GeniusPay works with the real API.

### Phase 4: content translation model and review workflow (24 October 2026), done

- One table, `content_translations` (item type, item id, locale, segments, status, `outdated`, translator, origin, reviewer, review date and note), for programmes, modules, lessons, labs, questions, exams, badges and articles. English stays in the content tables; nothing was migrated destructively.
- **Segments** (`App\Translation\Segments`): every piece of English prose has a path (`body_md`, `knowledge_checks.0.options.1`, `content.tasks[order].prompt`, `validator.tasks.order.feedback.correct`, `options.2` for question options by position). Each stores the English it was translated from and the French. Only prose keys are translated; ids, types, answer keys, lab data, KQL tables, starter policies and code are never touched, so a translated lab or quiz works exactly like the English (tested by marking the same answers in both languages). Accepted answers and required terms in written deliverables take **extra** French terms; the English ones always count.
- **What learners see:** an item is shown in French only when its translation is approved, complete and current. Otherwise the English is shown with a French notice ("Cette leçon n'est pas encore disponible en français…") and marked `lang="en"`. Lesson and lab pages, the course outline, the dashboard, the catalogue and the programme page all follow this. Exam attempts take their question snapshot in the learner's language and record it (`exam_attempts.locale`); a started attempt never changes.
- **Workflow:** draft → in review → approved. Saving any change to reviewed text returns it to draft. Sending for review needs every segment translated and current. Approval needs `translations.review` and a different person from the last editor or the sender (instructors and admins hold both permissions, so the reviewer can be given an instructor account). A reviewer can send back with a note. All steps are audited (`translation.saved`, `submitted`, `approved`, `returned`, `confirmed_current`).
- **Out of date:** when English content is saved, its translations are checked segment by segment; changed or new English marks them out of date, and learners see the English until it is dealt with. The editor shows each changed segment with the previous English. For a small correction a reviewer can confirm the French still matches; new English text must be translated and reviewed.
- **Versions:** a new draft course version copies the translations of its modules, lessons, labs and exam.
- **Files:** `programmes/<dir>/fr/translations.json` (format in `database/content/README.md`) is loaded by the seeder as drafts, insert-if-missing. `"origin": "machine"` marks tool or AI drafts; the editor warns the reviewer to check them.
- **Admin → French translations:** completeness per programme (approved, in review, draft, out of date, not started, with filters) and the editor, with each English segment beside its French field.
- **Sample:** SOC module 1, its first lesson, the phishing timeline lab and question `soc-001` were drafted in French (origin `machine`) and are waiting for the reviewer; none is approved.
- Tests: `ContentTranslationTest` (10 tests): segments never touch structure or answers, seeded drafts never reach learners, second-person approval, the sender of an imported draft cannot approve it, incomplete drafts refused, out-of-date detection and confirmation, the lab marked identically in both languages with French feedback, exam attempts keeping their language, version copies, and the admin editor over HTTP. Full suite: 84 tests passing.
- Checked in the browser: the dashboard (4 drafts out of 88 SOC items); the editor with the machine-draft warning; the instructor sends the lesson for review and is blocked from approving it; the admin approves; the French learner then reads the lesson in French (headings, table and Markdown intact, code block kept in English) while the draft lab shows the English with the French notice. Afterwards the lesson was returned to draft and the temporary enrolment revoked on the development database.
- Found and fixed while checking: sending an unchanged imported draft for review did not record the sender, which would have let them approve it themselves.

**Not yet French:** all other course content (Phase 5). Articles are a translatable type, but the public article pages do not use translations yet; legal pages come from `legal.json` and get their French in Phase 5 with the marketing pages. The certificate's programme title is also Phase 5.

### Phase 5: French content (24 October 2026), batches 1 and 2 drafted; all waiting for review

Nothing in this phase is approved. Every item is a draft with origin `machine`, so the editor shows the warning to check it carefully. Learners keep seeing English, with the French notice, until the reviewer approves each item in **Admin → French translations**.

**Batch 1: site pages and programme pages**

- Legal pages are now records (`legal_pages`), synced from `database/content/legal.json`, which stays the source of the English. A change to that file marks the French out of date. Public legal and article pages, and the article lists, show approved French.
- New **Site pages** tab in the dashboard for legal pages and articles.
- Drafted: the four legal pages, the three articles, and the public page, badge and exam title of all five programmes (files: `database/content/fr/`, `programmes/<dir>/fr/translations.json`).
- The marketing pages (home, about, corporate, contact) are interface text, drafted in Phase 1 in `lang/fr.json`. They are not in the Admin review workflow; the reviewer should read them on the site in French.

**Batch 2: the whole SOC Analyst programme**

- 10 modules, 23 lessons (with knowledge checks), 6 labs, 46 exam questions, the badge and the exam: 88 of 88 items drafted (files `programmes/soc-analyst/fr/translations-*.json` and `fr/lessons/*.md`).

**Conventions used (for the reviewer):**

- Commands, queries and machine output stay exactly as in English: KQL, bash, YAML/Sigma, log lines, event IDs, column and table names, file paths and registry keys. Where a code block carries English comments, a sentence after it explains it in French.
- Human-written templates are translated, because learners copy their structure: the escalation message, case notes, the investigation timeline, the plain-language rule logic.
- MITRE ATT&CK tactic and technique names keep their official English names; in lab options a French gloss follows in brackets.
- Product and framework names are not translated (Microsoft Sentinel, NIST CSF 2.0, Sigma, ISO/IEC 27001). Programme names are translated, matching the site footer ("Analyste SOC").
- Legal pages are a faithful translation of the English draft. Placeholders stay in square brackets as `[À compléter : …]`.
- Typography: typographic apostrophe (’), French quotation marks (« »), a space before « : ; ? ! ».

**Quality gate (automated):** a test checks that every French draft covers every English segment, was drafted from the current English, keeps the same number of code blocks with KQL, bash, YAML, PowerShell and JSON blocks byte-for-byte identical, and keeps every legal placeholder. It also checks that each translated SOC lab marks the known-correct answers exactly as the English does.

**Points for the owner (not translation issues):**

1. The terms of service say prices are in Nigerian naira. Since Phase 2 they can be in FCFA, charged through GeniusPay. The English needs updating first; the French will then show as out of date.
2. The privacy policy, the GRC programme page and the incident response lesson cite only Nigerian law (NDPA 2023, NDPC). For learners in Côte d'Ivoire, counsel should decide whether to add Ivorian data protection law and its authority. They are not added here, to avoid inventing legal content.
3. The Network Security and Cloud Security programme pages still say their curriculum is "in development". That may be out of date now that both programmes are complete.
4. In the SOC hunting lesson, the KQL example under "Beaconing" counts unique DNS subdomains, which matches the tunnelling hypothesis rather than timing-based beaconing. The English example should be corrected; the French follows the English.

Full suite: 86 tests passing.

**Batch 3: the whole GRC programme**

- 9 modules, 27 lessons (with knowledge checks), 5 labs, 57 exam questions, the badge and the exam: 101 of 101 items drafted (`programmes/grc/fr/translations-*.json` and `fr/lessons/*.md`). Nothing approved.
- **Lab reading documents are now translatable.** GRC, Network and Cloud labs give learners documents to read in the lab workspace (criteria tables, interview notes, policies). The segment walker now translates each document's title and, for `markdown` documents, its body. Evidence datasets, KQL tables, sample queries and `code` documents (JSON policies, configuration files) are still never touched; the quality gate checks this for every translated lab.
- Terminology: ISO vocabulary follows the usual French terms (SMSI, déclaration d'applicabilité (DdA), non-conformité, correction / action corrective, revue de direction). NIST CSF Functions and Categories and CIS Control names are translated descriptively with the official identifiers unchanged (GV.OC, PR.AA, IG1, Control 6); the reviewer should align them with an official French translation if AMANIQ adopts one. Money examples stay in naira (₦), as in the English.
- The NDPA 2023 lesson and the questions on it are a faithful translation of Nigerian-law content (see owner point 2 above).

Full suite after batch 3: 86 tests passing.

**Next batches:** Network Security and Cloud Security (the largest, about 100,000 words together), then the certificate's programme title in French.
