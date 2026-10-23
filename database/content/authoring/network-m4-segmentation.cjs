// Network Security, module 4: Network Segmentation and Zero Trust.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
upsertSources([
    { key: 'nist-sp-800-207a', title: 'SP 800-207A: A Zero Trust Architecture Model for Access Control in Cloud-Native Applications in Multi-Location Environments', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/207/a/final', topic: 'Zero trust for cloud-native applications', sourceType: 'official_standard', versionNote: 'Final, September 2023.', supports: 'Identity-based, application-level policy (further reading).' },
    { key: 'nist-sp-800-215', title: 'SP 800-215: Guide to a Secure Enterprise Network Landscape', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/215/final', topic: 'Enterprise network security', sourceType: 'official_standard', versionNote: 'Final, November 2022.', supports: 'Modern enterprise network architecture and segmentation context.' },
    { key: 'cisa-ztmm', title: 'Zero Trust Maturity Model', publisher: 'CISA', url: 'https://www.cisa.gov/zero-trust-maturity-model', topic: 'Zero trust planning', sourceType: 'official_standard', versionNote: 'Version 2.0 (April 2023); check the page for the current version.', supports: 'Five pillars, cross-cutting capabilities and four maturity stages.' },
    { key: 'ieee-8021q', title: 'IEEE 802.1Q-2022: Bridges and Bridged Networks', publisher: 'IEEE Standards Association', url: 'https://standards.ieee.org/ieee/802.1Q/10323/', topic: 'VLANs', sourceType: 'official_standard', versionNote: '802.1Q-2022. Paid standard: cited by title only.', supports: 'VLAN tagging and bridged networks.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'zones-dmz-and-trust-boundaries', title: 'Zones, DMZs and trust boundaries', minutes: 30, sources: ['nist-sp-800-215', 'nist-sp-800-41r1', 'cis-controls-v81'],
        objectives: ['Group systems into zones by trust and function', 'Distinguish north–south from east–west traffic', 'Design DMZ rules that keep paths inward narrow'],
        checks: [
            { question: 'The public website in the DMZ needs product data. Which design is best?', options: ['DMZ web server → database zone directly on 1433', 'DMZ web server → internal application API on one port; only the application tier reaches the database', 'Move the database into the DMZ', 'Allow the DMZ to reach the internal network on all ports'], answerIndex: 1, explanation: 'A compromised DMZ host should reach as little as possible; one API call is far narrower than direct database access.' },
            { question: 'Which traffic is east–west?', options: ['A customer reaching the public website', 'A staff laptop connecting to an internal application server', 'A laptop browsing the internet', 'An email arriving from the internet'], answerIndex: 1, explanation: 'East–west traffic stays inside the organisation and is the path of lateral movement.' },
            { question: 'Why is a staff laptop usually less trusted than a database server, even though both are internal?', options: ['Laptops are cheaper', 'Laptops browse the web, open email and travel, so they are more exposed to compromise', 'Databases are never attacked', 'Laptops have no IP address'], answerIndex: 1, explanation: 'Trust should follow exposure and function, not physical location.' },
        ] },
    { slug: 'vlans-and-enforcement', title: 'VLANs, subnets and where segmentation is enforced', minutes: 30, sources: ['ieee-8021q', 'cis-benchmarks', 'nist-sp-800-215'],
        objectives: ['Explain why a VLAN alone is not a security control', 'Align VLANs, subnets and zones', 'Apply switch hardening that keeps VLANs separate', 'Choose controls for east–west traffic inside a zone'],
        checks: [
            { question: 'All VLANs route through a core switch with routing enabled and no ACLs. How segmented is the network?', options: ['Fully segmented', 'Effectively flat: every VLAN can reach every other', 'Segmented at layer 7', 'Only the guest VLAN is isolated'], answerIndex: 1, explanation: 'The routing device decides; without filtering, VLANs only separate broadcast domains.' },
            { question: 'Which are sound switch hardening practices for keeping VLANs separate? Select all that apply.', options: ['Disable automatic trunk negotiation on access ports', 'Use VLAN 1 for user devices', 'Allow only the required VLANs on each trunk', 'Shut down unused ports'], answerIndexes: [0, 2, 3], explanation: 'Explicit trunks, pruned VLAN lists and disabled ports reduce layer-2 leakage. VLAN 1 should not carry users.' },
            { question: 'Ten database servers share one VLAN. What limits a compromised database from reaching the other nine?', options: ['The zone firewall', 'Host-based firewalls or microsegmentation on each server', 'The VLAN itself', 'DNS'], answerIndex: 1, explanation: 'Traffic inside a VLAN never reaches the zone firewall, so per-host controls are needed.' },
        ] },
    { slug: 'microsegmentation', title: 'Microsegmentation', minutes: 30, sources: ['nist-sp-800-207', 'nist-sp-800-207a'],
        objectives: ['Explain how microsegmentation differs from zone segmentation', 'Compare host, hypervisor, cloud-native and identity-based enforcement', 'Plan a staged rollout that avoids outages'],
        checks: [
            { question: 'What is the first step in a safe microsegmentation rollout?', options: ['Block everything and wait for complaints', 'Inventory and label workloads, then observe real traffic', 'Buy a new firewall', 'Disable logging to reduce noise'], answerIndex: 1, explanation: 'Policy must be built from known, legitimate flows; enforcing before observing causes outages.' },
            { question: 'Microsegmentation rules are usually expressed in terms of:', options: ['MAC addresses', 'Workload labels or identities (application, environment, role)', 'Cable colours', 'Physical rack positions'], answerIndex: 1, explanation: 'Labels and identities follow the workload wherever it runs.' },
            { question: 'Why enforce in monitoring mode before blocking?', options: ['It is faster', 'To find undocumented dependencies by logging what would be blocked', 'Monitoring mode encrypts traffic', 'Regulators forbid blocking'], answerIndex: 1, explanation: 'Logging would-be blocks reveals flows nobody documented before they cause an outage.' },
        ] },
    { slug: 'zero-trust-architecture', title: 'Zero Trust Architecture', minutes: 35, sources: ['nist-sp-800-207', 'cisa-ztmm', 'nist-sp-800-207a'],
        objectives: ['Explain the zero trust tenets in NIST SP 800-207', 'Describe the policy engine, administrator and enforcement point', 'Plan a zero trust journey using the CISA maturity model', 'Redesign remote access on zero trust principles'],
        checks: [
            { question: 'In NIST SP 800-207, which component makes the access decision?', options: ['Policy enforcement point', 'Policy engine', 'Firewall', 'VPN concentrator'], answerIndex: 1, explanation: 'The policy engine decides; the administrator signals the enforcement point, which allows or blocks the session.' },
            { question: 'Which statements reflect zero trust principles? Select all that apply.', options: ['Access is granted per session to specific resources', 'Being on the internal network is enough to be trusted', 'Decisions use identity, device health and context', 'Communication is secured regardless of network location'], answerIndexes: [0, 2, 3], explanation: 'Zero trust removes implicit trust based on location.' },
            { question: 'A contractor needs the loans application for three months. Which approach best follows zero trust?', options: ['A full VPN account with access to the whole network', 'Application-level access with MFA, device checks, a time limit and logging', 'A shared admin password', 'Open the application to the internet'], answerIndex: 1, explanation: 'Give access to the one resource needed, decided per session, for a limited time.' },
        ] },
    { slug: 'management-plane-and-validation', title: 'Protecting the management plane and validating segmentation', minutes: 30, sources: ['nist-sp-800-115', 'nist-sp-800-53r5', 'cis-controls-v81'],
        objectives: ['Design controls for administrative access to infrastructure', 'Plan segmentation validation with test flows, configuration review and flow logs', 'Record test results as evidence with owners and retest dates'],
        checks: [
            { question: 'Which controls belong in a protected management plane? Select all that apply.', options: ['Admin interfaces reachable only from the management zone', 'Jump hosts or privileged access workstations', 'Telnet for compatibility', 'Named admin accounts with MFA and central logging'], answerIndexes: [0, 1, 3], explanation: 'Telnet sends credentials in clear text; secure protocols only.' },
            { question: 'A segmentation test finds that the DMZ can still reach the database on 1433 through a "temp migration" rule. What should happen?', options: ['Ignore it: migrations are temporary', 'Record a finding with an owner and ticket, remove or narrow the rule, and retest', 'Delete the test record', 'Move the database into the DMZ'], answerIndex: 1, explanation: 'Each failed test becomes a tracked finding with remediation and retesting.' },
            { question: 'Why compare flow logs with the segmentation design?', options: ['To reduce storage', 'Traffic on paths that should be closed reveals misconfigurations or unknown dependencies', 'Flow logs replace firewalls', 'To speed up the network'], answerIndex: 1, explanation: 'Observed traffic is evidence of what the network actually allows.' },
        ] },
];

const zones = ['internet', 'dmz', 'users', 'app', 'db', 'cards', 'mgmt', 'guest'];
const flow = (label, source, destination, protocol, port, expect) => ({ label, source, destination, protocol, port, expect });
const R = (id, label) => ({ id, label });
const zoneOpts = [R('dmz', 'dmz'), R('users', 'users'), R('app', 'app'), R('db', 'db'), R('cards', 'cards'), R('mgmt', 'mgmt'), R('guest', 'guest')];

const lab = {
    slug: 'segmentation-design',
    title: 'Segment the Lagos data centre',
    difficulty: 'intermediate',
    labType: 'architecture_design',
    summary: 'Place Kanem\'s systems into zones, build the inter-zone policy and test it against 24 hidden flows, choose zero trust and management-plane controls, and write the segmentation rationale.',
    minutes: 90,
    moduleSlug: 'segmentation-zero-trust',
    passPercent: 80,
    content: {
        objectives: ['Assign systems to zones by trust and function', 'Build an inter-zone policy that enforces the design', 'Apply zero trust and management-plane principles to access requests', 'Justify a segmentation design and plan its validation'],
        scenario: 'Kanem Financial Services (fictional) is splitting the flat server VLAN in its Lagos data centre into zones. You have the inventory of systems, the approved flows from the architecture board, and three access requests waiting for a decision. The CIO\'s requirement: one compromised system must not take the rest with it.',
        scope: 'Zones: internet, dmz, users, app (application servers), db (databases), cards (card-payment systems), mgmt (management), guest. The firewall evaluates first match; unmatched traffic is denied; return traffic is stateful.',
        instructions: '1. Read **System inventory** and **Approved flows**.\n2. Task 1: place each system in a zone.\n3. Task 2: build the policy. It is tested against 24 flows you cannot see.\n4. Tasks 3–5: decide the access and design questions.\n5. Task 6: write the segmentation rationale. It is saved to your portfolio.\n\nYou need 80% to pass.\n\n:::reveal Advanced challenge (optional)\nIn your rationale, add a microsegmentation rule table for the **cards** zone only: the flows between the card switch nodes, the core banking app and the card processor, as in lesson 3. Which flow would you put in monitoring mode first, and why?\n:::',
        simulationNotice: 'This is a simulation. All systems and data are fictional.',
        workspace: { kind: 'documents', documents: [
            { title: 'System inventory', format: 'markdown', body: '| System | Purpose |\n| --- | --- |\n| WEB-01 | Public website and loan application form |\n| CORE-APP-01/02 | Core banking application servers |\n| CORE-DB-01 | Core banking database (customer accounts) |\n| CARD-SW-01/02 | Card-payment switch |\n| JUMP-01 | Administrators\' jump host |\n| Staff laptops | 380 laptops at head office |\n| Visitor Wi-Fi | Guests in the lobby |' },
            { title: 'Approved flows (architecture board)', format: 'markdown', body: '1. **internet → dmz** TCP 443 (public website).\n2. **dmz → app** TCP 8443 (the website calls the core banking API). The DMZ must **never** reach the databases directly.\n3. **users → app** TCP 443 (staff use the core banking app).\n4. **app → db** TCP 1433 (application to database).\n5. **app → cards** TCP 9443 (core banking calls the card switch).\n6. **cards → internet** TCP 443 (card switch to the card processor).\n7. **mgmt → dmz, app, db, cards** TCP 22 (administration from the jump host only).\n8. **users → internet** TCP 443 and **guest → internet** TCP 443.\n9. Nothing may connect **into mgmt**. app, db and cards must not start connections to the internet except flow 6.\n10. Everything else is blocked.' },
            { title: 'Access requests awaiting a decision', format: 'markdown', body: '**A.** A software contractor needs to support the core banking app for three months and asks for a full VPN account.\n\n**B.** The network team wants to keep the firewall\'s web admin console reachable from the users zone "so they can work from their desks".\n\n**C.** The database team asks for the ten database servers to be able to reach each other freely "because they\'re all in the same zone anyway".' },
        ] },
        tasks: [
            { id: 'placement', points: 2, prompt: 'Place each system in the most appropriate zone.', hints: ['What is internet-facing? What holds customer data? What is the highest-value system?'], input: { type: 'grid', rows: [R('web', 'WEB-01'), R('coreapp', 'CORE-APP-01/02'), R('coredb', 'CORE-DB-01'), R('card', 'CARD-SW-01/02'), R('jump', 'JUMP-01'), R('laptops', 'Staff laptops'), R('wifi', 'Visitor Wi-Fi')], columns: [{ id: 'zone', label: 'Zone', options: zoneOpts }] } },
            { id: 'policy', points: 6, prompt: 'Build the inter-zone policy from **Approved flows**. You can use up to 14 rules; unmatched traffic is denied.', hints: ['Flow 7 needs four rules.', 'Count: the approved flows need 12 allow rules.', 'Check: can the DMZ reach db? Can app reach the internet? Can users reach mgmt?'], input: { type: 'firewall', zones, maxRules: 14 } },
            { id: 'requestA', points: 1, prompt: 'Request A (contractor): what should Kanem do?', hints: ['Lesson 4.'], input: { type: 'choice', options: [R('ztna', 'Give named, MFA-protected access to the core banking app only, from a compliant or managed device, time-limited to the contract and logged'), R('vpn', 'Give a full VPN account, as requested'), R('shared', 'Share an existing staff account'), R('deny', 'Refuse all contractor support')] } },
            { id: 'requestB', points: 1, prompt: 'Request B (firewall console from users): what should Kanem do?', hints: ['Lesson 5.'], input: { type: 'choice', options: [R('jump', 'Refuse: administration goes through JUMP-01 in the management zone with MFA and logging'), R('allow', 'Allow it from the users zone'), R('vpn', 'Allow it from the internet over HTTPS')] } },
            { id: 'requestC', points: 1, prompt: 'Request C (databases talking freely): what is the best response?', hints: ['Lessons 2 and 3: east–west inside a zone.'], input: { type: 'choice', options: [R('micro', 'Allow only the specific replication or cluster flows each database needs, enforced by host firewalls or microsegmentation'), R('allow', 'Allow it: same zone means same trust'), R('vlan', 'Put each database in its own internet-facing VLAN')] } },
            { id: 'rationale', points: 2, prompt: 'Write the **segmentation rationale** for the architecture board: the zones and why, the key boundary decisions, how zero trust applies to remote and third-party access, and how you will validate the design.', hints: ['Explain why the DMZ never reaches db and why cards is separate.', 'Validation: test flows from each zone, configuration review, flow logs.'], input: { type: 'deliverable', title: 'Segmentation rationale', competency: 'Designing and justifying network segmentation with a validation plan', minWords: 160, rubric: ['Explains each zone by trust and function', 'Justifies the critical boundaries (DMZ to db, cards isolation, nothing into mgmt, no outbound internet from app and db)', 'Addresses east–west traffic inside zones', 'Applies zero trust to contractor and admin access', 'Gives a concrete validation plan with expected-allow and expected-deny tests and a schedule', 'States residual risks'], template: 'Zones and why:\n\nKey boundary decisions:\n\nEast–west controls:\n\nRemote and third-party access (zero trust):\n\nValidation plan:\n\nResidual risks:\n' } },
        ],
        reflection: ['Which boundary would cause the most damage if it failed, and how would you detect that failure?', 'What did you trade off between simplicity and isolation?', 'How would you explain microsegmentation to the database team in one sentence?', 'What would make you retest the segmentation before the next scheduled date?'],
    },
    validator: {
        tasks: {
            placement: { type: 'grid', minCorrectRatio: 0.85, cells: { web: { zone: ['dmz'] }, coreapp: { zone: ['app'] }, coredb: { zone: ['db'] }, card: { zone: ['cards'] }, jump: { zone: ['mgmt'] }, laptops: { zone: ['users'] }, wifi: { zone: ['guest'] } }, feedback: { correct: 'Every system is in the right zone.', incorrect: 'Group by exposure and function: internet-facing, application, data, payments, administration, staff and guests.' } },
            policy: { type: 'firewall', flows: [
                flow('internet → dmz HTTPS', 'internet', 'dmz', 'tcp', 443, 'allow'),
                flow('dmz → app API', 'dmz', 'app', 'tcp', 8443, 'allow'),
                flow('users → app HTTPS', 'users', 'app', 'tcp', 443, 'allow'),
                flow('app → db SQL', 'app', 'db', 'tcp', 1433, 'allow'),
                flow('app → cards API', 'app', 'cards', 'tcp', 9443, 'allow'),
                flow('cards → internet processor', 'cards', 'internet', 'tcp', 443, 'allow'),
                flow('mgmt → dmz SSH', 'mgmt', 'dmz', 'tcp', 22, 'allow'),
                flow('mgmt → app SSH', 'mgmt', 'app', 'tcp', 22, 'allow'),
                flow('mgmt → db SSH', 'mgmt', 'db', 'tcp', 22, 'allow'),
                flow('mgmt → cards SSH', 'mgmt', 'cards', 'tcp', 22, 'allow'),
                flow('users → internet HTTPS', 'users', 'internet', 'tcp', 443, 'allow'),
                flow('guest → internet HTTPS', 'guest', 'internet', 'tcp', 443, 'allow'),
                flow('dmz → db SQL (must be blocked)', 'dmz', 'db', 'tcp', 1433, 'deny'),
                flow('users → db SQL', 'users', 'db', 'tcp', 1433, 'deny'),
                flow('users → cards API', 'users', 'cards', 'tcp', 9443, 'deny'),
                flow('dmz → cards API', 'dmz', 'cards', 'tcp', 9443, 'deny'),
                flow('internet → app API', 'internet', 'app', 'tcp', 8443, 'deny'),
                flow('guest → app HTTPS', 'guest', 'app', 'tcp', 443, 'deny'),
                flow('users → mgmt SSH', 'users', 'mgmt', 'tcp', 22, 'deny'),
                flow('app → internet HTTPS', 'app', 'internet', 'tcp', 443, 'deny'),
                flow('db → internet HTTPS', 'db', 'internet', 'tcp', 443, 'deny'),
                flow('cards → app SSH', 'cards', 'app', 'tcp', 22, 'deny'),
                flow('users → app SSH', 'users', 'app', 'tcp', 22, 'deny'),
                flow('internet → dmz SSH', 'internet', 'dmz', 'tcp', 22, 'deny'),
            ], feedback: { correct: 'Your policy enforces the design exactly.', incorrect: 'At least one flow behaved incorrectly. Check each approved flow and each "must not".' } },
            requestA: { type: 'choice', correct: 'ztna', feedback: { correct: 'Right: access to one resource, decided per session, time-limited.', incorrect: 'A full VPN gives network-level access to far more than the contractor needs.' } },
            requestB: { type: 'choice', correct: 'jump', feedback: { correct: 'Right: the management plane is reached only from the management zone.', incorrect: 'Exposing the firewall\'s admin interface to every laptop undermines every rule it enforces.' } },
            requestC: { type: 'choice', correct: 'micro', feedback: { correct: 'Right: east–west inside a zone still follows least privilege.', incorrect: 'Same zone does not mean every server needs to reach every other.' } },
            rationale: { type: 'deliverable', minWords: 160, mustMention: [
                { label: 'the DMZ-to-database boundary', any: ['dmz', 'database', ' db'] },
                { label: 'the cards zone', any: ['card'] },
                { label: 'zero trust or contractor access', any: ['zero trust', 'contractor', 'ztna', 'per session'] },
                { label: 'how you will validate the design', any: ['test', 'validat', 'flow log', 'verify'] },
            ], feedback: { correct: 'Rationale complete. Compare it with the rubric and the debrief.', incorrect: 'Your rationale is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Placement:** WEB-01 → dmz; CORE-APP → app; CORE-DB → db; CARD-SW → cards; JUMP-01 → mgmt; laptops → users; visitor Wi-Fi → guest.\n\n### A policy that passes\n\n```text\n#   Action  Source    Destination  Proto  Port\n1   ALLOW   internet  dmz          tcp    443\n2   ALLOW   dmz       app          tcp    8443\n3   ALLOW   users     app          tcp    443\n4   ALLOW   app       db           tcp    1433\n5   ALLOW   app       cards        tcp    9443\n6   ALLOW   cards     internet     tcp    443\n7   ALLOW   mgmt      dmz          tcp    22\n8   ALLOW   mgmt      app          tcp    22\n9   ALLOW   mgmt      db           tcp    22\n10  ALLOW   mgmt      cards        tcp    22\n11  ALLOW   users     internet     tcp    443\n12  ALLOW   guest     internet     tcp    443\n    (everything else denied and logged)\n```\n\n**Requests:** A, application-level access with MFA, device checks, an end date and logging (zero trust); B, refuse, administration only through JUMP-01; C, allow only the specific cluster or replication flows, enforced per host.\n\n### What a strong rationale covers\n\nZones follow exposure and data: the DMZ is internet-facing and may only call the application API, never the databases, so a compromised website cannot read customer data directly. Cards is separate because it is the highest-value system and needs the smallest set of flows. Nothing connects into mgmt, and administration happens only from JUMP-01 with MFA. Application, database and card systems cannot start internet connections except the card processor flow, which limits command-and-control and exfiltration. Inside zones, host firewalls or microsegmentation limit east–west traffic. Contractors get application-level, time-limited access instead of a VPN. Validation: an expected-allow test for each approved flow and expected-deny tests from every other zone, firewall configuration review, and a check of flow logs for traffic on closed paths, repeated after major changes and at least annually.\n\n## What you have practised\n\nThis is network security architecture: turning a business requirement ("one compromise must not take the rest") into zones, boundaries, policy, access decisions and a validation plan, and documenting why. It reflects NIST SP 800-207 and the CISA Zero Trust Maturity Model.\n\n**Next:** module 5 adds detection: what you do when an attacker moves anyway.',
    },
};

replaceModule(p.data, 'segmentation-zero-trust', {
    slug: 'segmentation-zero-trust',
    title: 'Network Segmentation and Zero Trust',
    purpose: 'Kanem\'s Lagos data centre runs everything in one flat server network, and the CIO wants a design in which one compromised system cannot take the rest with it. This module covers zones, DMZs and trust boundaries; how VLANs and firewalls actually enforce segmentation; microsegmentation for east–west traffic; Zero Trust Architecture as described by NIST and CISA; and protecting and validating the management plane. You finish by designing and justifying the new segmentation.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written rationale.',
    prerequisiteKnowledge: 'Modules 1 and 3 (addressing, routing and firewall policy).',
    objectives: ['Design zones and trust boundaries for a data centre', 'Explain how VLANs, firewalls and host controls enforce segmentation', 'Plan microsegmentation for east–west traffic', 'Apply zero trust principles to remote, third-party and administrative access', 'Validate segmentation with recorded tests and evidence'],
    keyConcepts: ['Zones and trust boundaries', 'DMZ design', 'North–south and east–west traffic', 'VLANs and inter-VLAN enforcement', 'Microsegmentation', 'Zero Trust Architecture (PE, PA, PEP)', 'CISA Zero Trust Maturity Model', 'Management plane protection', 'Segmentation testing'],
    assessmentCriteria: ['Places systems in appropriate zones', 'Builds a policy that passes all test flows', 'Makes zero trust and management-plane decisions correctly', 'Writes a justified design with a validation plan'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1.5 hours of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'segmentation-design', lab);

const C = 'segmentation';
replaceQuestions(p.data, 'net-seg-', [
    single('net-seg-01', C, 'foundation', 'Explain segmentation', 'What is the main purpose of network segmentation?', ['Faster internet access', '*Limiting the blast radius of a compromise', 'Replacing antivirus', 'Encrypting traffic'], 'Segmentation limits what any one compromised system can reach.'),
    single('net-seg-02', C, 'foundation', 'Distinguish traffic directions', 'A compromised laptop connecting to an internal file server is an example of:', ['North–south traffic', '*East–west traffic', 'Out-of-band traffic', 'Broadcast traffic'], 'East–west traffic stays inside the organisation.'),
    single('net-seg-03', C, 'intermediate', 'Design DMZ rules', 'Which DMZ rule is the biggest concern?', ['internet → dmz tcp 443', 'mgmt → dmz tcp 22', '*dmz → db tcp 1433', 'dmz → app tcp 8443'], 'A direct path from an internet-facing host to the database removes a whole layer of protection.'),
    single('net-seg-04', C, 'intermediate', 'Explain VLAN limits', 'Why is a VLAN alone not a security control?', ['VLANs are encrypted', '*Whatever routes between VLANs decides what traffic passes; without filtering, VLANs only separate broadcast domains', 'VLANs only work on Wi-Fi', 'VLANs block all traffic'], 'Enforcement happens on the router or firewall between VLANs.'),
    multiple('net-seg-05', C, 'intermediate', 'Harden switches', 'Which practices help prevent traffic leaking between VLANs? Select all that apply.', ['*Configure trunks explicitly and disable automatic trunk negotiation on access ports', '*Allow only required VLANs on each trunk', 'Place all users in VLAN 1', '*Use an unused VLAN as the native VLAN'], 'These are standard hardening steps for switched networks.'),
    single('net-seg-06', C, 'intermediate', 'Control east–west traffic', 'Which control limits traffic between servers in the same VLAN?', ['The perimeter firewall', '*Host-based firewalls or microsegmentation', 'NAT', 'DHCP snooping'], 'Traffic within a VLAN never passes the zone firewall.'),
    single('net-seg-07', C, 'intermediate', 'Plan microsegmentation', 'What should happen before microsegmentation is enforced?', ['Nothing', '*Workloads are labelled and real traffic is observed to build the policy', 'All logging is disabled', 'Every server is rebooted'], 'Policy built from observed, approved flows avoids outages.'),
    single('net-seg-08', C, 'foundation', 'Explain zero trust', 'Which statement best describes zero trust?', ['Trust everything inside the firewall', '*No implicit trust based on network location; each access is decided on identity, device and context', 'Remove all firewalls', 'Only use VPNs'], 'NIST SP 800-207 removes location-based implicit trust.'),
    single('net-seg-09', C, 'intermediate', 'Describe ZTA components', 'In NIST SP 800-207, which component allows or blocks the session in front of the resource?', ['Policy engine', 'Policy administrator', '*Policy enforcement point', 'Identity provider'], 'The PEP enforces; the PE decides; the PA communicates the decision.'),
    single('net-seg-10', C, 'intermediate', 'Plan zero trust adoption', 'Which is one of the five pillars of the CISA Zero Trust Maturity Model?', ['Firewalls', '*Devices', 'Passwords', 'Datacentres'], 'The pillars are Identity, Devices, Networks, Applications and Workloads, and Data.'),
    single('net-seg-11', C, 'intermediate', 'Redesign remote access', 'How does ZTNA differ from a traditional full VPN?', ['ZTNA is slower', '*ZTNA grants access to specific applications after identity and device checks, rather than to the whole network', 'ZTNA needs no authentication', 'They are identical'], 'Application-level access shrinks what a compromised account or device can reach.'),
    single('net-seg-12', C, 'foundation', 'Protect the management plane', 'Where should network device administration interfaces be reachable from?', ['Anywhere on the internal network', '*Only the management zone, through hardened jump hosts', 'The internet, with a strong password', 'The guest network'], 'The management plane is the highest-value segment.'),
    single('net-seg-13', C, 'intermediate', 'Validate segmentation', 'Which evidence best shows that segmentation works as designed?', ['The network diagram', '*Recorded expected-allow and expected-deny tests from each zone, plus flow logs showing no traffic on closed paths', 'The firewall vendor\'s datasheet', 'The number of VLANs'], 'Validation is evidence, not design documents.'),
]);

save(p);
for (const old of ['zones-and-trust.md', 'securing-the-management-plane.md']) {
    const f = path.join(__dirname, '../programmes/network-security/lessons/segmentation-zero-trust', old);
    if (fs.existsSync(f)) fs.unlinkSync(f);
}
console.log('network module 4 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
