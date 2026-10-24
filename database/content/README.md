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

## Authoring tools (`authoring/`)

Large modules are written as small Node scripts that update `programme.json` and `sources.json` (run with `node database/content/authoring/<script>.cjs`). Each script owns one module: it replaces that module's lessons, lab and questions (by key prefix), and adds its sources with the date they were checked. Lesson text lives in the Markdown files. Running a script twice gives identical files.

Downloadable lab files are generated the same way. `pcap-kanem-portal.cjs` builds the synthetic capture for Network module 8 and checks its checksums; `network-m8-traffic-analysis.cjs` writes it to `public/content/network-security/` and puts its packet list and SHA-256 in the lab. Commit the regenerated file whenever the generator changes.

## Teaching features available to content

**Lesson Markdown blocks** (rendered by `App\Support\RichMarkdown`, HTML still escaped):

```text
:::note | :::tip | :::warning | :::scenario | :::takeaway  [optional title]
...Markdown...
:::

:::reveal Title shown on the button
...hidden until opened (hints, worked answers, advanced challenges)...
:::
```

Images go in `public/content/<programme>/` and are referenced as `![alt text](/content/<programme>/file.svg)`. Always write meaningful alt text.

**Knowledge checks** (lesson `checks`): `question`, `options`, and either `answerIndex` (one answer) or `answerIndexes` (select all that apply); optional `explanation`, `code` (shown as a code block) and `optionFeedback` (one string per option, shown for the options a learner picked).

**Lab task types**: `text`, `number`, `choice`, `multi`, `kql`, `firewall`, `grid`, plus:
- `iam-policy`: the learner edits a JSON policy (`input.starter`); the validator lists `requests` (`label`, `action`, `resource`, `expect`). AWS-style evaluation; Condition, NotAction, NotResource and Principal are rejected.
- `deliverable`: a written answer with `input.title`, `input.competency`, `input.minWords`, `input.rubric` and an optional `input.template`; the validator's `mustMention` groups (`label`, `any`) check completeness. Deliverables appear in the learner's Portfolio.

Put the worked solution and debrief in `validator.debrief` (Markdown): it is sent only after the lab is passed. `content.reflection` is a list of questions shown at the end of the lab.

## Translations (`programmes/<dir>/fr/`)

French course content lives beside the English in `programmes/<dir>/fr/translations.json`, keyed by the same stable keys (module, lesson and lab slugs, question keys). Each item lists **segments**: the path of a piece of English prose and its French. Long texts can point at a file: `"body_md": {"file": "lessons/<slug>.md"}`.

```json
{
  "origin": "machine",
  "modules": { "soc-fundamentals": { "title": "…", "objectives.0": "…" } },
  "lessons": { "soc-role-and-workflows": { "title": "…", "body_md": { "file": "lessons/soc-role-and-workflows.md" } } },
  "labs": { "phishing-compromise-timeline": { "content.tasks[order].prompt": "…", "validator.tasks.order.feedback.correct": "…" } },
  "questions": { "soc-001": { "prompt": "…", "options.2": "…" } }
}
```

- Paths are listed in **Admin → French translations** next to each English segment (`App\Translation\Segments` defines them). Only prose can be translated: ids, answer keys, lab data, KQL, IP addresses, starter policies and code blocks are shared with the English and cannot change.
- `validator.tasks.<id>.accept` and `…mustMention.N.any` take **extra** French terms, one per line; the English terms are always accepted.
- The seeder loads these files as **drafts** (insert-if-missing). Nothing reaches learners until a reviewer approves it in Admin. Set `"origin": "machine"` for anything produced by a translation tool or AI, so the editor flags it for a careful check.
