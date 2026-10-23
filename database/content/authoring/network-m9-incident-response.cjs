// Network Security, module 9: Network Security Monitoring and Incident Response.
// The lab replays the firewall weaknesses from module 3 (rules 2 and 4, logging off) as an overnight
// incident across four synthetic tables. External addresses use the documentation ranges.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', r = 'open_resource';
upsertSources([
    { key: 'rfc-7011', title: 'RFC 7011: Specification of the IP Flow Information Export (IPFIX) Protocol', publisher: 'IETF (RFC Editor)', url: 'https://www.rfc-editor.org/rfc/rfc7011', topic: 'Flow data', sourceType: o, versionNote: 'RFC 7011; check for updating RFCs.', supports: 'What flow records contain and how they are exported.' },
    { key: 'cisa-event-logging', title: 'Best Practices for Event Logging and Threat Detection', publisher: 'CISA and partner agencies', url: 'https://www.cisa.gov/resources-tools/resources/best-practices-event-logging-and-threat-detection', topic: 'Log management', sourceType: o, versionNote: 'Joint guidance, August 2024.', supports: 'Logging policy, centralisation, retention and time synchronisation.' },
    { key: 'first-tlp', title: 'Traffic Light Protocol (TLP)', publisher: 'FIRST', url: 'https://www.first.org/tlp/', topic: 'Information sharing', sourceType: r, versionNote: 'TLP 2.0.', supports: 'Labels for how far incident information may be shared.' },
    { key: 'attack-t1110', title: 'ATT&CK T1110: Brute Force', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1110/', topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page; ATT&CK is versioned.', supports: 'Detection notes for repeated authentication attempts.' },
    { key: 'attack-t1021-002', title: 'ATT&CK T1021.002: Remote Services: SMB/Windows Admin Shares', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1021/002/', topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page; ATT&CK is versioned.', supports: 'Detection notes for unexpected SMB connections.' },
    { key: 'attack-t1041', title: 'ATT&CK T1041: Exfiltration Over C2 Channel', publisher: 'MITRE', url: 'https://attack.mitre.org/techniques/T1041/', topic: 'Threat behaviour', sourceType: r, versionNote: 'Live page; ATT&CK is versioned.', supports: 'Detection notes for data leaving over an existing session.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'log-sources-and-flow-data', title: 'Network evidence sources and flow data', minutes: 30, sources: ['nist-sp-800-92', 'rfc-7011', 'cisa-event-logging'],
        objectives: ['Describe what each network evidence source answers and misses', 'Explain flow records and why they matter', 'Make evidence usable: time sync, logging, collection, retention, inventory'],
        checks: [
            { question: 'An allow rule has logging switched off. Which source can still show the connections it allowed?', options: ['The firewall log', 'Flow records', 'The DHCP log', 'None'], answerIndex: 1, explanation: 'Flow records summarise conversations regardless of firewall logging.' },
            { question: 'Why must all devices use NTP and log in UTC?', options: ['It saves disk space', 'So events from different sources can be lined up', 'It is faster', 'Firewalls require it'], answerIndex: 1, explanation: 'Correlation depends on consistent time.' },
            { question: 'What do flow records contain? Select all that apply.', options: ['Source and destination addresses and ports', 'Duration', 'Bytes in each direction', 'The full content of each packet'], answerIndexes: [0, 1, 2], explanation: 'Flows are summaries, not content.' },
        ] },
    { slug: 'correlation-in-a-siem', title: 'Correlation in a SIEM', minutes: 35, sources: ['nist-sp-800-92', 'cisa-event-logging'],
        objectives: ['Explain normalisation', 'Correlate by address and time across sources', 'Write simple KQL patterns and design correlation rules'],
        checks: [
            { question: 'On an inbound session from the internet, large "bytes to source" means what?', options: ['Data is arriving', 'Data may be leaving your network through that session', 'The session failed', 'Nothing'], answerIndex: 1, explanation: 'Bytes sent back to the external source are leaving.' },
            { question: 'Which is a sequence correlation rule?', options: ['More than 1 GB in an hour', 'An IDS alert on a host followed within an hour by a new internal connection from it', 'Any traffic on port 53', 'Daily backup completed'], answerIndex: 1, explanation: 'Sequences link events in order.' },
            { question: 'Which KQL operator gives one row per value with a total?', options: ['take', 'summarize … by', 'project', 'sort'], answerIndex: 1, explanation: 'summarize aggregates by groups.' },
        ] },
    { slug: 'triage-and-escalation', title: 'Triage, severity and escalation', minutes: 25, sources: ['nist-sp-800-61r3', 'first-tlp'],
        objectives: ['Distinguish events, alerts and incidents', 'Set severity from confidence and impact', 'Run the first 30 minutes and communicate safely'],
        checks: [
            { question: 'Data from a restricted file server is confirmed to have left the network. What severity fits?', options: ['Low', 'Medium', 'Critical', 'Informational'], answerIndex: 2, explanation: 'Confirmed, high impact.' },
            { question: 'Why confirm key facts from two sources?', options: ['It is required by every law', 'One source can be wrong or incomplete', 'It slows the attacker', 'SIEMs need it'], answerIndex: 1, explanation: 'Corroboration avoids false conclusions.' },
            { question: 'What does the Traffic Light Protocol label?', options: ['Firewall rule priority', 'How far information may be shared', 'Network speed', 'Severity'], answerIndex: 1, explanation: 'TLP labels sharing limits.' },
        ] },
    { slug: 'containment-and-evidence', title: 'Containment, evidence and recovery', minutes: 30, sources: ['nist-sp-800-61r3', 'nist-sp-800-86'],
        objectives: ['Choose the narrowest effective containment', 'Preserve evidence before and during containment', 'Eradicate, rebuild, reset, restore and verify'],
        checks: [
            { question: 'A compromised server is still sending data out. What comes first?', options: ['A full forensic image taking six hours', 'Containment, such as isolating the host, while preserving what you can', 'Writing the report', 'Waiting for the weekly meeting'], answerIndex: 1, explanation: 'Stop ongoing harm first.' },
            { question: 'Why isolate a host rather than power it off?', options: ['It is cheaper', 'Volatile evidence such as memory and connections is kept', 'Power buttons are dangerous', 'It is faster to rebuild'], answerIndex: 1, explanation: 'Powering off loses memory evidence.' },
            { question: 'Which belong in evidence preservation? Select all that apply.', options: ['Export logs for the incident window and record hashes', 'Save the firewall configuration as it was', 'Record your own containment changes', 'Delete old logs to save space'], answerIndexes: [0, 1, 2], explanation: 'Preserve, and record what you change.' },
        ] },
    { slug: 'reporting-and-lessons-learned', title: 'Reporting and lessons learned', minutes: 25, sources: ['nist-sp-800-61r3'],
        objectives: ['Write a complete incident report', 'Track response metrics', 'Run a blameless review with owned actions'],
        checks: [
            { question: 'What is dwell time?', options: ['Time to rebuild a server', 'How long the intruder was present before detection', 'Report writing time', 'Firewall latency'], answerIndex: 1, explanation: 'From first malicious activity to detection.' },
            { question: 'What makes a review blameless?', options: ['Nobody attends', 'It focuses on improving systems, not punishing people', 'No actions are agreed', 'Only managers speak'], answerIndex: 1, explanation: 'People share more when they are not blamed.' },
            { question: 'What must each recommendation have? Select all that apply.', options: ['An owner', 'A priority', 'A date', 'A vague intention'], answerIndexes: [0, 1, 2], explanation: 'Otherwise nothing changes.' },
        ] },
];

// ---- Synthetic data ----------------------------------------------------------------------------
let seed = 1403;
const rnd = () => ((seed = (Math.imul(seed, 1103515245) + 12345) >>> 0) / 4294967296);
const int = (a, b) => a + Math.floor(rnd() * (b - a + 1));
const pick = (a) => a[Math.floor(rnd() * a.length)];
const BASE = Date.UTC(2026, 2, 13, 22, 0, 0) / 1000;   // Friday 13 March 2026, 22:00 UTC
const at = (h, m, s = 0) => (h < 22 ? BASE + (h + 2) * 3600 : BASE + (h - 22) * 3600) + m * 60 + s;   // hours 22–23 Friday, 0–6 Saturday
const iso = (t) => new Date(t * 1000).toISOString().replace('.000', '');

const X1 = '203.0.113.45', X2 = '203.0.113.77', VENDOR = '10.30.0.40', DMZDNS = '10.30.0.53', FILESRV = '10.10.0.20';
const flows = [], fw = [], ids = [], dns = [];
const F = (t, SrcIP, DstIP, DstPort, Protocol, BytesToDst, BytesToSrc, DurationSec) => flows.push({ TimeGenerated: iso(t), SrcIP, DstIP, DstPort, Protocol, BytesToDst, BytesToSrc, DurationSec });
const W = (t, SrcIP, DstIP, DstPort, Protocol, Action, Rule) => fw.push({ TimeGenerated: iso(t), SrcIP, DstIP, DstPort, Protocol, Action, Rule });
const I = (t, SrcIP, DstIP, DstPort, Signature, Severity) => ids.push({ TimeGenerated: iso(t), SrcIP, DstIP, DstPort, Signature, Severity });
const N = (t, ClientIP, Query, Type, Answer) => dns.push({ TimeGenerated: iso(t), ClientIP, Query, Type, Answer });

// Incident. Rule 2 (any → dmz, no log) lets SSH reach the vendor box; rule 4 (dmz → servers tcp any, no log) lets it reach SMB.
for (let i = 0; i < 60; i++) F(at(1, 38) + i * 13, X1, VENDOR, 22, 'TCP', int(2600, 3400), int(3000, 3900), int(3, 8));
const EXFIL = 1850000000;
F(at(1, 52, 10), X1, VENDOR, 22, 'TCP', 4200000, EXFIL, 7380);
I(at(1, 45, 2), X1, VENDOR, 22, 'SSH: many failed authentication attempts from one source', 'Medium');
I(at(1, 52, 12), X1, VENDOR, 22, 'SSH: successful authentication after repeated failures', 'High');
F(at(2, 4, 12), VENDOR, DMZDNS, 53, 'UDP', 78, 94, 0);
N(at(2, 4, 12), VENDOR, 'pkg-mirror-tx.example', 'A', X2);
for (const m of [5, 20, 35]) { F(at(2, m, 3), VENDOR, X2, 443, 'TCP', 60, 0, 0); W(at(2, m, 3), VENDOR, X2, 443, 'TCP', 'Deny', '12'); }
const SERVERS = ['10.10.0.21', '10.10.0.22', '10.10.0.23', '10.10.0.24', '10.10.0.25'];
SERVERS.forEach((s, i) => F(at(2, 9, 5 + i * 4), VENDOR, s, 445, 'TCP', int(300, 700), int(200, 600), 1));
F(at(2, 11, 40), VENDOR, FILESRV, 445, 'TCP', 3100000, 1920000000, 2350);
I(at(2, 11, 41), VENDOR, FILESRV, 445, 'Policy: SMB from DMZ to server zone', 'Medium');

// Normal traffic.
const ext = () => `198.51.100.${int(2, 250)}`;
const users = () => `10.20.${int(1, 9)}.${int(10, 240)}`;
for (let t = at(22, 0); t < at(6, 0); t += int(60, 240)) {
    const src = ext();
    F(t, src, '10.30.0.10', 443, 'TCP', int(800, 4000), int(20000, 400000), int(1, 20));
    if (rnd() < 0.4) W(t, src, '10.30.0.10', 443, 'TCP', 'Allow', '1');
}
for (let t = at(22, 5); t < at(6, 0); t += int(300, 900)) { const u = users(); F(t, u, '10.10.0.15', 443, 'TCP', int(2000, 9000), int(40000, 900000), int(2, 60)); W(t, u, '10.10.0.15', 443, 'TCP', 'Allow', '3'); }
for (let t = at(22, 10); t < at(6, 0); t += int(600, 1500)) { const u = users(), d = `192.0.2.${int(20, 90)}`; F(t, u, d, 443, 'TCP', int(3000, 20000), int(50000, 2000000), int(2, 120)); W(t, u, d, 443, 'TCP', 'Allow', '9'); N(t, u, pick(['outlook.office.example', 'news.example', 'portal.kanem.example', 'teams.example']), 'A', '192.0.2.40'); }
for (const t of [at(23, 0), at(0, 30), at(4, 15)]) { const d = pick(SERVERS); F(t, '10.99.0.12', d, 22, 'TCP', 40000, 90000, 600); W(t, '10.99.0.12', d, 22, 'TCP', 'Allow', '5'); }
F(at(1, 0), '10.30.0.10', '10.10.0.22', 1433, 'TCP', 120000, 3400000, 95);   // nightly legacy batch job over rule 4
F(at(3, 0), '10.10.0.30', '192.0.2.50', 443, 'TCP', 900000, 310000000, 420);  // patch download over rule 8
N(at(3, 0), '10.10.0.30', 'updates.os-vendor.example', 'A', '192.0.2.50');
N(at(22, 40), VENDOR, 'support.vendor-tools.example', 'A', '192.0.2.77');
for (let t = at(22, 20); t < at(6, 0); t += int(900, 2400)) { const s = ext(); W(t, s, `10.30.0.${int(2, 60)}`, pick([23, 3389, 445, 8080]), 'TCP', 'Deny', '12'); }
for (let t = at(23, 0); t < at(5, 0); t += 3600) I(t, '10.99.0.5', '10.10.0.0/24', 0, 'ICMP sweep (authorised vulnerability scanner)', 'Low');
for (let i = 0; i < 6; i++) I(at(22, 30) + i * 2900, ext(), '10.30.0.10', 443, 'Web: SQL keywords in URL', 'Low');
for (const a of [flows, fw, ids, dns]) a.sort((x, y) => x.TimeGenerated.localeCompare(y.TimeGenerated));

// ---- Lab ---------------------------------------------------------------------------------------
const R = (id, label) => ({ id, label });
const rules = 'Extract of FW-HQ-01 (from the module 3 review; not yet changed):\n\n#   Action  Source      Destination  Proto  Port  Log  Description\n1   ALLOW   internet    dmz          tcp    443   yes  Public website\n2   ALLOW   any         dmz          any    any   no   TEMP vendor troubleshooting INC-2291 (Nov 2025)\n3   ALLOW   users       servers      tcp    443   yes  Core banking application\n4   ALLOW   dmz         servers      tcp    any   no   Legacy batch job (owner unknown)\n5   ALLOW   management  servers      tcp    22    yes  Server administration\n8   ALLOW   servers     internet     any    any   no   Patching\n9   ALLOW   users       internet     tcp    443   yes  Web browsing\n12  DENY    any         any          any    any   yes  Default deny';
const assets = '| Address | Name | Zone | Owner | Notes |\n| --- | --- | --- | --- | --- |\n| 10.30.0.10 | dmz-web-01 | DMZ | Digital channels | Public website |\n| 10.30.0.40 | dmz-vendor-01 | DMZ | IT operations | Vendor support box set up for INC-2291; SSH with password login |\n| 10.30.0.53 | dmz-dns-01 | DMZ | Network team | Resolver for DMZ hosts |\n| 10.10.0.20 | file-srv-01 | Servers | Finance | **Finance shares: payroll, customer loan files (restricted)** |\n| 10.10.0.21–25 | app and database servers | Servers | Various | |\n| 10.10.0.30 | patch-01 | Servers | IT operations | Downloads updates nightly |\n| 10.99.0.5, 10.99.0.12 | scanner, admin workstation | Management | Security, IT | Authorised scanning |';

const lab = {
    slug: 'network-incident-investigation',
    title: 'Investigate an overnight network incident',
    difficulty: 'advanced',
    labType: 'log_investigation',
    summary: 'Correlate synthetic flow, firewall, IDS and DNS records with KQL to reconstruct an overnight incident, find why the firewall logs were silent, choose containment and write the incident report.',
    minutes: 90,
    moduleSlug: 'network-ir',
    passPercent: 80,
    content: {
        objectives: ['Correlate four evidence sources by address and time', 'Explain gaps in the evidence', 'Measure what data left the network', 'Set severity and choose containment', 'Write an incident report'],
        scenario: 'Saturday 14 March 2026, 09:00 UTC, Kanem Financial Services head office (fictional). The weekly flow review shows a very large overnight transfer involving **dmz-vendor-01** (10.30.0.40). The firewall logs show nothing for that host. You are the SOC analyst on duty.',
        scope: 'FlowRecords, FirewallLogs, IdsAlerts and DnsLogs are synthetic tables covering 22:00 Friday to 06:00 Saturday (UTC). Flow byte counts are per direction: BytesToDst from the connection\'s source to its destination, BytesToSrc back again.',
        instructions: '1. Read the rule extract and asset list, then explore the tables in the KQL console.\n2. Answer tasks 1–9.\n3. Write the incident report in task 10. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic and generated for training. No real systems, people or organisations are involved.',
        workspace: { kind: 'kql',
            sampleQuery: 'IdsAlerts\n| where Severity == "High"',
            tables: [
                { name: 'FlowRecords', description: 'Flow records exported by the head-office core router.', columns: ['TimeGenerated', 'SrcIP', 'DstIP', 'DstPort', 'Protocol', 'BytesToDst', 'BytesToSrc', 'DurationSec'], rows: flows },
                { name: 'FirewallLogs', description: 'FW-HQ-01 log (only rules with logging enabled).', columns: ['TimeGenerated', 'SrcIP', 'DstIP', 'DstPort', 'Protocol', 'Action', 'Rule'], rows: fw },
                { name: 'IdsAlerts', description: 'Network IDS alerts.', columns: ['TimeGenerated', 'SrcIP', 'DstIP', 'DstPort', 'Signature', 'Severity'], rows: ids },
                { name: 'DnsLogs', description: 'DMZ and internal resolver logs.', columns: ['TimeGenerated', 'ClientIP', 'Query', 'Type', 'Answer'], rows: dns },
            ],
            documents: [
                { title: 'Firewall rules (extract)', format: 'code', language: 'text', body: rules },
                { title: 'Asset list (extract)', format: 'markdown', body: assets },
            ] },
        tasks: [
            { id: 'alerts', points: 2, prompt: 'Write a query that returns the **Signature** of every IDS alert involving 10.30.0.40 as source or destination.', hints: ['| where SrcIP == "…" or DstIP == "…"', '| distinct Signature'], input: { type: 'kql', table: 'IdsAlerts' } },
            { id: 'entry', points: 1, prompt: 'How did the intruder first reach dmz-vendor-01?', hints: ['Which rule allows SSH from the internet to the DMZ?'], input: { type: 'choice', options: [R('rule2', 'SSH from 203.0.113.45, allowed by rule 2 (the temporary any → dmz vendor rule), after many failed login attempts'), R('rule1', 'HTTPS through rule 1 to the public website'), R('vpn', 'Through the staff VPN'), R('internal', 'From a staff laptop in the users zone')] } },
            { id: 'nolog', points: 1, prompt: 'Why are there no firewall log entries for the SSH session or the SMB connections?', hints: ['Look at the Log column in the rule extract.'], input: { type: 'choice', options: [R('nolog', 'Rules 2 and 4, which allowed them, have logging switched off; the flow records are the only network evidence'), R('deleted', 'The intruder deleted the firewall logs'), R('encrypted', 'SSH and SMB are encrypted so firewalls cannot log them'), R('ntp', 'The firewall clock was wrong')] } },
            { id: 'servers', points: 2, prompt: 'Write a query over **FlowRecords** that returns each server-zone address (10.10.0.x) that 10.30.0.40 connected to.', hints: ['| where SrcIP == "10.30.0.40" and DstIP startswith "10.10.0."', '| distinct DstIP'], input: { type: 'kql', table: 'FlowRecords' } },
            { id: 'fileserver', points: 1, prompt: 'Which server sent the most data to 10.30.0.40?', hints: ['Sum BytesToSrc by DstIP.'], input: { type: 'text', placeholder: '10.10.0.x' } },
            { id: 'exfil', points: 2, prompt: 'How many **megabytes** (1 MB = 1,000,000 bytes) left Kanem to 203.0.113.45? Round to the nearest 10.', hints: ['On the inbound SSH session, data leaving is BytesToSrc.', '| where SrcIP == "203.0.113.45" | summarize sum(BytesToSrc)'], input: { type: 'number', unit: 'MB' } },
            { id: 'second', points: 1, prompt: 'What do the DNS lookup at 02:04 and the denied connections to 203.0.113.77 show?', hints: ['Check DnsLogs and FirewallLogs for 10.30.0.40.'], input: { type: 'choice', options: [R('blocked', 'The box tried to reach a second external server (pkg-mirror-tx.example) on 443, and the default-deny rule blocked it; block the domain and check whether any other host looked it up'), R('normal', 'Normal vendor updates'), R('success', 'A successful upload to 203.0.113.77'), R('dns-failure', 'A DNS outage')] } },
            { id: 'severity', points: 1, prompt: 'What severity should the incident have?', hints: ['Lesson 3 matrix: confidence and impact.'], input: { type: 'choice', options: [R('critical', 'Critical: confirmed, with restricted finance and customer data from file-srv-01 likely taken'), R('medium', 'Medium: only a DMZ box is involved'), R('low', 'Low: the outbound connections were blocked')] } },
            { id: 'contain', points: 2, prompt: 'Which actions should be taken now? Select all that apply.', hints: ['Narrowest effective containment, evidence first where it costs little.'], input: { type: 'multi', options: [R('export', 'Export the four tables for 22:00–06:00 and record hashes'), R('isolate', 'Isolate dmz-vendor-01 (quarantine, do not power off) and ask IT operations to capture memory and disk'), R('rule2', 'Disable rule 2 and restrict rule 4 to the batch job\'s source, destination and port, with logging on'), R('block', 'Block 203.0.113.45, 203.0.113.77 and pkg-mirror-tx.example'), R('creds', 'Reset credentials used on dmz-vendor-01 and check file-srv-01 for changes'), R('rebuild', 'Wipe and rebuild dmz-vendor-01 immediately, before anything else'), R('keep', 'Keep rule 2 open so the vendor can carry on working')] } },
            { id: 'report', points: 2, prompt: 'Write the **incident report** for the CISO: summary, timeline (UTC) with sources, root cause, scope and data impact, containment and evidence, gaps, and recommendations with owners.', hints: ['Lesson 5 gives the structure.'], input: { type: 'deliverable', title: 'Network incident report: dmz-vendor-01', competency: 'Correlating network evidence and reporting an incident', minWords: 160, rubric: ['Gives a sourced UTC timeline from the failed logins to the end of the SSH session', 'States the root cause: rules 2 and 4 with logging off, password SSH on the vendor box', 'Quantifies the data (about 1,920 MB from file-srv-01, about 1,850 MB out to 203.0.113.45)', 'Lists containment and evidence preserved, and the evidence gaps', 'Recommends owned fixes: retire rule 2, tighten and log rule 4, logging on boundary rules, DMZ-to-server detections, DPO involvement'], template: 'Summary:\n\nTimeline (UTC, with sources):\n\nRoot cause:\n\nScope and data impact:\n\nContainment and evidence:\n\nGaps:\n\nRecommendations (owner, date):\n' } },
        ],
        reflection: ['Which module 3 finding, if fixed, would have prevented this incident?', 'What would you have been unable to show without flow records?', 'Which correlation rule would have alerted at 02:12 rather than Saturday morning?', 'Who outside the SOC needs to know about this, and when?'],
    },
    validator: {
        tasks: {
            alerts: { type: 'kql', column: 'Signature', values: ['SSH: many failed authentication attempts from one source', 'SSH: successful authentication after repeated failures', 'Policy: SMB from DMZ to server zone'], mode: 'set', feedback: { correct: 'Right: three alerts tell the story.', incorrect: `Filter on SrcIP == "${VENDOR}" or DstIP == "${VENDOR}" and return Signature.` } },
            entry: { type: 'choice', correct: 'rule2', feedback: { correct: 'Right.', incorrect: 'Look at the IDS alerts and flows to port 22, then the rule extract.' } },
            nolog: { type: 'choice', correct: 'nolog', feedback: { correct: 'Right: this is why every boundary-crossing allow rule should log.', incorrect: 'Check the Log column for the rules that allowed the traffic.' } },
            servers: { type: 'kql', column: 'DstIP', values: [FILESRV, ...SERVERS], mode: 'set', feedback: { correct: 'Right: five short probes and one long session.', incorrect: `Filter SrcIP == "${VENDOR}" and DstIP startswith "10.10.0.", then list DstIP.` } },
            fileserver: { type: 'text', accept: [FILESRV], feedback: { correct: 'Right: about 1.9 GB from the finance file server.', incorrect: 'Sum BytesToSrc by DstIP for flows from 10.30.0.40.' } },
            exfil: { type: 'number', value: 1850, tolerance: 10, feedback: { correct: 'Right: about 1,850 MB.', incorrect: 'Sum BytesToSrc for flows from 203.0.113.45 and divide by 1,000,000.' } },
            second: { type: 'choice', correct: 'blocked', feedback: { correct: 'Right.', incorrect: 'The lookup resolved to 203.0.113.77, and every connection to it was denied by rule 12.' } },
            severity: { type: 'choice', correct: 'critical', feedback: { correct: 'Right.', incorrect: 'The evidence confirms data left, and file-srv-01 holds restricted data.' } },
            contain: { type: 'multi', correct: ['export', 'isolate', 'rule2', 'block', 'creds'], feedback: { correct: 'Right.', incorrect: 'Preserve, isolate, close the way in, block, reset. Rebuilding first destroys evidence; keeping rule 2 leaves the door open.' } },
            report: { type: 'deliverable', minWords: 160, mustMention: [
                { label: 'dmz-vendor-01 or 10.30.0.40', any: ['dmz-vendor-01', '10.30.0.40'] },
                { label: 'rule 2', any: ['rule 2'] },
                { label: 'file-srv-01 or 10.10.0.20', any: ['file-srv-01', '10.10.0.20'] },
                { label: 'the volume of data', any: ['1,850', '1850', '1.85', '1,920', '1920', '1.9'] },
                { label: 'logging being off', any: ['logging', 'log'] },
            ], feedback: { correct: 'Report complete. Compare it with the rubric and the debrief.', incorrect: 'Your report is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Timeline (UTC, Saturday 14 March 2026)**\n\n| Time | Event | Source |\n| --- | --- | --- |\n| 01:38–01:51 | 60 short SSH connections from 203.0.113.45 to dmz-vendor-01 | FlowRecords |\n| 01:45 | "Many failed authentication attempts" | IdsAlerts |\n| 01:52:10 | Successful SSH login; session lasts 7,380 s (to about 03:55) and sends **about 1,850 MB back to 203.0.113.45** | IdsAlerts, FlowRecords |\n| 02:04 | dmz-vendor-01 looks up pkg-mirror-tx.example → 203.0.113.77 | DnsLogs |\n| 02:05, 02:20, 02:35 | Connections to 203.0.113.77:443 **denied** by rule 12 | FirewallLogs |\n| 02:09 | Short SMB probes to 10.10.0.21–25 | FlowRecords |\n| 02:11:40 | SMB session to **file-srv-01** pulls **about 1,920 MB** | FlowRecords, IdsAlerts |\n\n**Queries:**\n\n```text\nIdsAlerts\n| where SrcIP == "10.30.0.40" or DstIP == "10.30.0.40"\n| distinct Signature\n\nFlowRecords\n| where SrcIP == "10.30.0.40"\n| summarize Received = sum(BytesToSrc) by DstIP, DstPort\n| sort by Received desc\n\nFlowRecords\n| where SrcIP == "203.0.113.45"\n| summarize Out = sum(BytesToSrc)\n```\n\n**Root cause:** rule 2 (temporary vendor access since November 2025) exposed SSH with password login on dmz-vendor-01 to the internet; rule 4 let the DMZ reach every TCP port in the server zone. Both had logging off, so the firewall recorded nothing; flows were the only network evidence. The data was pulled from the file server and sent out through the intruder\'s own SSH session, which a stateful firewall treats as return traffic.\n\n**Severity:** critical. Finance and customer loan files were likely taken; brief the DPO and legal.\n\n**Containment:** export evidence; isolate dmz-vendor-01 without powering off and capture memory and disk; disable rule 2; restrict rule 4 to 10.30.0.10 → 10.10.0.22:1433 with logging; block both addresses and the domain; reset credentials; check file-srv-01.\n\n**Gaps:** no firewall logs for rules 2 and 4; no host logs reviewed yet; which files were copied is unknown until file-srv-01 is examined.\n\n**Recommendations:** retire temporary rules on their end date (module 3); log every allow rule crossing a boundary; key-based SSH only, and never internet-facing for support boxes; correlation rules for DMZ-to-server-zone connections and for large outbound volumes; quarterly rule reviews.\n\n## What you have practised\n\nCorrelating four evidence sources, reasoning about gaps and writing a report a CISO can act on: the core of network incident response.\n\n**Next:** module 10, the capstone.',
    },
};

replaceModule(p.data, 'network-ir', {
    slug: 'network-ir',
    title: 'Network Security Monitoring and Incident Response',
    purpose: 'This module brings network evidence together: log sources and flow data, correlation in a SIEM, triage and escalation, containment and evidence, and reporting. You finish by investigating an overnight incident that grew out of the firewall weaknesses from module 3.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written report.',
    prerequisiteKnowledge: 'Modules 3, 5, 7 and 8.',
    objectives: ['Use firewall, flow, IDS, DNS and other network evidence, knowing its blind spots', 'Correlate evidence by address and time with KQL', 'Triage, set severity and escalate', 'Contain while preserving evidence', 'Report incidents and drive improvements'],
    keyConcepts: ['Flow records and IPFIX', 'Logging on allow rules', 'Time synchronisation', 'Normalisation and correlation', 'Bytes per direction', 'Severity matrix', 'Containment options', 'Evidence preservation', 'Incident report', 'Blameless review'],
    assessmentCriteria: ['Reconstructs a timeline from four sources', 'Explains evidence gaps', 'Quantifies data loss', 'Chooses proportionate containment', 'Writes a complete incident report'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1.5 hours of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'network-incident-investigation', lab);

const C = 'network-ir';
replaceQuestions(p.data, 'net-ir-', [
    single('net-ir-01', C, 'foundation', 'Use evidence sources', 'Which source shows who talked to whom and how many bytes moved, even where firewall logging is off?', ['DHCP logs', '*Flow records', 'Proxy authentication logs', 'Antivirus logs'], 'Flows summarise every conversation the exporter sees.'),
    single('net-ir-02', C, 'foundation', 'Make evidence usable', 'What lets events from different devices be lined up accurately?', ['Larger disks', '*Synchronised clocks (NTP) and UTC timestamps', 'Longer passwords', 'More VLANs'], 'Consistent time underpins correlation.'),
    single('net-ir-03', C, 'intermediate', 'Close logging gaps', 'Which firewall practice would have left a record of this incident\'s connections?', ['Logging only denies', '*Logging every allow rule that crosses a trust boundary', 'Turning logging off to save space', 'Logging only rule 1'], 'The key connections used unlogged allow rules.'),
    single('net-ir-04', C, 'intermediate', 'Read flow direction', 'An inbound session from the internet shows 4 MB to the destination and 1,850 MB back to the source. What happened?', ['1,850 MB arrived at Kanem', '*About 1,850 MB left Kanem through that session', 'The session failed', 'It was a backup'], 'Bytes back to the external source are leaving.'),
    single('net-ir-05', C, 'foundation', 'Write queries', 'Which KQL pipeline returns total bytes per destination?', ['| take 10', '*| summarize sum(BytesToDst) by DstIP', '| project DstIP', '| sort by TimeGenerated'], 'summarize aggregates by group.'),
    single('net-ir-06', C, 'intermediate', 'Design correlation rules', 'Which rule would detect this incident early?', ['Alert on every DNS query', '*Alert on any new connection from the DMZ to the server zone on an unapproved port', 'Alert on web browsing', 'Alert when backups finish'], 'Policy rules on zone boundaries catch unusual paths.'),
    single('net-ir-07', C, 'foundation', 'Triage', 'What is the difference between an alert and an incident?', ['There is none', '*An incident threatens confidentiality, integrity or availability and needs a coordinated response', 'Alerts are always more serious', 'Incidents come only from IDS'], 'Declaring an incident starts the formal process.'),
    single('net-ir-08', C, 'intermediate', 'Set severity', 'Confirmed theft of restricted data from a core server is which severity?', ['Low', 'Medium', '*Critical', 'Informational'], 'Confirmed and high impact.'),
    single('net-ir-09', C, 'intermediate', 'Contain', 'A DMZ host is compromised and still connected to an attacker. What is the best first network action?', ['Power it off', '*Isolate it in quarantine while preserving memory and disk', 'Rebuild it', 'Wait for the weekly meeting'], 'Stop harm, keep evidence.'),
    multiple('net-ir-10', C, 'intermediate', 'Preserve evidence', 'Which should be preserved before rebuilding a compromised host? Select all that apply.', ['*Logs for the incident window, with hashes', '*Memory and disk images', '*The firewall configuration as it was', 'Nothing; speed matters most'], 'Preserve, then rebuild.'),
    single('net-ir-11', C, 'foundation', 'Share information', 'Which scheme labels how widely incident information may be shared?', ['CVSS', '*Traffic Light Protocol', 'ATT&CK', 'IPFIX'], 'TLP is maintained by FIRST.'),
    single('net-ir-12', C, 'foundation', 'Measure response', 'What does "time to contain" measure?', ['From intrusion to detection', '*From detection to stopping the harm', 'From the report to the review', 'Firewall latency'], 'Detection to containment.'),
    single('net-ir-13', C, 'foundation', 'Learn from incidents', 'What should every lessons-learned action have?', ['A long description', '*An owner and a date', 'The name of whoever made the mistake', 'Nothing'], 'Blameless, owned and dated.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/network-ir/network-evidence.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 9 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions; rows', flows.length, fw.length, ids.length, dns.length);
