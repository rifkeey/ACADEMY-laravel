# Database design (MySQL 8)

All tables use InnoDB, `utf8mb4_unicode_ci` and UTC timestamps. The connection runs in strict SQL mode. Migrations live in `database/migrations`; run `php artisan migrate`.

## Principles

- **Foreign keys everywhere**, mostly `RESTRICT` on delete. Learner records (orders, attempts, certificates) can never be orphaned by deleting a programme or user. Records are archived or revoked instead of deleted.
- **Money is exact**: integer minor units (`*_minor`, kobo for NGN) plus a `currency` column. No floats or decimals in calculations.
- **CHECK constraints** enforce every status vocabulary (e.g. `orders.status IN ('pending','paid','failed','cancelled','refunded')`), order totals (`total = subtotal − discount`) and revocation consistency on certificates.
- **Versioned content**: learners are enrolled in a specific `course_version`; their progress, labs, exams and certificates refer to that version.
- **Snapshots** keep history true: each exam attempt stores the questions and options it was given and the pass mark at the start; certificates store the holder name, programme title and version at issue time.

## Tables

| Group | Tables |
| --- | --- |
| Accounts | `users` (role, status, Fortify 2FA columns), `password_reset_tokens`, `sessions` |
| Catalogue | `specializations`, `course_versions`, `course_modules`, `lessons`, `source_references`, `lesson_source` |
| Commerce | `offers`, `discounts`, `orders`, `payments`, `payment_webhook_events`, `enrolments` |
| Learning | `lesson_progress`, `labs`, `lab_attempts`, `lab_completions` |
| Assessment | `question_banks`, `questions`, `question_options`, `exams`, `exam_attempts`, `exam_attempt_questions` |
| Credentials | `badge_definitions`, `badge_awards`, `certificates`, `certificate_events` |
| Operations | `enquiries`, `articles`, `audit_events`, `platform_settings`, plus Laravel's `cache`, `jobs` tables |

## Key constraints and indexes

| Rule | How it is enforced |
| --- | --- |
| One enrolment per learner per programme | `UNIQUE (user_id, specialization_id)` on `enrolments` |
| A payment is recorded once | `UNIQUE (provider, provider_reference)` on `payments` |
| A webhook is processed once | `UNIQUE (provider, event_key)` on `payment_webhook_events`; a duplicate insert is detected and acknowledged |
| One open exam attempt per learner per exam | MySQL has no partial indexes, so `exam_attempts.open_marker` is a stored generated column (`1` while in progress, `NULL` otherwise) with `UNIQUE (user_id, exam_id, open_marker)`. NULLs do not collide, so any number of submitted attempts are allowed |
| Attempt numbers never repeat | `UNIQUE (user_id, exam_id, attempt_number)` |
| One certificate and one badge per enrolment | `UNIQUE (enrolment_id)` on `certificates` and `badge_awards` |
| Lesson completion recorded once | Primary key `(user_id, lesson_id)` on `lesson_progress`, written with `INSERT IGNORE` |
| One completion per lab | `UNIQUE (user_id, lab_id)` on `lab_completions` |
| Public identifiers are unguessable | `exam_attempts.public_id` (ULID) in URLs; certificate `credential_id` is 26 random Crockford base-32 characters (130 bits); order references are random |
| Slugs unique where they are addressed | `specializations.slug`, `articles.slug`, `(course_version_id, slug)` for modules and labs, `(course_module_id, slug)` for lessons |

Composite indexes follow the queries: e.g. `(course_version_id, status, position)` for ordered published content, `(user_id, lab_id, submitted_at)` for attempt history, `(status, created_at)` for order lists, `(question_bank_id, review_status, category)` for exam selection.

## Transactions and locking

| Operation | Protection |
| --- | --- |
| Payment fulfilment | `SELECT … FOR UPDATE` on the order; the amount and currency are re-checked against the provider; payment + enrolment are written in one transaction |
| Exam submission | The attempt row is locked; a second submit returns the stored result unchanged |
| Exam start | Abandoned expired attempts are closed and the new attempt created in one transaction; the unique `open_marker` index makes a double click fail safely |
| Certificate issue | The enrolment row is locked, so concurrent requests cannot issue two certificates |
| Publishing a version, price changes, role changes | Locked rows and a single transaction; an audit event is written in the same transaction |

## Settings and secrets

`platform_settings` holds only non-secret operational values (contact email, support hours, signatory, verification name display). Secrets such as payment keys and mail passwords are read from `.env` only.
