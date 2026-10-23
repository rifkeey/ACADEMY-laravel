// Network Security, module 7: DNS, DHCP and Web Protocol Security.
// Covered at the level of configuration, detection and escalation decisions only.
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard';
const rfc = (n, title, topic, supports, note) => ({ key: `rfc-${n}`, title: `RFC ${n}: ${title}`, publisher: 'IETF (RFC Editor)', url: `https://www.rfc-editor.org/rfc/rfc${n}`, topic, sourceType: o, versionNote: note || `RFC ${n}; check for updating RFCs.`, supports });
upsertSources([
    rfc(4033, 'DNS Security Introduction and Requirements', 'DNSSEC', 'What DNSSEC provides and does not provide.'),
    rfc(7858, 'Specification for DNS over Transport Layer Security (TLS)', 'Encrypted DNS', 'DNS over TLS.'),
    rfc(8484, 'DNS Queries over HTTPS (DoH)', 'Encrypted DNS', 'DNS over HTTPS.'),
    rfc(7208, 'Sender Policy Framework (SPF) for Authorizing Use of Domains in Email, Version 1', 'Email authentication', 'SPF records.'),
    rfc(6376, 'DomainKeys Identified Mail (DKIM) Signatures', 'Email authentication', 'DKIM signing and verification.'),
    rfc(9989, 'Domain-Based Message Authentication, Reporting, and Conformance (DMARC)', 'Email authentication', 'DMARC policy and reporting.', 'RFC 9989; obsoletes RFC 7489.'),
    { key: 'nist-sp-800-81r3', title: 'SP 800-81 Rev. 3: Secure Domain Name System (DNS) Deployment Guide', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/81/r3/final', topic: 'DNS security', sourceType: o, versionNote: 'Revision 3, March 2026; supersedes SP 800-81-2.', supports: 'Secure DNS deployment, DNSSEC and protective DNS.' },
    { key: 'nist-sp-800-177r1', title: 'SP 800-177 Rev. 1: Trustworthy Email', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/177/r1/final', topic: 'Email security', sourceType: o, versionNote: 'Revision 1, February 2019.', supports: 'SPF, DKIM, DMARC and email transport security.' },
    { key: 'cisa-protective-dns', title: 'Protective Domain Name System (DNS) Resolver', publisher: 'CISA', url: 'https://www.cisa.gov/resources-tools/services/protective-domain-name-system-dns-resolver', topic: 'Protective DNS', sourceType: o, versionNote: 'Live service page.', supports: 'Protective DNS filtering as a service (US federal example).' },
    { key: 'attack-t1071-004', title: 'ATT&CK T1071.004: Application Layer Protocol: DNS', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1071/004/', topic: 'Threat behaviour', sourceType: 'open_resource', versionNote: 'Live page; ATT&CK is versioned.', supports: 'Description and detection notes for DNS used as a communication channel.' },
    { key: 'attack-t1568', title: 'ATT&CK T1568: Dynamic Resolution', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1568/', topic: 'Threat behaviour', sourceType: 'open_resource', versionNote: 'Live page; ATT&CK is versioned.', supports: 'Description and detection notes for dynamically generated domains.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'securing-dns', title: 'Securing DNS: resolvers, DNSSEC, encrypted and protective DNS', minutes: 30, sources: ['nist-sp-800-81r3', 'rfc-1034', 'rfc-4033', 'rfc-7858', 'rfc-8484', 'cisa-protective-dns'],
        objectives: ['Apply secure configuration to internal resolvers and authoritative servers', 'Explain what DNSSEC protects', 'Decide how encrypted DNS fits an organisation', 'Describe protective DNS filtering'],
        checks: [
            { question: 'What does DNSSEC protect?', options: ['The confidentiality of queries', 'The authenticity and integrity of DNS answers', 'The speed of resolution', 'Email delivery'], answerIndex: 1, explanation: 'DNSSEC signs DNS data; it does not encrypt it.' },
            { question: 'A browser sends encrypted DNS directly to a public resolver. What is the main organisational concern?', options: ['Queries become slower', 'Internal DNS logging and filtering are bypassed', 'DNSSEC stops working everywhere', 'The device loses its IP address'], answerIndex: 1, explanation: 'Use encrypted DNS to your own resolvers, not around them.' },
            { question: 'Which are good DNS security practices? Select all that apply.', options: ['Restrict zone transfers to authorised secondaries', 'Registrar lock and strong authentication on public domains', 'Resolvers that answer only internal clients', 'Publishing internal zones on public servers'], answerIndexes: [0, 1, 2], explanation: 'Keep internal names internal.' },
        ] },
    { slug: 'dns-as-evidence', title: 'DNS logs as evidence', minutes: 30, sources: ['nist-sp-800-81r3', 'attack-t1071-004', 'attack-t1568', 'attack-t1572'],
        objectives: ['Read DNS resolver logs', 'Recognise DNS indicators worth investigating and their benign causes', 'Decide when DNS evidence justifies escalation'],
        checks: [
            { question: 'One host makes thousands of TXT lookups for long, random-looking subdomains of one newly registered domain. What should you do?', options: ['Ignore it; DNS is harmless', 'Investigate: several indicators line up on one host', 'Block all TXT queries for everyone at once', 'Reinstall the DNS server'], answerIndex: 1, explanation: 'Multiple indicators on one host justify a closer look and usually escalation.' },
            { question: 'Many NXDOMAIN responses from one host can indicate what? Select all that apply.', options: ['Software trying many generated names', 'A misconfigured application', 'A typo in a script', 'A successful backup'], answerIndexes: [0, 1, 2], explanation: 'It is a reason to look closer; benign causes are common.' },
            { question: 'Why must devices actually use the internal resolvers?', options: ['It is faster', 'Otherwise DNS logs are incomplete and filtering does not apply', 'Public resolvers are illegal', 'DHCP requires it'], answerIndex: 1, explanation: 'Visibility depends on where queries go.' },
        ] },
    { slug: 'dhcp-security', title: 'DHCP security and lease evidence', minutes: 25, sources: ['rfc-2131', 'ieee-8021x'],
        objectives: ['Identify DHCP risks', 'Apply DHCP snooping, NAC, scope design and monitoring', 'Use lease logs to identify devices'],
        checks: [
            { question: 'Which switch feature drops DHCP replies from ports that are not connected to the real DHCP servers?', options: ['Port mirroring', 'DHCP snooping', 'Spanning tree', 'Link aggregation'], answerIndex: 1, explanation: 'Only trusted ports may send DHCP replies.' },
            { question: 'Why is an IP address in a log not enough to identify a device?', options: ['IP addresses are encrypted', 'Addresses are reassigned; you need the time and the lease history', 'Logs never contain IP addresses', 'Every device has the same address'], answerIndex: 1, explanation: 'Join IP and time to the DHCP lease.' },
            { question: 'What does a DHCP lease record contain? Select all that apply.', options: ['MAC address', 'Hostname', 'Lease start and end time', 'The user\'s password'], answerIndexes: [0, 1, 2], explanation: 'Leases connect addresses to devices over time.' },
        ] },
    { slug: 'web-traffic-and-proxies', title: 'Web traffic, proxies and email authentication', minutes: 30, sources: ['rfc-9110', 'nist-sp-800-177r1', 'rfc-7208', 'rfc-6376', 'rfc-9989'],
        objectives: ['Read HTTP request metadata for security relevance', 'Explain what a proxy or secure web gateway provides', 'Choose secure protocols', 'Describe SPF, DKIM and DMARC'],
        checks: [
            { question: 'Which proxy capability lets logs show which person made a request?', options: ['Caching', 'Authentication', 'Compression', 'Load balancing'], answerIndex: 1, explanation: 'Authenticated proxies log the user, not just the IP address.' },
            { question: 'Which mechanism tells receiving mail servers what to do when SPF and DKIM checks fail?', options: ['SPF', 'DKIM', 'DMARC', 'MX'], answerIndex: 2, explanation: 'DMARC publishes the policy and requests reports.' },
            { question: 'Which are secure protocol choices? Select all that apply.', options: ['HTTPS for internal applications', 'SFTP for file transfer', 'FTP for sensitive files', 'TLS between mail servers where supported'], answerIndexes: [0, 1, 3], explanation: 'FTP sends credentials and data unencrypted.' },
        ] },
    { slug: 'escalation-decisions', title: 'Joining the evidence and deciding to escalate', minutes: 30, sources: ['nist-sp-800-61r3', 'nist-sp-800-92'],
        objectives: ['Join DHCP, DNS, proxy, firewall and identity evidence', 'Weigh indicators with a rubric', 'Write an escalation the incident team can act on'],
        checks: [
            { question: 'Which observation carries the most weight towards escalation?', options: ['The host is a laptop', 'Unusual data leaving a high-value host, with a correlated endpoint alert', 'A single lookup of a new domain', 'The user is new'], answerIndex: 1, explanation: 'Data leaving high-value hosts and correlated alerts weigh heavily.' },
            { question: 'An alert is unclear but worrying. What is the right action?', options: ['Close it as nothing', 'Escalate with what you have and what you have not checked', 'Delete the logs', 'Wait a month'], answerIndex: 1, explanation: 'Never close without a reason.' },
            { question: 'Which belong in a good escalation? Select all that apply.', options: ['Host, user and owner', 'First and latest times in UTC', 'The specific evidence and its sources', 'Guesses presented as facts'], answerIndexes: [0, 1, 2], explanation: 'Also say what you have and have not checked.' },
        ] },
];

const R = (id, label) => ({ id, label });

const lab = {
    slug: 'dns-dhcp-evidence-triage',
    title: 'Triage DNS, DHCP and email evidence at Kanem',
    difficulty: 'intermediate',
    labType: 'log_investigation',
    summary: 'Join DHCP leases, resolver logs, proxy and switch records to decide which hosts need escalating, fix two configuration weaknesses, and write an escalation the incident team can act on.',
    minutes: 60,
    moduleSlug: 'protocol-security',
    passPercent: 80,
    content: {
        objectives: ['Identify a device from IP, time and DHCP leases', 'Recognise DNS indicators and weigh them', 'Decide escalate, monitor or close for each host', 'Recommend DHCP and email authentication fixes', 'Write an evidence-based escalation'],
        scenario: 'Tuesday 10 March 2026 at Kanem Financial Services (fictional). The overnight DNS report flagged four Abuja hosts. While you look, the service desk reports that some meeting-room laptops received a strange gateway, and the communications team asks why spoofed "Kanem" emails reached customers. The evidence is in the workspace.',
        scope: 'Work from the documents. All data is synthetic; domains use the reserved .example name.',
        instructions: '1. Read the five documents.\n2. Answer tasks 1–6.\n3. Write the escalation in task 7. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic.',
        workspace: { kind: 'documents', documents: [
            { title: 'DHCP lease log (Abuja staff scope 10.40.8.0/22, extract)', format: 'code', language: 'text', body: '2026-03-09T18:02:10Z  10.40.9.44  lease  mac=3c:52:82:aa:10:07  host=KAN-LT-077  ends=2026-03-10T01:00:00Z\n2026-03-10T01:12:31Z  10.40.9.44  lease  mac=3c:52:82:aa:11:18  host=KAN-LT-118  ends=2026-03-10T13:12:31Z\n2026-03-09T07:40:02Z  10.40.8.12  lease  mac=3c:52:82:aa:10:77  host=KAN-LT-201  ends=2026-03-10T19:40:02Z\n2026-03-01T09:00:00Z  10.40.8.9   reservation  host=KAN-PRN-02 (print server)' },
            { title: 'Overnight DNS report (internal resolvers, 00:00–06:00 UTC)', format: 'markdown', body: '| Host IP | Pattern | Count | Notes |\n| --- | --- | --- | --- |\n| **10.40.9.44** | TXT lookups for long random-looking subdomains of `k7q-sync.example`, every 60 seconds from 01:20 | 4,210 | Domain registered 3 days ago; never seen before at Kanem |\n| **10.40.8.31** | A lookups for `update.vendor-av.example` every hour | 6 | Kanem\'s endpoint protection vendor (asset register) |\n| **10.40.8.9** | NXDOMAIN for `fw01.oldprinters.example` … `fw40.oldprinters.example` at 02:00 | 40 | Print server firmware check scheduled 02:00 (change CHG-3301) |\n| **10.40.8.12** | No internal lookups; firewall shows TCP 853 to public resolver 203.0.113.53 | n/a | Browser setting "use secure DNS provider" enabled |' },
            { title: 'Proxy and endpoint notes for 10.40.9.44', format: 'markdown', body: '- Proxy: user **a.musa** (Finance) authenticated on KAN-LT-118 at 01:15; no web activity after 01:30.\n- Endpoint protection: "suspicious script launched from email attachment" alert on KAN-LT-118 at 01:18, status *not reviewed*.\n- Outbound DNS volume from the host overnight: about 9 MB, roughly 200 times its normal night.' },
            { title: 'Switch log: meeting-room access switch', format: 'code', language: 'text', body: '2026-03-10T08:41:05Z  sw-abj-mr1  Gi1/0/23 (Meeting room 2)  DHCPOFFER seen  server=192.168.1.1  offered-gateway=192.168.1.1\n2026-03-10T08:41:06Z  sw-abj-mr1  Gi1/0/48 (uplink)  DHCPOFFER seen  server=10.40.8.2  offered-gateway=10.40.8.1\nNote: DHCP snooping is not enabled on sw-abj-mr1.' },
            { title: 'Kanem public DNS records for email', format: 'code', language: 'text', body: 'kanem.example.         TXT  "v=spf1 include:mail.provider.example ~all"\nselector1._domainkey.kanem.example  TXT  "v=DKIM1; k=rsa; p=(public key)"\n_dmarc.kanem.example.  TXT  "v=DMARC1; p=none; rua=mailto:dmarc-reports@kanem.example"\n\nDMARC aggregate reports (last 30 days): 99.6% of Kanem mail from mail.provider.example passes; the payroll system sends about 300 messages a month directly and fails SPF and DKIM.' },
        ] },
        tasks: [
            { id: 'device', points: 1, prompt: 'Which device was using 10.40.9.44 at 03:30 UTC on 10 March?', hints: ['Compare the lease start and end times with 03:30.'], input: { type: 'choice', options: [R('lt118', 'KAN-LT-118 (lease from 01:12)'), R('lt077', 'KAN-LT-077 (its lease ended at 01:00)'), R('unknown', 'It cannot be determined')] } },
            { id: 'indicators', points: 2, prompt: 'Which indicators are present for 10.40.9.44? Select all that apply.', hints: ['Lesson 2 lists the indicators; check each against the evidence.'], input: { type: 'multi', options: [R('random', 'Long random-looking subdomains with many TXT lookups to one domain'), R('regular', 'Regular, machine-like timing'), R('new', 'A newly registered, never-seen domain'), R('volume', 'Unusual outbound volume'), R('nxdomain', 'A burst of NXDOMAIN responses'), R('external', 'Direct use of an external resolver')] } },
            { id: 'triage', points: 2, prompt: 'Decide the action for each host.', hints: ['Weigh indicators against confirmed benign explanations (lesson 5).'], input: { type: 'grid', rows: [R('h44', '10.40.9.44 (KAN-LT-118)'), R('h31', '10.40.8.31'), R('h9', '10.40.8.9 (print server)'), R('h12', '10.40.8.12 (KAN-LT-201)')], columns: [{ id: 'action', label: 'Action', options: [R('escalate', 'Escalate to incident handling'), R('monitor', 'Monitor and fix configuration'), R('close', 'Close with reason')] }] } },
            { id: 'dhcpfix', points: 1, prompt: 'Meeting-room laptops received gateway 192.168.1.1. What is the right lasting fix?', hints: ['Which port sent the unexpected offer?'], input: { type: 'choice', options: [R('snooping', 'Remove the unauthorised device on Gi1/0/23 and enable DHCP snooping, trusting only the uplink'), R('static', 'Give every laptop a static IP address'), R('reboot', 'Reboot the DHCP server'), R('dns', 'Change the DNS servers')] } },
            { id: 'resolver', points: 1, prompt: 'How should Kanem handle KAN-LT-201 and devices like it?', hints: ['Lesson 1: encrypted DNS to your own resolvers.'], input: { type: 'choice', options: [R('manage', 'Set managed browsers and devices to use Kanem\'s resolvers, and block or alert on direct DNS and DoT/DoH to outside resolvers'), R('allow', 'Allow any resolver users prefer'), R('ban', 'Ban encrypted DNS completely, including to Kanem\'s resolvers')] } },
            { id: 'dmarc', points: 1, prompt: 'What is the right next step for Kanem\'s email authentication?', hints: ['Don\'t block legitimate mail by surprise.'], input: { type: 'choice', options: [R('fixthen', 'Route payroll mail through the authorised provider (or add it to SPF and sign with DKIM), then move DMARC to quarantine and later reject'), R('rejectnow', 'Set DMARC to reject today without changes'), R('remove', 'Delete the DMARC record'), R('none', 'Leave p=none permanently')] } },
            { id: 'escalation', points: 2, prompt: 'Write the **escalation** to the incident lead for 10.40.9.44: what, who, when, evidence, why it matters, what you have and have not checked, and your recommended next step.', hints: ['Lesson 5 lists what a good escalation contains.'], input: { type: 'deliverable', title: 'DNS evidence escalation', competency: 'Joining network evidence and escalating with a clear summary', minWords: 120, rubric: ['Identifies the device and user correctly using the lease and time', 'Lists the specific indicators with counts and times', 'Explains why it matters (correlated alert, volume, Finance user)', 'States what was and was not checked', 'Recommends a proportionate next step agreed with the incident lead'], template: 'What:\n\nWho (host, user, owner):\n\nWhen (UTC):\n\nEvidence and sources:\n\nWhy it matters:\n\nChecked / not checked:\n\nRecommended next step:\n' } },
        ],
        reflection: ['What would have happened if you had used the IP address without checking the lease?', 'Which single log source would you add first if Kanem had none of these?', 'How would you explain to the communications team why DMARC cannot go to reject today?', 'What would you tune so the print-server check stops appearing in the report?'],
    },
    validator: {
        tasks: {
            device: { type: 'choice', correct: 'lt118', feedback: { correct: 'Right: the address changed hands at 01:12.', incorrect: 'Check which lease covers 03:30.' } },
            indicators: { type: 'multi', correct: ['random', 'regular', 'new', 'volume'], feedback: { correct: 'Right: four indicators on one host.', incorrect: 'Check each indicator against the report and notes; two are not present for this host.' } },
            triage: { type: 'grid', minCorrectRatio: 0.75, cells: { h44: { action: ['escalate'] }, h31: { action: ['close'] }, h9: { action: ['close', 'monitor'] }, h12: { action: ['monitor'] } }, feedback: { correct: 'Your decisions match.', incorrect: 'Escalate where indicators line up; close where a benign cause is confirmed; fix configuration gaps.' } },
            dhcpfix: { type: 'choice', correct: 'snooping', feedback: { correct: 'Right.', incorrect: 'An unauthorised server on a meeting-room port answered first. Remove it and stop it happening again.' } },
            resolver: { type: 'choice', correct: 'manage', feedback: { correct: 'Right.', incorrect: 'Keep privacy and visibility: encrypted DNS to Kanem\'s own resolvers.' } },
            dmarc: { type: 'choice', correct: 'fixthen', feedback: { correct: 'Right.', incorrect: 'Reject today would block payroll mail; p=none forever lets spoofing continue.' } },
            escalation: { type: 'deliverable', minWords: 120, mustMention: [
                { label: 'the device KAN-LT-118', any: ['lt-118', 'lt118'] },
                { label: 'the domain k7q-sync.example', any: ['k7q'] },
                { label: 'the DNS indicators', any: ['subdomain', 'txt', 'every 60', 'regular', 'newly registered', 'random'] },
                { label: 'a next step', any: ['isolat', 'contain', 'incident'] },
            ], feedback: { correct: 'Escalation complete. Compare it with the rubric and the debrief.', incorrect: 'Your escalation is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Device:** KAN-LT-118. KAN-LT-077 held 10.40.9.44 until 01:00; KAN-LT-118 took it at 01:12. Using the IP alone would have blamed the wrong laptop.\n\n**Indicators for 10.40.9.44:** long random-looking subdomains with TXT lookups to one domain, a 60-second rhythm, a domain registered three days ago and never seen before, and about 200 times the normal outbound DNS volume. There was no NXDOMAIN burst and no external resolver use.\n\n**Triage:** escalate 10.40.9.44 (four indicators, a Finance user and an unreviewed endpoint alert three minutes before the pattern began); close 10.40.8.31 (known vendor, hourly updates); close or monitor 10.40.8.9 (scheduled change CHG-3301; tune the report); monitor 10.40.8.12 and fix the browser setting.\n\n**DHCP:** a device on Gi1/0/23 answered DHCP with its own gateway. Remove it and enable DHCP snooping with only the uplink trusted.\n\n**Encrypted DNS:** manage browsers and devices to use Kanem\'s resolvers; block or alert on direct DNS, DoT and DoH to outside resolvers.\n\n**Email:** DMARC p=none only monitors. Fix the payroll sender first, then move to quarantine and later reject while watching the reports.\n\n**A model escalation:** "Since 01:20 UTC on 10 March, KAN-LT-118 (user a.musa, Finance; IP 10.40.9.44 per the DHCP lease from 01:12) has made 4,210 TXT lookups for long random-looking subdomains of k7q-sync.example every 60 seconds. The domain was registered three days ago and has not been seen at Kanem before. Overnight DNS volume was about 9 MB, roughly 200 times normal. An endpoint alert (suspicious script from an email attachment) fired at 01:18 and has not been reviewed. Checked: DHCP lease, resolver logs, proxy and endpoint console. Not checked: email gateway, file access, other hosts querying the domain. Recommend: incident lead to approve isolating KAN-LT-118, blocking k7q-sync.example at the resolvers, and searching for other hosts that queried it."\n\n## What you have practised\n\nJoining DHCP, DNS, proxy and endpoint evidence into a decision, and fixing the configuration gaps that make investigations harder: core skills for network security and SOC roles.\n\n**Next:** module 8 looks at network traffic analysis.',
    },
};

replaceModule(p.data, 'protocol-security', {
    slug: 'protocol-security',
    title: 'DNS, DHCP and Web Protocol Security',
    purpose: 'Every connection at Kanem depends on DNS, every device on DHCP, and most traffic is web traffic. This module covers securing DNS (resolvers, DNSSEC, encrypted and protective DNS), using DNS logs as evidence, protecting DHCP, controlling web traffic through proxies, email authentication, and deciding when to escalate. You finish by triaging real-looking evidence and writing an escalation.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written escalation.',
    prerequisiteKnowledge: 'Modules 1, 3 and 5.',
    objectives: ['Configure DNS services securely and explain DNSSEC and encrypted DNS', 'Recognise DNS indicators and their benign causes', 'Protect DHCP and use lease logs to identify devices', 'Control web traffic with proxies and choose secure protocols', 'Explain SPF, DKIM and DMARC', 'Join evidence and escalate with a clear summary'],
    keyConcepts: ['Internal resolvers', 'DNSSEC', 'DoT and DoH', 'Protective DNS', 'DNS indicators', 'DHCP snooping', 'Lease history', 'Secure web gateway', 'SPF, DKIM and DMARC', 'Escalation rubric'],
    assessmentCriteria: ['Identifies devices correctly from leases and time', 'Weighs indicators against benign explanations', 'Recommends proportionate configuration fixes', 'Writes a complete, evidence-based escalation'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab. Indicators are taught for detection only; no techniques for misusing these protocols are included.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'dns-dhcp-evidence-triage', lab);

const C = 'protocol-security';
replaceQuestions(p.data, 'net-dns-', [
    single('net-dns-01', C, 'foundation', 'Explain DNSSEC', 'What does DNSSEC add to DNS?', ['Encryption of queries', '*Signatures so answers can be checked for authenticity and integrity', 'Faster lookups', 'Automatic IP assignment'], 'DNSSEC protects integrity, not confidentiality.'),
    single('net-dns-02', C, 'intermediate', 'Plan encrypted DNS', 'How should an organisation use DNS over HTTPS or TLS?', ['Let each application choose any public resolver', '*Encrypt DNS to the organisation\'s own resolvers and manage devices to use them', 'Disable all DNS', 'Use it only for email'], 'This keeps privacy and visibility.'),
    single('net-dns-03', C, 'foundation', 'Harden DNS', 'Who should be allowed to receive zone transfers from Kanem\'s authoritative servers?', ['Anyone', '*Only authorised secondary servers', 'Every staff laptop', 'Public resolvers'], 'Restrict zone transfers.'),
    single('net-dns-04', C, 'foundation', 'Use protective DNS', 'What does a protective DNS resolver do?', ['Assigns IP addresses', '*Refuses or redirects lookups for known malicious or unwanted domains', 'Encrypts email', 'Signs zones'], 'Filtering at the resolver blocks many threats early.'),
    multiple('net-dns-05', C, 'intermediate', 'Recognise DNS indicators', 'Which DNS patterns are reasons to investigate a host? Select all that apply.', ['*Long random-looking subdomains to one domain', '*Regular machine-like lookups to a new domain', '*Many NXDOMAIN responses', 'A single lookup of the company intranet'], 'Each has benign causes; several together on one host weigh more.'),
    single('net-dns-06', C, 'intermediate', 'Weigh evidence', 'When does DNS evidence usually justify escalation?', ['Any single new domain', '*When several indicators line up on one host, especially with correlated alerts', 'Never; DNS is not evidence', 'Only when the user complains'], 'Weigh indicators together.'),
    single('net-dns-07', C, 'foundation', 'Protect DHCP', 'Which control stops an unauthorised DHCP server on an access port from serving clients?', ['Port mirroring', '*DHCP snooping with only uplinks trusted', 'A longer lease time', 'A new VLAN name'], 'Replies from untrusted ports are dropped.'),
    single('net-dns-08', C, 'intermediate', 'Identify devices', 'A firewall log shows 10.40.9.44 at 03:30. What do you need to identify the device?', ['Only the IP address', '*The DHCP lease covering that time', 'The DNS server name', 'The user\'s email address'], 'Addresses are reassigned over time.'),
    single('net-dns-09', C, 'foundation', 'Use a proxy', 'Which benefit does an authenticated web proxy add to investigations?', ['It hides the user', '*Logs show which user made each request', 'It removes the need for DNS', 'It encrypts disks'], 'User attribution matters.'),
    single('net-dns-10', C, 'foundation', 'Explain SPF', 'What does an SPF record publish?', ['A signing key', '*Which servers may send mail for the domain', 'The DMARC policy', 'The MX priority only'], 'SPF lists authorised senders.'),
    single('net-dns-11', C, 'intermediate', 'Deploy DMARC', 'Kanem\'s DMARC policy is p=none and one legitimate system fails SPF and DKIM. What next?', ['Set p=reject today', '*Fix the failing sender, then move to quarantine and later reject', 'Delete DMARC', 'Leave p=none permanently'], 'Enforce gradually using the reports.'),
    single('net-dns-12', C, 'foundation', 'Choose secure protocols', 'Which is the secure choice for transferring sensitive files?', ['FTP', 'Telnet', '*SFTP', 'TFTP'], 'SFTP runs over SSH.'),
    single('net-dns-13', C, 'intermediate', 'Write escalations', 'Which is most important to include in an escalation?', ['Your guesses as facts', '*Specific evidence with sources and times, and what you have not checked', 'Only the host name', 'A request to close it'], 'The incident team must be able to act immediately.'),
]);

save(p);
console.log('network module 7 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
