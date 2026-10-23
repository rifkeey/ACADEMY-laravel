// Cloud Security, module 8: Cloud Application, API and DevSecOps Security.
// Also puts the modules in the order of docs/NETWORK_CLOUD_PLAN.md section 3: workloads before posture,
// devsecops as module 8, and the old governance-resilience starter removed (its topics move to the capstone).
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation', r = 'open_resource';
upsertSources([
    { key: 'owasp-api-top10-2023', title: 'OWASP Top 10 API Security Risks – 2023', publisher: 'OWASP', url: 'https://owasp.org/API-Security/editions/2023/en/0x11-t10/', topic: 'API security', sourceType: r, versionNote: '2023 edition.', supports: 'The common API weaknesses and their mitigations.' },
    { key: 'owasp-top10-2025', title: 'OWASP Top 10:2025', publisher: 'OWASP', url: 'https://owasp.org/Top10/2025/', topic: 'Web application security', sourceType: r, versionNote: '2025 edition.', supports: 'Common web application risks.' },
    { key: 'owasp-asvs', title: 'OWASP Application Security Verification Standard (ASVS)', publisher: 'OWASP', url: 'https://owasp.org/www-project-application-security-verification-standard/', topic: 'Application security requirements', sourceType: r, versionNote: 'Project page; check the current version.', supports: 'Testable application security requirements.' },
    { key: 'owasp-cicd-top10', title: 'OWASP Top 10 CI/CD Security Risks', publisher: 'OWASP', url: 'https://owasp.org/www-project-top-10-ci-cd-security-risks/', topic: 'Pipeline security', sourceType: r, versionNote: 'Project page.', supports: 'Common CI/CD pipeline risks.' },
    { key: 'github-actions-secure-use', title: 'Secure use reference (GitHub Actions)', publisher: 'GitHub', url: 'https://docs.github.com/en/actions/reference/security/secure-use', topic: 'Pipeline security', sourceType: v, versionNote: 'GitHub Docs, current (example platform).', supports: 'Token permissions, pinning actions, untrusted input and runner guidance.' },
    { key: 'github-actions-oidc', title: 'OpenID Connect (GitHub Actions)', publisher: 'GitHub', url: 'https://docs.github.com/en/actions/concepts/security/openid-connect', topic: 'Workload identity federation', sourceType: v, versionNote: 'GitHub Docs, current (example platform).', supports: 'Short-lived cloud credentials for pipelines.' },
    { key: 'cisa-sbom', title: 'Software Bill of Materials (SBOM)', publisher: 'CISA', url: 'https://www.cisa.gov/sbom', topic: 'SBOM', sourceType: o, versionNote: 'Live topic page.', supports: 'What SBOMs are and how they are used.' },
    { key: 'cisa-kev', title: 'Known Exploited Vulnerabilities Catalog', publisher: 'CISA', url: 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog', topic: 'Vulnerability prioritisation', sourceType: o, versionNote: 'Live catalogue.', supports: 'Prioritising vulnerabilities known to be exploited.' },
    { key: 'nist-sp-800-218', title: 'SP 800-218: Secure Software Development Framework (SSDF) Version 1.1', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/218/final', topic: 'Secure development', sourceType: o, versionNote: 'Version 1.1, February 2022. SP 800-218 Rev. 1 (SSDF v1.2) was an initial public draft in December 2025; check for the final.', supports: 'Secure software development practices.' },
    { key: 'nist-sp-800-204d', title: 'SP 800-204D: Strategies for the Integration of Software Supply Chain Security in DevSecOps CI/CD Pipelines', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/204/d/final', topic: 'Supply chain security', sourceType: o, versionNote: 'Final, February 2024.', supports: 'Supply-chain controls in cloud-native pipelines.' },
    { key: 'slsa-levels', title: 'SLSA security levels (v1.1)', publisher: 'OpenSSF (SLSA)', url: 'https://slsa.dev/spec/v1.1/levels', topic: 'Build integrity', sourceType: r, versionNote: 'Specification v1.1.', supports: 'Build levels and provenance.' },
    { key: 'sigstore-docs', title: 'Sigstore documentation', publisher: 'Sigstore (OpenSSF)', url: 'https://docs.sigstore.dev/', topic: 'Artefact signing', sourceType: r, versionNote: 'Project documentation.', supports: 'Signing and verifying software artefacts.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'api-security', title: 'API security: authentication, authorisation and the OWASP API Top 10', minutes: 30, sources: ['owasp-api-top10-2023', 'owasp-top10-2025', 'owasp-asvs'],
        objectives: ['Explain the most common API weaknesses', 'Place controls at the identity, gateway, application and network layers', 'Explain why object-level authorisation belongs in the application'],
        checks: [
            { question: 'A signed-in customer can read another customer\'s loan by changing the ID in the URL. Which weakness is this?', options: ['Broken authentication', 'Broken object level authorisation', 'Unrestricted resource consumption', 'Security misconfiguration'], answerIndex: 1, explanation: 'The caller is authenticated, but ownership of the object is not checked.' },
            { question: 'Where must the check "does loan 4471 belong to this caller?" happen?', options: ['In the mobile app', 'In the application on the server', 'Only at the API gateway', 'In the DNS server'], answerIndex: 1, explanation: 'Only the application knows who owns which object.' },
            { question: 'Which controls help against unrestricted resource consumption? Select all that apply.', options: ['Rate limits', 'Request size limits', 'Timeouts and quotas', 'Longer access tokens'], answerIndexes: [0, 1, 2], explanation: 'Limit rate, size and cost.' },
        ] },
    { slug: 'securing-ci-cd-pipelines', title: 'Securing CI/CD pipelines', minutes: 35, sources: ['owasp-cicd-top10', 'github-actions-secure-use', 'github-actions-oidc'],
        objectives: ['Treat the pipeline as a privileged identity', 'Replace stored cloud keys with OIDC federation', 'Apply branch protection, approvals, minimal permissions and pinning'],
        checks: [
            { question: 'What is the best replacement for a long-lived cloud key stored as a pipeline secret?', options: ['A longer key', 'OIDC federation issuing short-lived credentials to one role', 'Storing the key in the repository', 'Sharing one key across all pipelines'], answerIndex: 1, explanation: 'Nothing long-lived is stored, and the trust policy limits which repository and branch may use it.' },
            { question: 'Why pin third-party actions to a full commit SHA?', options: ['It runs faster', 'A branch or tag can change; a commit SHA cannot', 'It is required by law', 'It hides the code'], answerIndex: 1, explanation: 'Pinning means you run exactly the code you reviewed.' },
            { question: 'Which are good pipeline controls? Select all that apply.', options: ['Required review before merging to main', 'Approval before deploying to production', 'Minimal token permissions per job', 'Running pull requests from forks with deployment secrets'], answerIndexes: [0, 1, 2], explanation: 'Untrusted code must never run with secrets.' },
        ] },
    { slug: 'dependencies-images-and-sbom', title: 'Dependencies, images and SBOMs', minutes: 30, sources: ['cisa-sbom', 'cisa-kev', 'nist-sp-800-190'],
        objectives: ['Make builds repeatable with lock files, pinning and trusted sources', 'Explain what an SBOM is for', 'Prioritise scanner findings by exploitability and exposure'],
        checks: [
            { question: 'A major library vulnerability is announced. What answers "do we use it, and where?" fastest?', options: ['Asking every developer', 'An SBOM for each release', 'A network scan', 'The firewall logs'], answerIndex: 1, explanation: 'SBOMs list every component and version.' },
            { question: 'Which finding should be fixed first?', options: ['A medium finding in an internal test tool', 'A known-exploited vulnerability in an internet-facing service', 'A low finding with no fix available', 'A licence warning'], answerIndex: 1, explanation: 'Known exploitation plus exposure comes first.' },
            { question: 'Why rescan images already in the registry?', options: ['Images change by themselves', 'New vulnerabilities are published every day', 'Registries delete scans', 'It is never needed'], answerIndex: 1, explanation: 'An image clean last month may not be clean today.' },
        ] },
    { slug: 'security-testing-in-the-pipeline', title: 'Security testing in the pipeline', minutes: 30, sources: ['nist-sp-800-218', 'owasp-asvs'],
        objectives: ['Place SAST, SCA, secret, IaC and image scanning, DAST and authorisation tests in the workflow', 'Decide which findings block, warn or record', 'Manage false positives and time-limited exceptions'],
        checks: [
            { question: 'A developer commits a cloud key and deletes it in the next commit. What must happen?', options: ['Nothing; it was deleted', 'Rotate the key immediately and check its use in audit logs', 'Make the repository private', 'Rename the file'], answerIndex: 1, explanation: 'The key is in the history and may have been copied. Rotate it.' },
            { question: 'Which test finds problems in the running application, such as session handling?', options: ['SAST', 'DAST', 'SCA', 'Linting'], answerIndex: 1, explanation: 'Dynamic testing exercises the running application.' },
            { question: 'What must a security exception include? Select all that apply.', options: ['An owner', 'A reason and compensating controls', 'An expiry date', 'A promise never to review it'], answerIndexes: [0, 1, 2], explanation: 'Exceptions expire so the gate applies again.' },
        ] },
    { slug: 'software-supply-chain', title: 'Software supply chain security', minutes: 30, sources: ['nist-sp-800-218', 'nist-sp-800-204d', 'slsa-levels', 'sigstore-docs'],
        objectives: ['Describe SSDF, SP 800-204D and SLSA', 'Explain provenance, signing and verification at deploy', 'Build a proportionate supply-chain checklist'],
        checks: [
            { question: 'What is build provenance?', options: ['A list of developers', 'A signed record of how an artefact was built, from which source and on which platform', 'A backup of the source code', 'A performance report'], answerIndex: 1, explanation: 'Provenance lets you verify where an artefact came from.' },
            { question: 'Where should image signatures be verified?', options: ['Only by developers on their laptops', 'By the deployment platform before the image runs', 'Never; signing is enough', 'By the DNS server'], answerIndex: 1, explanation: 'Admission policies refuse unverified images.' },
            { question: 'Which are supply-chain controls? Select all that apply.', options: ['Production builds only in the pipeline on ephemeral runners', 'Registry write access only for the pipeline', 'Signed images with provenance', 'Manual builds on a developer laptop for production'], answerIndexes: [0, 1, 2], explanation: 'Production artefacts must come from the controlled build.' },
        ] },
];

const R = (id, label) => ({ id, label });

const workflow = [
    'name: deploy-loans-api',
    'on:',
    '  pull_request_target:',
    '  push:',
    '    branches: [main]',
    'permissions: write-all',
    'jobs:',
    '  build-deploy:',
    '    runs-on: [self-hosted, shared]',
    '    steps:',
    '      - uses: actions/checkout@v4',
    '        with:',
    '          ref: ${{ github.event.pull_request.head.sha }}',
    '      - uses: quickdeploy-org/cloud-deploy@main',
    '      - run: docker build -t loans-api:latest .',
    '      - run: ./scripts/deploy.sh production',
    '        env:',
    '          AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ADMIN_KEY_ID }}',
    '          AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_ADMIN_SECRET }}',
].join('\n');

const lab = {
    slug: 'pipeline-security-review',
    title: 'Review and fix the loans API pipeline',
    difficulty: 'intermediate',
    labType: 'config_review',
    summary: 'Review Kanem\'s loans API pipeline, repository settings and scanner results, choose the right fixes and release gates, and write a corrected pipeline design.',
    minutes: 60,
    moduleSlug: 'devsecops',
    passPercent: 80,
    content: {
        objectives: ['Identify weaknesses in a pipeline definition and repository settings', 'Replace stored keys with short-lived credentials', 'Decide which findings block a release', 'Respond correctly to an exposed secret and an API authorisation finding', 'Write a corrected pipeline design'],
        scenario: 'Kanem Financial Services (fictional) is about to let partner banks call the **loans API**. Before go-live, the CISO asks you to review how the API is built and deployed. The workflow file, repository settings and the latest scanner results are in the workspace.',
        scope: 'Work from the documents. All data is synthetic. The workflow uses GitHub Actions syntax as an example; the same ideas apply to other CI/CD platforms.',
        instructions: '1. Read the four documents.\n2. Answer tasks 1–6.\n3. Write the corrected design in task 7. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic.',
        workspace: { kind: 'documents', documents: [
            { title: '.github/workflows/deploy.yml', format: 'code', language: 'yaml', body: workflow },
            { title: 'Dockerfile (first line)', format: 'code', language: 'text', body: 'FROM node:latest' },
            { title: 'Repository and cloud settings (extract)', format: 'markdown', body: '| Setting | Value |\n| --- | --- |\n| Branch protection on `main` | None |\n| Required reviews | 0 |\n| Deployment environments | None configured |\n| Secret `AWS_ADMIN_KEY_ID` | Created February 2023, never rotated |\n| Cloud identity behind the key | IAM user `ci-admin`, policy **AdministratorAccess** |\n| Self-hosted runner `shared` | Persistent VM used by 9 repositories |' },
            { title: 'Scanner and test results (latest build)', format: 'markdown', body: '| ID | Source | Finding |\n| --- | --- | --- |\n| S1 | Secret scanning | A database password for the **test** environment was committed 3 days ago and removed in the next commit |\n| S2 | Image scanning | Critical vulnerability in the base image, listed in CISA KEV; the loans API is internet-facing |\n| S3 | SCA | High-severity vulnerability in a PDF library used only by an internal report job |\n| S4 | DAST | Missing security header on the test site |\n| S5 | IaC scanning | Template change makes the statements bucket publicly readable |\n| S6 | API authorisation tests | `GET /loans/{id}` returns another customer\'s loan when the ID is changed |' },
        ] },
        tasks: [
            { id: 'issues', points: 2, prompt: 'Which weaknesses are present in the workflow and settings? Select all that apply.', hints: ['Lesson 2 has a table of common pipeline risks.'], input: { type: 'multi', options: [R('statickey', 'A long-lived administrator cloud key is stored as a secret'), R('perms', 'The workflow token has write access to everything'), R('unpinned', 'A third-party action is referenced by a branch, not a commit SHA'), R('prcode', 'Code from pull requests runs in a job that can read deployment secrets'), R('noapproval', 'Nothing requires review or approval before production'), R('https', 'The checkout uses HTTPS'), R('tests', 'The workflow runs unit tests')] } },
            { id: 'creds', points: 1, prompt: 'What should replace the stored administrator key?', hints: ['Module 2 workload identities; lesson 2.'], input: { type: 'choice', options: [R('oidc', 'OIDC federation to a deploy role with only the permissions the deployment needs, trusted only for kanem/loans-api, branch main, environment production'), R('rotate', 'Keep the key but rotate it every year'), R('user', 'Use a developer\'s personal key instead'), R('vault', 'Move the same administrator key to a different secret store')] } },
            { id: 'gates', points: 2, prompt: 'Decide the release gate for each finding.', hints: ['Lesson 4: block, warn or record.'], input: { type: 'grid', rows: [R('s2', 'S2 critical KEV in internet-facing base image'), R('s3', 'S3 high in internal report job library'), R('s4', 'S4 missing header on test site'), R('s5', 'S5 statements bucket made public'), R('s6', 'S6 another customer\'s loan returned')], columns: [{ id: 'gate', label: 'Gate', options: [R('block', 'Block the release'), R('warn', 'Warn and ticket with a deadline'), R('record', 'Record on the backlog')] }] } },
            { id: 'secret', points: 1, prompt: 'How should S1 (the committed test database password) be handled?', hints: ['Is removing it from the code enough?'], input: { type: 'choice', options: [R('rotate', 'Rotate the password now, check the database logs for use since it was committed, then move it to the secrets manager'), R('ignore', 'Nothing: it was removed in the next commit and it is only a test password'), R('private', 'Make the repository private'), R('rename', 'Rename the variable')] } },
            { id: 'bola', points: 1, prompt: 'What is the right fix for S6?', hints: ['Lesson 1: which layer knows who owns a loan?'], input: { type: 'choice', options: [R('ownership', 'Check on the server, for every request, that the loan belongs to the authenticated customer; add an automated test for it'), R('hideid', 'Use longer, random loan IDs only'), R('gateway', 'Add rate limiting at the gateway only'), R('app', 'Hide the field in the mobile app')] } },
            { id: 'image', points: 1, prompt: 'What should the Dockerfile use instead of `node:latest`?', hints: ['Lesson 3: repeatable builds.'], input: { type: 'choice', options: [R('pinned', 'A specific, maintained slim version pinned by digest, updated regularly through reviewed pull requests'), R('latest', 'Keep latest so it is always new'), R('old', 'An old version that is known to work, never updated')] } },
            { id: 'design', points: 2, prompt: 'Write the **corrected pipeline design** for the CISO: the workflow changes (you may include a corrected YAML outline), repository and environment settings, credentials, release gates, and how you will handle S1 and S6.', hints: ['Cover every weakness you selected in task 1.'], input: { type: 'deliverable', title: 'Loans API pipeline security design', competency: 'Securing CI/CD pipelines and release gates', minWords: 140, rubric: ['Replaces stored keys with OIDC to a least-privilege deploy role with a narrow trust condition', 'Sets minimal token permissions and pins third-party actions to commit SHAs', 'Separates untrusted pull-request builds from deployments with secrets', 'Adds branch protection, required reviews and a production environment approval', 'Defines release gates and handles S1 and S6 correctly'], template: 'Workflow changes:\n\nRepository and environment settings:\n\nCredentials:\n\nRelease gates:\n\nS1 and S6:\n' } },
        ],
        reflection: ['Which single change reduces the most risk, and why?', 'How would you persuade a team that says approvals slow them down?', 'What would you monitor to spot a deployment that did not come from the pipeline?', 'Which of the scanner findings would you have missed without automated tests?'],
    },
    validator: {
        tasks: {
            issues: { type: 'multi', correct: ['statickey', 'perms', 'unpinned', 'prcode', 'noapproval'], feedback: { correct: 'Right: five weaknesses.', incorrect: 'Check each line of the workflow and each setting against lesson 2. HTTPS checkout and unit tests are fine.' } },
            creds: { type: 'choice', correct: 'oidc', feedback: { correct: 'Right.', incorrect: 'Moving or rotating a long-lived administrator key keeps the core problem.' } },
            gates: { type: 'grid', minCorrectRatio: 0.8, cells: { s2: { gate: ['block'] }, s3: { gate: ['warn'] }, s4: { gate: ['record', 'warn'] }, s5: { gate: ['block'] }, s6: { gate: ['block'] } }, feedback: { correct: 'Your gates match.', incorrect: 'Block exploitable exposure of customer data and public storage; ticket internal highs; record minor items.' } },
            secret: { type: 'choice', correct: 'rotate', feedback: { correct: 'Right.', incorrect: 'A committed secret stays in the history and may already be copied. Rotate it.' } },
            bola: { type: 'choice', correct: 'ownership', feedback: { correct: 'Right.', incorrect: 'Random IDs and gateways do not check ownership. The application must.' } },
            image: { type: 'choice', correct: 'pinned', feedback: { correct: 'Right.', incorrect: 'Builds should be repeatable and kept current deliberately.' } },
            design: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'OIDC or short-lived credentials', any: ['oidc', 'openid', 'short-lived', 'temporary credential', 'id-token'] },
                { label: 'minimal token permissions', any: ['permission'] },
                { label: 'pinning actions', any: ['pin', 'sha'] },
                { label: 'approval or environment protection', any: ['approval', 'environment', 'review'] },
                { label: 'the S6 ownership check', any: ['ownership', 'belongs', 'object level', 'authoris', 'authoriz'] },
            ], feedback: { correct: 'Design complete. Compare it with the rubric and the debrief.', incorrect: 'Your design is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Weaknesses:** a long-lived AdministratorAccess key stored as a secret; `permissions: write-all`; a third-party action pinned to `@main`; `pull_request_target` checking out pull-request code in the same job that holds deployment secrets; no branch protection, reviews or production approval. The shared, persistent self-hosted runner and `node:latest` add risk. HTTPS checkout and unit tests are fine.\n\n**Credentials:** delete the `ci-admin` key and user. Create a deploy role with only the permissions the deployment needs, trusted through OIDC only for `repo:kanem/loans-api:environment:production`.\n\n**A corrected outline:**\n\n```yaml\non:\n  pull_request:          # build and test only, no secrets\n  push:\n    branches: [main]\npermissions:\n  contents: read\njobs:\n  test:\n    runs-on: ubuntu-latest   # or an ephemeral, per-project runner\n    steps:\n      - uses: actions/checkout@<full-commit-sha>\n      - run: npm ci && npm test\n  deploy:\n    if: github.ref == \'refs/heads/main\'\n    needs: test\n    environment: production     # required reviewers\n    permissions:\n      contents: read\n      id-token: write           # OIDC\n    steps:\n      - uses: actions/checkout@<full-commit-sha>\n      - uses: <cloud-credentials-action>@<full-commit-sha>\n        with:\n          role-to-assume: <deploy-role>\n      - run: ./scripts/deploy.sh production\n```\n\nAlso: branch protection with one required review and required status checks; workflow files reviewed like code; ephemeral runners; a pinned, maintained base image; SBOM, image signing and verification before deploy.\n\n**Gates:** block S2, S5 and S6; warn and ticket S3; record S4.\n\n**S1:** rotate the test password now, check the database logs for use since it was committed, move it to the secrets manager. **S6:** server-side ownership check on every request, plus an automated test that customer A cannot read customer B\'s loan.\n\n## What you have practised\n\nReviewing a pipeline as a privileged identity, setting release gates and fixing the root cause of API authorisation flaws: core DevSecOps skills.\n\n**Next:** module 9 covers cloud incident response.',
    },
};

const devsecops = {
    slug: 'devsecops',
    title: 'Cloud Application, API and DevSecOps Security',
    purpose: 'Kanem now ships its loans API to the cloud several times a week. This module covers API security, securing CI/CD pipelines, dependencies, images and SBOMs, security testing in the pipeline, and software supply-chain security. You finish by reviewing and fixing a real-looking pipeline.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written design.',
    prerequisiteKnowledge: 'Modules 2, 6 and 7.',
    objectives: ['Explain and mitigate common API weaknesses', 'Secure CI/CD pipelines as privileged identities', 'Manage dependencies and images with SBOMs and prioritised scanning', 'Place security tests in the pipeline and define release gates', 'Apply supply-chain controls: provenance, signing and verification'],
    keyConcepts: ['OWASP API Top 10', 'Object-level authorisation', 'OIDC federation for pipelines', 'Branch protection and environment approvals', 'Pinning', 'SCA, SAST, DAST', 'SBOM', 'Release gates and exceptions', 'SSDF and SLSA', 'Provenance and signing'],
    assessmentCriteria: ['Identifies pipeline weaknesses from configuration', 'Chooses short-lived, least-privilege credentials', 'Sets proportionate release gates', 'Writes a complete corrected design'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab. The workflow example uses GitHub Actions syntax; check it against the current GitHub documentation before teaching, as the platform changes often.',
    contentStatus: 'complete',
    lessons,
};

// Insert or replace the module, then put the modules in the planned order.
const d = p.data;
const at = d.modules.findIndex((m) => m.slug === 'devsecops');
if (at >= 0) d.modules[at] = devsecops;
else d.modules.push(devsecops);
d.modules = d.modules.filter((m) => m.slug !== 'governance-resilience');
const ORDER = ['cloud-fundamentals', 'iam', 'cloud-networking', 'data-protection', 'logging-monitoring', 'workloads', 'posture', 'devsecops', 'cloud-ir', 'cloud-capstone'];
if (d.modules.length !== ORDER.length || ORDER.some((s) => !d.modules.find((m) => m.slug === s))) throw new Error('unexpected module set');
d.modules.sort((a, b) => ORDER.indexOf(a.slug) - ORDER.indexOf(b.slug));
if (d.labs.some((l) => l.moduleSlug === 'governance-resilience')) throw new Error('a lab still points at governance-resilience');

replaceLab(d, 'pipeline-security-review', lab);

const C = 'devsecops';
replaceQuestions(d, 'cld-dev-', [
    single('cld-dev-01', C, 'foundation', 'Explain API risks', 'Which weakness lets a signed-in user read another user\'s record by changing an ID?', ['Broken authentication', '*Broken object level authorisation', 'Server-side request forgery', 'Improper inventory management'], 'Ownership is not checked on the server.'),
    single('cld-dev-02', C, 'intermediate', 'Place API controls', 'Why can\'t an API gateway alone prevent broken object level authorisation?', ['Gateways cannot use TLS', '*The gateway does not know which objects belong to which caller', 'Gateways block all traffic', 'Gateways only work on-premises'], 'Ownership checks belong in the application.'),
    single('cld-dev-03', C, 'foundation', 'Protect APIs', 'Which control limits unrestricted resource consumption?', ['Longer tokens', '*Rate limits and request size limits', 'Removing authentication', 'Public back ends'], 'Limit rate, size and cost.'),
    single('cld-dev-04', C, 'intermediate', 'Secure pipeline credentials', 'What is the main benefit of OIDC federation for a deployment pipeline?', ['It removes the need for any permissions', '*No long-lived cloud keys are stored, and credentials are short-lived and limited to trusted repositories and branches', 'It speeds up builds', 'It encrypts source code'], 'The trust policy narrows who can obtain credentials.'),
    single('cld-dev-05', C, 'intermediate', 'Pin dependencies', 'Why reference a third-party pipeline action by full commit SHA rather than `@main`?', ['SHA references are shorter', '*The code cannot change without you updating the reference', 'Branches are not allowed', 'It disables logging'], 'You run what you reviewed.'),
    single('cld-dev-06', C, 'intermediate', 'Protect secrets in pipelines', 'Which practice protects deployment secrets from untrusted contributions?', ['Running fork pull requests with secrets for convenience', '*Building untrusted pull requests without secrets and deploying only from protected branches', 'Printing secrets in logs', 'Storing secrets in the repository'], 'Untrusted code must never run with secrets.'),
    multiple('cld-dev-07', C, 'foundation', 'Apply separation of duties', 'Which controls stop one person shipping a change to production alone? Select all that apply.', ['*Required reviews on the main branch', '*Production environment approval', '*Protecting workflow files like code', 'Giving everyone administrator rights'], 'Combine reviews, approvals and protected pipeline definitions.'),
    single('cld-dev-08', C, 'foundation', 'Use SBOMs', 'What does a software bill of materials list?', ['The project budget', '*The components and versions in a release', 'Developers\' passwords', 'Firewall rules'], 'It answers "do we use it, and where?"'),
    single('cld-dev-09', C, 'intermediate', 'Prioritise findings', 'Which scanner finding should block a release?', ['A low finding in an internal tool', '*A known-exploited critical vulnerability in an internet-facing service', 'A licence warning', 'A medium finding with no fix'], 'Exploitation and exposure come first.'),
    single('cld-dev-10', C, 'foundation', 'Respond to secret exposure', 'What is the first action after a key is found in Git history?', ['Delete the file', '*Rotate the key and check its recent use', 'Make the repository private', 'Wait for the next audit'], 'Assume it has been copied.'),
    single('cld-dev-11', C, 'foundation', 'Compare testing methods', 'Which testing method analyses source code without running it?', ['*SAST', 'DAST', 'Penetration test of production', 'Load testing'], 'Static testing reads the code.'),
    single('cld-dev-12', C, 'intermediate', 'Verify artefacts', 'What does verifying image signatures at deployment achieve?', ['Faster deployments', '*Only images built and signed by the trusted pipeline can run', 'It removes vulnerabilities', 'It replaces scanning'], 'Unsigned or unknown images are refused.'),
    single('cld-dev-13', C, 'foundation', 'Know the frameworks', 'Which NIST publication defines the Secure Software Development Framework?', ['SP 800-61', '*SP 800-218', 'SP 800-53', 'SP 800-207'], 'SSDF is SP 800-218.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/cloud-security/lessons/governance-resilience/resilience-and-governance.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('cloud module 8 applied:', d.modules.map((m) => m.slug).join(' > '), '|', d.labs.length, 'labs,', d.questions.length, 'questions');
