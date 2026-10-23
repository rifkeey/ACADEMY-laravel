// Network Security, module 10: Network Security Architecture and Capstone.
const fs = require('fs');
const path = require('path');
const { load, save, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

// All sources used here are already in the register (several were added by cloud-m10-capstone.cjs).
const p = load('network-security');

const lessons = [
    { slug: 'threat-modelling-networks', title: 'Threat modelling a network', minutes: 30, sources: ['threat-modeling-manifesto', 'ms-threat-modeling-stride', 'nist-csf-2'],
        objectives: ['Describe a network as zones, crown jewels, trust boundaries and flows', 'Ask structured questions at each boundary', 'Choose to reduce, accept, transfer or avoid each threat'],
        checks: [
            { question: 'Where should network threat modelling focus first?', options: ['Cable colours', 'Trust boundaries between zones, especially around crown-jewel systems', 'Printer drivers', 'Office layout'], answerIndex: 1, explanation: 'Boundaries are where controls and risks concentrate.' },
            { question: 'Which question reveals paths to crown jewels?', options: ['How fast is the link?', 'If one host in this zone is compromised, what can it reach?', 'Who bought the switch?', 'What colour is the rack?'], answerIndex: 1, explanation: 'Think about what a foothold can reach.' },
            { question: 'Which are valid ways to treat a threat? Select all that apply.', options: ['Reduce it with a control', 'Accept it with a named owner', 'Transfer it, for example through insurance', 'Ignore it without recording anything'], answerIndexes: [0, 1, 2], explanation: 'Avoiding the risky activity is the fourth option.' },
        ] },
    { slug: 'resilient-network-design', title: 'Resilient network design', minutes: 30, sources: ['nist-sp-800-34r1', 'nist-sp-800-160v2r1'],
        objectives: ['Find and remove single points of failure', 'Set recovery objectives', 'Design to anticipate, withstand, recover and adapt', 'Manage device configurations for recovery'],
        checks: [
            { question: 'Kanem has one perimeter firewall carrying every zone boundary. What is the risk?', options: ['None', 'It is a single point of failure for almost every service', 'It is too secure', 'It uses too much power'], answerIndex: 1, explanation: 'Use a high-availability pair.' },
            { question: 'What does "withstand" mean in cyber resiliency?', options: ['Ignore attacks', 'Keep critical functions running during an attack, for example through segmentation', 'Buy insurance', 'Restore from backup'], answerIndex: 1, explanation: 'Contain damage while operating.' },
            { question: 'What makes recovery of network devices faster? Select all that apply.', options: ['Automatic, versioned configuration backups stored off-device', 'Documented rebuild procedures', 'Spare hardware or support contracts', 'Keeping the only copy of the configuration on the device'], answerIndexes: [0, 1, 2], explanation: 'Off-device backups survive device failure.' },
        ] },
    { slug: 'hardening-and-security-assessment', title: 'Hardening and security assessment', minutes: 35, sources: ['cis-benchmarks', 'nist-sp-800-115', 'nist-sp-800-41r1'],
        objectives: ['Harden network devices against a benchmark', 'Describe SP 800-115 assessment techniques', 'Write rules of engagement and validation tests'],
        checks: [
            { question: 'Which must exist before scanning or testing a live network?', options: ['A fast laptop', 'Written authorisation with scope, timing, methods and contacts', 'A verbal "fine" from a colleague', 'Nothing'], answerIndex: 1, explanation: 'Rules of engagement protect everyone.' },
            { question: 'Reviewing firewall rule sets and configurations is which kind of SP 800-115 technique?', options: ['Review', 'Target vulnerability validation', 'Social engineering', 'Denial of service'], answerIndex: 0, explanation: 'Review techniques do not touch live systems.' },
            { question: 'Which are device hardening practices? Select all that apply.', options: ['SSH and HTTPS only for administration', 'Central authentication with MFA', 'Disable unused services and ports', 'Enable Telnet for convenience'], answerIndexes: [0, 1, 2], explanation: 'Telnet sends credentials in clear text.' },
        ] },
    { slug: 'risk-prioritisation', title: 'Risk assessment and prioritisation', minutes: 30, sources: ['nist-sp-800-30r1'],
        objectives: ['Score risks by likelihood and impact', 'Maintain a risk register', 'Build a now/next/later roadmap and accept risk explicitly'],
        checks: [
            { question: 'A weakness is reachable from the internet and affects core banking. How should it be rated?', options: ['Low', 'Medium', 'Critical', 'Not a risk'], answerIndex: 2, explanation: 'High likelihood and high impact.' },
            { question: 'What makes a risk acceptance valid?', options: ['Nobody had time', 'A named owner with authority, a reason, compensating controls and a review date', 'It was mentioned in a meeting', 'The vendor said so'], answerIndex: 1, explanation: 'Acceptance must be explicit.' },
            { question: 'What belongs in the first 30 days of a roadmap?', options: ['Only multi-year projects', 'Critical risks and cheap, high-value quick wins', 'Nothing until the budget is approved', 'Cosmetic changes'], answerIndex: 1, explanation: 'Fix the worst exposure quickly.' },
        ] },
    { slug: 'documenting-architecture', title: 'Documenting the architecture', minutes: 25, sources: ['nist-csf-2', 'cis-controls-v81', 'adr-github'],
        objectives: ['Assemble an architecture pack', 'Draw useful network diagrams', 'Map the design to CSF 2.0 and keep documentation current'],
        checks: [
            { question: 'What does a segmentation rationale explain?', options: ['Cable lengths', 'Why each zone exists and why each allowed flow is needed', 'Staff holidays', 'Device prices'], answerIndex: 1, explanation: 'Every flow has an owner and purpose.' },
            { question: 'Which CSF 2.0 function covers the network security policy and risk acceptance?', options: ['Detect', 'Govern', 'Recover', 'Respond'], answerIndex: 1, explanation: 'Govern sets direction and accountability.' },
            { question: 'When should architecture documents be updated?', options: ['Never', 'As part of change control for every significant change, and after major incidents', 'Only when an auditor asks', 'Every ten years'], answerIndex: 1, explanation: 'Out-of-date documents cause mistakes.' },
        ] },
];

const R = (id, label) => ({ id, label });
const LH = [R('low', 'Low'), R('medium', 'Medium'), R('high', 'High')];

const current = `## Kanem head office: current state (March 2026, after incident INC-2318)

- **Perimeter:** one firewall, **FW-HQ-01**, separates internet, DMZ (10.30.0.0/24), users (10.20.0.0/16), servers (10.10.0.0/24), management (10.99.0.0/24) and guest Wi-Fi (172.16.50.0/24). One internet provider.
- **Firewall rules** (module 3 review, mostly unchanged): rule 2 *any → dmz any* (temporary vendor rule, Nov 2025, log off); rule 4 *dmz → servers tcp any* (legacy batch job, log off); rule 7 *users → any tcp 3389*; rule 8 *servers → internet any* (patching, log off); rule 10 *guest → servers tcp 443* (lobby kiosk); rule 11 *users → management tcp 8443* (firewall console).
- **Servers zone:** core banking application and database, finance file server (payroll, customer loan files), patch server, and other application servers, all in one /24 with no internal filtering.
- **Remote access:** staff VPN places users on the users network with full access to it; vendors use the SSH box dmz-vendor-01 with password login (the way in during INC-2318).
- **Wi-Fi:** staff on WPA3-Enterprise with 802.1X; guest network isolated except for rule 10.
- **Monitoring:** SIEM receives firewall logs (only rules with logging on), IDS alerts, DNS logs and flow records; no alerts on DMZ-to-server traffic.
- **Branches:** Abuja and Kano connect over single IPsec tunnels.
- **Portal:** TLS 1.2 and 1.3 only, certificates monitored.`;

const flowsDoc = `| # | Business need | Owner |
| --- | --- | --- |
| F1 | Customers reach the public website (HTTPS) | Digital channels |
| F2 | Staff use the core banking application (HTTPS) | Operations |
| F3 | IT administers the firewall and switches | IT operations |
| F4 | The software vendor supports its application twice a month | IT operations |
| F5 | The website reads product rates nightly (currently via rule 4 to the core banking database on any port) | Digital channels |
| F6 | Core banking application servers use the core banking database (PostgreSQL 5432) | Operations |
| F7 | Servers download operating system updates | IT operations |`;

const constraints = '| Constraint | Detail |\n| --- | --- |\n| Budget | Year one: one high-availability firewall pair, a second internet link, ZTNA licences for vendors and remote staff; NAC and microsegmentation in year two |\n| Staff | Network team of three; SOC of four analysts |\n| Change windows | Saturday nights; core banking freeze at month end |\n| Regulation | Customer and payroll data is personal data under the NDPA 2023 |';

const lab = {
    slug: 'kanem-network-target-architecture',
    title: 'Capstone: target network architecture for Kanem',
    difficulty: 'advanced',
    labType: 'capstone',
    summary: 'Review Kanem\'s current network after the incident, decide zones and flows, score risks and plan validation, then write a threat model, a target architecture pack and a risk register with roadmap and validation checklist.',
    minutes: 240,
    moduleSlug: 'network-capstone',
    passPercent: 80,
    content: {
        objectives: ['Identify architectural and configuration weaknesses', 'Assign assets to target zones and decide each required flow', 'Score and prioritise risks', 'Plan authorised validation and resilience', 'Write a threat model, a target architecture pack and a risk register with roadmap and validation checklist'],
        scenario: 'After incident INC-2318 (module 9), Kanem Financial Services\' (fictional) board has approved a network security programme. The CIO asks you for a target architecture, a risk register and a funded roadmap.',
        scope: 'Work from the documents. Kanem and all details are fictional. Regulatory points are for discussion with Kanem\'s compliance team, not legal advice.',
        instructions: '1. Read the four documents.\n2. Answer tasks 1–7 (the objective review).\n3. Write the three deliverables in tasks 8–10. Each is saved to your portfolio as part of your capstone.\n\nYou need 80% to pass. The deliverables are checked for completeness; judge their quality against each rubric, and compare with the debrief after passing.',
        simulationNotice: 'This is a design exercise on a fictional network. Nothing is scanned or changed.',
        workspace: { kind: 'documents', documents: [
            { title: 'Current state', format: 'markdown', body: current },
            { title: 'Zone diagram (current)', format: 'markdown', body: '![Kanem head office zones: internet, DMZ, users, guest, servers and management around FW-HQ-01](/content/network-security/kanem-hq-zones.svg)\n\nThe diagram from module 3, lesson 3. The servers zone is one flat /24.' },
            { title: 'Required business flows', format: 'markdown', body: flowsDoc },
            { title: 'Constraints', format: 'markdown', body: constraints },
        ] },
        tasks: [
            { id: 'weaknesses', points: 2, prompt: 'Which are significant weaknesses in the current state? Select all that apply.', hints: ['Check each bullet against modules 3, 4, 6, 7 and 9.'], input: { type: 'multi', options: [R('rule2', 'Rule 2 exposes every DMZ port to any source, with logging off'), R('rule4', 'Rule 4 lets the DMZ reach every TCP port on every server'), R('flatservers', 'The servers zone is flat: crown jewels share a segment with other servers'), R('mgmt', 'Users can reach the firewall console (rule 11) and RDP anywhere (rule 7)'), R('nolog', 'Several allow rules have logging off'), R('spof', 'One firewall and one internet link: single points of failure'), R('vpn', 'Remote staff and vendors get broad network access with weak authentication for vendors'), R('wpa3', 'Staff Wi-Fi uses WPA3-Enterprise with 802.1X'), R('tls', 'The portal accepts only TLS 1.2 and 1.3'), R('siem', 'Logs are collected centrally in a SIEM')] } },
            { id: 'zones', points: 2, prompt: 'Assign each asset to its target zone.', hints: ['Crown jewels get their own restricted zone; vendors never land on the network.'], input: { type: 'grid', rows: [R('web', 'Public website'), R('app', 'Core banking application servers'), R('db', 'Core banking database'), R('file', 'Finance file server'), R('jump', 'Administrator jump host'), R('laptops', 'Staff laptops'), R('kiosk', 'Lobby loan-calculator kiosk'), R('vendor', 'Vendor support access')], columns: [{ id: 'zone', label: 'Target zone', options: [R('dmz', 'DMZ'), R('appsvr', 'Application servers'), R('restricted', 'Restricted (crown jewels)'), R('mgmt', 'Management'), R('users', 'Users'), R('kiosk', 'Kiosk / device zone (isolated)'), R('ztna', 'No network zone: ZTNA to named applications only')] }] } },
            { id: 'flowplan', points: 2, prompt: 'Decide how each required flow (F1–F7) should be handled in the target design.', hints: ['Least privilege, named ports, and brokers for administration and vendors.'], input: { type: 'grid', rows: [R('f1', 'F1 customers → website'), R('f2', 'F2 staff → core banking app'), R('f3', 'F3 IT → firewall and switch administration'), R('f4', 'F4 vendor → its application'), R('f5', 'F5 website → product rates'), R('f6', 'F6 app servers → core banking database'), R('f7', 'F7 servers → OS updates')], columns: [{ id: 'how', label: 'Handling', options: [R('allow', 'Allow, specific ports and hosts, logged'), R('jump', 'Only from the management zone via the jump host with MFA'), R('ztna', 'Only through ZTNA, time-limited, to the named application'), R('proxy', 'Only through the patch server or egress proxy'), R('redesign', 'Deny; redesign as a published read-only rates API on a named port')] }] } },
            { id: 'risks', points: 2, prompt: 'Score likelihood and impact.', hints: ['Lesson 4 lists what raises each.'], input: { type: 'grid', rows: [R('nr1', 'NR-01 Rule 2: any → DMZ any'), R('nr2', 'NR-02 Flat servers zone with crown jewels'), R('nr3', 'NR-03 Single firewall and single internet link'), R('nr4', 'NR-04 Rule 10: kiosk on guest Wi-Fi reaches the servers zone on 443')], columns: [{ id: 'l', label: 'Likelihood', options: LH }, { id: 'i', label: 'Impact', options: LH }] } },
            { id: 'assessment', points: 1, prompt: 'How should the new segmentation be validated?', hints: ['Lesson 3: SP 800-115 and rules of engagement.'], input: { type: 'choice', options: [R('roe', 'Agree written rules of engagement, then run approved reachability tests from each zone in a change window, and commission an independent penetration test of the DMZ and restricted zone'), R('adhoc', 'Run scanners from a laptop whenever convenient, without telling anyone'), R('isp', 'Also scan the internet provider\'s network to be thorough'), R('none', 'No testing: the diagrams show it is correct')] } },
            { id: 'resilience', points: 1, prompt: 'Which resilience change should come first?', hints: ['Which single failure would take down the most?'], input: { type: 'choice', options: [R('ha', 'A high-availability firewall pair and a second internet link on a different route, with failover tested'), R('branch', 'Second tunnels for the branches only'), R('dns', 'A third DNS server'), R('none', 'None; outages are rare')] } },
            { id: 'quickwins', points: 1, prompt: 'Which belongs in the first 30 days?', hints: ['Critical risks, cheap changes.'], input: { type: 'choice', options: [R('rules', 'Remove rule 2, restrict rules 4, 7, 10 and 11, and turn on logging for every boundary allow rule'), R('nac', 'Roll out NAC to every switch port'), R('micro', 'Microsegment every server'), R('rebuild', 'Replace all switches')] } },
            { id: 'threatmodel', points: 2, prompt: '**Deliverable 1: threat model.** Describe zones, crown jewels, trust boundaries and flows, then the main threats at each boundary and how you will treat them (reduce, accept, transfer, avoid).', hints: ['Lesson 1.'], input: { type: 'deliverable', title: 'Kanem network threat model', competency: 'Threat modelling a network', minWords: 150, rubric: ['Identifies crown jewels and trust boundaries', 'Covers exposure, paths to crown jewels, identity, visibility, availability and the management plane', 'States a treatment for each main threat', 'Links threats to evidence from earlier modules (for example INC-2318)'], template: 'Zones and crown jewels:\n\nTrust boundaries and flows:\n\nThreats and treatments:\n\n| Boundary | Threat | Treatment |\n| --- | --- | --- |\n' } },
            { id: 'architecture', points: 3, prompt: '**Deliverable 2: target architecture pack.** Describe the target zones and trust boundaries, the segmentation rationale for flows F1–F7, the key network security policy statements, remote and vendor access, resilience, and the monitoring plan. Map the main controls to CSF 2.0 functions.', hints: ['Lessons 2, 3 and 5, and modules 3, 4, 6, 7 and 9.'], input: { type: 'deliverable', title: 'Kanem target network architecture', competency: 'Designing a secure, resilient network architecture', minWords: 220, rubric: ['Target zones including a restricted zone for crown jewels and a management zone reached only through the jump host', 'Least-privilege, logged flows for F1–F7 with owners, and a redesigned F5', 'Policy: default deny, logging on boundary rules, rule review, change control, hardening standard', 'ZTNA for vendors and remote staff; HA firewalls and a second link', 'Monitoring plan with specific detections (for example DMZ-to-restricted connections, large outbound volumes)', 'Controls mapped to CSF 2.0 functions'], template: 'Target zones and boundaries:\n\nSegmentation rationale (F1–F7):\n\nPolicy statements:\n\nRemote and vendor access:\n\nResilience:\n\nMonitoring plan:\n\nCSF 2.0 mapping:\n' } },
            { id: 'register', points: 2, prompt: '**Deliverable 3: risk register, roadmap and validation checklist.** Give the top risks with likelihood, impact, owner, treatment and date; a now / next / later roadmap within the budget; and tests that prove the design works.', hints: ['Lessons 3 and 4.'], input: { type: 'deliverable', title: 'Kanem network risk register, roadmap and validation checklist', competency: 'Prioritising network risks and planning validation', minWords: 160, rubric: ['Register with likelihood, impact, owner, treatment and date', 'Roadmap with critical quick wins first and dependencies respected', 'Fits the year-one budget and change windows', 'Validation tests for each key design claim, under rules of engagement', 'Explicit risk acceptances with owner and review date'], template: 'Risk register:\n\n| ID | Risk | Likelihood | Impact | Owner | Treatment | Date |\n| --- | --- | --- | --- | --- | --- | --- |\n\nRoadmap:\n- Now (0–30 days):\n- Next (1–6 months):\n- Later (6–18 months):\n\nValidation checklist:\n- [ ]\n\nRisk acceptances:\n' } },
        ],
        reflection: ['Which part of the target design would have stopped INC-2318 earliest?', 'Which risk would you accept, and how would you justify it to the board?', 'What would you monitor to prove the new segmentation stays in place?', 'How will this architecture pack stay current after you leave?'],
    },
    validator: {
        tasks: {
            weaknesses: { type: 'multi', correct: ['rule2', 'rule4', 'flatservers', 'mgmt', 'nolog', 'spof', 'vpn'], feedback: { correct: 'Right: seven weaknesses; WPA3-Enterprise, modern TLS and central logging are strengths.', incorrect: 'Three of the options are strengths. Check each bullet of the current state.' } },
            zones: { type: 'grid', minCorrectRatio: 0.85, cells: { web: { zone: ['dmz'] }, app: { zone: ['appsvr'] }, db: { zone: ['restricted'] }, file: { zone: ['restricted'] }, jump: { zone: ['mgmt'] }, laptops: { zone: ['users'] }, kiosk: { zone: ['kiosk'] }, vendor: { zone: ['ztna'] } }, feedback: { correct: 'Your zones match.', incorrect: 'Crown jewels go in the restricted zone; administration lives in management; vendors get application access, not network access.' } },
            flowplan: { type: 'grid', minCorrectRatio: 0.85, cells: { f1: { how: ['allow'] }, f2: { how: ['allow'] }, f3: { how: ['jump'] }, f4: { how: ['ztna'] }, f5: { how: ['redesign'] }, f6: { how: ['allow'] }, f7: { how: ['proxy'] } }, feedback: { correct: 'Your decisions match.', incorrect: 'Allow only specific, logged flows; broker administration and vendors; never let the DMZ reach the database on any port.' } },
            risks: { type: 'grid', minCorrectRatio: 0.75, cells: { nr1: { l: ['high'], i: ['high'] }, nr2: { l: ['medium', 'high'], i: ['high'] }, nr3: { l: ['low', 'medium'], i: ['high'] }, nr4: { l: ['low', 'medium'], i: ['medium', 'high'] } }, feedback: { correct: 'Your scores are reasonable.', incorrect: 'Rule 2 was used in a real incident; the flat servers zone puts crown jewels one hop from any server; outages are less likely but stop everything.' } },
            assessment: { type: 'choice', correct: 'roe', feedback: { correct: 'Right.', incorrect: 'Testing needs written authorisation and scope; the provider\'s network is never in scope.' } },
            resilience: { type: 'choice', correct: 'ha', feedback: { correct: 'Right: the firewall and link carry every boundary.', incorrect: 'Which component, if it failed, would take down the most services?' } },
            quickwins: { type: 'choice', correct: 'rules', feedback: { correct: 'Right: cheap, fast and they close the worst exposure.', incorrect: 'NAC and microsegmentation are year-two projects in the budget.' } },
            threatmodel: { type: 'deliverable', minWords: 150, mustMention: [
                { label: 'trust boundaries', any: ['boundar'] },
                { label: 'crown jewels', any: ['crown jewel', 'core banking', 'finance file'] },
                { label: 'the DMZ', any: ['dmz'] },
                { label: 'the management plane', any: ['management'] },
                { label: 'treatment (reduce, accept, transfer or avoid)', any: ['reduce', 'accept', 'transfer', 'avoid'] },
            ], feedback: { correct: 'Threat model complete. Compare it with the rubric.', incorrect: 'Your threat model is not complete yet.' } },
            architecture: { type: 'deliverable', minWords: 220, mustMention: [
                { label: 'a restricted zone', any: ['restricted'] },
                { label: 'the jump host', any: ['jump'] },
                { label: 'ZTNA or vendor access', any: ['ztna', 'zero trust', 'vendor'] },
                { label: 'logging or the SIEM', any: ['log', 'siem'] },
                { label: 'default deny', any: ['default deny', 'default-deny'] },
                { label: 'CSF 2.0 functions', any: ['govern', 'csf'] },
            ], feedback: { correct: 'Architecture pack complete. Compare it with the rubric and the debrief.', incorrect: 'Your architecture pack is not complete yet.' } },
            register: { type: 'deliverable', minWords: 160, mustMention: [
                { label: 'owners', any: ['owner'] },
                { label: 'likelihood and impact', any: ['likelihood', 'impact'] },
                { label: 'the first 30 days', any: ['30 days', 'now', 'quick win'] },
                { label: 'validation tests', any: ['validat', 'test'] },
            ], feedback: { correct: 'Register, roadmap and checklist complete.', incorrect: 'Your register, roadmap and checklist are not complete yet.' } },
        },
        debrief: '## Worked solution (one good answer; others are possible)\n\n**Weaknesses:** rules 2 and 4 (the path in INC-2318), the flat servers zone, user access to management and RDP everywhere, unlogged allow rules, single firewall and link, broad VPN and weak vendor access. WPA3-Enterprise, modern TLS and the SIEM are strengths.\n\n**Target zones**\n\n| Zone | Contents | Reached from |\n| --- | --- | --- |\n| DMZ | Public website | Internet on 443 only |\n| Application servers | Core banking app, other app servers, patch server | Users on 443 to the app; DMZ only to the new rates API |\n| Restricted | Core banking database, finance file server | Application servers on named ports only |\n| Management | Jump host, admin workstations | IT staff via MFA; device management only from here |\n| Users | Staff laptops | Internet via proxy; app servers on 443 |\n| Kiosk | Lobby kiosk | One published calculator endpoint only |\n| Guest | Visitors | Internet only |\n| (none) | Vendors and remote staff | ZTNA to named applications, time-limited for vendors |\n\n**Flows:** F1, F2 and F6 allowed on named ports and logged; F3 only via the jump host with MFA; F4 through ZTNA; F5 redesigned as a read-only rates API (the DMZ never reaches the database); F7 through the patch server or egress proxy.\n\n**Policy statements:** default deny at every boundary; every boundary allow rule logs; every rule has an owner, purpose and review date (temporary rules expire automatically); quarterly rule review; changes through change control with rollback; devices hardened to CIS benchmarks with central MFA authentication.\n\n**Monitoring plan:** SIEM detections for any connection into the restricted zone from outside the application servers, any DMZ connection to internal zones other than the rates API, large outbound volumes, management access not from the jump host, and logging changes on firewalls.\n\n**Roadmap:** *Now:* remove rule 2; restrict rules 4, 7, 10 and 11; log every boundary rule; retire dmz-vendor-01 and move the vendor to ZTNA. *Next:* HA firewall pair and second link; split servers into application and restricted zones; ZTNA for remote staff. *Later (year two):* NAC and microsegmentation of the restricted zone.\n\n**Validation:** approved tests under rules of engagement: guest reaches nothing internal; the DMZ reaches only the rates API; the users zone cannot reach management except via the jump host; every boundary rule produces SIEM logs; firewall failover completes within the agreed time; an independent penetration test of the DMZ and restricted zone.\n\n**CSF 2.0:** Govern (policy, owners, acceptances), Identify (asset and flow inventory, register), Protect (zones, rules, ZTNA, hardening), Detect (logging and detections), Respond (module 9 playbooks), Recover (HA, configuration backups, tested failover).\n\n## What you have practised\n\nThe whole job of a network security architect: turning evidence and requirements into a defensible design, a funded plan and tests that prove it. Keep these deliverables for your portfolio.',
    },
};

replaceModule(p.data, 'network-capstone', {
    slug: 'network-capstone',
    title: 'Network Security Architecture and Capstone',
    purpose: 'The capstone brings the programme together. Five lessons cover threat modelling a network, resilient design, hardening and security assessment, risk prioritisation and documenting the architecture. Then you review Kanem\'s network after the module 9 incident and produce a threat model, a target architecture pack and a risk register with roadmap and validation checklist.\n\n**To complete this module:** finish all five lessons and pass the capstone lab (80%), including all three deliverables.',
    prerequisiteKnowledge: 'Modules 1–9.',
    objectives: ['Threat-model a network', 'Design for resilience', 'Harden devices and plan authorised assessment', 'Prioritise risks into a roadmap', 'Document a target architecture and map it to CSF 2.0'],
    keyConcepts: ['Crown jewels and trust boundaries', 'Reduce, accept, transfer, avoid', 'Single points of failure', 'Cyber resiliency', 'Device hardening benchmarks', 'SP 800-115 and rules of engagement', 'Likelihood and impact', 'Risk register and roadmap', 'Architecture pack', 'CSF 2.0 mapping'],
    assessmentCriteria: ['Identifies weaknesses', 'Assigns zones and flows correctly', 'Scores risks sensibly', 'Writes a complete threat model, architecture pack and risk register with roadmap and checklist'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus about 4 hours for the capstone. The deliverables are checked for completeness only; an instructor review of capstone deliverables is a recommended follow-up (see docs/NETWORK_CLOUD_PLAN.md).',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'kanem-network-target-architecture', lab);

const C = 'network-capstone';
replaceQuestions(p.data, 'net-cap-', [
    single('net-cap-01', C, 'foundation', 'Threat-model networks', 'What are "crown jewels" in a network threat model?', ['The most expensive switches', '*The systems and data whose compromise would hurt the organisation most', 'Guest Wi-Fi users', 'Backup tapes only'], 'Protect them first.'),
    single('net-cap-02', C, 'intermediate', 'Threat-model networks', 'Which question best reveals lateral risk?', ['How old is the firewall?', '*If a host in this zone is compromised, what can it reach?', 'Who manages the budget?', 'Which vendor sold the cables?'], 'Paths to crown jewels.'),
    single('net-cap-03', C, 'foundation', 'Treat risk', 'Buying cyber insurance is an example of which risk treatment?', ['Reduce', 'Accept', '*Transfer', 'Avoid'], 'Part of the impact is transferred.'),
    single('net-cap-04', C, 'foundation', 'Design for resilience', 'Which removes a single point of failure at the perimeter?', ['A bigger firewall', '*A high-availability firewall pair with tested failover', 'More firewall rules', 'Disabling logging'], 'Redundancy plus testing.'),
    single('net-cap-05', C, 'intermediate', 'Design for resilience', 'Why store device configuration backups off the device?', ['It is required by law', '*So the configuration survives device failure or compromise', 'It speeds up routing', 'It saves licences'], 'Recovery depends on it.'),
    single('net-cap-06', C, 'foundation', 'Harden devices', 'Which protocol should replace SNMPv1/v2c?', ['Telnet', '*SNMPv3', 'FTP', 'HTTP'], 'SNMPv3 adds authentication and encryption.'),
    single('net-cap-07', C, 'intermediate', 'Plan assessment', 'Which SP 800-115 technique group shows whether a weakness can really be exploited?', ['Review', 'Target identification and analysis', '*Target vulnerability validation', 'Documentation'], 'Penetration testing validates weaknesses.'),
    multiple('net-cap-08', C, 'intermediate', 'Plan assessment', 'What must rules of engagement include? Select all that apply.', ['*Scope of systems in and out', '*Timing and blackout periods', '*Emergency contacts', 'The tester\'s favourite tools only'], 'Also methods, data handling and sign-off.'),
    single('net-cap-09', C, 'foundation', 'Score risk', 'In a likelihood-impact matrix, high likelihood and high impact gives which rating?', ['Low', 'Medium', 'High', '*Critical'], 'Top-right of the matrix.'),
    single('net-cap-10', C, 'intermediate', 'Prioritise', 'Which item belongs in the first 30 days?', ['A two-year microsegmentation programme', '*Removing an internet-exposed temporary rule', 'Replacing every switch', 'Writing a five-year strategy'], 'Critical and cheap first.'),
    single('net-cap-11', C, 'intermediate', 'Accept risk', 'Which is a valid risk acceptance?', ['"We will get to it"', '*A named owner with authority accepts it, with a reason, compensating controls and a review date', 'An email from the vendor', 'Silence'], 'Acceptance must be explicit.'),
    single('net-cap-12', C, 'foundation', 'Document architecture', 'What is a segmentation rationale?', ['A list of switches', '*An explanation of why each zone exists and why each allowed flow is needed', 'A price list', 'An incident report'], 'Each flow has a purpose and owner.'),
    single('net-cap-13', C, 'intermediate', 'Map to frameworks', 'Configuration backups and tested failover map to which CSF 2.0 function?', ['Govern', 'Identify', 'Detect', '*Recover'], 'They restore capability.'),
    single('net-cap-14', C, 'intermediate', 'Design zones', 'How should a vendor that supports one application get access?', ['A permanent rule from the internet to the DMZ', '*ZTNA to that application only, time-limited and logged', 'A shared admin password', 'Full VPN access'], 'Application access, not network access.'),
    single('net-cap-15', C, 'foundation', 'Validate designs', 'Which is a good validation test for "guests cannot reach internal systems"?', ['"The diagram shows it"', '*An approved test from a guest-network laptop showing no internal address responds', 'Asking guests', 'Checking the Wi-Fi password'], 'A runnable test under rules of engagement.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/network-capstone/capstone-overview.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 10 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
