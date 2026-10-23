// Network Security, module 2: Network Threats and Attack Surfaces.
// Taught from the defender's side: what each threat risks, the signs it leaves and the controls that
// prevent or limit it. The lab maps threats to signs and controls; it contains no attack data.
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const r = 'open_resource';
const att = (id, path, title, supports) => ({ key: `attack-${id}`, title: `ATT&CK ${title}`, publisher: 'MITRE', url: `https://attack.mitre.org/${path}/`, topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page; ATT&CK is versioned.', supports });
upsertSources([
    att('t1595', 'techniques/T1595', 'T1595: Active Scanning', 'Detection and mitigation notes.'),
    att('t1557', 'techniques/T1557', 'T1557: Adversary-in-the-Middle', 'Detection and mitigation notes.'),
    att('t1498', 'techniques/T1498', 'T1498: Network Denial of Service', 'Detection and mitigation notes.'),
    att('t1499', 'techniques/T1499', 'T1499: Endpoint Denial of Service', 'Detection and mitigation notes.'),
    att('t1110-003', 'techniques/T1110/003', 'T1110.003: Brute Force: Password Spraying', 'Detection and mitigation notes.'),
    att('t1110-004', 'techniques/T1110/004', 'T1110.004: Brute Force: Credential Stuffing', 'Detection and mitigation notes.'),
    att('t1078', 'techniques/T1078', 'T1078: Valid Accounts', 'Detection and mitigation notes.'),
    att('t1021', 'techniques/T1021', 'T1021: Remote Services', 'Detection and mitigation notes.'),
    att('t1071-001', 'techniques/T1071/001', 'T1071.001: Application Layer Protocol: Web Protocols', 'Detection and mitigation notes.'),
    att('t1573', 'techniques/T1573', 'T1573: Encrypted Channel', 'Detection and mitigation notes.'),
    att('t1102', 'techniques/T1102', 'T1102: Web Service', 'Detection and mitigation notes.'),
    att('ta0011', 'tactics/TA0011', 'TA0011: Command and Control (tactic)', 'Overview of the tactic and its mitigations.'),
    { key: 'rfc-2827', title: 'RFC 2827 (BCP 38): Network Ingress Filtering', publisher: 'IETF (RFC Editor)', url: 'https://www.rfc-editor.org/rfc/rfc2827', topic: 'Anti-spoofing', sourceType: 'official_standard', versionNote: 'BCP 38; updated by RFC 3704.', supports: 'Filtering forged source addresses at network edges.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'reconnaissance-and-scanning', title: 'Reconnaissance and scanning', minutes: 30, sources: ['attack-t1595', 'attack-t1046', 'cis-controls-v81'],
        objectives: ['Distinguish passive and active reconnaissance', 'Recognise scanning in firewall and IDS logs', 'Reduce what reconnaissance can learn'],
        checks: [
            { question: 'Which kind of reconnaissance leaves no trace in your own logs?', options: ['Probing your public addresses', 'Searching public sources such as DNS records and job adverts', 'Internal scanning', 'Connection attempts to many ports'], answerIndex: 1, explanation: 'Passive reconnaissance never touches your network.' },
            { question: 'An internal host suddenly tries many ports on many servers. What is the right first step?', options: ['Ignore it', 'Check whether an authorised scanner or change ticket explains it', 'Shut down the servers', 'Block the whole users zone'], answerIndex: 1, explanation: 'Authorised scanning looks the same; context decides.' },
            { question: 'Which reduce what reconnaissance can learn? Select all that apply.', options: ['Exposing only the services that are needed', 'Removing old DNS records and version banners', 'Default-deny firewall rules', 'Publishing the internal network diagram'], answerIndexes: [0, 1, 2], explanation: 'Less exposed means less to learn.' },
        ] },
    { slug: 'spoofing-and-interception', title: 'Spoofing and interception', minutes: 30, sources: ['attack-t1557', 'rfc-2827', 'rfc-2131'],
        objectives: ['Explain why some protocols can be impersonated', 'Recognise signs of spoofing', 'Choose controls that prevent or limit it'],
        checks: [
            { question: 'Which control protects traffic even if it is redirected?', options: ['A faster switch', 'TLS with certificate validation', 'Static IP addresses', 'A longer Wi-Fi password'], answerIndex: 1, explanation: 'Encryption with certificate checking is the backstop.' },
            { question: 'Which switch features block false DHCP servers and forged address-resolution replies?', options: ['Port mirroring and trunking', 'DHCP snooping and dynamic ARP inspection', 'Spanning tree', 'Link aggregation'], answerIndex: 1, explanation: 'Both rely on the table of trusted address assignments.' },
            { question: 'Which suggest spoofing? Select all that apply.', options: ['The gateway\'s hardware address suddenly changes', 'Certificate warnings for internal sites', 'A familiar Wi-Fi name from an unknown access point', 'A routine password change'], answerIndexes: [0, 1, 2], explanation: 'Each points to impersonation.' },
        ] },
    { slug: 'denial-of-service', title: 'Denial of service', minutes: 25, sources: ['attack-t1498', 'attack-t1499', 'rfc-2827'],
        objectives: ['Distinguish volumetric, state-exhaustion and application-layer denial of service', 'Tell an attack from a legitimate surge', 'Prepare protection and a response runbook'],
        checks: [
            { question: 'Why must protection against very large floods sit upstream of your internet link?', options: ['It is cheaper', 'Once the link is full, nothing behind it can help', 'Firewalls cannot log floods', 'Floods only affect DNS'], answerIndex: 1, explanation: 'Scrubbing happens before traffic reaches you.' },
            { question: 'Salary day doubles traffic to the portal. How do you avoid mistaking it for an attack?', options: ['Block all new users', 'Compare with baselines and a calendar of business events', 'Turn off monitoring', 'Assume every surge is an attack'], answerIndex: 1, explanation: 'Know what normal peaks look like.' },
            { question: 'Which help against application-layer floods? Select all that apply.', options: ['A CDN and web application firewall', 'Rate limits on logins and expensive pages', 'Autoscaling', 'Removing all monitoring'], answerIndexes: [0, 1, 2], explanation: 'Absorb, filter and limit.' },
        ] },
    { slug: 'credential-attacks-and-lateral-movement', title: 'Credential attacks and lateral movement', minutes: 30, sources: ['attack-t1110', 'attack-t1110-003', 'attack-t1110-004', 'attack-t1078', 'attack-t1021'],
        objectives: ['Recognise credential misuse in authentication logs', 'Recognise unusual movement between systems', 'Reduce both with MFA, segmentation and privilege controls'],
        checks: [
            { question: 'Failed sign-ins spread across many accounts from one source, a few each, suggest what?', options: ['A forgotten password', 'Password spraying', 'A backup job', 'A DNS fault'], answerIndex: 1, explanation: 'Few attempts per account avoid lockouts.' },
            { question: 'Which control most reduces the value of a stolen password?', options: ['A longer screensaver timeout', 'Phishing-resistant MFA', 'A bigger firewall', 'More VLANs'], answerIndex: 1, explanation: 'A password alone is then not enough.' },
            { question: 'Which limit how far an intruder can move from one compromised machine? Select all that apply.', options: ['Administration only from a management zone through a jump host', 'Unique local administrator passwords', 'Segmentation between zones', 'One shared admin password for every server'], answerIndexes: [0, 1, 2], explanation: 'A shared password lets one capture open everything.' },
        ] },
    { slug: 'command-and-control', title: 'Command and control', minutes: 30, sources: ['attack-ta0011', 'attack-t1071-001', 'attack-t1071-004', 'attack-t1572', 'attack-t1568', 'attack-t1573', 'attack-t1102'],
        objectives: ['Explain why malicious software needs to communicate out', 'Recognise behavioural signs in outbound traffic', 'Use egress controls and detections to make it harder and more visible'],
        checks: [
            { question: 'Why is outbound traffic a good place to find an intrusion?', options: ['It is always encrypted', 'Malicious software usually has to talk out to its operator', 'Inbound traffic is never logged', 'It is smaller'], answerIndex: 1, explanation: 'Watch the exits.' },
            { question: 'Which control forces outbound traffic through points you monitor?', options: ['Allow-all outbound rules', 'Egress filtering with proxies and internal resolvers', 'Disabling DNS logs', 'Public IPs on every laptop'], answerIndex: 1, explanation: 'Default-deny outbound, through watched routes.' },
            { question: 'Which together justify escalating a laptop? Select all that apply.', options: ['Regular connections at fixed intervals', 'A destination domain first seen three days ago', 'Traffic bypassing the proxy at night', 'Reading the company intranet at lunch'], answerIndexes: [0, 1, 2], explanation: 'Several signs on one host.' },
        ] },
];

const module2 = {
    slug: 'network-threats',
    title: 'Network Threats and Attack Surfaces',
    purpose: 'Defenders need to know what they are defending against. This module covers the main network threats from the defender\'s side: reconnaissance and scanning, spoofing and interception, denial of service, credential attacks and lateral movement, and command and control. For each one you learn what is at risk, the signs it leaves and the controls that prevent or limit it. You finish by matching threats to their signs and controls for Kanem and prioritising the fixes.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written threat summary.',
    prerequisiteKnowledge: 'Module 1.',
    objectives: ['Describe the main network threats and what they put at risk', 'Recognise the signs each threat leaves in logs and monitoring', 'Choose preventive and detective controls for each threat', 'Use MITRE ATT&CK to name and look up behaviours', 'Prioritise controls for an organisation'],
    keyConcepts: ['Attack surface', 'Passive and active reconnaissance', 'Spoofing and interception', 'Volumetric, state and application denial of service', 'Password spraying and credential stuffing', 'Lateral movement', 'Command and control', 'Beaconing', 'Egress filtering', 'MITRE ATT&CK'],
    assessmentCriteria: ['Matches threats to their signs', 'Matches threats to effective controls', 'Distinguishes preventive and detective controls', 'Prioritises controls sensibly', 'Writes a clear threat summary'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab. Threats are taught from the defender\'s side only: risks, signs and controls.',
    contentStatus: 'complete',
    lessons,
};
replaceModule(p.data, 'network-threats', module2);

const R = (id, label) => ({ id, label });
const THREATS = [R('recon', 'Reconnaissance and scanning'), R('spoof', 'Spoofing and interception on the LAN'), R('dos', 'Denial of service against the portal'), R('spray', 'Password spraying against remote access'), R('lateral', 'Lateral movement between internal systems'), R('c2', 'Command and control from an infected laptop')];
const controlsDoc = '| Area | Kanem today |\n| --- | --- |\n| Remote access | VPN with MFA, **except a legacy group of 35 accounts exempted in 2024** |\n| Access switches | No DHCP snooping or dynamic ARP inspection; no 802.1X on wired ports |\n| Web traffic | Proxy for browsers, but **outbound HTTPS from any internal host is also allowed directly** by the firewall |\n| DNS | Internal resolvers; direct DNS to the internet not blocked |\n| Internet link | One provider; no DDoS protection contract |\n| Administration | RDP allowed from the users zone to any host (module 3, rule 7); shared local administrator password on all workstations |\n| Monitoring | SIEM with firewall, IDS (module 5), VPN and sign-in logs; no alert for many failed sign-ins across accounts |\n| Encryption | TLS on all internal web applications; certificate warnings appear on two old intranet sites and staff are told to click through |';

const lab = {
    slug: 'threat-control-mapping',
    title: 'Map Kanem\'s threats to signs and controls',
    difficulty: 'foundation',
    labType: 'control_mapping',
    summary: 'Match six network threats to the signs they leave, the controls that stop them and their ATT&CK entries, then find the gaps in Kanem\'s current controls and prioritise the fixes.',
    minutes: 50,
    moduleSlug: 'network-threats',
    passPercent: 80,
    content: {
        objectives: ['Match threats to their signs', 'Match threats to effective controls', 'Tell preventive and detective controls apart', 'Name behaviours with ATT&CK', 'Find and prioritise control gaps'],
        scenario: 'Kanem Financial Services\' (fictional) security manager wants a one-page view of the main network threats, how the team would notice each one, and which controls are missing. You prepare it from the lessons and the current-controls summary.',
        scope: 'Work from the lessons and the document. Kanem is fictional. This lab contains no attack data or techniques; it is about recognising and controlling threats.',
        instructions: '1. Read the current-controls summary.\n2. Answer tasks 1–6.\n3. Write the threat summary in task 7. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a paper exercise on a fictional organisation.',
        workspace: { kind: 'documents', documents: [{ title: 'Kanem: current network controls (summary)', format: 'markdown', body: controlsDoc }] },
        tasks: [
            { id: 'signs', points: 2, prompt: 'Match each threat to the sign it most typically leaves.', hints: ['Each lesson has a "signs" table.'], input: { type: 'grid', rows: THREATS, columns: [{ id: 'sign', label: 'Typical sign', options: [R('ports', 'Many connection attempts to many ports or hosts in a short time'), R('gateway', 'The gateway\'s hardware address changes, or certificate warnings appear'), R('link', 'Link or connection tables near capacity, errors rising, with no matching business event'), R('failures', 'Failed sign-ins spread across many accounts from one source'), R('admin', 'An account or workstation reaching many systems over administration protocols'), R('beacon', 'Regular outbound connections to a new, rare destination')] }] } },
            { id: 'controls', points: 2, prompt: 'Match each threat to the control that best prevents or limits it.', hints: ['Each lesson has a "preventing" or "reducing" table.'], input: { type: 'grid', rows: THREATS, columns: [{ id: 'control', label: 'Best control', options: [R('exposure', 'Expose only needed services; default-deny rules'), R('snoop', 'TLS with certificate validation, plus DHCP snooping and ARP inspection'), R('scrub', 'Upstream scrubbing, CDN and rate limits'), R('mfa', 'Phishing-resistant MFA with no exemptions'), R('tier', 'Segmentation, jump host and unique local admin passwords'), R('egress', 'Egress filtering through proxies and internal resolvers')] }] } },
            { id: 'kind', points: 2, prompt: 'Is each control mainly preventive or detective?', hints: ['Preventive stops it happening; detective tells you it happened.'], input: { type: 'grid', rows: [R('k1', 'Phishing-resistant MFA'), R('k2', 'SIEM alert on failed sign-ins across many accounts'), R('k3', 'DHCP snooping'), R('k4', 'IDS alert for internal port sweeps'), R('k5', 'Default-deny egress rules'), R('k6', 'Alert on regular connections to newly seen domains')], columns: [{ id: 'type', label: 'Type', options: [R('prev', 'Preventive'), R('det', 'Detective')] }] } },
            { id: 'attack', points: 2, prompt: 'Match each threat to its ATT&CK entry.', hints: ['The lessons name the technique IDs.'], input: { type: 'grid', rows: THREATS, columns: [{ id: 'id', label: 'ATT&CK', options: [R('t1595', 'T1595 Active Scanning'), R('t1557', 'T1557 Adversary-in-the-Middle'), R('t1498', 'T1498 Network Denial of Service'), R('t1110', 'T1110.003 Password Spraying'), R('t1021', 'T1021 Remote Services'), R('ta0011', 'TA0011 Command and Control')] }] } },
            { id: 'gaps', points: 2, prompt: 'Using the current-controls summary, which are real gaps? Select all that apply.', hints: ['Compare each row with the controls you matched in task 2.'], input: { type: 'multi', options: [R('mfaexempt', 'The 35 accounts exempt from MFA leave remote access open to password spraying'), R('switches', 'No DHCP snooping, ARP inspection or 802.1X on access switches'), R('egress', 'Direct outbound HTTPS and DNS bypass the proxy and resolvers'), R('ddos', 'No DDoS protection and a single provider'), R('admin', 'RDP from users to any host and a shared local admin password'), R('certs', 'Staff told to click through certificate warnings'), R('tls', 'TLS is used on internal web applications'), R('siem', 'Sign-in logs are collected in the SIEM')] } },
            { id: 'priority', points: 1, prompt: 'With budget for one change this month, which should come first?', hints: ['Which gap is cheapest to close and most likely to be used?'], input: { type: 'choice', options: [R('mfa', 'Remove the MFA exemption for the 35 legacy accounts and add an alert for failed sign-ins across many accounts'), R('nac', 'Roll out 802.1X on every switch port'), R('ddos', 'Sign a DDoS protection contract'), R('none', 'Wait for next year\'s budget')] } },
            { id: 'summary', points: 2, prompt: 'Write the **threat summary** for the security manager: for each of the six threats, what is at risk at Kanem, how the team would notice it, the current gap and the recommended control, then your top three priorities.', hints: ['Keep it to what a manager needs: risk, signs, gap, fix.'], input: { type: 'deliverable', title: 'Kanem network threat summary', competency: 'Relating network threats to signs and controls', minWords: 150, rubric: ['Covers all six threats with risk, sign, gap and control', 'Uses the current-controls summary accurately', 'Distinguishes preventive and detective controls', 'Gives three sensible, justified priorities'], template: '| Threat | At risk | How we would notice | Current gap | Recommended control |\n| --- | --- | --- | --- | --- |\n\nTop three priorities:\n1.\n2.\n3.\n' } },
        ],
        reflection: ['Which threat would Kanem be least likely to notice today, and why?', 'Which single control helps against the most threats?', 'Why is telling staff to click through certificate warnings dangerous?', 'How would you check in six months that the gaps stay closed?'],
    },
    validator: {
        tasks: {
            signs: { type: 'grid', minCorrectRatio: 0.8, cells: { recon: { sign: ['ports'] }, spoof: { sign: ['gateway'] }, dos: { sign: ['link'] }, spray: { sign: ['failures'] }, lateral: { sign: ['admin'] }, c2: { sign: ['beacon'] } }, feedback: { correct: 'Your matches are right.', incorrect: 'Use the signs table in each lesson.' } },
            controls: { type: 'grid', minCorrectRatio: 0.8, cells: { recon: { control: ['exposure'] }, spoof: { control: ['snoop'] }, dos: { control: ['scrub'] }, spray: { control: ['mfa'] }, lateral: { control: ['tier'] }, c2: { control: ['egress'] } }, feedback: { correct: 'Your matches are right.', incorrect: 'Use the controls table in each lesson.' } },
            kind: { type: 'grid', minCorrectRatio: 0.8, cells: { k1: { type: ['prev'] }, k2: { type: ['det'] }, k3: { type: ['prev'] }, k4: { type: ['det'] }, k5: { type: ['prev'] }, k6: { type: ['det'] } }, feedback: { correct: 'Right.', incorrect: 'Alerts are detective; settings that block are preventive.' } },
            attack: { type: 'grid', minCorrectRatio: 0.8, cells: { recon: { id: ['t1595'] }, spoof: { id: ['t1557'] }, dos: { id: ['t1498'] }, spray: { id: ['t1110'] }, lateral: { id: ['t1021'] }, c2: { id: ['ta0011'] } }, feedback: { correct: 'Right.', incorrect: 'Each lesson names its ATT&CK entry near the start.' } },
            gaps: { type: 'multi', correct: ['mfaexempt', 'switches', 'egress', 'ddos', 'admin', 'certs'], feedback: { correct: 'Right: six gaps; TLS and central sign-in logs are strengths.', incorrect: 'Two of the options describe good practice.' } },
            priority: { type: 'choice', correct: 'mfa', feedback: { correct: 'Right: cheap, fast and closes the most likely way in.', incorrect: 'Compare cost, effort and how likely each gap is to be used.' } },
            summary: { type: 'deliverable', minWords: 150, mustMention: [
                { label: 'MFA', any: ['mfa', 'multi-factor'] },
                { label: 'egress or the proxy', any: ['egress', 'proxy'] },
                { label: 'denial of service', any: ['denial of service', 'ddos', 'dos'] },
                { label: 'lateral movement or administration', any: ['lateral', 'jump host', 'admin'] },
                { label: 'priorities', any: ['priorit', 'first'] },
            ], feedback: { correct: 'Summary complete. Compare it with the rubric and the debrief.', incorrect: 'Your summary is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n| Threat | Typical sign | Best control | ATT&CK | Kanem gap |\n| --- | --- | --- | --- | --- |\n| Reconnaissance | Many attempts to many ports or hosts | Expose only needed services; default deny | T1595 | Few: keep exposure low |\n| Spoofing | Gateway address changes, certificate warnings | TLS with validation; DHCP snooping and ARP inspection | T1557 | No switch protections; staff click through warnings |\n| Denial of service | Capacity exhausted with no business reason | Upstream scrubbing, CDN, rate limits | T1498 | No protection, one provider |\n| Password spraying | Failures across many accounts from one source | Phishing-resistant MFA, no exemptions | T1110.003 | 35 exempt accounts; no alert |\n| Lateral movement | Accounts reaching many systems over admin protocols | Segmentation, jump host, unique local passwords | T1021 | RDP from users to anything; shared admin password |\n| Command and control | Regular outbound connections to rare destinations | Egress filtering through proxies and resolvers | TA0011 | Direct HTTPS and DNS allowed |\n\n**Preventive:** MFA, DHCP snooping, default-deny egress. **Detective:** the sign-in, port-sweep and beaconing alerts.\n\n**Priorities:** (1) remove the MFA exemption and alert on failures across accounts; (2) block direct outbound HTTPS and DNS so traffic goes through the proxy and resolvers; (3) remove RDP from users to any host and deploy unique local admin passwords. Then DDoS protection, switch protections and fixing the two intranet certificates.\n\n## What you have practised\n\nThinking about threats the way a defender does: what is at risk, how you would know, and what stops it.\n\n**Next:** module 3 covers firewalls and network access control.',
    },
};
replaceLab(p.data, 'threat-control-mapping', lab);

const C = 'threats';
replaceQuestions(p.data, 'net-thr-', [
    single('net-thr-01', C, 'foundation', 'Recognise reconnaissance', 'Which reconnaissance does not appear in your own logs?', ['Probing of public addresses', '*Searching public sources such as DNS records and job adverts', 'Internal port sweeps', 'Connection attempts blocked by the firewall'], 'Passive reconnaissance never touches your network.'),
    single('net-thr-02', C, 'intermediate', 'Separate authorised activity', 'An internal port sweep comes from the vulnerability scanner during its scheduled window. What is it?', ['A false positive', '*Authorised activity: document it and scope any exception narrowly', 'An attack', 'A sensor fault'], 'Context decides.'),
    single('net-thr-03', C, 'foundation', 'Prevent spoofing', 'Which control best protects traffic even when it is redirected through an impersonator?', ['Static IP addresses', '*TLS with certificate validation', 'A faster switch', 'Hidden Wi-Fi names'], 'Encryption with certificate checking is the backstop.'),
    single('net-thr-04', C, 'intermediate', 'Prevent spoofing', 'Which pair of switch features blocks false DHCP servers and forged address-resolution replies?', ['Port mirroring and trunking', '*DHCP snooping and dynamic ARP inspection', 'Spanning tree and VTP', 'QoS and LACP'], 'Both use the table of trusted assignments.'),
    single('net-thr-05', C, 'foundation', 'Limit spoofing at the edge', 'What does BCP 38 (RFC 2827) recommend?', ['Encrypting all DNS', '*Filtering packets with forged source addresses at network edges', 'Blocking all ICMP', 'Using IPv6 only'], 'Ingress filtering stops forged sources.'),
    single('net-thr-06', C, 'foundation', 'Classify denial of service', 'Which kind of denial of service fills the internet link?', ['*Volumetric', 'Application layer', 'State exhaustion', 'Credential stuffing'], 'It exhausts bandwidth.'),
    single('net-thr-07', C, 'intermediate', 'Respond to denial of service', 'Why keep the DDoS provider\'s emergency contact in a runbook?', ['It is a legal requirement', '*The first minutes are often lost finding who to call and how to switch on protection', 'Providers charge less', 'It replaces monitoring'], 'Rehearse the call.'),
    single('net-thr-08', C, 'intermediate', 'Recognise credential attacks', 'Failed sign-ins spread across many accounts from one source, a few attempts each, indicate what?', ['A user who forgot their password', '*Password spraying', 'A denial-of-service attack', 'DNS failure'], 'Few attempts per account avoid lockouts.'),
    single('net-thr-09', C, 'foundation', 'Reduce credential risk', 'Which control most reduces the value of stolen passwords?', ['Longer screensaver timeouts', '*Phishing-resistant MFA with no exemptions', 'More firewall rules', 'Password expiry every week'], 'A password alone is then not enough.'),
    multiple('net-thr-10', C, 'intermediate', 'Limit lateral movement', 'Which limit how far an intruder can move from one machine? Select all that apply.', ['*Administration only via a jump host in the management zone', '*Unique local administrator passwords', '*Segmentation between zones', 'One shared admin password across all servers'], 'Shared passwords let one capture open everything.'),
    single('net-thr-11', C, 'foundation', 'Recognise command and control', 'Which behaviour is a typical sign of command and control?', ['Daily backups at 01:00', '*Regular outbound connections at fixed intervals to a rare, new destination', 'Users browsing news sites', 'Printers sending jobs'], 'Look for beaconing.'),
    single('net-thr-12', C, 'intermediate', 'Control command and control', 'Which control forces outbound traffic through monitored points?', ['Allow-all outbound', '*Default-deny egress through proxies and internal resolvers', 'Public IPs on laptops', 'Disabling DNS logs'], 'Watch the exits.'),
    single('net-thr-13', C, 'foundation', 'Use ATT&CK', 'What is MITRE ATT&CK used for in defence?', ['Writing malware', '*Naming adversary behaviours consistently and mapping detections and mitigations to them', 'Encrypting traffic', 'Assigning IP addresses'], 'A shared language for behaviours and coverage.'),
]);

save(p);
console.log('network module 2 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
