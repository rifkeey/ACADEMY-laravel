// Cloud Security, module 2 (pilot): Cloud Identity and Access Management.
const fs = require('fs');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation', r = 'open_resource';
upsertSources([
    { key: 'nist-sp-800-210', title: 'SP 800-210: General Access Control Guidance for Cloud Systems', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/210/final', topic: 'Cloud access control models', sourceType: o, versionNote: 'Final, July 2020.', supports: 'RBAC/ABAC concepts and access control considerations for IaaS, PaaS and SaaS.' },
    { key: 'nist-sp-800-144', title: 'SP 800-144: Guidelines on Security and Privacy in Public Cloud Computing', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/144/final', topic: 'Public cloud security and privacy', sourceType: o, versionNote: 'Final, December 2011. Older guidance: principles still apply, service details have changed.', supports: 'Shared responsibility and governance context.' },
    { key: 'csa-ccm', title: 'Cloud Controls Matrix (CCM)', publisher: 'Cloud Security Alliance', url: 'https://cloudsecurityalliance.org/research/cloud-controls-matrix', topic: 'Cloud control framework', sourceType: o, versionNote: 'CCM v4 family; check the current release on the page. Identity and Access Management (IAM) domain referenced.', supports: 'Mapping IAM controls to a cloud control framework.' },
    { key: 'aws-policy-evaluation', title: 'Policy evaluation logic', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/IAM/latest/UserGuide/reference_policies_evaluation-logic.html', topic: 'How AWS decides requests', sourceType: v, versionNote: 'IAM User Guide, current.', supports: 'Explicit deny, allow and implicit deny; interaction with other policy types.' },
    { key: 'aws-policy-elements', title: 'IAM JSON policy elements reference', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/IAM/latest/UserGuide/reference_policies_elements.html', topic: 'Policy syntax', sourceType: v, versionNote: 'IAM User Guide, current.', supports: 'Effect, Action, Resource, Condition and Version elements.' },
    { key: 'aws-temp-credentials', title: 'Temporary security credentials in IAM', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_temp.html', topic: 'Short-lived credentials', sourceType: v, versionNote: 'IAM User Guide, current.', supports: 'Roles and STS instead of long-lived keys.' },
    { key: 'aws-passrole', title: 'Grant a user permissions to pass a role to an AWS service', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_passrole.html', topic: 'iam:PassRole', sourceType: v, versionNote: 'IAM User Guide, current.', supports: 'Why PassRole must be scoped to specific roles.' },
    { key: 'aws-access-analyzer', title: 'Using AWS IAM Access Analyzer', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/IAM/latest/UserGuide/what-is-access-analyzer.html', topic: 'Finding external and unused access', sourceType: v, versionNote: 'IAM User Guide, current.', supports: 'Evidence-based least privilege.' },
    { key: 'aws-scps', title: 'Service control policies (SCPs)', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/organizations/latest/userguide/orgs_manage_policies_scps.html', topic: 'Organisation guardrails', sourceType: v, versionNote: 'AWS Organizations User Guide, current.', supports: 'Guardrails above account administrators.' },
    { key: 'aws-imds', title: 'Use the Instance Metadata Service to access instance metadata', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/configuring-instance-metadata-service.html', topic: 'Instance credentials', sourceType: v, versionNote: 'EC2 User Guide, current. IMDSv2 session tokens.', supports: 'Hardening how workloads obtain credentials.' },
    { key: 'azure-rbac', title: 'What is Azure role-based access control (Azure RBAC)?', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/role-based-access-control/overview', topic: 'Azure authorisation model', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Role assignments, scopes and inheritance.' },
    { key: 'azure-deny-assignments', title: 'List Azure deny assignments', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/azure/role-based-access-control/deny-assignments', topic: 'Azure deny assignments', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'Deny assignments and how they are created.' },
    { key: 'azure-managed-identities', title: 'What are managed identities for Azure resources?', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/entra/identity/managed-identities-azure-resources/overview', topic: 'Workload identity in Azure', sourceType: v, versionNote: 'Microsoft Learn, current.', supports: 'System- and user-assigned managed identities.' },
    { key: 'azure-pim', title: 'What is Microsoft Entra Privileged Identity Management?', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/entra/id-governance/privileged-identity-management/pim-configure', topic: 'Just-in-time privileged access', sourceType: v, versionNote: 'Microsoft Learn, current. Licensed feature.', supports: 'JIT elevation and approval.' },
    { key: 'gcp-iam', title: 'IAM overview', publisher: 'Google Cloud', url: 'https://cloud.google.com/iam/docs/overview', topic: 'Google Cloud authorisation model', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Allow policies, roles and the resource hierarchy.' },
    { key: 'gcp-deny', title: 'Deny policies', publisher: 'Google Cloud', url: 'https://cloud.google.com/iam/docs/deny-overview', topic: 'Google Cloud deny policies', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Deny evaluated before allow.' },
    { key: 'gcp-sa-best-practices', title: 'Best practices for using service accounts securely', publisher: 'Google Cloud', url: 'https://cloud.google.com/iam/docs/best-practices-service-accounts', topic: 'Workload identity in Google Cloud', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Avoiding service-account keys; impersonation risks.' },
    { key: 'gcp-wif', title: 'Workload Identity Federation', publisher: 'Google Cloud', url: 'https://cloud.google.com/iam/docs/workload-identity-federation', topic: 'Keyless access for external workloads', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Federation for pipelines and external workloads.' },
    { key: 'gcp-role-recommendations', title: 'Overview of role recommendations', publisher: 'Google Cloud', url: 'https://cloud.google.com/policy-intelligence/docs/role-recommendations-overview', topic: 'Removing unused permissions', sourceType: v, versionNote: 'Google Cloud documentation, current.', supports: 'Usage-based least privilege.' },
    { key: 'attack-t1078-004', title: 'ATT&CK T1078.004: Valid Accounts: Cloud Accounts', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1078/004/', topic: 'Abuse of cloud identities', sourceType: r, versionNote: 'Enterprise ATT&CK.', supports: 'Why identity is the main cloud attack surface.' },
    { key: 'attack-t1098', title: 'ATT&CK T1098: Account Manipulation', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1098/', topic: 'Persistence and escalation', sourceType: r, versionNote: 'Enterprise ATT&CK.', supports: 'Privilege changes as attacker behaviour.' },
    { key: 'attack-t1548-005', title: 'ATT&CK T1548.005: Temporary Elevated Cloud Access', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1548/005/', topic: 'Cloud privilege escalation', sourceType: r, versionNote: 'Enterprise ATT&CK.', supports: 'Abuse of role passing and impersonation.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    {
        slug: 'identities-authn-authz', title: 'Identities, authentication and authorisation in the cloud', minutes: 30,
        objectives: ['Classify human, group and workload principals across AWS, Azure and Google Cloud', 'Distinguish authentication from authorisation in a cloud API request', 'Recommend controls for human access (federation, MFA, groups, protected root accounts)'],
        sources: ['aws-iam-best-practices', 'nist-sp-800-210', 'attack-t1078-004'],
        checks: [
            { question: 'A user signs in with a password and MFA, then deletes a production database they should never have been able to touch. Which control failed?', options: ['Authentication', 'Authorisation', 'Encryption', 'Logging'], answerIndex: 1, explanation: 'The user proved who they were; the problem is that policy allowed the action. MFA does not reduce what an authenticated identity is allowed to do.' },
            { question: 'Which practices improve human access to a cloud environment? Select all that apply.', options: ['Single sign-on from the corporate identity provider', 'Granting permissions to groups rather than individuals', 'Sharing one administrator account among the IT team', 'Using the root or Global Administrator account only for tasks that require it'], answerIndexes: [0, 1, 3], explanation: 'Federation, groups and a protected top-level account are standard guidance. Shared accounts destroy accountability and are hard to protect.' },
            { question: 'In AWS, an IAM role is best described as:', options: ['A user with a password', 'A set of permissions that a person or service assumes temporarily', 'A group of users', 'An encryption key'], answerIndex: 1, explanation: 'Roles have no long-term password; principals assume them and receive temporary credentials.' },
        ],
    },
    {
        slug: 'how-policies-are-evaluated', title: 'How cloud access policies are evaluated', minutes: 35,
        objectives: ['Read an IAM policy statement as Effect, Action, Resource and Condition', 'Predict AWS decisions using explicit deny, allow and implicit deny', 'Explain how scope and inheritance work in Azure RBAC and Google Cloud IAM'],
        sources: ['aws-policy-evaluation', 'aws-policy-elements', 'azure-rbac', 'azure-deny-assignments', 'gcp-iam', 'gcp-deny'],
        checks: [
            { question: 'A policy allows "s3:*" on "arn:aws:s3:::kanem-invoices/*" and denies "s3:DeleteObject" on the same resource. What happens to a DeleteObject request on an invoice?', options: ['Allowed, because s3:* includes DeleteObject', 'Denied, because an explicit deny overrides any allow', 'Allowed if the user has MFA', 'Denied only if no other policy allows it'], answerIndex: 1, explanation: 'In AWS evaluation an explicit deny always wins, regardless of allows elsewhere.' },
            { question: 'Policy: Allow s3:GetObject on arn:aws:s3:::kanem-invoices/*. Which requests are denied? Select all that apply.', code: 'Request A: s3:GetObject  arn:aws:s3:::kanem-invoices/2026/inv-1.pdf\nRequest B: s3:GetObject  arn:aws:s3:::kanem-payroll/march.csv\nRequest C: s3:ListBucket arn:aws:s3:::kanem-invoices\nRequest D: s3:PutObject  arn:aws:s3:::kanem-invoices/2026/inv-2.pdf', options: ['Request A', 'Request B', 'Request C', 'Request D'], answerIndexes: [1, 2, 3], explanation: 'Only A matches both the action and the resource. B is another bucket, C is a different action on the bucket ARN, D is a different action. All three are implicitly denied.' },
            { question: 'In Azure, a user is assigned Contributor on a subscription. What access do they have to a resource group inside it?', options: ['None, unless also assigned on the resource group', 'Contributor, because assignments are inherited by child scopes', 'Reader only', 'Owner, because subscription roles are promoted'], answerIndex: 1, explanation: 'Azure RBAC assignments apply at their scope and every scope beneath it.' },
        ],
    },
    {
        slug: 'least-privilege-in-practice', title: 'Least privilege in practice', minutes: 35,
        objectives: ['Derive the minimum permissions for a workload from its documented needs', 'Recognise dangerous wildcards and broad built-in roles', 'Use usage evidence and access reviews to remove unneeded permissions'],
        sources: ['nist-sp-800-210', 'aws-access-analyzer', 'gcp-role-recommendations', 'csa-ccm'],
        checks: [
            { question: 'Which statement is the least privileged way to let an application store generated invoices?', options: ['Allow s3:* on *', 'Allow s3:PutObject on arn:aws:s3:::kanem-invoices/generated/*', 'Allow s3:PutObject on *', 'Allow s3:* on arn:aws:s3:::kanem-invoices/*'], answerIndex: 1, explanation: 'One action, scoped to the one prefix the application writes to.' },
            { question: 'Which of these are reasonable ways to find and remove unused permissions? Select all that apply.', options: ['IAM last-accessed information or Access Analyzer unused-access findings in AWS', 'Google Cloud IAM role recommendations', 'Asking each developer which permissions they would like', 'Regular access reviews where the owner confirms each grant'], answerIndexes: [0, 1, 3], explanation: 'Usage evidence and owner-confirmed reviews remove excess. Wish lists add it.' },
            { question: 'Why must tags be protected when access decisions use them (ABAC)?', options: ['Tags are expensive', 'If a user can change a resource\'s tags, they may be able to change who can access it', 'Tags are not supported in Azure', 'ABAC does not use tags'], answerIndex: 1, explanation: 'In ABAC, attributes are part of the policy. Whoever controls the attributes controls access.' },
        ],
    },
    {
        slug: 'workload-identities', title: 'Workload identities and temporary credentials', minutes: 30,
        objectives: ['Explain the risks of long-lived access keys and client secrets', 'Choose the right workload identity mechanism in each provider', 'Respond to an exposed access key'],
        sources: ['aws-temp-credentials', 'azure-managed-identities', 'gcp-sa-best-practices', 'gcp-wif', 'aws-imds'],
        checks: [
            { question: 'An application on an Azure VM needs to read from a storage account. What is the best way for it to authenticate?', options: ['Store the storage account key in the application settings', 'Use a managed identity for the VM with a role scoped to that storage account', 'Use the administrator\'s personal account', 'Make the storage account public'], answerIndex: 1, explanation: 'A managed identity removes stored secrets; the scoped role keeps it least privilege.' },
            { question: 'You discover a valid cloud access key committed to a Git repository eight months ago. What should you do first?', options: ['Delete the file from the latest commit', 'Deactivate the key, then investigate its use in the audit logs', 'Make the repository private', 'Wait for the 90-day rotation'], answerIndex: 1, explanation: 'Clones and forks already hold copies, so the key must be treated as compromised and disabled before anything else.' },
            { question: 'What is the critical security control in workload identity federation for a CI pipeline?', options: ['The length of the access key', 'The trust rule that says which issuer, repository and branch may obtain credentials', 'The pipeline\'s colour theme', 'Running the pipeline at night'], answerIndex: 1, explanation: 'Federation is only as strong as the conditions on the trust rule. "Any repository" is effectively public access.' },
        ],
    },
    {
        slug: 'privileged-access-and-escalation', title: 'Privileged access and privilege escalation', minutes: 35,
        objectives: ['Identify permissions that enable privilege escalation in each provider', 'Explain the iam:PassRole escalation path', 'Design privileged access with just-in-time elevation, break-glass accounts and guardrails'],
        sources: ['aws-passrole', 'azure-pim', 'aws-scps', 'attack-t1098', 'attack-t1548-005'],
        checks: [
            { question: 'Which permissions should be treated as administrative because they allow privilege escalation? Select all that apply.', options: ['iam:CreatePolicyVersion on a policy attached to yourself', 'Microsoft.Authorization/roleAssignments/write', 's3:GetObject on one bucket', 'iam:PassRole on *'], answerIndexes: [0, 1, 3], explanation: 'Editing your own policy, writing role assignments and passing any role all let an identity gain more access. Reading objects in one bucket does not.' },
            { question: 'An identity has ec2:RunInstances and iam:PassRole on *. Why is this dangerous?', options: ['It can launch an instance with an administrator role attached and use that role\'s credentials', 'It can read every bucket directly', 'It can delete the account', 'It is not dangerous'], answerIndex: 0, explanation: 'The identity can create compute that holds more privilege than it has, then use it.' },
            { question: 'What makes a break-glass account safe enough to keep?', options: ['It has no password', 'Strong credentials stored offline, exclusion from controls that could lock everyone out, and an alert on every use', 'It is shared with all administrators', 'It is only used by contractors'], answerIndex: 1, explanation: 'Break-glass accounts exist for emergencies, so they must be protected and every use must be noticed.' },
        ],
    },
];

const policy = `{
  "Version": "2012-10-17",
  "Statement": [
    { "Sid": "S1", "Effect": "Allow", "Action": "s3:GetObject", "Resource": "arn:aws:s3:::kanem-invoices/*" },
    { "Sid": "S2", "Effect": "Allow", "Action": "s3:*", "Resource": "*" },
    { "Sid": "S3", "Effect": "Allow", "Action": ["logs:CreateLogStream", "logs:PutLogEvents"], "Resource": "arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/invoice-app:*" },
    { "Sid": "S4", "Effect": "Allow", "Action": "iam:PassRole", "Resource": "*" },
    { "Sid": "S5", "Effect": "Allow", "Action": "kms:Decrypt", "Resource": "arn:aws:kms:eu-west-1:111122223333:key/0000aaaa-11bb-22cc-33dd-444455556666" },
    { "Sid": "S6", "Effect": "Allow", "Action": "ec2:*", "Resource": "*" },
    { "Sid": "S7", "Effect": "Allow", "Action": ["cloudtrail:StopLogging", "cloudtrail:DeleteTrail"], "Resource": "*" }
  ]
}`;
const KEY = 'arn:aws:kms:eu-west-1:111122223333:key/0000aaaa-11bb-22cc-33dd-444455556666';
const LOG = 'arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/invoice-app:log-stream:web-01';
const req = (label, action, resource, expect) => ({ label, action, resource, expect });
const st = (ids) => ids.map((s) => ({ id: s, label: s }));

const lab = {
    slug: 'iam-excessive-permissions',
    title: 'Access review: least privilege for the invoice application',
    difficulty: 'intermediate',
    labType: 'config_review',
    summary: 'Review the invoice application\'s cloud permissions and usage evidence, find the escalation and evasion paths, write a least-privilege policy that passes 15 hidden access tests, and report the findings.',
    minutes: 90,
    moduleSlug: 'iam',
    passPercent: 80,
    content: {
        objectives: ['Identify excessive, escalation and defence-evasion permissions in a policy', 'Interpret access-activity evidence', 'Write a least-privilege policy that meets the application\'s needs exactly', 'Report an access-review finding to a security manager'],
        scenario: 'Kanem Financial Services (fictional) has moved its **invoice application** to AWS. Before go-live, the security manager has asked you to review the permissions of its identity, `invoice-app-prod`. The application team says the policy was "copied from a tutorial to get it working". You have the policy, the application\'s documented needs and 90 days of access activity.',
        scope: 'Account 111122223333, region eu-west-1. All identifiers are synthetic. The policy simulator uses AWS-style identity-policy evaluation (explicit deny wins, otherwise an allow is needed); conditions are not evaluated.',
        instructions: '1. Read the three documents in the workspace.\n2. Answer tasks 1–5.\n3. In task 6, **edit the policy** so it grants exactly what the application needs. The grader runs 15 access requests you cannot see; all must be decided correctly.\n4. In task 7, write the finding for the security manager. It is saved to your portfolio.\n\nYou need 80% to pass.\n\n:::reveal Advanced challenge (optional)\nAdd an explicit Deny statement that protects invoices from deletion even if someone later attaches a broad policy to this identity. Does your policy still pass? Explain in your finding why an explicit deny is useful here, and what it cannot protect against (hint: other identities and bucket policies).\n:::',
        simulationNotice: 'This is a simulation. All data is synthetic and generated by AMANIQ Academy for training. No real cloud accounts are used, nothing here costs money, and nothing you do leaves the platform.',
        workspace: {
            kind: 'documents',
            documents: [
                { title: 'invoice-app-prod policy (current)', format: 'code', language: 'json', body: policy },
                { title: 'What the application needs (from the application team)', format: 'markdown', body: '1. **Read** invoice PDFs: any object in the `kanem-invoices` bucket.\n2. **Write** newly generated invoices, only under the `generated/` prefix of `kanem-invoices`.\n3. **Decrypt** with the invoice KMS key `' + KEY + '`.\n4. **Write logs** to the log group `/kanem/invoice-app` (create log streams and put log events).\n5. Nothing else. Invoices must never be deleted by the application (7-year retention).' },
                { title: 'Access activity for invoice-app-prod (last 90 days)', format: 'markdown', body: '| Permission | Last used | Notes |\n| --- | --- | --- |\n| s3:GetObject | today | ~42,000 calls/day from the application servers |\n| s3:PutObject | today | Only under `kanem-invoices/generated/` |\n| kms:Decrypt | today | Invoice key only |\n| logs:CreateLogStream, logs:PutLogEvents | today | `/kanem/invoice-app` |\n| s3:DeleteObject, s3:PutBucketPolicy | never | |\n| ec2:RunInstances | 28 Feb 2026, 02:41 UTC | **1 call**, source IP `203.0.113.50` (not a Kanem address) |\n| iam:PassRole | 28 Feb 2026, 02:41 UTC | **1 call**, role `kanem-admin`, source IP `203.0.113.50` |\n| cloudtrail:StopLogging | never | |\n\n*The application team says the application has never launched servers.*' },
            ],
        },
        tasks: [
            { id: 'excessive', points: 3, prompt: 'Which statements grant more than the application needs? Select all that apply.', hints: ['Compare each statement with the five needs.', 'Wildcards and unrelated services are the giveaway.'], input: { type: 'multi', options: st(['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7']) } },
            { id: 'escalation', points: 1, prompt: 'Which **combination** of statements would let this identity effectively become an administrator?', hints: ['Lesson 5: what can you do if you can launch compute and pass any role to it?'], input: { type: 'choice', options: [{ id: 's4s6', label: 'S4 and S6 together' }, { id: 's2', label: 'S2 alone' }, { id: 's1s5', label: 'S1 and S5 together' }, { id: 's3', label: 'S3 alone' }] } },
            { id: 'evasion', points: 1, prompt: 'Which statement would let an attacker using this identity **hide** what they do next?', hints: ['Which service records API activity?'], input: { type: 'choice', options: st(['S2', 'S5', 'S6', 'S7']) } },
            { id: 'evidence', points: 1, prompt: 'What does the access activity for `ec2:RunInstances` and `iam:PassRole` on 28 February most likely indicate?', hints: ['Who made the calls, from where, and what does the application team say?'], input: { type: 'choice', options: [{ id: 'normal', label: 'Normal deployment activity' }, { id: 'incident', label: 'Possible misuse of the application\'s credentials from an external address to obtain the kanem-admin role: escalate as a potential incident' }, { id: 'billing', label: 'A billing alert' }, { id: 'nothing', label: 'Nothing, because PassRole is read-only' }] } },
            { id: 'credentials', points: 1, prompt: 'The application currently authenticates with an access key stored in its configuration file. What is the best change?', hints: ['Lesson 4.'], input: { type: 'choice', options: [{ id: 'role', label: 'Attach the role to the application\'s compute so it receives temporary credentials, then deactivate and delete the key' }, { id: 'rotate', label: 'Rotate the key every 90 days' }, { id: 'mfa', label: 'Add MFA to the key' }, { id: 'env', label: 'Move the key to an environment variable' }] } },
            { id: 'policy', points: 6, prompt: 'Edit the policy so it grants **exactly** the five documented needs. Remove everything else. The grader tests 15 requests against your policy.', hints: ['Needs 1 and 2 use different actions and different resource paths.', 'The log group ARN in S3 is already correct.', 'Check: could your policy read the payroll bucket? Change a bucket policy? Pass the kanem-admin role?'], input: { type: 'iam-policy', starter: policy } },
            { id: 'finding', points: 2, prompt: 'Write the **access-review finding** for the security manager: what is wrong, the escalation path, what the activity evidence shows, and what should happen now.', hints: ['Lead with the most serious issue.', 'Be specific: name statements, actions, the IP address and the date.'], input: { type: 'deliverable', title: 'Access-review finding', competency: 'Assessing cloud permissions, recognising escalation risk and escalating evidence of misuse', minWords: 150, rubric: ['States the most serious risk first, in plain language', 'Explains the S4 + S6 escalation path and the S7 logging risk', 'Uses the activity evidence (date, source IP, kanem-admin) and recommends incident escalation', 'Recommends specific remediation: least-privilege policy, role-based credentials, key deactivation', 'Notes what remains to be checked (other identities, CloudTrail for 28 Feb, the kanem-admin role\'s activity)'], template: 'Summary of the finding:\n\nWhy it matters (escalation and evasion paths):\n\nEvidence:\n\nRecommended actions (now / this week):\n\nWhat still needs to be checked:\n' } },
        ],
        reflection: ['Which finding would you report first, and why?', 'If you could make only one change today, which would reduce the most risk?', 'How would you explain "PassRole on *" to an application owner who says the permission is harmless?', 'What would you check in the audit logs before declaring the incident closed?', 'How would you stop the next project from copying a tutorial policy into production?'],
    },
    validator: {
        tasks: {
            excessive: { type: 'multi', correct: ['S2', 'S4', 'S6', 'S7'], feedback: { correct: 'Correct: S2, S4, S6 and S7 go beyond the documented needs.', incorrect: 'Compare each statement with the needs list. S1, S3 and S5 match needs; four statements do not.' } },
            escalation: { type: 'choice', correct: 's4s6', feedback: { correct: 'Right: launch an instance and pass it the admin role.', incorrect: 'Look for a permission that hands a role to a service, plus one that creates something to hand it to.' } },
            evasion: { type: 'choice', correct: 'S7', feedback: { correct: 'Right: stopping or deleting the trail removes the audit record.', incorrect: 'Which service records API activity in AWS?' } },
            evidence: { type: 'choice', correct: 'incident', feedback: { correct: 'Right: unexpected, external, admin-role activity is an incident until proven otherwise.', incorrect: 'The application never launches servers, the source IP is not Kanem\'s, and the role passed was kanem-admin.' } },
            credentials: { type: 'choice', correct: 'role', feedback: { correct: 'Right: temporary credentials from a role remove the stored secret.', incorrect: 'Rotating, moving or adding MFA keeps a long-lived secret; MFA does not apply to keys used by applications.' } },
            policy: {
                type: 'iam-policy',
                requests: [
                    req('Read an invoice', 's3:GetObject', 'arn:aws:s3:::kanem-invoices/2026/03/inv-1042.pdf', 'allow'),
                    req('Store a generated invoice', 's3:PutObject', 'arn:aws:s3:::kanem-invoices/generated/inv-1043.pdf', 'allow'),
                    req('Decrypt with the invoice key', 'kms:Decrypt', KEY, 'allow'),
                    req('Create a log stream', 'logs:CreateLogStream', LOG, 'allow'),
                    req('Write log events', 'logs:PutLogEvents', LOG, 'allow'),
                    req('Delete an invoice', 's3:DeleteObject', 'arn:aws:s3:::kanem-invoices/2026/03/inv-1042.pdf', 'deny'),
                    req('Overwrite an existing invoice outside generated/', 's3:PutObject', 'arn:aws:s3:::kanem-invoices/2026/03/inv-1042.pdf', 'deny'),
                    req('Read the payroll bucket', 's3:GetObject', 'arn:aws:s3:::kanem-payroll/2026/march.csv', 'deny'),
                    req('Change the invoice bucket policy', 's3:PutBucketPolicy', 'arn:aws:s3:::kanem-invoices', 'deny'),
                    req('Pass the kanem-admin role', 'iam:PassRole', 'arn:aws:iam::111122223333:role/kanem-admin', 'deny'),
                    req('Launch an instance', 'ec2:RunInstances', 'arn:aws:ec2:eu-west-1:111122223333:instance/*', 'deny'),
                    req('Stop audit logging', 'cloudtrail:StopLogging', 'arn:aws:cloudtrail:eu-west-1:111122223333:trail/kanem-org-trail', 'deny'),
                    req('Decrypt with another key', 'kms:Decrypt', 'arn:aws:kms:eu-west-1:111122223333:key/9999ffff-88ee-77dd-66cc-555544443333', 'deny'),
                    req('Write to the payroll log group', 'logs:PutLogEvents', 'arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/payroll:log-stream:a', 'deny'),
                    req('Edit its own policy', 'iam:CreatePolicyVersion', 'arn:aws:iam::111122223333:policy/invoice-app-policy', 'deny'),
                ],
                feedback: { correct: 'Your policy grants exactly the documented needs.', incorrect: 'At least one request was decided incorrectly. Check each need, and remove every statement the needs do not justify.' },
            },
            finding: {
                type: 'deliverable', minWords: 150,
                mustMention: [
                    { label: 'the escalation path (iam:PassRole)', any: ['passrole', 'pass role', 'pass the'] },
                    { label: 'the suspicious activity in the evidence (source IP or date)', any: ['203.0.113.50', '28 feb', '28 february', 'external ip', 'external address'] },
                    { label: 'what should happen to the access key', any: ['access key', 'the key', 'keys'] },
                    { label: 'escalation as an incident', any: ['incident', 'escalat', 'investigat'] },
                ],
                feedback: { correct: 'Finding complete. Compare it with the rubric and the example in the debrief.', incorrect: 'Your finding is not complete yet.' },
            },
        },
        debrief: '## Worked solution\n\n**Excessive: S2, S4, S6, S7.** S2 (`s3:*` on `*`) lets the application read, change and delete every bucket, including payroll; S4 (`iam:PassRole` on `*`) and S6 (`ec2:*`) together are a privilege-escalation path; S7 lets anyone holding these credentials switch off or delete the audit trail. S1, S3 and S5 match needs 1, 4 and 3; need 2 (writing to `generated/`) was not granted at all, which is why the team added S2.\n\n**Evidence:** on 28 February, from an address that is not Kanem\'s, the application identity launched an instance and passed it `kanem-admin`. The application never does this. Treat it as a probable credential compromise: escalate now, review CloudTrail around 02:41 UTC, find the instance and everything `kanem-admin` did, and deactivate the access key. (You will investigate this incident in module 9.)\n\n### A policy that passes\n\n```json\n{\n  "Version": "2012-10-17",\n  "Statement": [\n    { "Sid": "ReadInvoices", "Effect": "Allow", "Action": "s3:GetObject", "Resource": "arn:aws:s3:::kanem-invoices/*" },\n    { "Sid": "WriteGenerated", "Effect": "Allow", "Action": "s3:PutObject", "Resource": "arn:aws:s3:::kanem-invoices/generated/*" },\n    { "Sid": "DecryptInvoices", "Effect": "Allow", "Action": "kms:Decrypt", "Resource": "' + KEY + '" },\n    { "Sid": "WriteLogs", "Effect": "Allow", "Action": ["logs:CreateLogStream", "logs:PutLogEvents"], "Resource": "arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/invoice-app:*" },\n    { "Sid": "NeverDeleteInvoices", "Effect": "Deny", "Action": "s3:DeleteObject", "Resource": "arn:aws:s3:::kanem-invoices/*" }\n  ]\n}\n```\n\nThe final Deny is optional (the advanced challenge). It protects against a broad policy being attached to this identity later, but not against other identities or the bucket policy: retention is better enforced with S3 Object Lock or a bucket policy as well.\n\n### What a strong finding says\n\n"The invoice application\'s identity can make itself an administrator (iam:PassRole on all roles plus full EC2), read and delete every S3 bucket, and turn off audit logging. Activity on 28 February shows this path being used from an external address (203.0.113.50) to obtain the kanem-admin role, which the application never does: this should be handled as a security incident today. Recommended: deactivate the access key; attach a role with the attached least-privilege policy; investigate CloudTrail for 28 February and all kanem-admin activity; add an organisation guardrail preventing CloudTrail from being stopped."\n\n## What you have practised\n\nThis is a cloud access review: reading policy as the platform does, connecting permissions to attack paths (ATT&CK T1098, T1548.005), using evidence instead of opinion, and turning findings into actions a manager can approve. It maps to the Identity and Access Management domain of the CSA Cloud Controls Matrix and AWS IAM security best practices.\n\n**Next:** module 3 applies the same thinking to cloud networks; module 9 picks up the 28 February activity as a full incident.',
    },
};

replaceModule(p.data, 'iam', {
    slug: 'iam',
    title: 'Cloud Identity and Access Management',
    purpose: 'Kanem Financial Services\' invoice application is about to go live in the cloud with a policy "copied from a tutorial". In this module you learn how cloud identities authenticate, how AWS, Azure and Google Cloud decide what an identity may do, how to apply least privilege with evidence, how workloads should get credentials, and how attackers escalate privileges. You finish with a real-looking access review.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the policy you write and your written finding.',
    prerequisiteKnowledge: 'Cloud service models and shared responsibility (module 1). Comfortable reading JSON.',
    objectives: ['Distinguish authentication from authorisation and classify cloud principals', 'Predict access decisions from AWS IAM policies and explain Azure RBAC and Google Cloud IAM inheritance', 'Design least-privilege permissions from documented needs and usage evidence', 'Recommend workload identity mechanisms instead of long-lived keys', 'Identify privilege-escalation and defence-evasion permissions and design privileged access controls'],
    keyConcepts: ['Principals and federation', 'Authentication vs authorisation', 'Explicit deny, allow and implicit deny', 'Scope and inheritance', 'Least privilege and ABAC', 'Temporary credentials and workload identity', 'Privilege escalation (PassRole, role assignment)', 'Just-in-time and break-glass access'],
    assessmentCriteria: ['Correctly identifies excessive, escalation and evasion permissions', 'Writes a policy that passes all hidden access tests', 'Interprets access-activity evidence and escalates appropriately', 'Communicates the finding clearly to a manager'],
    instructorNotes: 'Estimated time: 2.75 hours of lessons plus 1.5 hours of lab. The 28 February PassRole activity seeds the module 9 incident.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'iam-excessive-permissions', lab);

const C = 'iam';
replaceQuestions(p.data, 'cld-iam-', [
    single('cld-iam-01', C, 'foundation', 'Distinguish authentication and authorisation', 'Which question does authorisation answer?', ['Who are you?', '*Are you allowed to perform this action on this resource?', 'Is the connection encrypted?', 'Which region is the request in?'], 'Authentication proves identity; authorisation evaluates policy for the requested action and resource.'),
    single('cld-iam-02', C, 'foundation', 'Classify principals', 'Which is a workload identity rather than a human identity?', ['An Entra ID user', '*An Azure managed identity', 'A Google group of developers', 'An IAM Identity Center user'], 'Managed identities, IAM roles used by services and Google service accounts are identities for workloads.'),
    single('cld-iam-03', C, 'intermediate', 'Predict AWS decisions', 'No policy mentions s3:DeleteObject for a role. What happens when the role calls it?', ['Allowed, because nothing denies it', '*Denied by default (implicit deny)', 'Allowed only with MFA', 'Allowed in the same region'], 'In AWS everything is denied unless a policy allows it.'),
    single('cld-iam-04', C, 'intermediate', 'Predict AWS decisions', 'One policy allows s3:* on a bucket\'s objects and another denies s3:GetObject on the same objects. A GetObject request is:', ['Allowed, because allows are evaluated first', '*Denied, because an explicit deny overrides any allow', 'Allowed if both policies are in the same account', 'Undecided'], 'Explicit deny always wins in AWS policy evaluation.'),
    single('cld-iam-05', C, 'intermediate', 'Read resource ARNs', 'An Allow statement has Resource "arn:aws:s3:::kanem-invoices/*". Which request does it NOT cover?', ['s3:GetObject on kanem-invoices/a.pdf', 's3:GetObject on kanem-invoices/2026/b.pdf', '*s3:PutBucketPolicy on arn:aws:s3:::kanem-invoices', 'None; it covers the whole bucket'], 'The "/*" form matches objects in the bucket. Bucket-level actions use the bucket ARN without "/*".'),
    single('cld-iam-06', C, 'intermediate', 'Explain inheritance', 'A user is granted the Google Cloud basic Editor role on a project. What is the main concern?', ['Editor is read-only', '*Basic roles are very broad and apply to resources across the whole project', 'Editor only works in one region', 'Basic roles cannot be removed'], 'Basic roles grant wide permissions and are inherited by resources in the project; predefined or custom roles are preferred.'),
    multiple('cld-iam-07', C, 'intermediate', 'Design least privilege', 'Which practices support least privilege? Select all that apply.', ['*Granting only the actions and resources a documented need requires', '*Removing permissions that usage data shows are never used', 'Granting Owner at subscription scope to speed up projects', '*Separating development and production into different accounts or subscriptions'], 'Least privilege is built from documented needs, evidence of use and separation of environments.'),
    single('cld-iam-08', C, 'foundation', 'Explain RBAC and ABAC', 'What distinguishes attribute-based access control (ABAC)?', ['It only uses user names', '*Decisions use attributes such as tags, department or request context', 'It replaces authentication', 'It only works on-premises'], 'ABAC evaluates attributes of the principal, resource and request; RBAC grants permissions through roles.'),
    single('cld-iam-09', C, 'foundation', 'Recommend workload identity', 'Why are temporary credentials preferred over long-lived access keys for workloads?', ['They are cheaper', '*They expire and rotate automatically, so a leaked copy has limited value and nothing is stored in code', 'They give more permissions', 'They work without a network'], 'Short-lived credentials from roles, managed identities or attached service accounts remove the stored secret.'),
    single('cld-iam-10', C, 'intermediate', 'Respond to exposed keys', 'A valid access key is found in a public code repository. Which action comes first?', ['Rewrite the Git history', '*Deactivate the key', 'Email the developer', 'Increase the key\'s permissions'], 'Copies may already exist, so the key is disabled first; then investigate its use and replace it.'),
    single('cld-iam-11', C, 'intermediate', 'Secure federation', 'A CI role trust policy accepts tokens from any repository on the CI platform. What is the risk?', ['None, because tokens are signed', '*Any repository owner on that platform could obtain the role\'s credentials', 'The pipeline will run slower', 'Tokens will expire too quickly'], 'Federation must restrict the issuer, repository and branch that may assume the role.'),
    single('cld-iam-12', C, 'advanced', 'Identify escalation paths', 'An identity can call iam:PassRole on * and lambda:CreateFunction. How can it escalate?', ['It cannot', '*Create a function that runs with a highly privileged role and use it to act as that role', 'Read every log group', 'Change its password'], 'Passing any role to compute the identity controls lets it act with that role\'s permissions.'),
    single('cld-iam-13', C, 'intermediate', 'Identify escalation paths', 'Which Azure built-in roles can create role assignments by default?', ['Reader', 'Contributor', '*Owner and User Access Administrator', 'All roles'], 'Contributor can manage resources but not grant access; Owner and User Access Administrator can write role assignments.'),
    multiple('cld-iam-14', C, 'advanced', 'Design privileged access', 'Which controls reduce the risk of privileged cloud accounts? Select all that apply.', ['*Just-in-time activation of admin roles with a reason and time limit', '*Organisation-level guardrails that prevent disabling audit logging', 'Sharing one admin account so fewer accounts exist', '*Alerts on new role assignments, policy changes and break-glass sign-ins'], 'Fewer standing privileges, guardrails above accounts and alerting on privilege changes are core controls. Shared accounts remove accountability.'),
]);

save(p);
const oldLesson = require('path').join(__dirname, '../programmes/') + 'cloud-security/lessons/iam/least-privilege-policies.md';
if (fs.existsSync(oldLesson)) fs.unlinkSync(oldLesson);
console.log('cloud module 2 applied:', lessons.length, 'lessons,', p.data.questions.length, 'questions');
