// Network Security, module 5: IDS, IPS and Network Detection.
// The lab is about triage and tuning: separating authorised and benign activity from the one alert that
// matters, and tuning without creating blind spots. All data is synthetic.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const v = 'vendor_documentation';
upsertSources([
    { key: 'suricata-docs', title: 'Suricata User Guide', publisher: 'Open Information Security Foundation (OISF)', url: 'https://docs.suricata.io/en/latest/', topic: 'Network IDS/IPS', sourceType: v, versionNote: 'The "latest" guide tracks development; use the guide for your installed version.', supports: 'Signature-based IDS/IPS concepts and rule structure (example tool).' },
    { key: 'zeek-docs', title: 'Zeek Documentation (Book of Zeek)', publisher: 'The Zeek Project', url: 'https://docs.zeek.org/en/current/', topic: 'Network monitoring', sourceType: v, versionNote: 'Current release documentation.', supports: 'Connection and protocol logs for network security monitoring (example tool).' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'how-network-detection-works', title: 'How network detection works', minutes: 30, sources: ['nist-sp-800-94', 'suricata-docs'],
        objectives: ['Compare signature, anomaly and stateful protocol analysis', 'Explain IDS versus IPS and network versus host detection', 'Read what an alert contains'],
        checks: [
            { question: 'Which detection method is most precise for known threats but misses new ones?', options: ['Anomaly-based', 'Signature-based', 'Stateful protocol analysis', 'Random sampling'], answerIndex: 1, explanation: 'Signatures match known patterns.' },
            { question: 'What is the main extra risk of running a system inline as an IPS?', options: ['It cannot see traffic', 'A wrong block stops legitimate traffic, and the device can become a bottleneck or single point of failure', 'It needs no tuning', 'It only works in the cloud'], answerIndex: 1, explanation: 'Prevention needs well-tuned rules and resilience.' },
            { question: 'Which details does a useful alert include? Select all that apply.', options: ['Time and the rule that fired', 'Source and destination addresses and ports', 'Severity and some context', 'The analyst\'s home address'], answerIndexes: [0, 1, 2], explanation: 'Enough to start triage.' },
        ] },
    { slug: 'alert-quality', title: 'Alert quality: true and false positives', minutes: 30, sources: ['nist-sp-800-94', 'mitre-attack'],
        objectives: ['Use the four outcomes: TP, FP, TN, FN', 'Explain why most alerts are noise', 'Measure detections by volume, true-positive rate, coverage and time to triage'],
        checks: [
            { question: 'Which outcome is the most dangerous?', options: ['True positive', 'False positive', 'True negative', 'False negative'], answerIndex: 3, explanation: 'A missed attack leaves no alert at all.' },
            { question: 'How do you measure coverage?', options: ['Count alerts per day', 'Map detections to the attacker behaviours (ATT&CK techniques) they can see', 'Ask the vendor', 'Measure disk use'], answerIndex: 1, explanation: 'Coverage is about what you can detect at all.' },
            { question: 'A rule produces 300 alerts a day and almost none are worth investigating. What is the effect?', options: ['Better security', 'Analysts lose trust in the rule and may miss the real one', 'No effect', 'Lower cost'], answerIndex: 1, explanation: 'Noise hides signal.' },
        ] },
    { slug: 'sensor-placement-and-telemetry', title: 'Sensor placement and telemetry', minutes: 30, sources: ['nist-sp-800-94', 'zeek-docs', 'rfc-7011'],
        objectives: ['Choose how traffic reaches a sensor', 'Place sensors where they see what matters', 'Collect telemetry beyond alerts and keep time and context consistent'],
        checks: [
            { question: 'Which way of feeding a sensor is most reliable and complete?', options: ['A switch mirror port under heavy load', 'A passive network tap', 'Guessing', 'Email reports'], answerIndex: 1, explanation: 'Mirror ports can drop copies under load.' },
            { question: 'Which placement sees movement between internal zones towards critical systems?', options: ['Only the internet edge', 'The data-centre core, between zones', 'The guest Wi-Fi', 'The printer VLAN'], answerIndex: 1, explanation: 'East-west traffic needs internal sensors.' },
            { question: 'Which telemetry helps triage beyond alerts? Select all that apply.', options: ['Connection (flow) records', 'DNS and TLS protocol logs', 'Selective packet capture', 'Office temperature'], answerIndexes: [0, 1, 2], explanation: 'Context comes from many sources.' },
        ] },
    { slug: 'triage-and-tuning', title: 'Triage and tuning', minutes: 35, sources: ['nist-sp-800-94', 'attack-t1046'],
        objectives: ['Apply a five-step triage routine', 'Tell true positives, benign true positives and false positives apart', 'Tune with scoped exceptions, thresholds and enrichment without creating blind spots'],
        checks: [
            { question: 'The vulnerability scanner triggers a port-scan rule during its scheduled window. What is the best tuning?', options: ['Disable the rule', 'A scoped exception for that source, rule and time window', 'Raise the threshold for everyone', 'Ignore all port-scan alerts'], answerIndex: 1, explanation: 'Scope the exception narrowly.' },
            { question: 'The same rule fires for an unknown VPN address with no change ticket. What is it most likely to be?', options: ['A false positive', 'A possible true positive to investigate and escalate', 'A benign true positive', 'A sensor fault'], answerIndex: 1, explanation: 'Only context separates the two.' },
            { question: 'When is disabling a rule acceptable?', options: ['Whenever it is noisy', 'Only with a documented reason and a replacement detection or accepted risk', 'Never', 'On Fridays'], answerIndex: 1, explanation: 'Disabling creates a blind spot.' },
        ] },
    { slug: 'encrypted-traffic-and-limits', title: 'Encrypted traffic and the limits of detection', minutes: 25, sources: ['nist-sp-800-94', 'rfc-8446'],
        objectives: ['Explain what encryption hides and what remains visible', 'Weigh selective TLS inspection', 'Treat detection as a programme, not a device'],
        checks: [
            { question: 'What can a sensor still see in encrypted TLS traffic without decryption?', options: ['Full URLs and form data', 'Addresses, timing, volumes and usually the server name', 'Passwords', 'File contents'], answerIndex: 1, explanation: 'Metadata remains.' },
            { question: 'What is a risk of TLS inspection?', options: ['It improves privacy', 'The inspection device holds decrypted data and becomes a high-value target', 'It speeds up traffic', 'It removes the need for certificates'], answerIndex: 1, explanation: 'Inspect selectively, with documented exemptions.' },
            { question: 'What makes detection effective over time? Select all that apply.', options: ['Combining network, host and identity telemetry', 'Regular tuning and testing', 'Measuring coverage', 'Buying one device and leaving it'], answerIndexes: [0, 1, 2], explanation: 'Detection is a programme.' },
        ] },
];

// ---- Synthetic alerts ----------------------------------------------------------------------------
let seed = 517;
const rnd = () => ((seed = (Math.imul(seed, 1103515245) + 12345) >>> 0) / 4294967296);
const int = (a, b) => a + Math.floor(rnd() * (b - a + 1));
const BASE = Date.UTC(2026, 2, 17, 0, 0, 0) / 1000;   // Tuesday 17 March 2026
const at = (h, m, s = 0) => BASE + h * 3600 + m * 60 + s;
const iso = (t) => new Date(t * 1000).toISOString().replace('.000', '');
const alerts = [];
const A = (t, Signature, Severity, SrcIP, DstIP, DstPort, Sensor) => alerts.push({ TimeGenerated: iso(t), Signature, Severity, SrcIP, DstIP, DstPort, Sensor });

const SCAN = 'Internal port sweep', TLSOLD = 'Policy: outdated TLS version', SQLI = 'Web: SQL keywords in URL', NEWDOM = 'DNS: newly observed domain', SMB = 'SMB: workstation connecting to several servers', PING = 'ICMP: large ping packets';
for (let i = 0; i < 110; i++) A(at(1, 30) + i * 70 + int(0, 30), SCAN, 'Medium', '10.99.0.20', `10.10.0.${int(10, 60)}`, int(1, 1024), 'core-1');
for (let i = 0; i < 38; i++) A(at(0, 10) + i * 1800 + int(0, 600), TLSOLD, 'Low', `10.20.9.${int(200, 210)}`, '10.10.0.45', 443, 'core-1');
for (let i = 0; i < 31; i++) A(at(0, 0) + int(0, 23 * 3600), SQLI, 'Low', `198.51.100.${int(2, 250)}`, '10.30.0.10', 443, 'edge-1');
const NEWDOM_LAPTOPS = ['10.20.5.12', '10.20.5.40', '10.20.3.19'];
for (let i = 0; i < 9; i++) A(at(9, 0) + int(0, 8 * 3600), NEWDOM, 'Low', NEWDOM_LAPTOPS[i % 3], '10.30.0.53', 53, 'core-1');
for (let i = 0; i < 48; i++) A(at(0, 0) + i * 1800, PING, 'Low', '10.99.0.12', `10.10.0.${int(10, 60)}`, 0, 'core-1');
// The one that matters: a VPN-pool address reaching several servers over SMB, outside any change window.
const ODD = '10.20.6.31';
for (const [m, d] of [[12, 21], [12, 22], [13, 23], [13, 24]]) A(at(3, m, int(0, 59)), SMB, 'Medium', ODD, `10.10.0.${d}`, 445, 'core-1');
A(at(3, 14, 5), SCAN, 'Medium', ODD, '10.10.0.0/24', 0, 'core-1');
alerts.sort((x, y) => x.TimeGenerated.localeCompare(y.TimeGenerated));

const R = (id, label) => ({ id, label });
const context = '| Address | What it is | Notes |\n| --- | --- | --- |\n| 10.99.0.20 | Vulnerability scanner (management zone) | **CHG-310**: scheduled nightly scans 01:30–03:30 |\n| 10.99.0.12 | Network monitoring server | Sends large ICMP packets every 30 minutes to measure path MTU |\n| 10.20.9.200–210 | Branch label printers | Firmware supports only TLS 1.0; replacement ordered for April (CHG-334) |\n| 10.20.5.12, 10.20.5.40, 10.20.3.19 | Marketing laptops | New events partner announced last week (domain verified by IT) |\n| 10.20.6.0/24 | VPN address pool | Addresses are reassigned; check VPN logs for the user |\n| 10.30.0.10 | Public website (DMZ) | Web logs show all requests with SQL keywords received HTTP 404 or 403 |\n| 10.10.0.21–24 | Core banking application and database servers | Restricted data |\n\nVPN log extract: **10.20.6.31** assigned to user **t.okafor** (Finance) at 02:58, from a device that is not company-managed. No change ticket covers activity from 10.20.6.0/24 tonight.';

const lab = {
    slug: 'ids-alert-triage',
    title: 'Triage a night of IDS alerts and tune the rules',
    difficulty: 'intermediate',
    labType: 'detection_engineering',
    summary: 'Use KQL over a night of synthetic IDS alerts to find what is noise, what is authorised and what needs escalating, then design tuning that cuts noise without creating blind spots.',
    minutes: 60,
    moduleSlug: 'ids-ips',
    passPercent: 80,
    content: {
        objectives: ['Summarise alert volume by rule', 'Triage alerts with context', 'Tell true positives, benign true positives and false positives apart', 'Design scoped tuning', 'Write a tuning plan'],
        scenario: `The overnight shift at Kanem Financial Services (fictional) ended with **${alerts.length} IDS alerts** and a tired analyst. You are asked to triage the night and propose tuning so tomorrow is better.`,
        scope: 'IdsAlerts is a synthetic table for Tuesday 17 March 2026 (UTC). The asset and change context is in the document tab.',
        instructions: '1. Read the context document, then explore IdsAlerts in the KQL console.\n2. Answer tasks 1–7.\n3. Write the tuning plan in task 8. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic and generated for training.',
        workspace: { kind: 'kql',
            sampleQuery: 'IdsAlerts\n| take 20',
            tables: [{ name: 'IdsAlerts', description: 'Network IDS alerts, 17 March 2026.', columns: ['TimeGenerated', 'Signature', 'Severity', 'SrcIP', 'DstIP', 'DstPort', 'Sensor'], rows: alerts }],
            documents: [{ title: 'Asset, change and VPN context', format: 'markdown', body: context }] },
        tasks: [
            { id: 'volume', points: 2, prompt: 'Write a query that returns **one row per Signature** with the number of alerts for each.', hints: ['| summarize count() by Signature'], input: { type: 'kql', table: 'IdsAlerts' } },
            { id: 'sources', points: 1, prompt: 'Which source address raised the **"Internal port sweep"** alert outside the scanner?', hints: ['| where Signature == "Internal port sweep" | distinct SrcIP'], input: { type: 'text', placeholder: '10.x.x.x' } },
            { id: 'triage', points: 2, prompt: 'Classify each group of alerts.', hints: ['Lesson 4: what would a true positive look like, and what does the context say?'], input: { type: 'grid', rows: [R('scan', 'Port sweeps from 10.99.0.20, 01:30–03:30'), R('ping', 'Large ICMP from 10.99.0.12'), R('tls', 'Outdated TLS from the branch label printers'), R('sqli', 'SQL keywords in URLs to the website, all answered 404/403'), R('newdom', 'Newly observed domain from marketing laptops'), R('smb', 'SMB to four core servers plus a port sweep from 10.20.6.31')], columns: [{ id: 'outcome', label: 'Outcome', options: [R('tp', 'True positive: escalate'), R('btp', 'Benign true positive: authorised or expected, document'), R('fp', 'False positive: the rule is wrong for this traffic'), R('lowtp', 'Real but failed or low risk: record and track')] }] } },
            { id: 'escalate', points: 1, prompt: 'What should happen with the 10.20.6.31 alerts?', hints: ['Who was on that address, from what device, and what did it touch?'], input: { type: 'choice', options: [R('escalate', 'Escalate now: Finance user t.okafor on an unmanaged device reached four restricted servers over SMB and swept the server zone, with no change ticket; check flows, VPN and endpoint data, and consider suspending the VPN session'), R('suppress', 'Add 10.20.6.0/24 to the scanner exception'), R('wait', 'Wait for tomorrow\'s shift'), R('close', 'Close: VPN users often do this')] } },
            { id: 'scanner', points: 1, prompt: 'How should the scanner alerts be tuned?', hints: ['Lesson 4 table.'], input: { type: 'choice', options: [R('scoped', 'A scoped exception: source 10.99.0.20, this rule only, 01:30–03:30, tied to CHG-310, with an expiry review'), R('disable', 'Disable the port-sweep rule'), R('subnet', 'Suppress the rule for the whole 10.0.0.0/8 range'), R('threshold', 'Raise the threshold to 500 sweeps per minute for everyone')] } },
            { id: 'others', points: 2, prompt: 'Which other tuning changes are sensible? Select all that apply.', hints: ['Each change must not hide something useful.'], input: { type: 'multi', options: [R('ping', 'Scoped exception for 10.99.0.12 on the large-ICMP rule'), R('printers', 'Keep the TLS alert but lower it to informational for the printers until CHG-334 completes, then remove the exception'), R('sqli', 'Raise SQL-keyword alerts to High only when the web server responds with HTTP 200 or 500'), R('enrich', 'Raise severity automatically when the destination is a restricted core server'), R('disableall', 'Disable every Low-severity rule'), R('vpn', 'Exclude the VPN pool from all internal rules')] } },
            { id: 'gap', points: 1, prompt: 'All the alerts come from sensors core-1 and edge-1. What gap should the tuning plan also mention?', hints: ['Lesson 3: where else should sensors look?'], input: { type: 'choice', options: [R('vpn', 'There is no sensor or flow telemetry after the VPN concentrator or between the core banking application and database tiers, so movement there would be missed'), R('none', 'No gap: two sensors are enough'), R('guest', 'Guest Wi-Fi needs the most sensors'), R('printers', 'Every printer needs its own sensor')] } },
            { id: 'plan', points: 2, prompt: 'Write the **tuning plan** for the SOC lead: alert volume before and expected after, each tuning change with its scope, owner and review date, what you escalated and why, the coverage gap, and how you will check that tuning did not hide real activity.', hints: ['Lesson 2: tune for precision, test for coverage.'], input: { type: 'deliverable', title: 'IDS tuning plan', competency: 'Triaging and tuning network detections', minWords: 150, rubric: ['States the volume by rule and the expected reduction', 'Each change is scoped (source, rule, time) with owner and review date', 'Explains the escalation of 10.20.6.31 with evidence', 'Identifies the sensor coverage gap', 'Describes how tuning will be tested, for example replaying known-bad examples'], template: 'Volume today:\n\nTuning changes (scope, owner, review date):\n\nEscalated:\n\nCoverage gap:\n\nTesting the tuning:\n' } },
        ],
        reflection: ['What would have happened if the scanner exception had been scoped to the whole subnet?', 'Which of tonight\'s rules gives the most value per alert?', 'How would you know if a tuning change caused a false negative?', 'What telemetry would have made the 10.20.6.31 decision faster?'],
    },
    validator: {
        tasks: {
            volume: { type: 'kql', column: 'Signature', values: [SCAN, TLSOLD, SQLI, NEWDOM, SMB, PING], mode: 'set', unique: true, feedback: { correct: 'Right: six rules, dominated by the scanner and monitoring.', incorrect: 'Summarize by Signature so each appears once.' } },
            sources: { type: 'text', accept: [ODD], feedback: { correct: 'Right.', incorrect: 'List the distinct SrcIP values for that signature.' } },
            triage: { type: 'grid', minCorrectRatio: 0.8, cells: { scan: { outcome: ['btp'] }, ping: { outcome: ['fp', 'btp'] }, tls: { outcome: ['lowtp', 'btp'] }, sqli: { outcome: ['lowtp'] }, newdom: { outcome: ['btp'] }, smb: { outcome: ['tp'] } }, feedback: { correct: 'Your triage matches.', incorrect: 'Use the context: authorised scanner, monitoring server, known printers, failed web probes, a verified partner, and one unexplained VPN user.' } },
            escalate: { type: 'choice', correct: 'escalate', feedback: { correct: 'Right.', incorrect: 'An unmanaged device reaching restricted servers with no ticket needs escalation now.' } },
            scanner: { type: 'choice', correct: 'scoped', feedback: { correct: 'Right: and it would not have hidden 10.20.6.31.', incorrect: 'Broad suppression or disabling would have hidden tonight\'s real alert.' } },
            others: { type: 'multi', correct: ['ping', 'printers', 'sqli', 'enrich'], feedback: { correct: 'Right.', incorrect: 'Disabling all Low rules or excluding the VPN pool would create blind spots; the VPN pool produced the real alert.' } },
            gap: { type: 'choice', correct: 'vpn', feedback: { correct: 'Right.', incorrect: 'Think about where 10.20.6.31\'s traffic entered, and where restricted data moves.' } },
            plan: { type: 'deliverable', minWords: 150, mustMention: [
                { label: 'the scanner exception scoped to 10.99.0.20', any: ['10.99.0.20', 'scanner'] },
                { label: 'the escalation of 10.20.6.31', any: ['10.20.6.31', 't.okafor'] },
                { label: 'owners or review dates', any: ['owner', 'review'] },
                { label: 'the sensor gap', any: ['sensor', 'coverage', 'vpn concentrator'] },
                { label: 'testing the tuning', any: ['test', 'replay', 'known-bad'] },
            ], feedback: { correct: 'Tuning plan complete. Compare it with the rubric and the debrief.', incorrect: 'Your tuning plan is not complete yet.' } },
        },
        debrief: `## Worked solution\n\n**Volume:**\n\n\`\`\`text\nIdsAlerts\n| summarize Alerts = count() by Signature\n| sort by Alerts desc\n\`\`\`\n\nThe scanner's port sweeps (110) and the monitoring server's ICMP (48) make up most of the night; the printers' TLS alerts (38) and website probes (31) follow; 9 newly observed domain alerts and 5 alerts from 10.20.6.31.\n\n**Triage:** scanner, benign true positive (CHG-310); large ICMP, false positive for the monitoring server's MTU checks; printers, a real policy issue already being fixed (CHG-334); SQL keywords, real but failed probes (404/403); new domain, benign (verified partner); **10.20.6.31, true positive: escalate**. A Finance user on an unmanaged device reached four restricted servers over SMB and swept the server zone at 03:12–03:14, with no ticket.\n\n**Tuning that cuts noise without blind spots:**\n\n| Change | Scope | Owner / review |\n| --- | --- | --- |\n| Scanner exception | Source 10.99.0.20, port-sweep rule only, 01:30–03:30, CHG-310 | SOC lead, review quarterly |\n| Monitoring exception | Source 10.99.0.12, large-ICMP rule only | SOC lead, quarterly |\n| Printer TLS to informational | 10.20.9.200–210 until CHG-334 closes | Network team, April |\n| SQL keywords | High only when the response is 200 or 500 | Detection engineer |\n| Enrichment | Raise severity for restricted core servers as destination | Detection engineer |\n\nExpected effect: about 160 fewer alerts a night (the scanner and monitoring exceptions), with the printers downgraded and the 10.20.6.31 alerts still firing.\n\n**Gap:** no sensor after the VPN concentrator or between the core banking tiers; add one (or flow telemetry) there.\n\n**Testing tuning:** replay tonight's alerts and known-bad examples against the new rules; confirm that a sweep from any address other than 10.99.0.20 still alerts.\n\n## What you have practised\n\nThe daily detection work of a SOC: triage with context, scoped tuning and measuring what you can see.\n\n**Next:** module 6 covers secure remote access.`,
    },
};

replaceModule(p.data, 'ids-ips', {
    slug: 'ids-ips',
    title: 'IDS, IPS and Network Detection',
    purpose: 'Detection tools only help if their alerts are trusted. This module covers how network detection works, alert quality, sensor placement and telemetry, triage and tuning, and the limits that encryption and prevention impose. You finish by triaging a night of alerts and writing a tuning plan.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written tuning plan.',
    prerequisiteKnowledge: 'Modules 1, 3 and 4.',
    objectives: ['Explain detection methods, IDS versus IPS, and alert contents', 'Measure alert quality and coverage', 'Place sensors and collect telemetry', 'Triage alerts with context', 'Tune detections without creating blind spots', 'Explain encrypted-traffic limits and TLS inspection trade-offs'],
    keyConcepts: ['Signature, anomaly and protocol analysis', 'IDS and IPS', 'True and false positives and negatives', 'Coverage', 'Tap, SPAN and inline', 'Flow and protocol telemetry', 'Triage routine', 'Scoped exceptions and thresholds', 'Enrichment', 'Selective TLS inspection'],
    assessmentCriteria: ['Summarises alert volume with KQL', 'Triages alerts correctly using context', 'Designs scoped, safe tuning', 'Writes a complete tuning plan'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab. Lessons name Suricata and Zeek as example open-source tools; the concepts apply to commercial products.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'ids-alert-triage', lab);

const C = 'detection';
replaceQuestions(p.data, 'net-ids-', [
    single('net-ids-01', C, 'foundation', 'Compare detection methods', 'Which method compares traffic with a baseline of normal behaviour?', ['Signature-based', '*Anomaly-based', 'Stateful protocol analysis', 'Packet filtering'], 'It can catch the unknown but is noisier.'),
    single('net-ids-02', C, 'foundation', 'Compare IDS and IPS', 'What is the main difference between an IDS and an IPS?', ['There is none', '*An IPS sits inline and can block; an IDS only alerts', 'An IDS encrypts traffic', 'An IPS only works on hosts'], 'Prevention needs inline placement.'),
    single('net-ids-03', C, 'foundation', 'Classify outcomes', 'Benign traffic triggers an alert because the rule is wrong for it. What is this?', ['True positive', '*False positive', 'True negative', 'False negative'], 'Wasted effort and lost trust.'),
    single('net-ids-04', C, 'intermediate', 'Classify outcomes', 'An authorised scanner triggers a port-scan rule during its scheduled window. What is this?', ['False positive', '*Benign true positive', 'False negative', 'True negative'], 'The rule worked; the activity is authorised.'),
    single('net-ids-05', C, 'intermediate', 'Measure detections', 'How do you find what attacker behaviours you cannot detect at all?', ['Count alerts', '*Map detections to ATT&CK techniques and look for gaps', 'Ask users', 'Check CPU use'], 'Coverage mapping shows blind spots.'),
    single('net-ids-06', C, 'foundation', 'Place sensors', 'Which feed is best for a sensor on a busy core link?', ['A mirror port under load', '*A passive network tap', 'Email', 'A printer port'], 'Taps copy everything reliably.'),
    single('net-ids-07', C, 'intermediate', 'Place sensors', 'Where must a sensor sit to see VPN users\' traffic to internal servers?', ['Only at the internet edge', '*After the VPN concentrator, inside the network', 'In the guest network', 'On the website'], 'Traffic is decrypted after the concentrator.'),
    multiple('net-ids-08', C, 'intermediate', 'Collect telemetry', 'Which telemetry helps triage an alert? Select all that apply.', ['*Flow records for the hosts involved', '*DNS and TLS logs', '*VPN and authentication logs', 'The rule author\'s name only'], 'Context decides the outcome.'),
    single('net-ids-09', C, 'intermediate', 'Tune safely', 'Which tuning creates the biggest blind-spot risk?', ['A scoped exception for one source and rule', '*Disabling a rule without a replacement', 'Enrichment with asset data', 'Raising severity for restricted servers'], 'Disabling removes visibility.'),
    single('net-ids-10', C, 'intermediate', 'Tune safely', 'Why scope a scanner exception to its source, rule and time window?', ['It is faster to write', '*So the same rule still alerts for any other source', 'Scanners require it', 'To save disk space'], 'Context, not the rule, separates benign from malicious.'),
    single('net-ids-11', C, 'foundation', 'Triage', 'What is the first step of the triage routine?', ['Close the alert', '*Understand what the rule detects and what a true positive would look like', 'Disable the rule', 'Email the user'], 'Read the rule before the data.'),
    single('net-ids-12', C, 'intermediate', 'Handle encryption', 'What remains visible to a sensor in TLS 1.3 traffic without decryption?', ['Page content', '*Addresses, timing, volume and usually the server name', 'Passwords', 'Certificates'], 'Metadata remains.'),
    single('net-ids-13', C, 'intermediate', 'Decide on inspection', 'Which is a good TLS inspection policy?', ['Decrypt everything, no exceptions', '*Inspect selectively with documented exemptions for categories such as banking and health', 'Never inspect anything', 'Inspect only on weekends'], 'Balance visibility, privacy and risk.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/ids-ips/network-detection-basics.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 5 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions; alerts', alerts.length);
