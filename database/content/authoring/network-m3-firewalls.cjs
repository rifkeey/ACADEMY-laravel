// Network Security, module 3 (pilot): Firewalls and Network Access Control.
const fs = require('fs');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
upsertSources([
    { key: 'nist-sp-800-53r5', title: 'SP 800-53 Rev. 5: Security and Privacy Controls for Information Systems and Organizations', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/53/r5/upd1/final', topic: 'Control catalogue', sourceType: 'official_standard', versionNote: 'Revision 5, update 1. Controls cited: SC-7 and SC-7(5), AC-4, AC-6, CM-3, AU family.', supports: 'Boundary protection, deny-by-default, least privilege and change control objectives.' },
    { key: 'cis-controls-v81', title: 'CIS Critical Security Controls Version 8.1', publisher: 'Center for Internet Security', url: 'https://www.cisecurity.org/controls/v8-1', topic: 'Prioritised safeguards', sourceType: 'official_standard', versionNote: 'Version 8.1. Controls cited: 12 (Network Infrastructure Management), 13 (Network Monitoring and Defense).', supports: 'Rule review, network infrastructure configuration and network monitoring objectives.' },
    { key: 'ieee-8021x', title: 'IEEE 802.1X-2020: Port-Based Network Access Control', publisher: 'IEEE Standards Association', url: 'https://standards.ieee.org/ieee/802.1X/7345/', topic: 'Network access control', sourceType: 'official_standard', versionNote: '802.1X-2020. Paid standard: cited by title and roles only.', supports: 'Supplicant, authenticator and authentication server roles; port-based access.' },
    { key: 'attack-t1046', title: 'ATT&CK T1046: Network Service Discovery', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1046/', topic: 'Reconnaissance on the network', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK, page checked on the date shown.', supports: 'Why denied connections are logged.' },
    { key: 'attack-t1071', title: 'ATT&CK T1071: Application Layer Protocol', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1071/', topic: 'Command and control', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK.', supports: 'Egress filtering rationale.' },
    { key: 'attack-t1572', title: 'ATT&CK T1572: Protocol Tunneling', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1572/', topic: 'Command and control', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK.', supports: 'Egress filtering and DNS restrictions.' },
    { key: 'attack-t1048', title: 'ATT&CK T1048: Exfiltration Over Alternative Protocol', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1048/', topic: 'Exfiltration', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK.', supports: 'Egress filtering rationale.' },
    { key: 'attack-t1021-001', title: 'ATT&CK T1021.001: Remote Services: Remote Desktop Protocol', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1021/001/', topic: 'Lateral movement', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK.', supports: 'Why broad RDP rules enable lateral movement.' },
    { key: 'attack-t1190', title: 'ATT&CK T1190: Exploit Public-Facing Application', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1190/', topic: 'Initial access', sourceType: 'open_resource', versionNote: 'Enterprise ATT&CK.', supports: 'Why DMZ-to-internal rules must be narrow.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    {
        slug: 'how-firewalls-decide', title: 'How firewalls decide: stateless, stateful and application-aware', minutes: 30,
        objectives: ['Compare stateless, stateful and application-aware filtering', 'Explain what a stateful firewall can and cannot protect against', 'Choose the right firewall type for a given boundary'],
        sources: ['nist-sp-800-41r1', 'rfc-9293'],
        checks: [
            { question: 'A router ACL allows users to reach a web server on TCP 443. Users report the page never loads. The ACL has no other rules. What is the most likely cause?', options: ['The web server is down', 'The ACL is stateless, so the return traffic from the server is not allowed', 'TCP 443 needs TLS inspection to work', 'ACLs cannot filter TCP'], answerIndex: 1, explanation: 'Stateless filters judge each packet alone, so replies need their own rule. A stateful firewall would allow the replies automatically.', optionFeedback: ['Possible in general, but the question points at the filter design.', 'Right: no rule allows server → user packets back to the ephemeral port.', 'TLS inspection is optional and unrelated to basic connectivity.', 'ACLs filter on TCP ports all the time.'] },
            { question: 'Which threats does a stateful firewall that allows internet → dmz TCP 443 NOT stop? Select all that apply.', options: ['An injection attack against the web application on port 443', 'A forged "reply" packet that matches no existing connection', 'A connection attempt from the internet to the DMZ on TCP 22', 'Malware on the web server downloading a tool over HTTPS if outbound 443 is allowed'], answerIndexes: [0, 3], explanation: 'Stateful inspection tracks conversations, not content. Traffic on an allowed port, inbound or outbound, passes. Unmatched replies and non-allowed ports are blocked.' },
            { question: 'Which control is designed to inspect the content of HTTP requests for attacks such as injection?', options: ['Stateless ACL', 'Stateful firewall', 'Web application firewall (WAF)', 'Network access control'], answerIndex: 2, explanation: 'A WAF understands HTTP requests and their parameters. Network firewalls decide on addresses, ports, applications and users.' },
        ],
    },
    {
        slug: 'rule-order-and-default-deny', title: 'Rule order, shadowing and default deny', minutes: 35,
        objectives: ['Predict the result of a connection against an ordered rule set', 'Identify shadowed, redundant and over-general rules', 'Justify an explicit, logged default deny'],
        sources: ['nist-sp-800-41r1', 'nist-sp-800-53r5', 'attack-t1190'],
        checks: [
            { question: 'Using first-match evaluation, what happens to a connection from users to servers on TCP 3389?', code: '1  ALLOW  users  any      tcp 3389\n2  DENY   any    servers  tcp 3389\n3  DENY   any    any      any any', options: ['Denied by rule 2', 'Allowed by rule 1', 'Denied by rule 3', 'It depends on the server'], answerIndex: 1, explanation: 'Rule 1 matches first (destination "any" includes servers), so the connection is allowed. Rules 1 and 2 overlap with different actions, a correlation problem.' },
            { question: 'A deny rule has had zero hits in 90 days and sits below a broad allow rule for the same zone pair. What is the most likely explanation?', options: ['Nobody has tried that traffic', 'The rule is shadowed by the broad allow above it', 'Logging is broken', 'Deny rules never record hits'], answerIndex: 1, explanation: 'Zero hits is consistent with shadowing: the earlier allow matches the traffic first. Confirm by checking whether the allow covers every field of the deny.' },
            { question: 'Why make the default deny explicit and logged, even when the firewall has an implicit deny?', options: ['It makes the firewall faster', 'It records blocked attempts (scans, malware, misconfigurations) and makes the policy obvious to reviewers', 'Implicit denies do not work on stateful firewalls', 'Standards forbid implicit denies'], answerIndex: 1, explanation: 'The explicit, logged deny turns blocked traffic into evidence and documents the policy. It is good practice rather than a hard rule in standards.' },
        ],
    },
    {
        slug: 'least-privilege-and-egress', title: 'Least privilege, inbound and egress filtering', minutes: 35,
        objectives: ['Translate business requirements into least-privilege rules', 'Explain why egress filtering limits command-and-control and exfiltration', 'Correct rules written in the wrong direction or with excessive scope'],
        sources: ['nist-sp-800-53r5', 'attack-t1071', 'attack-t1572', 'attack-t1048', 'attack-t1021-001'],
        checks: [
            { question: 'IT support must take remote control of staff laptops with RDP. Which rule is least privilege?', options: ['ALLOW users → any tcp 3389', 'ALLOW any → users tcp 3389', 'ALLOW management → users tcp 3389', 'ALLOW management → any tcp any'], answerIndex: 2, explanation: 'Support connects from the management zone to users, on RDP only. The other rules are in the wrong direction or far too broad.', optionFeedback: ['Wrong direction, and lets every laptop RDP to every server and the internet.', 'Lets guest, DMZ and internet sources reach laptop RDP.', 'Right: one source zone, one destination zone, one service.', 'Every port to every zone.'] },
            { question: 'Which outbound rules give an attacker on a compromised server the easiest path to steal data? Select all that apply.', options: ['servers → internet any any', 'servers → internal DNS resolvers udp 53', 'servers → internet tcp any', 'servers → patch proxy tcp 3128'], answerIndexes: [0, 2], explanation: 'Unrestricted outbound access from servers allows command-and-control and exfiltration on any port. DNS to internal resolvers and patching through a proxy are narrow and monitored.' },
            { question: 'A rule reads "ALLOW any → dmz any any — temp vendor access INC-2291 (Nov 2025)". It is now March 2026. What is the main issue?', options: ['The ticket number is in the wrong format', 'A temporary, all-ports rule from the internet into the DMZ was never removed', 'DMZ rules should be at the bottom', 'Vendors should use FTP'], answerIndex: 1, explanation: 'Temporary rules need an expiry, an owner and a check that they were removed. This one exposes every port on every DMZ host.' },
        ],
    },
    {
        slug: 'logging-review-and-change', title: 'Firewall logging, rule review and change management', minutes: 30,
        objectives: ['Select the firewall events that must be logged', 'Review a rule set against usage, ownership and design', 'Write a firewall change request with testing and rollback'],
        sources: ['nist-sp-800-92', 'nist-sp-800-53r5', 'cis-controls-v81', 'attack-t1046'],
        checks: [
            { question: 'Put these change steps in a sensible order for retiring a broad rule that has many hits: (A) disable the broad rule with logging on, (B) analyse logs to see what legitimately uses it, (C) remove the broad rule after the agreed period, (D) add narrow rules for the legitimate traffic above it.', options: ['A, B, C, D', 'B, D, A, C', 'D, B, C, A', 'C, B, D, A'], answerIndex: 1, explanation: 'Understand the traffic, add the narrow replacements, disable the broad rule while watching for breakage, then remove it.' },
            { question: 'Which events should be logged on a perimeter firewall? Select all that apply.', options: ['Connections denied by the default rule', 'Administrator logins and rule changes on the firewall', 'Allowed connections into the server and management zones', 'Every packet of every allowed web-browsing session'], answerIndexes: [0, 1, 2], explanation: 'Denies, administrative actions and allows across sensitive boundaries are the most valuable. Per-packet logging of normal browsing adds cost and noise; connection-level logs are enough.' },
            { question: 'In a rule review, an allow rule has an owner and ticket but zero hits for 180 days. What is the best action?', options: ['Keep it, because it has an owner', 'Confirm with the owner whether it is still needed; if not, remove it through change control', 'Delete it immediately without asking', 'Convert it to a deny rule'], answerIndex: 1, explanation: 'An unused allow rule is pure risk, but confirm before removing: some flows are seasonal (year-end jobs). Removal still goes through change control.' },
        ],
    },
    {
        slug: 'network-access-control', title: 'Network access control: who gets on the network', minutes: 30,
        objectives: ['Describe the 802.1X supplicant, authenticator and authentication server roles', 'Choose suitable access methods for managed devices, legacy devices and guests', 'Position NAC alongside firewalls and zero trust'],
        sources: ['ieee-8021x', 'nist-sp-800-207'],
        checks: [
            { question: 'In 802.1X, which component keeps the switch port closed until authentication succeeds?', options: ['Supplicant', 'Authenticator', 'Authentication server', 'Directory'], answerIndex: 1, explanation: 'The authenticator (switch or access point) controls the port. The supplicant is the client; the authentication server (usually RADIUS) makes the decision.' },
            { question: 'A building has CCTV cameras that cannot run an 802.1X supplicant. What is the most appropriate approach?', options: ['Disable NAC for the whole switch', 'Use MAC authentication bypass and place the cameras in a restricted segment that only reaches the video recorder', 'Put the cameras in the users VLAN', 'Give the cameras the Wi-Fi password'], answerIndex: 1, explanation: 'MAB is the usual fallback, but MAC addresses are easy to copy, so the segment must be tightly restricted and monitored.' },
            { question: 'Why is EAP-TLS generally considered stronger than password-based 802.1X methods?', options: ['It is faster', 'Each device proves its identity with a certificate, so there is no password to phish or guess and devices can be revoked individually', 'It does not need a RADIUS server', 'It works without a supplicant'], answerIndex: 1, explanation: 'Certificate-based authentication removes shared or user passwords from the network login. It needs a certificate infrastructure to manage.' },
        ],
    },
];

const flawed = [
    '#   Action  Source      Destination  Proto  Port  Log  Hits (90 d)  Description',
    '1   ALLOW   internet    dmz          tcp    443   yes  2,914,005    Public website',
    '2   ALLOW   any         dmz          any    any   no   88,120       TEMP vendor troubleshooting INC-2291 (Nov 2025)',
    '3   ALLOW   users       servers      tcp    443   yes  1,204,553    Core banking application',
    '4   ALLOW   dmz         servers      tcp    any   no   410,778      Legacy batch job (owner unknown)',
    '5   ALLOW   management  servers      tcp    22    yes  5,312        Server administration',
    '6   DENY    dmz         servers      tcp    22    yes  0            Block SSH from DMZ',
    '7   ALLOW   users       any          tcp    3389  no   2,045        Remote support',
    '8   ALLOW   servers     internet     any    any   no   97,330       Patching',
    '9   ALLOW   users       internet     tcp    443   yes  8,771,210    Web browsing',
    '10  ALLOW   guest       servers      tcp    443   no   1,870        Lobby kiosk: loan calculator',
    '11  ALLOW   users       management   tcp    8443  no   96           Firewall admin console',
    '12  DENY    any         any          any    any   no   n/a          Default deny',
].join('\n');

const zones = ['internet', 'dmz', 'users', 'guest', 'servers', 'management'];
const flow = (label, source, destination, protocol, port, expect) => ({ label, source, destination, protocol, port, expect });
const ruleOpts = (ids) => ids.map((n) => ({ id: `r${n}`, label: `Rule ${n}` }));

const lab = {
    slug: 'firewall-policy-builder',
    title: 'Firewall rule review and redesign at Kanem head office',
    difficulty: 'intermediate',
    labType: 'firewall_rules',
    summary: 'Audit a real-looking firewall policy full of drift, explain the risks, rebuild it as a least-privilege rule set tested against 22 connections, and write the change request to deploy it.',
    minutes: 90,
    moduleSlug: 'firewalls',
    passPercent: 80,
    content: {
        objectives: ['Identify excessive, shadowed and unlogged rules in an ordered rule set', 'Predict how the firewall decides a connection', 'Rebuild the policy so it allows exactly the required traffic', 'Write a change request that a change board could approve'],
        scenario: 'You have joined the security team at **Kanem Financial Services** (fictional). The internal auditor has asked for a review of **FW-HQ-01**, the stateful firewall between the six head-office zones (see the network diagram in lesson 3). The policy has grown for five years without a review. Your manager wants three things by Friday: what is wrong and why it matters, a corrected rule set, and a change request to deploy it safely.',
        scope: 'Work only from the documents in the workspace. The firewall evaluates rules top to bottom, first match wins, and return traffic is handled statefully. All addresses and data are synthetic.',
        instructions: '1. Read **Current policy** and **Rule 4 traffic summary**.\n2. Answer tasks 1–6 (the review).\n3. Build the replacement policy in task 7 from **Connectivity requirements**. The grader tests it against 22 connections it does not show you; all must behave as required.\n4. Write the change request in task 8. It is saved to your portfolio.\n\nYou need 80% to pass. You can resubmit as often as you like.\n\n:::reveal Advanced challenge (optional)\nThe rule 4 summary contains SSH connections from the DMZ web server at night. Write two sentences in your change request explaining why these need investigating, and what evidence you would collect before the rule is changed. You will meet this traffic again in module 9.\n:::',
        simulationNotice: 'This is a simulation. All data is synthetic and generated by AMANIQ Academy for training. No real systems, people or organisations are involved, and nothing you do here leaves the platform.',
        workspace: {
            kind: 'documents',
            documents: [
                { title: 'Current policy: FW-HQ-01 (exported 3 March 2026)', format: 'code', language: 'text', body: flawed },
                { title: 'Rule 4 traffic summary (last 90 days)', format: 'markdown', body: '| Source | Destination | Port | Connections | Notes |\n| --- | --- | --- | --- | --- |\n| 10.30.0.10 (dmz web server) | 10.10.0.20 (loans database) | TCP 5432 | 409,912 | Constant, business hours and overnight |\n| 10.30.0.10 | 10.10.0.31 (file server) | TCP 445 | 838 | Started 12 Feb 2026 |\n| 10.30.0.10 | 10.10.0.20 | TCP 22 | 27 | Between 01:40 and 03:10 only, since 28 Feb 2026 |\n| 10.30.0.11 (old CMS, decommissioned Jan 2026) | 10.10.0.20 | TCP 5432 | 1 | Single connection, 4 Jan 2026 |\n\nThe digital channels team confirms the website only needs to **read loan products from the database on TCP 5432**.' },
                { title: 'Connectivity requirements (approved by the CIO)', format: 'markdown', body: '1. Anyone on the **internet** may reach the public website in the **dmz** on TCP 443.\n2. **users** may browse the **internet** on TCP 443.\n3. **users** may reach the core banking application in **servers** on TCP 443.\n4. The **dmz** web server may reach the database in **servers** on TCP 5432 and nothing else.\n5. **management** may administer **servers** and the **dmz** over SSH (TCP 22).\n6. IT support in **management** may take remote control of **users** laptops over RDP (TCP 3389).\n7. **servers** may reach the **internet** for DNS (UDP 53) and patch downloads (TCP 443).\n8. **guest** Wi-Fi may reach the **internet** on TCP 80 and 443 only.\n9. No other zone may connect into **management**.\n10. Everything else must be blocked.\n\n*In production, server patching and DNS would go through a proxy and internal resolvers, and rules would name hosts rather than whole zones. The simulator works with zones to keep the exercise focused.*' },
            ],
        },
        tasks: [
            { id: 'excessive', points: 3, prompt: 'Which **ALLOW** rules grant more access than their stated purpose needs, or break the zone design? Select all that apply.', hints: ['Look for "any" in the source, destination or port of an allow rule.', 'Check the direction: who starts the connection?', 'Guest and user zones should never reach servers or management directly unless a requirement says so.'], input: { type: 'multi', options: ruleOpts([1, 2, 3, 4, 5, 7, 8, 9, 10, 11]) } },
            { id: 'shadowed', points: 1, prompt: 'Which rule can **never** take effect because an earlier rule matches all of its traffic?', hints: ['It has zero hits.'], input: { type: 'choice', options: ruleOpts([4, 6, 9, 12]) } },
            { id: 'predict', points: 1, prompt: 'The DMZ web server opens a TCP 22 connection to 10.10.0.20. Which rule decides, and what happens?', hints: ['Walk down from rule 1 and stop at the first full match.'], input: { type: 'choice', options: [{ id: 'r4-allow', label: 'Rule 4 allows it' }, { id: 'r6-deny', label: 'Rule 6 denies it' }, { id: 'r5-allow', label: 'Rule 5 allows it' }, { id: 'r12-deny', label: 'Rule 12 denies it' }] } },
            { id: 'logging', points: 1, prompt: 'What is the main problem with rule 12 as configured?', hints: ['Compare its Log column with the purpose of a default deny.'], input: { type: 'choice', options: [{ id: 'position', label: 'It should be the first rule' }, { id: 'nolog', label: 'Logging is off, so blocked scans and outbound malware attempts leave no evidence' }, { id: 'action', label: 'It should be ALLOW so nothing breaks' }, { id: 'none', label: 'Nothing; default denies should not log' }] } },
            { id: 'replace4', points: 1, prompt: 'Using the rule 4 traffic summary, what should replace rule 4?', hints: ['What does the business owner say the website needs?'], input: { type: 'choice', options: [{ id: 'narrow', label: 'ALLOW dmz web server → loans database TCP 5432, logged; investigate the TCP 22 and 445 traffic separately' }, { id: 'move6', label: 'Keep rule 4 and move rule 6 above it' }, { id: 'all3', label: 'ALLOW dmz → servers TCP 5432, 445 and 22, since all three are in use' }, { id: 'delete', label: 'Delete rule 4 immediately and tell nobody' }] } },
            { id: 'risks', points: 2, prompt: 'For each finding, choose the **main** risk it creates.', hints: ['Rule 8 is about what can leave the network.', 'Rule 11 lets a user zone reach the firewall\'s own administration interface.'], input: { type: 'grid', rows: [{ id: 'r2', label: 'Rule 2 (any → dmz, any port)' }, { id: 'r7', label: 'Rule 7 (users → any, RDP)' }, { id: 'r8', label: 'Rule 8 (servers → internet, any)' }, { id: 'r10', label: 'Rule 10 (guest → servers, 443)' }, { id: 'r11', label: 'Rule 11 (users → management, 8443)' }], columns: [{ id: 'risk', label: 'Main risk', options: [{ id: 'exposure', label: 'Internet-facing services exposed' }, { id: 'lateral', label: 'Lateral movement between internal systems' }, { id: 'egress', label: 'Command-and-control or data exfiltration' }, { id: 'segmentation', label: 'Untrusted zone reaches internal systems' }, { id: 'mgmt', label: 'Management plane exposed' }] }] } },
            { id: 'rules', points: 6, prompt: 'Build the replacement policy for FW-HQ-01 from **Connectivity requirements**. Unmatched traffic is denied, so you do not need a final deny rule. You can use up to 12 rules.', hints: ['Requirements 5, 7 and 8 each need two rules.', 'Count your allow rules: the requirements need 11.', 'Test yourself: can guest reach servers? Can users reach management? Can servers reach the internet on TCP 8443?'], input: { type: 'firewall', zones, maxRules: 12 } },
            { id: 'change', points: 2, prompt: 'Write the **change request** for the change board to retire rules 2, 4 and 8 and deploy your new policy. Use the headings in the box.', hints: ['Lesson 4 lists what a change request must contain.', 'How will you know nothing legitimate broke? Hit counters, test connections and the deny logs are your evidence.'], input: { type: 'deliverable', title: 'Firewall change request', competency: 'Planning and justifying a production security change with testing and rollback', minWords: 150, rubric: ['Names the rules being changed and what replaces each one', 'Explains the business justification and the risk of not changing', 'Identifies who or what could be affected and when the change will happen', 'Describes concrete testing and validation (test connections, hit counters, monitoring)', 'Gives a specific rollback plan', 'Is clear and concise enough for a non-specialist approver'], template: 'Summary:\n\nRules affected (before → after):\n\nBusiness justification and risk of not changing:\n\nBusiness impact and timing:\n\nTesting and validation:\n\nRollback plan:\n\nApproval requested from:\n' } },
        ],
        debrief: '## Worked solution\n\n**Excessive rules: 2, 4, 7, 8, 10 and 11.** Rule 2 exposes every port on every DMZ host to the internet (and other zones); rule 4 lets a compromised web server reach every TCP port on every server; rule 7 lets every laptop open RDP to every server and the internet (lateral movement, ATT&CK T1021.001); rule 8 lets servers send anything anywhere (command-and-control and exfiltration); rule 10 lets an untrusted guest network reach internal servers; rule 11 exposes the firewall administration console to the users zone. Rules 1, 3, 5 and 9 match approved needs.\n\n**Shadowed: rule 6.** Rule 4 (dmz → servers, any TCP port) matches DMZ SSH first, so rule 6 never fires: zero hits confirms it. The DMZ SSH connection in task 3 is therefore **allowed by rule 4**.\n\n**Rule 12** is a correct default deny, but with logging off it destroys the evidence of scans and blocked outbound attempts.\n\n**Rule 4 replacement:** a single logged rule for the web server to the loans database on TCP 5432. The SMB and night-time SSH traffic from the web server is not business traffic: treat it as a possible compromise, preserve logs and escalate before the change, not after.\n\n### A rule set that passes\n\n```text\n#   Action  Source      Destination  Proto  Port\n1   ALLOW   internet    dmz          tcp    443\n2   ALLOW   users       internet     tcp    443\n3   ALLOW   users       servers      tcp    443\n4   ALLOW   dmz         servers      tcp    5432\n5   ALLOW   management  servers      tcp    22\n6   ALLOW   management  dmz          tcp    22\n7   ALLOW   management  users        tcp    3389\n8   ALLOW   servers     internet     udp    53\n9   ALLOW   servers     internet     tcp    443\n10  ALLOW   guest       internet     tcp    80\n11  ALLOW   guest       internet     tcp    443\n    (implicit deny for everything else; log it)\n```\n\nOrder matters less here because no rule overlaps another, which is itself a sign of a clean design.\n\n### What a strong change request includes\n\nThe rules affected with before/after definitions; why now (audit finding, exposure of the DMZ and servers); impact and timing (an agreed low-traffic window, digital channels and IT support informed); testing (run each required connection, watch hit counters on the new rules and the deny log for 7 days); rollback (re-enable the old rules from the saved configuration within 15 minutes); and approval by the change board and the rule owners.\n\n## What you have practised\n\nThis is the work of a firewall or network security review in a real organisation: reading a rule set the way the device does, connecting each finding to a concrete attack path, rebuilding the policy from approved requirements and planning the change so the business keeps running. It maps to NIST SP 800-53 SC-7 and CM-3 and CIS Controls 12 and 13.\n\n**Next:** module 4 replaces zone-wide rules with real segmentation and identity-aware access.',
        reflection: ['Which single finding would you fix first, and what evidence supports that choice?', 'What would happen to the business if you removed rule 4 without the traffic analysis?', 'How would you explain the risk of rule 8 to a manager with no technical background, in two sentences?', 'What would you check before declaring the new policy successful?', 'Which of these findings could have been prevented by a quarterly rule review?'],
        tasksVersion: 2,
    },
    validator: {
        tasks: {
            excessive: { type: 'multi', correct: ['r2', 'r4', 'r7', 'r8', 'r10', 'r11'], feedback: { correct: 'All six excessive rules identified.', incorrect: 'Not quite. Check every allow rule for "any", wrong direction, and untrusted zones reaching internal or management zones. Rules 1, 3, 5 and 9 match approved needs.' } },
            shadowed: { type: 'choice', correct: 'r6', feedback: { correct: 'Right: rule 4 matches DMZ SSH before rule 6 can.', incorrect: 'Find a deny rule below an allow rule that already covers all its traffic. Its hit count is a clue.' } },
            predict: { type: 'choice', correct: 'r4-allow', feedback: { correct: 'Right: rule 4 matches first and allows it, despite rule 6.', incorrect: 'Walk down the list: rule 4 (dmz → servers, any TCP port) matches before rule 6.' } },
            logging: { type: 'choice', correct: 'nolog', feedback: { correct: 'Right: the default deny is where scans and blocked malware show up.', incorrect: 'The deny is in the right place; look at the Log column.' } },
            replace4: { type: 'choice', correct: 'narrow', feedback: { correct: 'Right: allow only what the owner confirms is needed, and treat the rest as suspicious.', incorrect: 'Only TCP 5432 is a confirmed business need. The other traffic from the web server is unexplained.' } },
            risks: { type: 'grid', minCorrectRatio: 0.8, cells: { r2: { risk: ['exposure'] }, r7: { risk: ['lateral'] }, r8: { risk: ['egress'] }, r10: { risk: ['segmentation'] }, r11: { risk: ['mgmt'] } }, feedback: { correct: 'Your risk assessment matches.', incorrect: 'Revisit lesson 3: who can start the connection, and what could an attacker do with it?' } },
            rules: {
                type: 'firewall',
                flows: [
                    flow('internet → dmz HTTPS', 'internet', 'dmz', 'tcp', 443, 'allow'),
                    flow('users → internet HTTPS', 'users', 'internet', 'tcp', 443, 'allow'),
                    flow('users → servers HTTPS', 'users', 'servers', 'tcp', 443, 'allow'),
                    flow('dmz → servers PostgreSQL', 'dmz', 'servers', 'tcp', 5432, 'allow'),
                    flow('management → servers SSH', 'management', 'servers', 'tcp', 22, 'allow'),
                    flow('management → dmz SSH', 'management', 'dmz', 'tcp', 22, 'allow'),
                    flow('management → users RDP', 'management', 'users', 'tcp', 3389, 'allow'),
                    flow('servers → internet DNS', 'servers', 'internet', 'udp', 53, 'allow'),
                    flow('servers → internet HTTPS (patches)', 'servers', 'internet', 'tcp', 443, 'allow'),
                    flow('guest → internet HTTP', 'guest', 'internet', 'tcp', 80, 'allow'),
                    flow('guest → internet HTTPS', 'guest', 'internet', 'tcp', 443, 'allow'),
                    flow('internet → dmz SSH', 'internet', 'dmz', 'tcp', 22, 'deny'),
                    flow('internet → servers HTTPS', 'internet', 'servers', 'tcp', 443, 'deny'),
                    flow('dmz → servers SSH', 'dmz', 'servers', 'tcp', 22, 'deny'),
                    flow('dmz → servers SMB', 'dmz', 'servers', 'tcp', 445, 'deny'),
                    flow('dmz → internet HTTPS', 'dmz', 'internet', 'tcp', 443, 'deny'),
                    flow('users → servers SSH', 'users', 'servers', 'tcp', 22, 'deny'),
                    flow('users → internet RDP', 'users', 'internet', 'tcp', 3389, 'deny'),
                    flow('users → management admin console', 'users', 'management', 'tcp', 8443, 'deny'),
                    flow('guest → servers HTTPS', 'guest', 'servers', 'tcp', 443, 'deny'),
                    flow('guest → internet SMTP', 'guest', 'internet', 'tcp', 25, 'deny'),
                    flow('servers → internet TCP 8443', 'servers', 'internet', 'tcp', 8443, 'deny'),
                ],
                feedback: { correct: 'Your policy allows exactly the required traffic.', incorrect: 'At least one test connection behaved incorrectly. Check each requirement in turn, and watch for "any".' },
            },
            change: {
                type: 'deliverable', minWords: 150,
                mustMention: [
                    { label: 'the rules being changed (for example "rule 2")', any: ['rule 2', 'rule 4', 'rule 8', 'rules 2'] },
                    { label: 'how you will test or validate the change', any: ['test', 'validat', 'verify', 'hit count', 'monitor'] },
                    { label: 'a rollback plan', any: ['rollback', 'roll back', 'back out', 'backout', 'revert', 're-enable'] },
                    { label: 'the business impact', any: ['impact', 'downtime', 'outage', 'affect'] },
                ],
                feedback: { correct: 'Change request complete. Compare it with the rubric and the worked example in the debrief.', incorrect: 'Your change request is not complete yet.' },
            },
        },
    },
};
delete lab.content.tasksVersion;
// The debrief holds the worked solution: keep it with the server-only answer key.
lab.validator.debrief = lab.content.debrief;
delete lab.content.debrief;

replaceModule(p.data, 'firewalls', {
    slug: 'firewalls',
    title: 'Firewalls and Network Access Control',
    purpose: 'An auditor has found that Kanem Financial Services\' head-office firewall has not been reviewed in five years. In this module you learn how firewalls make decisions, how rule sets drift, how to rebuild a policy on least privilege, how to run firewall changes safely, and how network access control keeps unknown devices out. You finish by reviewing and redesigning the real-looking policy yourself.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written change request.',
    prerequisiteKnowledge: 'IP addressing, TCP/UDP ports and the client–server model (module 1). Basic familiarity with network diagrams.',
    objectives: ['Analyse an ordered rule set and predict how it decides a connection', 'Identify shadowed, redundant, over-general and unlogged rules', 'Design a least-privilege policy, including egress filtering, from business requirements', 'Document a firewall change with justification, testing and rollback', 'Recommend network access control methods for managed, legacy and guest devices'],
    keyConcepts: ['Stateless and stateful filtering', 'Application-aware firewalls', 'First-match evaluation', 'Shadowing and redundancy', 'Default deny', 'Egress filtering', 'Rule review and hit counts', 'Change control', '802.1X and MAB'],
    assessmentCriteria: ['Correctly identifies excessive and shadowed rules in a rule set', 'Builds a rule set that passes all test connections', 'Produces a change request with testing and rollback', 'Explains the risk of each finding in business terms'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1.5 hours of lab. The lab\'s night-time DMZ SSH traffic is a deliberate seed for the module 9 incident.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'firewall-policy-builder', lab);

const C = 'firewalls';
replaceQuestions(p.data, 'net-fw-', [
    single('net-fw-01', C, 'foundation', 'Compare filtering types', 'What does a stateful firewall record that a stateless packet filter does not?', ['The application payload of every packet', '*The state of each connection, so replies can be matched to requests', 'The user who sent each packet', 'The physical port of the switch'], 'Stateful firewalls keep a connection table and allow replies that belong to an allowed conversation.'),
    single('net-fw-02', C, 'foundation', 'Explain first-match evaluation', 'A firewall evaluates rules top to bottom. What happens when a connection matches rule 3?', ['Rules 4 onwards are still checked and the most specific wins', '*Rule 3\'s action is applied and later rules are not consulted', 'The connection is logged and dropped', 'The firewall asks the administrator'], 'Most network firewalls stop at the first matching rule.'),
    single('net-fw-03', C, 'intermediate', 'Predict rule outcomes', 'Rules: 1 ALLOW users → any tcp any; 2 DENY users → management tcp any; 3 DENY any → any. What happens to users → management TCP 22?', ['Denied by rule 2', '*Allowed by rule 1', 'Denied by rule 3', 'Depends on logging'], 'Rule 1 matches first because "any" includes management, so rule 2 is shadowed for this traffic.'),
    single('net-fw-04', C, 'intermediate', 'Identify shadowed rules', 'Which pair shows a shadowed rule?', ['*1 ALLOW dmz → servers tcp any; 2 DENY dmz → servers tcp 22', '1 DENY dmz → servers tcp 22; 2 ALLOW dmz → servers tcp any', '1 ALLOW users → internet tcp 443; 2 ALLOW users → servers tcp 443', '1 ALLOW management → servers tcp 22; 2 DENY any → any any'], 'In the first pair, the broad allow matches all SSH traffic first, so the deny never takes effect.'),
    single('net-fw-05', C, 'foundation', 'Justify default deny', 'What does "deny by default, allow by exception" mean for a firewall policy?', ['Everything is allowed unless a rule blocks it', '*Only traffic explicitly allowed by a rule passes; everything else is blocked', 'All traffic is blocked at night', 'Only administrators can change rules'], 'It is the basis of firewall policy in NIST SP 800-41 and SP 800-53 SC-7(5).'),
    single('net-fw-06', C, 'intermediate', 'Design least-privilege rules', 'The web server in the DMZ must read from a PostgreSQL database in the server zone. Which rule is least privilege?', ['ALLOW dmz → servers tcp any', '*ALLOW dmz-web-01 → loans-db-01 tcp 5432', 'ALLOW any → loans-db-01 tcp 5432', 'ALLOW dmz → any tcp 5432'], 'Name the specific source and destination hosts and the single required service.'),
    multiple('net-fw-07', C, 'intermediate', 'Explain egress filtering', 'Which attacker activities does strict egress filtering make harder? Select all that apply.', ['*Command-and-control from a compromised server', '*Exfiltrating data over an unusual port', 'Exploiting an allowed inbound web service', '*Downloading tools from an attacker\'s server'], 'Egress filtering limits what a compromised host can reach outbound. It does not stop exploitation of a service you deliberately expose inbound.'),
    single('net-fw-08', C, 'intermediate', 'Correct rule direction', 'IT support connects from the management zone to staff laptops over RDP. Which rule is correct?', ['ALLOW users → management tcp 3389', 'ALLOW users → any tcp 3389', '*ALLOW management → users tcp 3389', 'ALLOW any → users tcp 3389'], 'Rules are written for the side that starts the connection. Support starts it from management.'),
    single('net-fw-09', C, 'foundation', 'Select events to log', 'Which firewall log is most useful for spotting internal network scanning?', ['Allowed web browsing sessions', '*Connections denied by the default rule', 'Firewall CPU usage', 'VPN login banners'], 'Scans hit many closed or disallowed ports and show up as bursts of denies.'),
    single('net-fw-10', C, 'intermediate', 'Plan a rule review', 'A broad legacy rule has 400,000 hits in 90 days. What should you do first?', ['Delete it to reduce risk', '*Analyse the logs to identify the legitimate traffic it carries', 'Move it to the bottom of the policy', 'Disable logging on it'], 'Understand what depends on it, replace that with narrow rules, then disable and remove the broad rule.'),
    single('net-fw-11', C, 'intermediate', 'Document a firewall change', 'Which element is essential in a firewall change request because it limits the damage if the change goes wrong?', ['The vendor\'s marketing name for the feature', '*A specific rollback plan', 'A list of every rule in the firewall', 'The firewall\'s serial number'], 'A tested rollback plan lets the team restore service quickly; change control (SP 800-53 CM-3) expects it.'),
    single('net-fw-12', C, 'foundation', 'Describe 802.1X roles', 'In 802.1X, which component is usually a RADIUS server?', ['Supplicant', 'Authenticator', '*Authentication server', 'Access switch'], 'The authentication server decides; the authenticator (switch or access point) enforces; the supplicant is the client.'),
    single('net-fw-13', C, 'intermediate', 'Choose NAC methods', 'Why should devices admitted by MAC authentication bypass be placed in restricted segments?', ['MAB is slower than 802.1X', '*MAC addresses are easy to copy, so MAB is weak proof of identity', 'MAB devices cannot use IP addresses', 'MAB only works on Wi-Fi'], 'An attacker can spoof a printer\'s MAC address, so the printer\'s segment must reach only what printers need.'),
    multiple('net-fw-14', C, 'advanced', 'Evaluate firewall findings', 'A rule reads "ALLOW users → management tcp 8443 (firewall admin console)". Which concerns are valid? Select all that apply.', ['*Any compromised laptop can reach the firewall\'s administration interface', '*The management plane should only be reachable from the management zone', 'Port 8443 cannot be used for HTTPS', '*Password guessing against the console becomes possible from every user device'], 'Management interfaces are high-value targets and should be reachable only from dedicated administration systems.'),
]);

save(p);
// Remove the superseded starter lesson file.
const oldLesson = require('path').join(__dirname, '../programmes/') + 'network-security/lessons/firewalls/firewall-policy-design.md';
if (fs.existsSync(oldLesson)) fs.unlinkSync(oldLesson);
console.log('network module 3 applied:', lessons.length, 'lessons,', p.data.questions.length, 'questions');
