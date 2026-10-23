// Cloud Security, module 7: Cloud Security Posture and Configuration Management.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const v = 'vendor_documentation';
upsertSources([
    { key: 'nist-sp-800-128', title: 'SP 800-128: Guide for Security-Focused Configuration Management of Information Systems', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/128/upd1/final', topic: 'Configuration management', sourceType: 'official_standard', versionNote: 'Update 1 (2019).', supports: 'Baselines, configuration change control and monitoring.' },
    { key: 'aws-security-hub', title: 'Introduction to AWS Security Hub', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/securityhub/latest/userguide/what-is-securityhub.html', topic: 'Posture management', sourceType: v, versionNote: 'Security Hub User Guide, current.', supports: 'CSPM findings mapped to standards.' },
    { key: 'aws-config', title: 'What is AWS Config?', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/config/latest/developerguide/WhatIsConfig.html', topic: 'Configuration history and rules', sourceType: v, versionNote: 'AWS Config Developer Guide, current.', supports: 'Configuration recording, rules and history.' },
    { key: 'azure-policy', title: 'Overview of Azure Policy', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/governance/policy/overview', topic: 'Policy as code', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Deny, audit and remediation effects.' },
    { key: 'gcp-org-policy', title: 'Organization Policy overview', publisher: 'Google Cloud', url: 'https://cloud.google.com/resource-manager/docs/organization-policy/overview', topic: 'Guardrails', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Organisation policy constraints.' },
    { key: 'opa-docs', title: 'Open Policy Agent documentation', publisher: 'Open Policy Agent (CNCF)', url: 'https://www.openpolicyagent.org/docs/latest/', topic: 'Policy as code', sourceType: 'open_resource', versionNote: 'Latest documentation.', supports: 'General-purpose policy engine (example).' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'misconfiguration-and-cspm', title: 'Misconfiguration and posture management', minutes: 25, sources: ['aws-security-hub', 'azure-defender-for-cloud', 'gcp-scc', 'cis-benchmarks'],
        objectives: ['Recognise the main categories of cloud misconfiguration', 'Explain what CSPM tools do and what a finding contains', 'Plan the response to a first, overwhelming report'],
        checks: [
            { question: 'Which field in a finding most helps get it fixed?', options: ['The finding ID', 'The owner of the resource', 'The scan time', 'The tool version'], answerIndex: 1, explanation: 'Without an owner, findings sit unassigned; tagging supplies owners.' },
            { question: 'What should you do first with a report of 1,240 findings?', options: ['Fix them in order of the report', 'Confirm coverage, then sort by real risk', 'Ignore it', 'Turn the tool off'], answerIndex: 1, explanation: 'Coverage first, then risk-based prioritisation, then prevention.' },
            { question: 'Why must configuration be checked continuously in the cloud?', options: ['Providers require it', 'Resources and settings change constantly through self-service and automation', 'Audits happen daily', 'It is cheaper'], answerIndex: 1, explanation: 'Annual checks miss changes made every day.' },
        ] },
    { slug: 'baselines-and-policy-as-code', title: 'Baselines, guardrails and policy as code', minutes: 30, sources: ['nist-sp-800-128', 'azure-policy', 'gcp-org-policy', 'aws-scps', 'opa-docs'],
        objectives: ['Write a configuration baseline from recognised benchmarks', 'Choose preventive, detective or corrective controls', 'Manage policies as reviewed and tested code with explicit exceptions'],
        checks: [
            { question: 'Which settings deserve preventive (blocking) controls? Select all that apply.', options: ['Audit logging must not be disabled', 'Restricted data must not be public', 'Every resource must have a description field', 'Only approved regions may be used'], answerIndexes: [0, 1, 3], explanation: 'Prevent the few settings that must never be wrong; detect the rest.' },
            { question: 'Why should automatic remediation be used carefully?', options: ['It is slow', 'An automatic fix can break a service if applied blindly', 'It is not supported', 'It disables logging'], answerIndex: 1, explanation: 'Automate fixes only where they cannot cause outages.' },
            { question: 'What makes an exception well managed?', options: ['It covers the whole account forever', 'A business reason, approval, narrow scope and an expiry date', 'Nobody knows about it', 'It is granted verbally'], answerIndex: 1, explanation: 'Scoped, time-limited, approved exceptions keep controls credible.' },
        ] },
    { slug: 'infrastructure-as-code-security', title: 'Infrastructure as code security', minutes: 25, sources: ['nist-sp-800-128'],
        objectives: ['Explain how IaC improves security', 'Place IaC scanning in the editor, pull request and pipeline', 'Apply secure IaC practices and recognise IaC\'s limits'],
        checks: [
            { question: 'Where should IaC security scanning run? Select all that apply.', options: ['In the developer\'s editor or pre-commit', 'In pull requests as a required check', 'In the deployment pipeline', 'Only once a year'], answerIndexes: [0, 1, 2], explanation: 'Earlier checks give faster feedback; the pipeline check is the gate.' },
            { question: 'Why must IaC state files be protected?', options: ['They are large', 'They can contain sensitive values', 'They are public by design', 'They replace backups'], answerIndex: 1, explanation: 'Store state encrypted with restricted access.' },
            { question: 'What can IaC scanning NOT see?', options: ['Settings in the templates', 'Resources created by hand in the console', 'Module parameters', 'Pull request changes'], answerIndex: 1, explanation: 'Runtime posture checks cover what the code does not.' },
        ] },
    { slug: 'drift-and-continuous-validation', title: 'Drift and continuous control validation', minutes: 25, sources: ['aws-config', 'nist-sp-800-128'],
        objectives: ['Identify sources of configuration drift', 'Detect drift with IaC, configuration history, CSPM and audit logs', 'Validate that controls actually work'],
        checks: [
            { question: 'Which signal strongly indicates drift in production?', options: ['A change made by the deployment pipeline', 'A change made by an identity other than the pipeline', 'A successful backup', 'A new log entry'], answerIndex: 1, explanation: 'Production should change through the pipeline; anything else needs review.' },
            { question: 'An emergency fix was made in the console. What should happen next?', options: ['Nothing', 'Decide the source of truth: update the code or revert, and record why', 'Delete the resource', 'Disable logging'], answerIndex: 1, explanation: 'Code and reality must be reconciled, with a record.' },
            { question: 'How do you show a preventive guardrail works?', options: ['Read the policy', 'Test it: attempt the blocked action in a test account and confirm it is denied', 'Ask the vendor', 'Assume it does'], answerIndex: 1, explanation: 'Validation is evidence, not assumption.' },
        ] },
    { slug: 'prioritising-remediation', title: 'Prioritising remediation and mapping to compliance', minutes: 30, sources: ['csa-ccm', 'cis-benchmarks'],
        objectives: ['Prioritise findings by risk factors', 'Build a remediation plan grouped by root cause', 'Map findings to frameworks without confusing compliance with security'],
        checks: [
            { question: 'Which finding should normally be fixed first?', options: ['A missing tag on a development VM', 'Public access on a production bucket holding restricted data', 'An old image in a sandbox', 'A low-severity naming issue'], answerIndex: 1, explanation: 'Exposure plus sensitive production data is critical.' },
            { question: '300 findings come from one insecure IaC module. What is the best remediation?', options: ['Fix each resource by hand', 'Fix the module once and redeploy', 'Accept all 300', 'Delete the module'], answerIndex: 1, explanation: 'Group by root cause.' },
            { question: 'Why is a 95% compliance score not enough on its own?', options: ['Scores are always wrong', 'It can hide one critical exposure; critical items must be reported explicitly', 'Frameworks are optional', '95% is too low'], answerIndex: 1, explanation: 'Compliance sets minimums; risk lives in the exceptions.' },
        ] },
];

const R = (id, label) => ({ id, label });
const pri = [R('critical', 'Critical'), R('high', 'High'), R('medium', 'Medium'), R('low', 'Low')];

const lab = {
    slug: 'posture-findings-prioritisation',
    title: 'Turn 1,240 findings into a remediation plan',
    difficulty: 'intermediate',
    labType: 'risk_assessment',
    summary: 'Prioritise a sample of posture findings by risk, group them by root cause, choose preventive controls, and write the remediation roadmap for the CISO.',
    minutes: 60,
    moduleSlug: 'posture',
    passPercent: 80,
    content: {
        objectives: ['Prioritise posture findings using exposure, sensitivity, environment, privilege and control weakening', 'Group findings by root cause', 'Choose preventive and detective controls', 'Write a remediation roadmap with measures'],
        scenario: 'Kanem Financial Services (fictional) has its first full posture report. You have a representative sample of findings and the summary counts. The CISO wants a roadmap she can take to the board.',
        scope: 'Use Kanem\'s classification (module 4) and baseline (lesson 2). All findings are fictional.',
        instructions: '1. Read **Sample findings** and **Summary**.\n2. Prioritise the findings in task 1.\n3. Answer tasks 2–5.\n4. Write the roadmap in task 6. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. No cloud accounts are used.',
        workspace: { kind: 'documents', documents: [
            { title: 'Sample findings', format: 'markdown', body: '| ID | Account | Finding | Data | Internet-exposed? |\n| --- | --- | --- | --- | --- |\n| F1 | loans-prod | Storage public access not blocked; bucket readable by anyone | Restricted | Yes |\n| F2 | invoice-prod | Organisation audit trail stopped | n/a (control) | n/a |\n| F3 | invoice-prod | Database encrypted with provider-managed key; baseline requires customer-managed key | Confidential | No |\n| F4 | invoice-dev | Container namespace allows privileged pods | Synthetic | No |\n| F5 | marketing-sandbox | Resource missing `owner` tag | Unknown | No |\n| F6 | loans-prod | Administrator user without MFA | n/a (identity) | n/a |\n| F7 | shared-tools | Old base image with high-severity vulnerability, not deployed | n/a | No |\n| F8 | invoice-prod | VM disk snapshot shared with another account outside Kanem | Confidential | Yes (external share) |' },
            { title: 'Summary', format: 'markdown', body: '- 1,240 findings across 4 accounts.\n- **612** come from storage buckets created with an old internal IaC module `kanem-bucket v1` (public access block not set, no encryption setting).\n- 210 are missing tags.\n- Coverage: marketing-sandbox was added to the scan only yesterday.' },
        ] },
        tasks: [
            { id: 'priority', points: 3, prompt: 'Assign a priority to each finding.', hints: ['Critical: exposure or control weakening on production with sensitive data or privileged identities.', 'Development with synthetic data rarely comes first.'], input: { type: 'grid', rows: [R('f1', 'F1'), R('f2', 'F2'), R('f3', 'F3'), R('f4', 'F4'), R('f5', 'F5'), R('f6', 'F6'), R('f7', 'F7'), R('f8', 'F8')], columns: [{ id: 'p', label: 'Priority', options: pri }] } },
            { id: 'rootcause', points: 1, prompt: 'What is the most efficient fix for the 612 bucket findings?', hints: ['Lesson 5: group by root cause.'], input: { type: 'choice', options: [R('module', 'Fix the kanem-bucket module (public access blocked, encryption set), redeploy affected buckets, and retire v1'), R('manual', 'Fix all 612 buckets by hand in the console'), R('accept', 'Accept them as legacy risk')] } },
            { id: 'prevent', points: 2, prompt: 'Which controls would stop F1 and F2 recurring? Select all that apply.', hints: ['Lesson 2: preventive guardrails above accounts.'], input: { type: 'multi', options: [R('pab', 'Organisation policy enforcing storage public-access blocking'), R('trail', 'Organisation policy preventing audit trails from being stopped'), R('email', 'An email reminding teams'), R('iac', 'IaC scanning in pull requests and the pipeline')] } },
            { id: 'drift', points: 1, prompt: 'F2 was caused by a console change. Which alert would catch this kind of drift immediately?', hints: ['Lesson 4.'], input: { type: 'choice', options: [R('nonpipeline', 'Alert on production changes made by any identity other than the deployment pipeline, and on logging configuration changes'), R('cost', 'Alert on cost'), R('weekly', 'A weekly report')] } },
            { id: 'score', points: 1, prompt: 'The board asks for "our compliance score". What should the report also show?', hints: ['Lesson 5.'], input: { type: 'choice', options: [R('critical', 'Critical and high findings explicitly, time to remediate and recurrence, not just a percentage'), R('nothing', 'Nothing else'), R('all', 'All 1,240 findings in full')] } },
            { id: 'roadmap', points: 2, prompt: 'Write the **remediation roadmap** for the CISO: now / next / prevent / accept, owners, and the measures you will report.', hints: ['Lead with the critical items and who owns them.'], input: { type: 'deliverable', title: 'Posture remediation roadmap', competency: 'Prioritising cloud posture findings and planning remediation and prevention', minWords: 140, rubric: ['Lists critical items first with owners and timescales (F1, F2, F6, F8)', 'Groups fixes by root cause (the bucket module)', 'Adds preventive guardrails and IaC checks so findings do not recur', 'States accepted risks with owners and review dates', 'Defines measures: critical/high counts, time to remediate, recurrence, coverage'], template: 'Now (this week):\n\nNext (this quarter):\n\nPrevent recurrence:\n\nAccepted risks:\n\nMeasures for the board:\n' } },
        ],
        reflection: ['Which single change would remove the most findings?', 'Which critical finding would you tell the CISO about by phone, today?', 'How would you stop new accounts from arriving with no tags?', 'What does a "good" posture report look like to a board member?'],
    },
    validator: {
        tasks: {
            priority: { type: 'grid', minCorrectRatio: 0.75, cells: {
                f1: { p: ['critical'] }, f2: { p: ['critical'] }, f3: { p: ['medium', 'high'] }, f4: { p: ['low', 'medium'] },
                f5: { p: ['low', 'medium'] }, f6: { p: ['critical', 'high'] }, f7: { p: ['low', 'medium'] }, f8: { p: ['critical', 'high'] },
            }, feedback: { correct: 'Your priorities are defensible.', incorrect: 'Use the factors: exposure, sensitive production data, privileged identity and weakened controls come first.' } },
            rootcause: { type: 'choice', correct: 'module', feedback: { correct: 'Right.', incorrect: 'One root cause, one fix.' } },
            prevent: { type: 'multi', correct: ['pab', 'trail', 'iac'], feedback: { correct: 'Right.', incorrect: 'Reminders do not prevent anything.' } },
            drift: { type: 'choice', correct: 'nonpipeline', feedback: { correct: 'Right.', incorrect: 'Look for changes outside the pipeline as they happen.' } },
            score: { type: 'choice', correct: 'critical', feedback: { correct: 'Right.', incorrect: 'A percentage can hide a critical exposure.' } },
            roadmap: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'F1 or the public bucket', any: ['f1', 'public'] },
                { label: 'the bucket module', any: ['module', 'kanem-bucket'] },
                { label: 'preventive guardrails', any: ['guardrail', 'organisation policy', 'organization policy', 'scp', 'prevent'] },
                { label: 'measures', any: ['measure', 'metric', 'time to remediate', 'recurrence'] },
            ], feedback: { correct: 'Roadmap complete. Compare it with the rubric and the debrief.', incorrect: 'Your roadmap is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Critical:** F1 (restricted data publicly readable), F2 (audit trail stopped in production), F6 (administrator without MFA) and F8 (confidential snapshot shared outside Kanem). **Medium/high:** F3 (baseline gap without exposure). **Low/medium:** F4, F5, F7 (development, tagging, undeployed image).\n\n**Root cause:** fix the `kanem-bucket` module, redeploy the affected buckets and retire v1: one task removes about 612 findings.\n\n**Prevention:** organisation policies enforcing storage public-access blocking and preventing audit trails from being stopped; IaC scanning in pull requests and the pipeline; alerts on production changes by identities other than the pipeline.\n\n**Reporting:** critical and high counts over time, time to remediate by severity, recurrence and coverage, with critical items named, alongside any compliance percentage.\n\n## What you have practised\n\nTurning raw posture output into a risk-based, root-cause-grouped plan with prevention and measures is a core cloud security responsibility, and exactly what boards and regulators expect to see.\n\n**Next:** module 8 secures the pipelines that build all of this.',
    },
};

replaceModule(p.data, 'posture', {
    slug: 'posture',
    title: 'Cloud Security Posture and Configuration Management',
    purpose: 'Kanem\'s first posture report lists 1,240 findings, and the CISO wants to know which matter and how to stop new ones. This module covers misconfiguration and CSPM, baselines and policy as code, infrastructure-as-code security, drift and continuous validation, and risk-based remediation with compliance mapping. You finish by turning a findings report into a roadmap.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written roadmap.',
    prerequisiteKnowledge: 'Modules 1–6.',
    objectives: ['Explain CSPM and the main misconfiguration categories', 'Define baselines and enforce them with preventive, detective and corrective controls', 'Secure infrastructure as code and its pipeline', 'Detect and correct drift and validate controls', 'Prioritise and plan remediation, mapping to frameworks'],
    keyConcepts: ['CSPM', 'Configuration baselines', 'Preventive, detective and corrective controls', 'Policy as code', 'Exceptions', 'IaC scanning and secure modules', 'Drift detection', 'Risk-based prioritisation', 'Compliance mapping'],
    assessmentCriteria: ['Prioritises findings defensibly', 'Groups remediation by root cause', 'Chooses effective preventive controls', 'Writes a complete roadmap with measures'],
    instructorNotes: 'Estimated time: 2.25 hours of lessons plus 1 hour of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'posture-findings-prioritisation', lab);

const C = 'posture';
replaceQuestions(p.data, 'cld-pos-', [
    single('cld-pos-01', C, 'foundation', 'Explain CSPM', 'What does a cloud security posture management (CSPM) tool do?', ['Encrypts data', '*Inventories resources and compares their configuration with security rules', 'Replaces firewalls', 'Writes application code'], 'CSPM finds configuration that deviates from rules and benchmarks.'),
    single('cld-pos-02', C, 'foundation', 'Define baselines', 'What is a configuration baseline?', ['A backup', '*A documented set of required settings for a type of resource', 'A firewall', 'A cost report'], 'Baselines define what secure looks like.'),
    single('cld-pos-03', C, 'intermediate', 'Choose control types', 'Which is a preventive control?', ['A weekly findings report', '*An organisation policy that denies creating public storage', 'An audit log', 'A dashboard'], 'Preventive controls stop non-compliant changes.'),
    single('cld-pos-04', C, 'intermediate', 'Use policy as code', 'Which is a benefit of policy as code?', ['No review needed', '*Policies are version-controlled, reviewed and testable', 'It removes exceptions', 'It replaces logging'], 'Treating policy as software brings review, history and testing.'),
    single('cld-pos-05', C, 'intermediate', 'Manage exceptions', 'Which exception is well managed?', ['Whole account exempt indefinitely', '*One named bucket exempt for 90 days with an approved business reason', 'Verbal approval', 'An exception nobody recorded'], 'Scoped, time-limited, approved and recorded.'),
    single('cld-pos-06', C, 'foundation', 'Secure IaC', 'Where is the earliest place IaC security problems can be caught?', ['In production', '*In the developer\'s editor or pre-commit checks', 'In the annual audit', 'After an incident'], 'Earlier feedback is cheaper and faster.'),
    multiple('cld-pos-07', C, 'intermediate', 'Secure IaC', 'Which are secure IaC practices? Select all that apply.', ['*Reusable modules with secure defaults', '*Encrypted, access-controlled state files', 'Secrets written into templates', '*Pipeline-only changes to production'], 'Keep secrets out of templates and state.'),
    single('cld-pos-08', C, 'intermediate', 'Detect drift', 'What is configuration drift?', ['A new region', '*A difference between the intended configuration and what is actually deployed', 'A cost overrun', 'A log format'], 'Drift accumulates through manual and untracked changes.'),
    single('cld-pos-09', C, 'intermediate', 'Detect drift', 'Which alert catches console changes in production quickly?', ['Monthly cost alerts', '*Changes made by identities other than the deployment pipeline', 'Backup success alerts', 'CPU alerts'], 'Production should change only through the pipeline.'),
    single('cld-pos-10', C, 'intermediate', 'Validate controls', 'How do you prove a guardrail works?', ['Read the documentation', '*Test it in a test account and confirm the blocked action is denied', 'Ask a colleague', 'Assume it works'], 'Validation produces evidence.'),
    single('cld-pos-11', C, 'intermediate', 'Prioritise findings', 'Which factor most raises a finding\'s priority?', ['The finding is old', '*Restricted production data is exposed to the internet', 'The resource name is long', 'The account is new'], 'Exposure of sensitive production data comes first.'),
    single('cld-pos-12', C, 'intermediate', 'Plan remediation', 'Why group findings by root cause?', ['To hide them', '*One fix to a shared cause (such as an IaC module) can resolve many findings', 'Tools require it', 'It increases the count'], 'Fix causes, not symptoms.'),
    single('cld-pos-13', C, 'foundation', 'Report posture', 'Which measure shows that prevention is missing?', ['Total number of resources', '*Recurrence: findings that return after being fixed', 'Number of tools', 'Report length'], 'Recurring findings mean the cause was not prevented.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/cloud-security/lessons/posture/posture-management.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('cloud module 7 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
