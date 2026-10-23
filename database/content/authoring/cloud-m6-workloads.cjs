// Cloud Security, module 6: Cloud Workload and Compute Security.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation';
upsertSources([
    { key: 'nist-sp-800-190', title: 'SP 800-190: Application Container Security Guide', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/190/final', topic: 'Container security', sourceType: o, versionNote: 'Final, September 2017. Principles still apply; tooling has evolved.', supports: 'Image, registry, orchestrator and host risks and countermeasures.' },
    { key: 'nist-sp-800-40r4', title: 'SP 800-40 Rev. 4: Guide to Enterprise Patch Management Planning', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/40/r4/final', topic: 'Patch management', sourceType: o, versionNote: 'Revision 4, April 2022.', supports: 'Patching as preventive maintenance.' },
    { key: 'cis-kubernetes', title: 'CIS Kubernetes Benchmarks', publisher: 'Center for Internet Security', url: 'https://www.cisecurity.org/benchmark/kubernetes', topic: 'Kubernetes configuration', sourceType: o, versionNote: 'Check the benchmark version for your Kubernetes release.', supports: 'Cluster configuration baseline.' },
    { key: 'k8s-security', title: 'Kubernetes security concepts', publisher: 'Kubernetes', url: 'https://kubernetes.io/docs/concepts/security/', topic: 'Kubernetes security', sourceType: v, versionNote: 'Official documentation, current.', supports: 'RBAC, secrets, network policies and cluster hardening.' },
    { key: 'k8s-pod-security-standards', title: 'Pod Security Standards', publisher: 'Kubernetes', url: 'https://kubernetes.io/docs/concepts/security/pod-security-standards/', topic: 'Workload restrictions', sourceType: v, versionNote: 'Official documentation, current.', supports: 'Privileged, Baseline and Restricted profiles.' },
    { key: 'aws-lambda-security', title: 'Security in AWS Lambda', publisher: 'Amazon Web Services', url: 'https://docs.aws.amazon.com/lambda/latest/dg/lambda-security.html', topic: 'Serverless security', sourceType: v, versionNote: 'Lambda Developer Guide, current.', supports: 'Shared responsibility for serverless functions.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'vm-hardening-and-patching', title: 'Virtual machine hardening and patching', minutes: 30, sources: ['cis-benchmarks', 'nist-sp-800-40r4'],
        objectives: ['Build VMs from hardened, automatically produced images', 'Run patching as planned preventive maintenance', 'Apply a configuration baseline for access, identity, encryption and logging'],
        checks: [
            { question: 'What is the main advantage of a golden image built by a pipeline?', options: ['It is free', 'Every VM starts from the same reviewed, hardened configuration', 'It removes the need for patching', 'It disables logging'], answerIndex: 1, explanation: 'Consistency and review are the point; patching still continues.' },
            { question: 'Which VMs should be patched first? Select all that apply.', options: ['Internet-facing systems', 'Systems with known-exploited vulnerabilities', 'Development VMs with no network access', 'Critical production systems'], answerIndexes: [0, 1, 3], explanation: 'Prioritise by exposure, exploitability and criticality.' },
            { question: 'In immutable infrastructure, how is a server updated?', options: ['Logging in and editing it', 'Deploying new instances from an updated image and removing the old ones', 'Never', 'Restarting it'], answerIndex: 1, explanation: 'Replacing instead of changing removes drift and simplifies rollback.' },
        ] },
    { slug: 'container-image-security', title: 'Container image security', minutes: 30, sources: ['nist-sp-800-190'],
        objectives: ['Identify where image risks come from', 'Apply controls at build, scan, store, deploy and run', 'Explain why containers share more than VMs and what that implies'],
        checks: [
            { question: 'Where should secrets for a containerised app come from?', options: ['Copied into the image during the build', 'A secrets manager at run time', 'The base image', 'A public registry'], answerIndex: 1, explanation: 'Anything in an image is readable by anyone who can pull it.' },
            { question: 'Which practices reduce image risk? Select all that apply.', options: ['Minimal, maintained base images', 'Scanning in the pipeline and the registry', 'Running containers as root by default', 'Signing images and deploying only approved ones'], answerIndexes: [0, 1, 3], explanation: 'Least privilege at run time means non-root wherever possible.' },
            { question: 'Why is an SBOM useful?', options: ['It speeds up containers', 'It shows which images contain a newly announced vulnerable component', 'It encrypts images', 'It replaces scanning'], answerIndex: 1, explanation: 'Knowing your components turns a long hunt into a quick lookup.' },
        ] },
    { slug: 'kubernetes-security-basics', title: 'Kubernetes security basics', minutes: 35, sources: ['k8s-security', 'k8s-pod-security-standards', 'cis-kubernetes'],
        objectives: ['Divide responsibilities on managed Kubernetes', 'Apply RBAC, Pod Security Standards, network policies and workload identity', 'Review a cluster with a structured checklist'],
        checks: [
            { question: 'On a managed Kubernetes service, who usually runs the control plane?', options: ['The customer', 'The provider', 'Nobody', 'The application team'], answerIndex: 1, explanation: 'The provider runs the control plane; the customer secures configuration and workloads.' },
            { question: 'What do the Pod Security Standards control?', options: ['Pod names', 'What pods may do, for example running privileged or as root', 'Billing', 'DNS'], answerIndex: 1, explanation: 'They define Privileged, Baseline and Restricted profiles enforced at admission.' },
            { question: 'Why are namespaces not enough to separate very different trust levels?', options: ['They do not exist', 'They organise resources but are not a strong isolation boundary on their own', 'They are only for billing', 'They disable RBAC'], answerIndex: 1, explanation: 'Separate clusters or node pools give stronger isolation.' },
        ] },
    { slug: 'serverless-security', title: 'Serverless security', minutes: 25, sources: ['aws-lambda-security'],
        objectives: ['Identify what remains the customer\'s responsibility in serverless', 'Apply least privilege per function', 'Review triggers as entry points'],
        checks: [
            { question: 'What is the most common serverless permission problem?', options: ['Functions have no permissions', 'One broad role shared by many functions', 'Too many log groups', 'Short timeouts'], answerIndex: 1, explanation: 'A compromise of any one function then gives access intended for all of them.' },
            { question: 'Which are entry points to review in a serverless application? Select all that apply.', options: ['Public API routes', 'Storage upload events', 'Queue messages', 'The provider\'s data-centre doors'], answerIndexes: [0, 1, 2], explanation: 'Triggers are the perimeter; physical security is the provider\'s.' },
            { question: 'Why set concurrency limits and budget alerts on functions?', options: ['To slow them down', 'To stop runaway or abused functions creating unbounded cost', 'They are required for logging', 'To improve accuracy'], answerIndex: 1, explanation: 'Measured service makes abuse expensive; limits contain it.' },
        ] },
    { slug: 'runtime-protection-and-vulnerability-management', title: 'Runtime protection and vulnerability management', minutes: 30, sources: ['azure-defender-for-cloud', 'aws-guardduty', 'nist-sp-800-40r4'],
        objectives: ['Choose runtime protection for each workload type', 'Run vulnerability management across VMs, images and functions', 'Prioritise findings by exploitability, exposure and criticality'],
        checks: [
            { question: 'Thousands of vulnerability findings exist. What should be fixed first?', options: ['The oldest findings', 'Internet-facing, critical workloads with known-exploited vulnerabilities', 'Development images', 'Findings with the longest names'], answerIndex: 1, explanation: 'Prioritise by real risk, not volume.' },
            { question: 'How is a vulnerable container image fixed?', options: ['Patching the running container', 'Rebuilding from an updated base and redeploying', 'Restarting the pod', 'Ignoring it'], answerIndex: 1, explanation: 'Containers are replaced, not patched in place.' },
            { question: 'Where should runtime protection findings go?', options: ['A separate inbox nobody reads', 'The same central alert queue as other security findings', 'Only to developers', 'Nowhere'], answerIndex: 1, explanation: 'One queue lets analysts correlate and prioritise.' },
        ] },
];

const R = (id, label) => ({ id, label });
const status = [R('ok', 'Meets the baseline'), R('fix', 'Needs fixing')];

const lab = {
    slug: 'workload-deployment-review',
    title: 'Review three ways of running the reporting service',
    difficulty: 'intermediate',
    labType: 'config_review',
    summary: 'Review the security of the reporting service as a VM, on Kubernetes and as serverless functions; find what breaks the baseline, prioritise, and write the review.',
    minutes: 60,
    moduleSlug: 'workloads',
    passPercent: 80,
    content: {
        objectives: ['Compare workload configurations against a security baseline', 'Identify the most important fixes for each workload type', 'Prioritise vulnerability findings', 'Write a workload security review'],
        scenario: 'Kanem Financial Services (fictional) has three candidate deployments of its reporting service. Before choosing, the architecture board wants a security review of each against Kanem\'s workload baseline (the checklist in lesson 5).',
        scope: 'Work from the deployment summary. All values are fictional.',
        instructions: '1. Read **Deployment summary** and **Vulnerability findings**.\n2. Complete the review grid in task 1.\n3. Answer tasks 2–5.\n4. Write the review in task 6. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. No cloud accounts are used.',
        workspace: { kind: 'documents', documents: [
            { title: 'Deployment summary', format: 'markdown', body: '| Area | A: Virtual machine | B: Managed Kubernetes | C: Serverless functions |\n| --- | --- | --- | --- |\n| Identity | Own instance role, reporting permissions only | All pods share the node\'s broad role | One role shared by all 14 functions, with administrator rights |\n| Secrets | Read from the secrets manager | Database password in the container image | Read from the secrets manager |\n| Patching / updates | Rebuilt monthly from the golden image | Base image last updated 14 months ago | Runtime version supported |\n| Workload restrictions | n/a | Namespace allows privileged pods | n/a |\n| Remote administration | Provider session service with MFA | Cluster API endpoint private | n/a |\n| Monitoring | EDR agent and OS logs to the central platform | No audit logs collected | Function logs to the central platform |' },
            { title: 'Vulnerability findings (summary)', format: 'markdown', body: '| ID | Workload | Severity | Known exploited? | Internet-facing? |\n| --- | --- | --- | --- | --- |\n| V1 | B: base image library | Critical | Yes | Yes (reporting web UI) |\n| V2 | A: OS package | Medium | No | No |\n| V3 | C: dependency in a scheduled function | High | No | No |\n| V4 | B: development image only | High | No | No |' },
        ] },
        tasks: [
            { id: 'review', points: 3, prompt: 'For each workload and area, does the configuration meet the baseline?', hints: ['Least-privilege identity per workload; secrets never in images; current patches; restricted pods; audit logs collected.'], input: { type: 'grid', rows: [R('identity', 'Identity'), R('secrets', 'Secrets'), R('patching', 'Patching / updates'), R('monitoring', 'Monitoring')], columns: [{ id: 'a', label: 'A: VM', options: status }, { id: 'b', label: 'B: Kubernetes', options: status }, { id: 'c', label: 'C: Serverless', options: status }] } },
            { id: 'worstC', points: 1, prompt: 'What is the most important fix for option C?', hints: ['Lesson 4.'], input: { type: 'choice', options: [R('roles', 'Give each function its own least-privilege role'), R('runtime', 'Change the runtime version'), R('logs', 'Add more logging'), R('none', 'Nothing')] } },
            { id: 'worstB', points: 2, prompt: 'Which issues in option B must be fixed before production? Select all that apply.', hints: ['Lessons 2 and 3.'], input: { type: 'multi', options: [R('secret', 'Database password in the image'), R('identity', 'Pods sharing the node\'s broad role'), R('privileged', 'Namespace allows privileged pods'), R('audit', 'No audit logs collected'), R('private', 'Private cluster API endpoint')] } },
            { id: 'vuln', points: 1, prompt: 'Which vulnerability should be fixed first?', hints: ['Exploitability, exposure, criticality.'], input: { type: 'choice', options: [R('V1', 'V1'), R('V2', 'V2'), R('V3', 'V3'), R('V4', 'V4')] } },
            { id: 'fixV1', points: 1, prompt: 'How should V1 be fixed?', hints: ['Lesson 2.'], input: { type: 'choice', options: [R('rebuild', 'Rebuild the image from an updated base, rescan, and redeploy'), R('patch', 'Patch the running containers by hand'), R('restart', 'Restart the pods'), R('accept', 'Accept the risk')] } },
            { id: 'report', points: 2, prompt: 'Write the **workload security review** for the architecture board: findings per option, the fixes required before production, and your recommendation.', hints: ['Be specific about which option is closest to the baseline and what each needs.'], input: { type: 'deliverable', title: 'Workload security review', competency: 'Reviewing VM, container and serverless deployments against a security baseline', minWords: 140, rubric: ['Summarises findings for all three options', 'Requires the critical fixes before production (identities, secrets, image currency, pod restrictions, audit logs)', 'Prioritises V1 and explains how to fix it', 'Makes a clear, justified recommendation', 'Notes what to monitor after go-live'], template: 'Option A (VM):\n\nOption B (Kubernetes):\n\nOption C (Serverless):\n\nVulnerability priorities:\n\nRecommendation:\n' } },
        ],
        reflection: ['Which option moves the most security work to the provider, and what does Kanem still own in it?', 'How would you stop shared broad roles from appearing again?', 'Who should be told about V1 today?', 'What would you check one month after go-live?'],
    },
    validator: {
        tasks: {
            review: { type: 'grid', minCorrectRatio: 0.8, cells: {
                identity: { a: ['ok'], b: ['fix'], c: ['fix'] },
                secrets: { a: ['ok'], b: ['fix'], c: ['ok'] },
                patching: { a: ['ok'], b: ['fix'], c: ['ok'] },
                monitoring: { a: ['ok'], b: ['fix'], c: ['ok'] },
            }, feedback: { correct: 'Your review matches the baseline.', incorrect: 'Compare each cell with the lesson 5 checklist.' } },
            worstC: { type: 'choice', correct: 'roles', feedback: { correct: 'Right.', incorrect: 'One shared administrator role is the biggest risk.' } },
            worstB: { type: 'multi', correct: ['secret', 'identity', 'privileged', 'audit'], feedback: { correct: 'Right.', incorrect: 'A private API endpoint is good; the other four are not.' } },
            vuln: { type: 'choice', correct: 'V1', feedback: { correct: 'Right: critical, known exploited and internet-facing.', incorrect: 'Prioritise by exploitability, exposure and criticality.' } },
            fixV1: { type: 'choice', correct: 'rebuild', feedback: { correct: 'Right.', incorrect: 'Containers are replaced, not patched in place.' } },
            report: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'option B\'s issues (image secret, privileged pods, identity or audit logs)', any: ['privileged', 'image', 'audit'] },
                { label: 'option C\'s shared role', any: ['role'] },
                { label: 'V1', any: ['v1'] },
                { label: 'a recommendation', any: ['recommend'] },
            ], feedback: { correct: 'Review complete. Compare it with the rubric and the debrief.', incorrect: 'Your review is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Option A** meets the baseline in every area reviewed. **Option B** needs four fixes before production: move the database password from the image to the secrets manager, give pods their own workload identities, apply the Restricted (or at least Baseline) Pod Security Standard to the namespace, and collect audit logs; its base image is also 14 months old. **Option C** needs one major fix: a least-privilege role per function instead of one shared administrator role.\n\n**Vulnerabilities:** fix V1 first (critical, known exploited, internet-facing) by rebuilding from an updated base image, rescanning and redeploying; then V3, then V2; V4 is in a development image only and can follow.\n\n**Recommendation (one defensible answer):** A is ready now; C is attractive once roles are split; B needs the most work before it is safe, but could be revisited with a hardened cluster baseline.\n\n## What you have practised\n\nReviewing workloads against a baseline is routine work for cloud security teams. The same checklist applies whatever the compute model; what changes is who owns each item.\n\n**Next:** module 7 automates these checks with posture management.',
    },
};

replaceModule(p.data, 'workloads', {
    slug: 'workloads',
    title: 'Cloud Workload and Compute Security',
    purpose: 'Kanem is deciding whether to run its reporting service on a VM, on Kubernetes or as serverless functions. This module covers VM hardening and patching, container image security, Kubernetes basics, serverless security, and runtime protection and vulnerability management, so you can review any of the three. You finish by reviewing all three options.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written review.',
    prerequisiteKnowledge: 'Modules 1–5.',
    objectives: ['Harden and patch virtual machines with images and routine maintenance', 'Secure container images through their life cycle', 'Apply core Kubernetes security controls', 'Secure serverless functions and their triggers', 'Run runtime protection and vulnerability management across workload types'],
    keyConcepts: ['Golden images and immutable infrastructure', 'Patch management', 'Image scanning, signing and SBOMs', 'Kubernetes RBAC, Pod Security Standards and network policies', 'Workload identity', 'Serverless triggers and per-function roles', 'Runtime protection', 'Vulnerability prioritisation'],
    assessmentCriteria: ['Assesses workloads against the baseline correctly', 'Identifies the critical fixes for each workload type', 'Prioritises vulnerabilities defensibly', 'Writes a clear, justified review'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'workload-deployment-review', lab);

const C = 'workloads';
replaceQuestions(p.data, 'cld-wl-', [
    single('cld-wl-01', C, 'foundation', 'Harden VMs', 'What is a golden image?', ['A paid OS licence', '*A standard, hardened image used to build every VM of a type', 'A backup', 'A container registry'], 'Golden images give consistent, reviewed starting points.'),
    single('cld-wl-02', C, 'foundation', 'Patch VMs', 'NIST SP 800-40 Rev. 4 describes patching as:', ['An emergency activity only', '*Planned preventive maintenance', 'Optional for cloud VMs', 'The provider\'s job in IaaS'], 'Routine, planned patching reduces risk and disruption.'),
    single('cld-wl-03', C, 'intermediate', 'Use immutable infrastructure', 'What is a security benefit of immutable infrastructure?', ['Servers never need updating', '*Changes made to running servers, including an attacker\'s, are wiped by the next deployment', 'It removes logging', 'It makes servers public'], 'Replacing instead of changing removes drift.'),
    single('cld-wl-04', C, 'foundation', 'Secure images', 'Why must secrets never be built into container images?', ['Images become larger', '*Anyone who can pull the image can read them', 'It breaks scanning', 'Registries reject them'], 'Image contents are readable by everyone with access to the image.'),
    multiple('cld-wl-05', C, 'intermediate', 'Secure images', 'Which are good container image practices? Select all that apply.', ['*Minimal, maintained base images', '*Scanning in the pipeline and registry', '*Deploying only signed images from approved registries', 'Running as root by default'], 'Least privilege at run time means non-root wherever possible.'),
    single('cld-wl-06', C, 'intermediate', 'Explain container isolation', 'Why is container isolation weaker than VM isolation?', ['Containers are encrypted', '*Containers on a host share its kernel', 'Containers cannot use networks', 'VMs share kernels'], 'Shared kernels mean host hardening and least privilege matter.'),
    single('cld-wl-07', C, 'foundation', 'Divide Kubernetes responsibility', 'On a managed Kubernetes service, which is the customer\'s responsibility?', ['The control plane', '*Cluster access control and workload configuration', 'The provider\'s data centres', 'The API server binaries'], 'Customers secure configuration, workloads and access.'),
    single('cld-wl-08', C, 'intermediate', 'Restrict workloads', 'Which Kubernetes feature prevents pods from running privileged unless explicitly allowed?', ['Namespaces alone', '*Pod Security Standards enforced at admission', 'Labels', 'Services'], 'Pod Security Standards define Privileged, Baseline and Restricted profiles.'),
    single('cld-wl-09', C, 'intermediate', 'Segment Kubernetes', 'What limits which pods can talk to which?', ['RBAC', '*Network policies', 'Image signing', 'Resource quotas'], 'Network policies are the cluster form of microsegmentation.'),
    single('cld-wl-10', C, 'foundation', 'Secure serverless', 'In serverless computing, which remains the customer\'s responsibility?', ['Patching the host OS', '*Function code, dependencies, configuration and permissions', 'The physical servers', 'The hypervisor'], 'The provider runs the infrastructure; the customer owns what it deploys.'),
    single('cld-wl-11', C, 'intermediate', 'Secure serverless', 'What is the risk of one broad role shared by many functions?', ['Higher cost', '*Compromising any one function gives access intended for all of them', 'Slower start-up', 'None'], 'Per-function least privilege limits blast radius.'),
    single('cld-wl-12', C, 'intermediate', 'Prioritise vulnerabilities', 'Which finding should normally be fixed first?', ['High severity in a development image', '*Critical, known-exploited vulnerability in an internet-facing production workload', 'Medium severity on an isolated VM', 'Low severity in a test function'], 'Exploitability, exposure and criticality drive priority.'),
    single('cld-wl-13', C, 'foundation', 'Use SBOMs', 'What does a software bill of materials (SBOM) list?', ['Cloud costs', '*The components inside an application or image', 'User accounts', 'Network routes'], 'SBOMs make it fast to find where a vulnerable component is used.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/cloud-security/lessons/workloads/secrets-and-workloads.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('cloud module 6 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
