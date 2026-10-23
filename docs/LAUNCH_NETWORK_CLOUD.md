# Launch checklist: Network Security and Cloud Security

Both programmes have all ten modules written and tested (see `NETWORK_CLOUD_PLAN.md`). They are not yet open to learners. This checklist covers what a person must do before launch, in the order the platform enforces it. Steps marked **Admin** are done in the admin area; nothing here needs code changes.

## Where things stand

| Item | State |
| --- | --- |
| Lessons, checks and labs | Written; 50 lessons and 10 labs per programme; every lab passes with reference answers in the test suite |
| Exam question banks | 133 questions each, loaded as **draft** (not exam-ready) |
| Final exams | Draft; 40 questions, 75% pass mark, 3 attempts, 24-hour cooldown |
| Programme status | In development, enrolment closed |
| Prices | Set in the catalogue (Network ₦180,000; Cloud ₦220,000); confirm before launch |

## 1. Review the content (people, not the platform)

- [ ] A subject-matter expert reads every lesson, starting with those listed in `NETWORK_CLOUD_PLAN.md` section 6 (Network 2, Network 5, Cloud 3), which no person has read yet.
- [ ] They try each lab as a learner (Admin → Orders and enrolments → Grant complimentary enrolment, using your own email) and read each debrief.
- [ ] A lawyer confirms the NDPA breach-notification statements (Cloud modules 4 and 9).
- [ ] Someone opens `public/content/network-security/kanem-portal-slow.pcap` in Wireshark and checks it matches the Network 8 lab.
- [ ] Fix anything found: either in the content files (then rebuild locally) or, after launch, in Admin.

## 2. Approve the exam questions (Admin → Question bank)

Only **approved** questions are drawn into exams.

1. Choose the bank (Network Security or Cloud Security), filter **Status: Draft** and, if helpful, one **Category** (one category per module).
2. Open a question with **Edit** to see its options, correct answer and explanation. Fix or retire anything wrong.
3. Tick the questions you have reviewed, choose **Set selected to: Approved** and press **Apply to selected**. Each change is recorded in the audit log under your name.
4. Approve at least **40** per programme, ideally all, spread across the categories (the exam balances its draw across categories).

## 3. Publish the exams (Admin → Exams)

- [ ] For each programme, check the settings and set **Status: Published**. The platform refuses if fewer approved questions exist than the exam draws.

## 4. Open the programmes (Admin → Programmes)

- [ ] Confirm the active price.
- [ ] Set **Content status: Complete**, **Status: Published**, then **Enrolment open**. The platform refuses to open enrolment without a published programme and an active price.

## 5. Deploy

Follow `DEPLOYMENT_GO54.md`. For a site that is already live, use its section 10 ("Updating the site"): build with `bash scripts/build-release.sh`, upload, run `php artisan migrate --force` and `php artisan db:seed --class=CatalogueSeeder --force`.

Notes for an existing production database:

- The catalogue seeder only **adds** what is missing. Network and Cloud questions will arrive as drafts (step 2 then happens on the live site).
- Lessons and labs that already exist in production are **not** overwritten by the seeder. If an earlier version of these programmes was ever loaded on the live site, update it through Admin course versions instead (or ask a developer to plan the migration).
- Do steps 2–4 on the live site after deploying, not only locally: approvals made on your computer don't travel with the code.

## Still open after launch

- Capstone deliverables are checked for completeness only; an instructor review queue is a recommended follow-up.
- Paystack payments and real email sending have not yet been tested against the live services (see `DEPLOYMENT_GO54.md`).
