// Cloud Security, module 9: Cloud Incident Response and Threat Hunting.
// Replaces the starter audit-log lab with a two-table investigation. All data is synthetic and generated
// deterministically below; attacker addresses use the documentation range 198.51.100.0/24.
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation', r = 'open_resource';
upsertSources([
    { key: 'attack-t1136-003', title: 'ATT&CK T1136.003: Create Account: Cloud Account', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1136/003/', topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page; ATT&CK is versioned.', supports: 'Detection notes for new cloud accounts.' },
    { key: 'attack-t1685-002', title: 'ATT&CK T1685.002: Disable or Modify Tools: Disable or Modify Cloud Log', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1685/002/', topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page. Formerly T1562.008; the old ID redirects here.', supports: 'Detection notes for changes to cloud logging.' },
    { key: 'aws-ir-guide', title: 'AWS Security Incident Response Guide', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/whitepapers/latest/aws-security-incident-response-guide/welcome.html', topic: 'Cloud incident response', sourceType: v, versionNote: 'Whitepaper, current web edition.', supports: 'Preparation, isolation and forensics in AWS (example provider).' },
    { key: 'azure-ir-overview', title: 'Incident response overview for Azure', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/security/fundamentals/incident-response-overview', topic: 'Cloud incident response', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Incident response in Azure (example provider).' },
    { key: 'gcp-data-incident-response', title: 'Data incident response process', publisher: 'Google Cloud', url: 'https://cloud.google.com/docs/security/incident-response', topic: 'Cloud incident response', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'How the provider handles incidents on its side of shared responsibility.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'preparing-for-cloud-incidents', title: 'Preparing for cloud incidents', minutes: 30, sources: ['nist-sp-800-61r3', 'aws-ir-guide', 'azure-ir-overview', 'gcp-data-incident-response'],
        objectives: ['Explain what makes cloud incidents different', 'Prepare logging, responder access, playbooks, isolation tools and contacts', 'Describe incident roles'],
        checks: [
            { question: 'What is the primary evidence source in most cloud incidents?', options: ['Packet captures', 'The control-plane audit log', 'Antivirus logs', 'Printer logs'], answerIndex: 1, explanation: 'Every change is an API call recorded in the audit log.' },
            { question: 'Which preparations should exist before an incident? Select all that apply.', options: ['A tested incident response role in every account', 'Playbooks for likely cases', 'A quarantine security group and deny-all policy', 'Sharing the root password with the on-call team'], answerIndexes: [0, 1, 2], explanation: 'Root credentials stay sealed and monitored.' },
            { question: 'Who decides whether a personal data breach must be notified?', options: ['The on-call engineer alone', 'The DPO and legal, using facts from the investigators', 'The cloud provider', 'Nobody; it is optional'], answerIndex: 1, explanation: 'Investigators supply accurate facts; DPO and legal decide.' },
        ] },
    { slug: 'compromised-credentials', title: 'Responding to compromised credentials', minutes: 35, sources: ['attack-t1078-004', 'mitre-attack-cloud', 'nist-sp-800-61r3'],
        objectives: ['Recognise signs of a compromised identity', 'Scope what the identity did and created', 'Contain a leaked credential completely'],
        checks: [
            { question: 'A developer who always uses single sign-on suddenly appears using a long-lived access key from a new IP at 02:00. What should you assume?', options: ['She is working late', 'The key may be compromised; investigate now', 'The logs are wrong', 'Nothing until she complains'], answerIndex: 1, explanation: 'A new credential type, source and time together are strong signals.' },
            { question: 'Why deactivate rather than delete a leaked key at first?', options: ['Deleting is impossible', 'It stops use immediately while keeping the record for the investigation', 'Deactivated keys work faster', 'Deleting notifies the attacker'], answerIndex: 1, explanation: 'Preserve the record; delete later.' },
            { question: 'Which containment steps apply to a leaked access key? Select all that apply.', options: ['Deactivate the key and any keys the attacker created', 'Revoke active sessions', 'Restore and protect logging', 'Wait to see what the attacker does next for a week'], answerIndexes: [0, 1, 2], explanation: 'Contain completely and quickly.' },
        ] },
    { slug: 'persistence-and-privilege-changes', title: 'Finding persistence and privilege changes', minutes: 30, sources: ['attack-t1136-003', 'attack-t1098', 'attack-t1548-005', 'attack-t1685-002'],
        objectives: ['Recognise identity, credential, trust, permission and logging changes in audit logs', 'Hunt for them in and before the incident window', 'Remove persistence and prevent recurrence'],
        checks: [
            { question: 'Which audit events most suggest an intruder is keeping access? Select all that apply.', options: ['Create user', 'Create access key for another identity', 'Change a role trust policy to allow an outside account', 'List buckets'], answerIndexes: [0, 1, 2], explanation: 'Listing is discovery; the others create new ways in.' },
            { question: 'What should you do first with an identity the attacker created?', options: ['Delete it immediately without recording anything', 'Attach a deny-all policy and record its configuration', 'Give it fewer permissions', 'Ignore it'], answerIndex: 1, explanation: 'Block now, preserve, then remove together once scoped.' },
            { question: 'Which guardrail stops logging being switched off outside the pipeline?', options: ['A longer password policy', 'An organisation policy that denies stopping or deleting audit trails', 'A bigger log bucket', 'Daily backups'], answerIndex: 1, explanation: 'Organisation-level policies apply even to administrators in member accounts.' },
        ] },
    { slug: 'data-exposure-and-access', title: 'Data exposure and access', minutes: 30, sources: ['attack-t1530', 'ndpc', 'aws-cloudtrail'],
        objectives: ['Use data-access, key-usage and audit logs to establish what was read', 'Combine evidence with data classification', 'Support notification decisions with accurate facts'],
        checks: [
            { question: 'Data-access logging was off for one bucket. What should the report say?', options: ['No data was read from it', 'Reads from it cannot be ruled out, because the logs needed were not enabled', 'Nothing; leave it out', 'All data was taken'], answerIndex: 1, explanation: 'Record gaps honestly.' },
            { question: 'Which evidence helps when object-level logs are missing?', options: ['Key management decrypt events', 'The office visitor book', 'The billing address', 'The DNS zone file'], answerIndex: 0, explanation: 'Decrypt calls can show data being used.' },
            { question: 'What should investigators give the DPO? Select all that apply.', options: ['What data and how many records', 'Which people are affected', 'What is known and what is not', 'A guess presented as certain'], answerIndexes: [0, 1, 2], explanation: 'Accurate, honest facts.' },
        ] },
    { slug: 'evidence-recovery-and-lessons', title: 'Evidence, recovery, threat hunting and lessons learned', minutes: 30, sources: ['nist-sp-800-61r3', 'aws-ir-guide'],
        objectives: ['Preserve cloud evidence before making changes', 'Recover by rebuilding from known-good code and backups', 'Turn incidents into hunting hypotheses and improvement actions'],
        checks: [
            { question: 'What should you do with a suspicious virtual machine?', options: ['Terminate it immediately', 'Isolate it, snapshot its disks and tag it as evidence', 'Reboot it', 'Give it more permissions'], answerIndex: 1, explanation: 'Termination destroys evidence.' },
            { question: 'What is the safest way to recover a compromised workload?', options: ['Clean the server by hand', 'Rebuild from known-good infrastructure as code and images', 'Restore yesterday\'s snapshot of the compromised server', 'Leave it running'], answerIndex: 1, explanation: 'Rebuild, don\'t repair.' },
            { question: 'Which is a threat-hunting hypothesis?', options: ['"An alert fired at 02:14"', '"Other old access keys may have leaked the same way; list them and their last use"', '"The server is up"', '"Logs are stored in a bucket"'], answerIndex: 1, explanation: 'Hunting starts from a testable idea.' },
        ] },
];

// ---- Synthetic data ----------------------------------------------------------------------------
let seed = 911;
const rnd = () => ((seed = (Math.imul(seed, 1103515245) + 12345) >>> 0) / 4294967296);
const pick = (a) => a[Math.floor(rnd() * a.length)];
const t = (h, m, s) => `2026-03-11T${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}Z`;
const sec = (iso) => { const [h, m, s] = iso.slice(11, 19).split(':').map(Number); return h * 3600 + m * 60 + s; };
const at = (n) => t(Math.floor(n / 3600), Math.floor((n % 3600) / 60), n % 60);

const ATTACKER = '198.51.100.140', ATTACKER2 = '198.51.100.141';
const audit = [];
const A = (time, Identity, CredentialType, AccessKey, SourceIP, EventName, Target, Region, Result) => audit.push({ EventTime: time, Identity, CredentialType, AccessKey, SourceIP, EventName, Target, Region, Result });

// The leaked long-lived key of dev.akande.
const k1 = ['dev.akande', 'AccessKey', 'AKEY-0193', ATTACKER];
A(t(2, 3, 11), ...k1, 'GetCallerIdentity', '', 'eu-west-1', 'Success');
A(t(2, 3, 40), ...k1, 'ListUsers', '', 'eu-west-1', 'Success');
A(t(2, 4, 2), ...k1, 'ListRoles', '', 'eu-west-1', 'Success');
A(t(2, 4, 30), ...k1, 'ListBuckets', '', 'eu-west-1', 'Success');
A(t(2, 5, 12), ...k1, 'GetBucketPolicy', 'kanem-loans-restricted', 'eu-west-1', 'Success');
A(t(2, 6, 0), ...k1, 'CreateUser', 'svc-backup2', 'eu-west-1', 'Success');
A(t(2, 6, 21), ...k1, 'CreateAccessKey', 'svc-backup2', 'eu-west-1', 'Success');
A(t(2, 6, 45), ...k1, 'AttachUserPolicy', 'svc-backup2: AdministratorAccess', 'eu-west-1', 'Success');
A(t(2, 8, 10), ...k1, 'RunInstances', 'p4d.24xlarge x 8', 'ap-southeast-1', 'AccessDenied');
A(t(2, 8, 11), ...k1, 'RunInstances', 'p4d.24xlarge x 8', 'ap-southeast-1', 'AccessDenied');
A(t(2, 14, 55), ...k1, 'StopLogging', 'kanem-prod-trail', 'eu-west-1', 'Success');
// The identity the attacker created.
const k2 = ['svc-backup2', 'AccessKey', 'AKEY-7731', ATTACKER2];
A(t(2, 20, 5), ...k2, 'GetCallerIdentity', '', 'eu-west-1', 'Success');
A(t(2, 21, 0), ...k2, 'ListBuckets', '', 'eu-west-1', 'Success');
A(t(2, 40, 18), ...k2, 'DeleteTrail', 'kanem-org-trail', 'eu-west-1', 'AccessDenied');

// Normal activity: SSO sessions from Kanem's office NAT addresses, the pipeline role and the invoice service.
const staff = [['dev.akande', '198.18.1.43'], ['dev.obi', '198.18.1.27'], ['ops.usman', '198.18.1.26'], ['dev.eze', '198.18.1.41']];
const normalEvents = ['ListBuckets', 'GetObject', 'DescribeInstances', 'GetLogEvents', 'ListFunctions', 'GetBucketPolicy', 'DescribeSecurityGroups'];
for (let n = sec(t(7, 2, 0)); n < sec(t(11, 0, 0)); n += 180 + Math.floor(rnd() * 420)) {
    const [who, ip] = pick(staff);
    A(at(n), who, 'SSO session', '', ip, pick(normalEvents), '', 'eu-west-1', 'Success');
}
for (const n of [sec(t(9, 30, 4)), sec(t(9, 31, 40)), sec(t(9, 32, 2))]) {
    A(at(n), 'role/ci-deploy', 'OIDC (pipeline)', '', '192.0.2.10', pick(['UpdateFunctionCode', 'PutObject', 'CreateDeployment']), '', 'eu-west-1', 'Success');
}
for (let n = sec(t(0, 15, 0)); n < sec(t(11, 0, 0)); n += 1800) A(at(n), 'role/svc-invoice', 'Instance role', '', '10.50.2.14', 'AssumeRole', '', 'eu-west-1', 'Success');
audit.sort((a, b) => a.EventTime.localeCompare(b.EventTime));

const data = [];
const D = (time, Identity, SourceIP, Bucket, ObjectKey, Bytes) => data.push({ EventTime: time, Identity, SourceIP, Bucket, Operation: 'GetObject', ObjectKey, Bytes });
const RESTRICTED = 186, STATEMENTS = 40;
for (let i = 0; i < RESTRICTED; i++) D(at(sec(t(2, 9, 0)) + i), 'dev.akande', ATTACKER, 'kanem-loans-restricted', `applications/2025/LA-${String(41000 + i * 7)}.pdf`, 180000 + Math.floor(rnd() * 240000));
for (let i = 0; i < STATEMENTS; i++) D(at(sec(t(2, 22, 0)) + i * 3), 'svc-backup2', ATTACKER2, 'kanem-statements', `2026/02/ST-${String(880100 + i * 13)}.pdf`, 60000 + Math.floor(rnd() * 90000));
for (let n = sec(t(7, 10, 0)); n < sec(t(11, 0, 0)); n += 240 + Math.floor(rnd() * 600)) {
    const [who, ip] = pick([['role/svc-invoice', '10.50.2.14'], ['dev.obi', '198.18.1.27'], ['ops.usman', '198.18.1.26']]);
    const bucket = who === 'role/svc-invoice' ? 'kanem-invoices' : pick(['kanem-invoices', 'kanem-build-artifacts']);
    D(at(n), who, ip, bucket, `${bucket === 'kanem-invoices' ? 'out' : 'builds'}/${Math.floor(rnd() * 90000)}`, 20000 + Math.floor(rnd() * 500000));
}
data.sort((a, b) => a.EventTime.localeCompare(b.EventTime));

// ---- Lab ---------------------------------------------------------------------------------------
const R = (id, label) => ({ id, label });
const PHASE = [R('discovery', 'Discovery'), R('persistence', 'Persistence'), R('privilege', 'Privilege escalation'), R('evasion', 'Defence evasion'), R('collection', 'Data access / collection')];

const lab = {
    slug: 'cloud-compromise-investigation',
    title: 'Investigate a compromised developer key',
    difficulty: 'intermediate',
    labType: 'query_exercise',
    summary: 'Use KQL over synthetic audit and data-access logs to scope a leaked access key: what the intruder did, what they created, what data they read, and how to contain and report it.',
    minutes: 75,
    moduleSlug: 'cloud-ir',
    passPercent: 80,
    content: {
        objectives: ['Scope an identity\'s actions from the audit log', 'Identify persistence and privilege changes', 'Establish what data was read', 'Choose containment and notification steps', 'Write an incident report'],
        scenario: 'At 02:15 UTC on 11 March 2026 an alert fired at Kanem Financial Services (fictional): **audit logging was stopped** in the production account. The organisation trail, kept in the separate log account, was still recording. You are the investigator on call.',
        scope: 'CloudAudit and DataAccess are synthetic tables in a provider-neutral format modelled on common cloud logs. 198.18.1.0/24 is Kanem\'s office NAT range; 192.0.2.10 is the CI/CD platform.',
        instructions: '1. Use the KQL console to explore both tables.\n2. Answer tasks 1–9.\n3. Write the incident report in task 10. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic and generated for training. No real systems, people or organisations are involved.',
        workspace: { kind: 'kql',
            sampleQuery: 'CloudAudit\n| where EventName == "StopLogging"',
            tables: [
                { name: 'CloudAudit', description: 'Control-plane API calls, 11 March 2026 (organisation trail).', columns: ['EventTime', 'Identity', 'CredentialType', 'AccessKey', 'SourceIP', 'EventName', 'Target', 'Region', 'Result'], rows: audit },
                { name: 'DataAccess', description: 'Object-level read events for storage buckets, 11 March 2026.', columns: ['EventTime', 'Identity', 'SourceIP', 'Bucket', 'Operation', 'ObjectKey', 'Bytes'], rows: data },
            ] },
        tasks: [
            { id: 'actions', points: 2, prompt: 'Write a query that returns the **EventName** of every call made from the source IP that stopped logging.', hints: ['Run the sample query to find the SourceIP.', '| where SourceIP == "…" | distinct EventName'], input: { type: 'kql', table: 'CloudAudit' } },
            { id: 'credential', points: 1, prompt: 'Which credential did the intruder use first?', hints: ['Compare CredentialType and AccessKey with dev.akande\'s normal activity after 07:00.'], input: { type: 'choice', options: [R('key', 'dev.akande\'s long-lived access key AKEY-0193; she normally works through an SSO session from the office'), R('sso', 'dev.akande\'s SSO session from the office'), R('pipeline', 'The CI/CD pipeline role'), R('invoice', 'The invoice service role')] } },
            { id: 'newidentity', points: 1, prompt: 'Which new identity did the intruder create?', hints: ['Filter on EventName == "CreateUser".'], input: { type: 'text' } },
            { id: 'tactics', points: 2, prompt: 'Classify each group of events.', hints: ['Lesson 3 maps events to ATT&CK tactics.'], input: { type: 'grid', rows: [R('e1', 'GetCallerIdentity, ListUsers, ListRoles, ListBuckets'), R('e2', 'CreateUser and CreateAccessKey for svc-backup2'), R('e3', 'AttachUserPolicy AdministratorAccess to svc-backup2'), R('e4', 'StopLogging on kanem-prod-trail; DeleteTrail on kanem-org-trail'), R('e5', 'GetObject from kanem-loans-restricted and kanem-statements')], columns: [{ id: 'phase', label: 'Tactic', options: PHASE }] } },
            { id: 'buckets', points: 2, prompt: 'Write a query over **DataAccess** that returns one row per **Bucket** read from either intruder IP address (198.51.100.140 and 198.51.100.141).', hints: ['| where SourceIP in ("…", "…")', '| summarize count() by Bucket'], input: { type: 'kql', table: 'DataAccess' } },
            { id: 'restricted', points: 1, prompt: 'How many objects were read from **kanem-loans-restricted** by the intruder?', hints: ['Count the rows for that bucket and source.'], input: { type: 'number' } },
            { id: 'why', points: 1, prompt: 'Logging was stopped at 02:14:55, yet you can see events after that. Why?', hints: ['Which trail was stopped, and which one recorded these events?'], input: { type: 'choice', options: [R('org', 'Only the account trail was stopped; the organisation trail in the separate log account kept recording, and an organisation policy denied the attempt to delete it'), R('cache', 'The logs were cached'), R('provider', 'The provider ignores StopLogging'), R('fake', 'The events after 02:14:55 are not real')] } },
            { id: 'contain', points: 2, prompt: 'Which containment steps should happen now? Select all that apply.', hints: ['Lesson 2: contain completely, preserve first where it costs little.'], input: { type: 'multi', options: [R('preserve', 'Export the audit and data-access logs for the incident window to the forensics account'), R('keys', 'Deactivate AKEY-0193 and AKEY-7731'), R('denyall', 'Attach a deny-all policy to svc-backup2 and record its configuration'), R('sessions', 'Revoke active sessions for dev.akande and svc-backup2'), R('logging', 'Restart kanem-prod-trail and confirm the organisation policy protects it'), R('close', 'Delete the production account to be safe'), R('wait', 'Leave the keys active for a week to watch the intruder')] } },
            { id: 'notify', points: 1, prompt: 'Loan application files (restricted, containing personal data) were read. What should the incident lead do?', hints: ['Lesson 4.'], input: { type: 'choice', options: [R('dpo', 'Brief the DPO and legal now with the facts (which data, how many files, what is not known) so they can decide on NDPC notification within the 72-hour window and on telling customers'), R('wait', 'Wait until the investigation is fully finished before telling anyone'), R('public', 'Post a public statement immediately before the facts are known'), R('none', 'No action: the data was only read, not deleted')] } },
            { id: 'report', points: 2, prompt: 'Write the **incident report** for the CISO: summary, timeline (UTC), how the intruder got in, what they created and changed, what data was read, containment done, what is not yet known, and recommendations.', hints: ['Separate facts from interpretation, as in Network module 8.'], input: { type: 'deliverable', title: 'Cloud incident report: leaked developer key', competency: 'Investigating and reporting a cloud credential compromise', minWords: 160, rubric: ['Gives an accurate UTC timeline from first call to the denied DeleteTrail', 'Identifies the leaked key, the new identity and its administrator policy', 'States which buckets and how many objects were read, and what remains unknown', 'Lists containment completed and evidence preserved', 'Recommends root-cause fixes (no long-lived keys, guardrails, alerts) and notification steps'], template: 'Summary:\n\nTimeline (UTC):\n\nHow access was gained:\n\nPersistence and changes:\n\nData accessed:\n\nContainment and evidence:\n\nNot yet known:\n\nRecommendations:\n' } },
        ],
        reflection: ['What single control would have prevented this incident?', 'Why did the organisation trail matter so much?', 'What would you have been unable to say if data-access logging had been off?', 'Which threat-hunting query would you run tomorrow?'],
    },
    validator: {
        tasks: {
            actions: { type: 'kql', column: 'EventName', values: ['GetCallerIdentity', 'ListUsers', 'ListRoles', 'ListBuckets', 'GetBucketPolicy', 'CreateUser', 'CreateAccessKey', 'AttachUserPolicy', 'RunInstances', 'StopLogging'], mode: 'set', feedback: { correct: 'Right: discovery, a new user with a key and administrator rights, blocked compute launches and logging tampering.', incorrect: `Filter SourceIP == "${ATTACKER}" and return EventName.` } },
            credential: { type: 'choice', correct: 'key', feedback: { correct: 'Right.', incorrect: 'Compare the CredentialType and SourceIP of the 02:00 calls with dev.akande\'s calls after 07:00.' } },
            newidentity: { type: 'text', accept: ['svc-backup2'], feedback: { correct: 'Right.', incorrect: 'Filter on CreateUser and read the Target.' } },
            tactics: { type: 'grid', minCorrectRatio: 0.8, cells: { e1: { phase: ['discovery'] }, e2: { phase: ['persistence'] }, e3: { phase: ['privilege'] }, e4: { phase: ['evasion'] }, e5: { phase: ['collection'] } }, feedback: { correct: 'Your classification matches.', incorrect: 'Listing is discovery; new identities are persistence; admin policies are privilege escalation; logging changes are defence evasion.' } },
            buckets: { type: 'kql', column: 'Bucket', values: ['kanem-loans-restricted', 'kanem-statements'], mode: 'set', unique: true, feedback: { correct: 'Right: two buckets.', incorrect: `Filter SourceIP in ("${ATTACKER}", "${ATTACKER2}") and summarize by Bucket.` } },
            restricted: { type: 'number', value: RESTRICTED, tolerance: 0, feedback: { correct: 'Right.', incorrect: 'Count the DataAccess rows for kanem-loans-restricted from the intruder.' } },
            why: { type: 'choice', correct: 'org', feedback: { correct: 'Right: this is why the log account and its guardrails matter (module 5).', incorrect: 'Compare the Target of StopLogging and DeleteTrail, and the Result of each.' } },
            contain: { type: 'multi', correct: ['preserve', 'keys', 'denyall', 'sessions', 'logging'], feedback: { correct: 'Right.', incorrect: 'Preserve, deactivate both keys, block the new identity, revoke sessions and restore logging. Deleting the account destroys evidence; waiting lets the intruder continue.' } },
            notify: { type: 'choice', correct: 'dpo', feedback: { correct: 'Right.', incorrect: 'Reading personal data can be a notifiable breach. Brief the DPO and legal early with accurate facts.' } },
            report: { type: 'deliverable', minWords: 160, mustMention: [
                { label: 'the leaked key AKEY-0193 or dev.akande', any: ['akey-0193', '0193', 'dev.akande'] },
                { label: 'svc-backup2', any: ['svc-backup2'] },
                { label: 'the restricted bucket and object count', any: ['kanem-loans-restricted', String(RESTRICTED)] },
                { label: 'logging being stopped', any: ['stoplogging', 'logging', 'trail'] },
                { label: 'notification or the DPO', any: ['dpo', 'data protection', 'ndpc', 'notif'] },
            ], feedback: { correct: 'Report complete. Compare it with the rubric and the debrief.', incorrect: 'Your report is not complete yet.' } },
        },
        debrief: `## Worked solution\n\n**Timeline (UTC, 11 March 2026)**\n\n| Time | Event |\n| --- | --- |\n| 02:03–02:05 | dev.akande's long-lived key **AKEY-0193** used from ${ATTACKER}: GetCallerIdentity, ListUsers, ListRoles, ListBuckets, GetBucketPolicy on kanem-loans-restricted |\n| 02:06 | CreateUser **svc-backup2**, CreateAccessKey for it, AttachUserPolicy **AdministratorAccess** |\n| 02:08 | RunInstances for eight large GPU instances in ap-southeast-1, **denied** (probably an attempt to run cryptocurrency mining) |\n| 02:09–02:12 | **${RESTRICTED}** loan application files read from kanem-loans-restricted |\n| 02:14:55 | StopLogging on kanem-prod-trail (the account trail) |\n| 02:20–02:24 | svc-backup2 (AKEY-7731) from ${ATTACKER2}: GetCallerIdentity, ListBuckets, then **${STATEMENTS}** statements read from kanem-statements |\n| 02:40 | DeleteTrail on kanem-org-trail **denied** by the organisation policy |\n| From about 07:00 | dev.akande's normal work through SSO from the office (198.18.1.43) |\n\n**Queries:**\n\n\`\`\`text\nCloudAudit\n| where SourceIP == "${ATTACKER}"\n| distinct EventName\n\nDataAccess\n| where SourceIP in ("${ATTACKER}", "${ATTACKER2}")\n| summarize Objects = count(), Bytes = sum(Bytes) by Bucket\n\`\`\`\n\n**Containment:** export logs first; deactivate AKEY-0193 and AKEY-7731; deny-all on svc-backup2 (then remove it once scoped); revoke sessions; restart the account trail; reset dev.akande's credentials and find where the key leaked (check repositories and pipelines, module 8).\n\n**Notification:** restricted personal data was read. Brief the DPO and legal at once with: ${RESTRICTED} loan applications and ${STATEMENTS} statements, the customers concerned, and the time window. They decide on NDPC notification (72-hour window) and telling customers.\n\n**Not yet known:** where the key leaked; whether it was used before 11 March (hunt back 90 days); whether the downloaded files were published.\n\n**Recommendations:** remove all long-lived user access keys; require SSO with phishing-resistant MFA; alert on any CreateUser, CreateAccessKey or policy attachment in production; keep the organisation trail and its deny policy; add a detection for StopLogging.\n\n## What you have practised\n\nScoping a credential compromise end to end from logs, the core skill of a cloud incident responder.\n\n**Next:** module 10, the capstone, brings everything together in a secure design.`,
    },
};

replaceModule(p.data, 'cloud-ir', {
    slug: 'cloud-ir',
    title: 'Cloud Incident Response and Threat Hunting',
    purpose: 'Most cloud incidents start with a stolen credential and play out in the audit log. This module covers preparing for cloud incidents, responding to compromised credentials, finding persistence and privilege changes, establishing data exposure, and preserving evidence, recovering and hunting. You finish by investigating a leaked developer key across two log tables and writing the incident report.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written report.',
    prerequisiteKnowledge: 'Modules 2, 4 and 5.',
    objectives: ['Prepare logging, access, playbooks and tools for cloud incidents', 'Detect and scope compromised credentials', 'Find and remove persistence and privilege changes', 'Establish what data was accessed and support notification decisions', 'Preserve evidence, recover safely and run threat hunts'],
    keyConcepts: ['NIST SP 800-61r3 and CSF 2.0', 'Incident response role and playbooks', 'Valid cloud accounts', 'Scoping an identity', 'Persistence and privilege escalation', 'Disabling cloud logs', 'Data-access logs', 'NDPA notification', 'Evidence preservation', 'Threat hunting'],
    assessmentCriteria: ['Scopes a compromise accurately with KQL', 'Classifies intruder actions', 'Chooses complete containment', 'Writes an accurate, honest incident report'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1.25 hours of lab. The NDPA notification timing is given as a pointer for the DPO, not legal advice; have a lawyer confirm it before publication.',
    contentStatus: 'complete',
    lessons,
});
p.data.labs = p.data.labs.filter((l) => l.slug !== 'cloud-audit-log-investigation');
replaceLab(p.data, 'cloud-compromise-investigation', lab);

const C = 'cloud-ir';
replaceQuestions(p.data, 'cld-ir-', [
    single('cld-ir-01', C, 'foundation', 'Prepare for incidents', 'Which preparation most improves a cloud investigation?', ['A bigger firewall', '*Organisation-wide audit logs kept in a separate, protected log account', 'More regions', 'Shorter passwords'], 'Without protected logs you cannot investigate.'),
    single('cld-ir-02', C, 'foundation', 'Prepare for incidents', 'What is a pre-approved incident response role for?', ['Daily development', '*Giving responders tested read and quarantine access in every account during an incident', 'Billing', 'Running production'], 'Prepared access saves hours.'),
    single('cld-ir-03', C, 'intermediate', 'Detect compromise', 'Which combination most strongly suggests a stolen key?', ['Office IP, working hours, SSO', '*New IP, 02:00, long-lived key, listing calls and access-denied bursts', 'A pipeline deployment', 'A password change by the user'], 'Several anomalies together.'),
    single('cld-ir-04', C, 'intermediate', 'Contain credentials', 'Why revoke sessions as well as deactivating keys?', ['Sessions are free', '*Temporary credentials already issued stay valid until they expire or are revoked', 'It deletes the logs', 'It is not necessary'], 'Close every way in.'),
    single('cld-ir-05', C, 'intermediate', 'Find persistence', 'Which event is the clearest sign of persistence?', ['ListBuckets', '*CreateAccessKey for a newly created user', 'GetCallerIdentity', 'DescribeInstances'], 'A new credential survives revocation of the original.'),
    single('cld-ir-06', C, 'intermediate', 'Classify events', 'Attaching AdministratorAccess to a new user is an example of which tactic?', ['Discovery', '*Privilege escalation', 'Collection', 'Initial access'], 'It increases permissions.'),
    single('cld-ir-07', C, 'foundation', 'Recognise defence evasion', 'StopLogging on an audit trail maps to which ATT&CK behaviour?', ['Create Cloud Account', '*Disable or Modify Cloud Log', 'Valid Accounts', 'Data from Cloud Storage'], 'Now T1685.002 (formerly T1562.008).'),
    single('cld-ir-08', C, 'intermediate', 'Protect logging', 'An intruder\'s DeleteTrail on the organisation trail returns AccessDenied. What most likely denied it?', ['A network ACL', '*An organisation-level policy protecting logging', 'The user\'s password policy', 'The trail was already deleted'], 'Guardrails apply even to administrators in member accounts.'),
    single('cld-ir-09', C, 'intermediate', 'Establish data access', 'Which log shows which objects an identity read from storage?', ['The control-plane audit log', '*Data-access (object-level) logs', 'DNS logs', 'Billing reports'], 'Object-level events must be enabled.'),
    single('cld-ir-10', C, 'foundation', 'Support notification', 'Who decides whether a personal data breach must be reported to the NDPC?', ['The intruder', 'The investigator alone', '*The DPO and legal, based on the investigators\' facts', 'The cloud provider'], 'Investigators give accurate facts quickly.'),
    single('cld-ir-11', C, 'foundation', 'Preserve evidence', 'What should you do before terminating a suspicious VM?', ['Nothing', '*Isolate it and snapshot its disks', 'Reboot it', 'Delete its logs'], 'Termination destroys evidence.'),
    single('cld-ir-12', C, 'foundation', 'Recover', 'How should a compromised workload be recovered?', ['Patch it in place', '*Rebuild from known-good code and images and rotate secrets', 'Restore the compromised server\'s snapshot', 'Restart it'], 'Rebuild, don\'t repair.'),
    multiple('cld-ir-13', C, 'intermediate', 'Hunt for threats', 'Which are good threat-hunting hypotheses after this incident? Select all that apply.', ['*Other long-lived keys may have leaked', '*The intruder may have tried other accounts from the same addresses', '*Logging may have been changed elsewhere unnoticed', 'The office printer is out of paper'], 'Each can be tested with the logs.'),
]);

save(p);
console.log('cloud module 9 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions; rows', audit.length, '+', data.length);
