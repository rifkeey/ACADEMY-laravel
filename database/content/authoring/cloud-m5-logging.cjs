// Cloud Security, module 5: Cloud Logging, Monitoring and Detection.
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const v = 'vendor_documentation';
upsertSources([
    { key: 'aws-cloudtrail', title: 'What is AWS CloudTrail?', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/awscloudtrail/latest/userguide/cloudtrail-user-guide.html', topic: 'Cloud audit logging', sourceType: v, versionNote: 'CloudTrail User Guide, current.', supports: 'Management and data events, trails, organisation trails.' },
    { key: 'aws-cloudtrail-integrity', title: 'Validating CloudTrail log file integrity', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/awscloudtrail/latest/userguide/cloudtrail-log-file-validation-intro.html', topic: 'Log integrity', sourceType: v, versionNote: 'CloudTrail User Guide, current.', supports: 'Proving log files were not modified.' },
    { key: 'azure-activity-log', title: 'Activity log in Azure Monitor', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/azure-monitor/essentials/activity-log', topic: 'Cloud audit logging', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Subscription-level operations.' },
    { key: 'entra-sign-in-logs', title: 'Sign-in logs in Microsoft Entra ID', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/entra/identity/monitoring-health/concept-sign-ins', topic: 'Identity logging', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Interactive, non-interactive and workload sign-ins.' },
    { key: 'gcp-audit-logs', title: 'Cloud Audit Logs overview', publisher: 'Google Cloud', url: 'https://cloud.google.com/logging/docs/audit', topic: 'Cloud audit logging', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Admin Activity and Data Access audit logs.' },
    { key: 'aws-guardduty', title: 'What is Amazon GuardDuty?', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/guardduty/latest/ug/what-is-guardduty.html', topic: 'Managed threat detection', sourceType: v, versionNote: 'GuardDuty User Guide, current.', supports: 'Provider-managed detection findings.' },
    { key: 'azure-defender-for-cloud', title: 'Microsoft Defender for Cloud overview', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/defender-for-cloud/defender-for-cloud-introduction', topic: 'Posture and threat protection', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Provider-managed posture and threat detection.' },
    { key: 'gcp-scc', title: 'Security Command Center overview', publisher: 'Google Cloud', url: 'https://cloud.google.com/security-command-center/docs/security-command-center-overview', topic: 'Posture and threat detection', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Organisation-level findings.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'cloud-audit-logs', title: 'Cloud audit logs: management and data events', minutes: 30, sources: ['aws-cloudtrail', 'azure-activity-log', 'gcp-audit-logs', 'nist-sp-800-92'],
        objectives: ['Distinguish management (control-plane) from data (data-plane) events', 'Identify where each provider records them and what is on by default', 'Define a logging baseline by data classification'],
        checks: [
            { question: 'Which kind of event records someone reading an object in a storage bucket?', options: ['A management event', 'A data (data-plane) event', 'A billing event', 'A DNS event'], answerIndex: 1, explanation: 'Reading and writing data are data events, usually off by default.' },
            { question: 'Which fields should a useful audit record provide? Select all that apply.', options: ['The identity that made the call', 'The operation and the resource', 'The source IP address and time', 'The user\'s home address'], answerIndexes: [0, 1, 2], explanation: 'Who, what, when, from where and whether it worked.' },
            { question: 'Why use an organisation-wide trail or policy rather than configuring logging account by account?', options: ['It is required by every provider', 'New accounts are covered automatically and nobody can forget one', 'It disables data events', 'It is the only way to read logs'], answerIndex: 1, explanation: 'Organisation-level configuration removes coverage gaps as the estate grows.' },
        ] },
    { slug: 'identity-and-sign-in-events', title: 'Identity and sign-in events', minutes: 25, sources: ['entra-sign-in-logs', 'aws-cloudtrail', 'gcp-audit-logs'],
        objectives: ['Find sign-in and identity-change events in each provider', 'Recognise identity signals that need review', 'Make identity logs meaningful through individual, clearly named identities'],
        checks: [
            { question: 'Which identity events deserve review? Select all that apply.', options: ['Use of the root or Global Administrator account', 'A new access key created for an administrator', 'A change to a conditional-access policy', 'A user reading the staff handbook'], answerIndexes: [0, 1, 2], explanation: 'Top-level account use, new credentials and changes to protective policies are high-value signals.' },
            { question: 'Why do shared accounts weaken sign-in logs?', options: ['They create more logs', 'The log cannot show which person acted', 'They disable MFA automatically', 'They are faster'], answerIndex: 1, explanation: 'Accountability needs one identity per person.' },
            { question: 'What makes an unusual sign-in stand out?', options: ['A larger log file', 'A documented picture of what is normal for that identity', 'Turning off MFA', 'Sampling the logs'], answerIndex: 1, explanation: 'Detections compare activity with an expected pattern.' },
        ] },
    { slug: 'centralising-and-protecting-logs', title: 'Centralising and protecting logs', minutes: 25, sources: ['aws-cloudtrail-integrity', 'aws-multi-account', 'nist-sp-800-92'],
        objectives: ['Design a central log archive outside workload accounts', 'Set retention with legal and compliance', 'Protect log integrity with immutability, validation and access control'],
        checks: [
            { question: 'Why should logs be stored in a separate logging account?', options: ['It is cheaper', 'Administrators of the workload accounts cannot alter or delete them', 'It speeds up applications', 'Providers require it'], answerIndex: 1, explanation: 'Separation makes logs trustworthy evidence even if a workload account is compromised.' },
            { question: 'Which measures protect log integrity? Select all that apply.', options: ['Immutable (write-once) storage', 'Integrity validation of log files', 'Giving all developers delete rights on the archive', 'Alerts on changes to logging configuration'], answerIndexes: [0, 1, 3], explanation: 'Integrity needs immutability, validation and tight control of the archive.' },
            { question: 'Who should help decide log retention periods?', options: ['Only the storage team', 'Legal and compliance, with the security team', 'Nobody: keep the default', 'The cloud provider'], answerIndex: 1, explanation: 'Retention must meet investigation, legal and regulatory needs.' },
        ] },
    { slug: 'threat-detection-and-siem', title: 'Managed threat detection, SIEM and prioritisation', minutes: 30, sources: ['aws-guardduty', 'azure-defender-for-cloud', 'gcp-scc', 'ms-sentinel'],
        objectives: ['Explain the role of provider-managed detection services', 'Justify a SIEM for correlation across sources', 'Prioritise alerts with an explainable scheme', 'Use runbooks for consistent response'],
        checks: [
            { question: 'What does a SIEM add beyond each provider\'s own detection service?', options: ['Nothing', 'Correlation across cloud, identity, on-premises and endpoint sources, plus one alert queue and long-term search', 'Encryption', 'Faster virtual machines'], answerIndex: 1, explanation: 'Correlation and a single queue are the main reasons for a SIEM.' },
            { question: 'Which factors should raise an alert\'s priority? Select all that apply.', options: ['The resource holds restricted data', 'The identity involved is an administrator', 'The activity suggests impact, such as disabling logging', 'The alert arrived on a Tuesday'], answerIndexes: [0, 1, 2], explanation: 'Asset criticality, privilege, confidence, stage and exposure drive priority.' },
            { question: 'What is the purpose of an alert runbook?', options: ['To replace logging', 'To make response consistent: what to check, who owns it, how to contain, when to escalate', 'To hide alerts', 'To score staff'], answerIndex: 1, explanation: 'Runbooks give analysts a reliable first set of steps.' },
        ] },
    { slug: 'monitoring-gaps-and-detection-engineering', title: 'Monitoring gaps and detection engineering', minutes: 30, sources: ['mitre-attack-cloud', 'sigma'],
        objectives: ['Find gaps in sources, behaviours and past incidents', 'Treat detections as documented, versioned and tested code', 'Alert when logging itself breaks'],
        checks: [
            { question: 'A new account was created last month and sends no logs. What kind of gap is this?', options: ['A behaviour gap', 'A source (coverage) gap', 'A tuning issue', 'Not a gap'], answerIndex: 1, explanation: 'Missing sources are the most basic gap, often caused by new accounts.' },
            { question: 'Which practices belong to detection engineering? Select all that apply.', options: ['Documenting each detection\'s purpose and known benign cases', 'Testing detections against known-good and known-bad examples', 'Keeping detections under version control', 'Adding rules without ever reviewing them'], answerIndexes: [0, 1, 2], explanation: 'Detections are maintained like software.' },
            { question: 'Why alert when a log source stops sending?', options: ['To save money', 'Silent logging failures create blind spots nobody notices', 'Providers require it', 'It improves query speed'], answerIndex: 1, explanation: 'Monitoring the monitoring prevents unnoticed gaps.' },
        ] },
];

const R = (id, label) => ({ id, label });
const yn = [R('enabled', 'Enabled'), R('needed', 'Needed, missing'), R('optional', 'Optional')];

const lab = {
    slug: 'logging-coverage-assessment',
    title: 'Assess and design Kanem\'s cloud logging and monitoring',
    difficulty: 'intermediate',
    labType: 'architecture_review',
    summary: 'Review logging coverage across Kanem\'s cloud accounts, decide where logs should live and how they are protected, prioritise alerts, and write the logging and monitoring design.',
    minutes: 60,
    moduleSlug: 'logging-monitoring',
    passPercent: 80,
    content: {
        objectives: ['Assess logging coverage against data classification', 'Design central, protected log storage', 'Prioritise alerts with an explainable scheme', 'Document a logging and monitoring design'],
        scenario: 'Kanem Financial Services (fictional) has four cloud accounts. After the storage exposure in module 4, the head of risk wants to know whether security changes and sensitive data access would be recorded and noticed in every account, and what needs to change.',
        scope: 'Work from the documents. All accounts and data are fictional.',
        instructions: '1. Read **Accounts and current logging**.\n2. Complete the coverage grid in task 1.\n3. Answer tasks 2–5.\n4. Write the design in task 6. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. No cloud accounts are used.',
        workspace: { kind: 'documents', documents: [
            { title: 'Accounts and current logging', format: 'markdown', body: '| Account | Holds | Management events | Data access logging | Provider threat detection | Where logs go |\n| --- | --- | --- | --- | --- | --- |\n| invoice-prod | Invoices (confidential), customer details | Kept 90 days in the account | Off | On | Same account |\n| loans-prod | Loan documents (restricted) | Kept 90 days in the account | Off | Off | Same account |\n| invoice-dev | Synthetic test data only | Kept 90 days | Off | On | Same account |\n| marketing-sandbox (created last month) | Unknown | Not configured | Off | Off | Nowhere |\n\nThere is no separate logging account yet. Legal says audit records must be kept for at least 5 years.' },
        ] },
        tasks: [
            { id: 'coverage', points: 3, prompt: 'For each account, decide the status of each log source against Kanem\'s baseline (lesson 1): management events everywhere; data access for confidential and restricted data; provider threat detection everywhere.', hints: ['Development holds only synthetic data.', 'An account nobody knows the contents of should be treated as needing the baseline.'], input: { type: 'grid', rows: [R('invprod', 'invoice-prod'), R('loans', 'loans-prod'), R('invdev', 'invoice-dev'), R('mkt', 'marketing-sandbox')], columns: [
                { id: 'mgmt', label: 'Management events', options: yn }, { id: 'data', label: 'Data access logging', options: yn }, { id: 'threat', label: 'Threat detection', options: yn },
            ] } },
            { id: 'storage', points: 1, prompt: 'Where should the logs be kept?', hints: ['Lesson 3.'], input: { type: 'choice', options: [R('archive', 'In a new, separate logging account that workload administrators cannot alter, with immutable storage'), R('same', 'In each workload account, as today'), R('laptop', 'Exported weekly to the security lead\'s laptop')] } },
            { id: 'retention', points: 1, prompt: 'What retention should Kanem set for audit records?', hints: ['Legal has given a requirement.'], input: { type: 'choice', options: [R('5y', 'At least 5 years, using cheaper storage tiers for older logs'), R('90d', '90 days, the default'), R('1y', 'One year to save cost')] } },
            { id: 'priority', points: 2, prompt: 'Which two alerts should be handled **first**? Select two.', hints: ['Lesson 4: asset criticality, privilege, stage.'], input: { type: 'multi', options: [R('stoplog', 'Audit logging disabled in loans-prod'), R('dev', 'Failed sign-in for a developer in invoice-dev'), R('public', 'Public access enabled on a restricted bucket in loans-prod'), R('newvm', 'New virtual machine created in invoice-dev')] } },
            { id: 'meta', points: 1, prompt: 'Which alert protects against the monitoring itself failing?', hints: ['Lesson 5.'], input: { type: 'choice', options: [R('silence', 'Alert when a log source stops sending or logging configuration changes'), R('cost', 'Alert when storage costs rise'), R('none', 'None is needed')] } },
            { id: 'design', points: 2, prompt: 'Write the **logging and monitoring design** for the head of risk: sources per account, where logs go and how they are protected, retention, what is monitored and alerted, and how gaps will be found.', hints: ['Start with the gaps you found.', 'Name the first high-value alerts.'], input: { type: 'deliverable', title: 'Logging and monitoring design', competency: 'Designing cloud logging, retention and monitoring that would detect security changes', minWords: 140, rubric: ['Identifies the coverage gaps (loans-prod data access and detection; marketing-sandbox entirely)', 'Proposes a separate, protected log archive with immutability and limited access', 'Sets retention to meet the 5-year requirement cost-effectively', 'Names high-value alerts (logging disabled, public exposure, privilege changes, top-level account use) and alerts on monitoring failures', 'Explains how new accounts will be covered automatically'], template: 'Gaps found:\n\nSources per account:\n\nCentral archive and protection:\n\nRetention:\n\nFirst alerts:\n\nKeeping coverage complete:\n' } },
        ],
        reflection: ['Which gap would have mattered most during the module 4 exposure?', 'How would you know if marketing-sandbox held customer data?', 'What would you tell a team that wants to turn off data logging to save money?', 'Which alert would you test first, and how?'],
    },
    validator: {
        tasks: {
            coverage: { type: 'grid', minCorrectRatio: 0.8, cells: {
                invprod: { mgmt: ['enabled'], data: ['needed'], threat: ['enabled'] },
                loans: { mgmt: ['enabled'], data: ['needed'], threat: ['needed'] },
                invdev: { mgmt: ['enabled'], data: ['optional'], threat: ['enabled'] },
                mkt: { mgmt: ['needed'], data: ['needed', 'optional'], threat: ['needed'] },
            }, feedback: { correct: 'Your coverage assessment matches.', incorrect: 'Apply the baseline: management events and threat detection everywhere; data access where data is confidential or restricted.' } },
            storage: { type: 'choice', correct: 'archive', feedback: { correct: 'Right.', incorrect: 'Logs in the workload account can be altered by its administrators.' } },
            retention: { type: 'choice', correct: '5y', feedback: { correct: 'Right.', incorrect: 'Legal has set a 5-year minimum.' } },
            priority: { type: 'multi', correct: ['stoplog', 'public'], feedback: { correct: 'Right: both affect restricted production data and weaken controls.', incorrect: 'Prioritise restricted production data and actions that weaken controls.' } },
            meta: { type: 'choice', correct: 'silence', feedback: { correct: 'Right.', incorrect: 'Monitoring must detect its own failure.' } },
            design: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'a separate or central log archive', any: ['separate', 'central', 'archive', 'logging account'] },
                { label: 'retention', any: ['retention', '5 years', 'five years'] },
                { label: 'the marketing-sandbox gap', any: ['marketing'] },
                { label: 'alerts', any: ['alert'] },
            ], feedback: { correct: 'Design complete. Compare it with the rubric and the debrief.', incorrect: 'Your design is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Coverage:** management events are present in three accounts but kept only 90 days in the same account; marketing-sandbox has nothing. Data access logging is needed in invoice-prod and loans-prod (confidential and restricted data), optional in invoice-dev (synthetic data). Threat detection is missing in loans-prod and marketing-sandbox.\n\n**Design:** create a separate logging account with immutable storage, write-only access for sources and read access for security only; configure an organisation-wide trail and organisation policies so every new account is covered automatically and nobody can disable logging; keep audit records for at least 5 years, moving older logs to cheaper tiers; enable data-access logging for confidential and restricted stores; enable provider threat detection everywhere from a central security account.\n\n**First alerts:** logging disabled anywhere; public access on confidential or restricted storage; privilege changes; top-level account use; and a log source going silent.\n\n## What you have practised\n\nAssessing logging coverage against data sensitivity and designing protected, central logging with retention and alerting: the foundation every investigation in module 9 depends on.\n\n**Next:** module 6 secures the workloads themselves.',
    },
};

replaceModule(p.data, 'logging-monitoring', {
    slug: 'logging-monitoring',
    title: 'Cloud Logging, Monitoring and Detection',
    purpose: 'After the storage exposure, Kanem\'s head of risk asks whether security changes would be recorded and noticed in every cloud account. This module covers cloud audit logs, identity and sign-in events, central and protected log storage, managed threat detection and SIEM, and finding and closing monitoring gaps. You finish by assessing Kanem\'s logging coverage and writing its design.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written design.',
    prerequisiteKnowledge: 'Modules 1, 2 and 4.',
    objectives: ['Identify management, data and identity events in each provider', 'Design central, protected log storage with appropriate retention', 'Use managed detection services and a SIEM to correlate and prioritise', 'Find and close monitoring gaps and keep detections working'],
    keyConcepts: ['Management and data events', 'Sign-in and identity-change events', 'Log archive account', 'Retention and integrity', 'Managed threat detection', 'SIEM and correlation', 'Alert prioritisation and runbooks', 'Detection engineering'],
    assessmentCriteria: ['Assesses logging coverage correctly', 'Designs protected central logging with retention', 'Prioritises alerts defensibly', 'Writes a complete logging and monitoring design'],
    instructorNotes: 'Estimated time: 2.25 hours of lessons plus 1 hour of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'logging-coverage-assessment', lab);

const C = 'logging';
replaceQuestions(p.data, 'cld-log-', [
    single('cld-log-01', C, 'foundation', 'Distinguish event types', 'Which is a management (control-plane) event?', ['Reading an object', '*Changing a storage bucket\'s access policy', 'Querying a database table', 'Using a key to decrypt data'], 'Creating, changing and deleting resources and settings are management events.'),
    single('cld-log-02', C, 'foundation', 'Identify default logging', 'Which kind of cloud logging is usually off by default and must be enabled for sensitive resources?', ['Management events', '*Data-access events', 'Billing records', 'Status pages'], 'Data events are high volume and usually need enabling per resource.'),
    single('cld-log-03', C, 'foundation', 'Find identity logs', 'Where are interactive and service-principal sign-ins recorded for Microsoft cloud services?', ['The Azure activity log only', '*Microsoft Entra ID sign-in logs', 'VPC flow logs', 'Storage logs'], 'Entra ID records sign-ins; the activity log records subscription operations.'),
    multiple('cld-log-04', C, 'intermediate', 'Recognise identity signals', 'Which identity events are high-value signals? Select all that apply.', ['*New credentials created for an identity', '*A new privileged role assignment', 'A user changing their profile photo', '*Use of the top-level administrator account'], 'Credential creation, privilege changes and top-level account use deserve review.'),
    single('cld-log-05', C, 'intermediate', 'Protect logs', 'What is the main reason for a separate log archive account?', ['Lower cost', '*Workload administrators, or an attacker controlling a workload account, cannot alter the logs', 'Faster search', 'Provider requirement'], 'Separation preserves logs as trustworthy evidence.'),
    single('cld-log-06', C, 'intermediate', 'Protect logs', 'Which feature proves CloudTrail log files have not been modified after delivery?', ['Versioning', '*Log file integrity validation', 'Lifecycle rules', 'Tags'], 'Integrity validation detects modification or deletion of delivered log files.'),
    single('cld-log-07', C, 'foundation', 'Set retention', 'How should audit-log retention be decided?', ['Keep the default', '*With legal and compliance, based on investigation and regulatory needs', 'Whatever is cheapest', 'Let each developer decide'], 'Retention must meet legal and investigative needs; tiers control cost.'),
    single('cld-log-08', C, 'intermediate', 'Explain SIEM value', 'What is the main benefit of a SIEM in a multi-cloud environment?', ['It replaces audit logs', '*Correlating events from many sources in one place and one alert queue', 'It makes logging free', 'It patches servers'], 'Correlation across sources is the core value.'),
    single('cld-log-09', C, 'intermediate', 'Prioritise alerts', 'Which alert should normally be handled first?', ['A failed sign-in to a development account', '*Audit logging disabled in a production account holding restricted data', 'A new test virtual machine', 'A tag changed on a dev resource'], 'Weakened controls on restricted production data come first.'),
    single('cld-log-10', C, 'foundation', 'Use managed detection', 'Which is a provider-managed threat detection service?', ['*Amazon GuardDuty', 'A spreadsheet', 'A VPN', 'A load balancer'], 'GuardDuty, Defender for Cloud and Security Command Center provide managed findings.'),
    single('cld-log-11', C, 'intermediate', 'Close coverage gaps', 'How do you make sure new accounts are logged from day one?', ['Ask teams to remember', '*Organisation-level trails and policies that apply to every account automatically', 'Check once a year', 'Disable new accounts'], 'Organisation-wide configuration removes manual steps.'),
    single('cld-log-12', C, 'intermediate', 'Engineer detections', 'What should happen before a new detection rule is deployed?', ['Nothing', '*It is documented and tested against known-good and known-bad examples', 'All other rules are disabled', 'Logging is reduced'], 'Testing confirms it fires when it should and stays quiet when it should.'),
    single('cld-log-13', C, 'foundation', 'Monitor the monitoring', 'Why alert when a log source goes silent?', ['To reduce cost', '*A silent failure creates a blind spot nobody would otherwise notice', 'It speeds up queries', 'It is required by every provider'], 'Monitoring must detect its own failures.'),
]);

save(p);
console.log('cloud module 5 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
