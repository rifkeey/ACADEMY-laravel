# Curriculum source files

The curriculum of the original AMANIQ Academy (Next.js) was exported here unchanged and is loaded into MySQL by `Database\Seeders\CatalogueSeeder`.

| File | Contents |
| --- | --- |
| `programmes/<dir>/programme.json` | One programme: public page fields, price (kobo), badge, exam settings, modules, labs (learner `content` and server-only `validator`) and the question bank |
| `programmes/<dir>/lessons/*.md` | Lesson bodies in Markdown, referenced from `programme.json` |
| `sources.json` | Source register: external references cited by lessons, with type and verification status |
| `articles.json` | Free resource articles |
| `legal.json` | Privacy, terms, acceptable use and refund pages |

## How seeding behaves

- The seeder is **insert-if-missing**: running it again never overwrites edits made in the admin area. On a fresh database, `php artisan migrate --seed` loads everything.
- After go-live, the database (edited through **Admin**) is the source of truth, not these files.
- Lab answer keys (`validator`) are stored in a separate column that is never sent to the browser.

## Changes compared with the original content

- `soc-analyst` → lab `kql-suspicious-signins` → task `spray-ips`: the validator now has `"unique": true`. In the original, the console's starter query (`| take 20`) happened to return only the spray source and passed this task by accident. The task now needs an aggregated answer, one row per IP. A regression test (`ContentIntegrityTest`) checks that no starter query passes any KQL task.
