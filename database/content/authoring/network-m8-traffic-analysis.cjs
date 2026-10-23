// Network Security, module 8: Network Traffic Analysis with Wireshark.
// Also writes the synthetic capture to public/content/network-security/ (see pcap-kanem-portal.cjs).
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');
const pcap = require('./pcap-kanem-portal.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation';
upsertSources([
    { key: 'wireshark-dfref', title: 'Wireshark Display Filter Reference', publisher: 'Wireshark Foundation', url: 'https://www.wireshark.org/docs/dfref/', topic: 'Packet analysis', sourceType: v, versionNote: 'Live reference; fields change between versions.', supports: 'Display filter field names.' },
    { key: 'wireshark-filter-man', title: 'wireshark-filter(4): Wireshark display filter syntax', publisher: 'Wireshark Foundation', url: 'https://www.wireshark.org/docs/man-pages/wireshark-filter.html', topic: 'Packet analysis', sourceType: v, versionNote: 'Manual page for the current release.', supports: 'Display filter operators and syntax.' },
    { key: 'tcpdump-pcap-filter', title: 'pcap-filter(7): packet filter syntax', publisher: 'The Tcpdump Group', url: 'https://www.tcpdump.org/manpages/pcap-filter.7.html', topic: 'Packet capture', sourceType: v, versionNote: 'Manual page.', supports: 'Capture (BPF) filter syntax.' },
    { key: 'rfc-6298', title: 'RFC 6298: Computing TCP\'s Retransmission Timer', publisher: 'IETF (RFC Editor)', url: 'https://www.rfc-editor.org/rfc/rfc6298', topic: 'Protocol standard', sourceType: o, versionNote: 'RFC 6298; check for updating RFCs.', supports: 'Retransmission timeout and back-off.' },
    { key: 'nist-sp-800-86', title: 'SP 800-86: Guide to Integrating Forensic Techniques into Incident Response', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/86/final', topic: 'Digital forensics', sourceType: o, versionNote: 'Final, August 2006. Old, but its evidence-handling principles still apply.', supports: 'Collecting and handling data as evidence.' },
    { key: 'ietf-pcap-draft', title: 'PCAP Capture File Format (draft-ietf-opsawg-pcap)', publisher: 'IETF', url: 'https://datatracker.ietf.org/doc/draft-ietf-opsawg-pcap/', topic: 'Capture file format', sourceType: 'open_resource', versionNote: 'Internet-Draft, not yet an RFC.', supports: 'The libpcap file format used by the lab capture.' },
], CHECKED);

// Write the capture and keep its packet list for the lab.
const cap = pcap.build();
pcap.verify(cap.file);
const PCAP_FILE = 'kanem-portal-slow.pcap';
const outDir = path.join(__dirname, '../../../public/content/network-security');
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, PCAP_FILE), cap.file);
const F = cap.frameNo;

const p = load('network-security');

const lessons = [
    { slug: 'capturing-safely', title: 'Capturing traffic safely and lawfully', minutes: 25, sources: ['nist-sp-800-86', 'tcpdump-pcap-filter', 'wireshark-guide'],
        objectives: ['Check authority, purpose and privacy before capturing', 'Choose a capture point', 'Use capture filters to limit what is recorded', 'Handle captures as evidence'],
        checks: [
            { question: 'What must you confirm before capturing traffic on a Kanem network?', options: ['That Wireshark is the latest version', 'That you are authorised to monitor that network for this purpose', 'That nobody is using the network', 'That the capture will be large'], answerIndex: 1, explanation: 'Capture only with authority, for a clear purpose.' },
            { question: 'What is the difference between a capture filter and a display filter?', options: ['There is none', 'A capture filter decides what is recorded; a display filter only hides packets on screen', 'Display filters delete packets', 'Capture filters only work in the cloud'], answerIndex: 1, explanation: 'Anything excluded by a capture filter is never recorded.' },
            { question: 'Which steps protect a capture that may become evidence? Select all that apply.', options: ['Record a SHA-256 hash straight away', 'Work on a copy', 'Log every transfer of the file', 'Email it to the whole team'], answerIndexes: [0, 1, 2], explanation: 'Hash, copy and keep a chain of custody.' },
        ] },
    { slug: 'wireshark-navigation-and-filters', title: 'Wireshark navigation and display filters', minutes: 30, sources: ['wireshark-guide', 'wireshark-dfref', 'wireshark-filter-man'],
        objectives: ['Set up time display and name resolution for analysis', 'Write display filters for hosts, ports, streams and problems', 'Use Conversations, Follow Stream and Expert Information'],
        checks: [
            { question: 'Which display filter shows only TCP connection attempts?', options: ['tcp.port == 443', 'tcp.flags.syn == 1 && tcp.flags.ack == 0', 'dns', 'ip.addr == 10.40.9.21'], answerIndex: 1, explanation: 'SYN set and ACK clear marks the first packet of a handshake.' },
            { question: 'Which tool shows who talked to whom and how much?', options: ['Statistics → Conversations', 'View → Zoom', 'File → Print', 'Edit → Preferences'], answerIndex: 0, explanation: 'Conversations lists address pairs with packets, bytes and duration.' },
            { question: 'Why set the time display to "seconds since previous displayed packet"?', options: ['It looks nicer', 'Delays between packets stand out', 'It encrypts the capture', 'It is required for DNS'], answerIndex: 1, explanation: 'Gaps show where time was lost.' },
        ] },
    { slug: 'tcp-behaviour', title: 'Reading TCP behaviour', minutes: 35, sources: ['rfc-9293', 'rfc-6298'],
        objectives: ['Measure round-trip time from the handshake', 'Follow sequence and acknowledgement numbers', 'Recognise unanswered SYNs, resets, retransmissions, duplicate ACKs and slow applications'],
        checks: [
            { question: 'A SYN is retransmitted after about 1, 2 and 4 seconds with no reply. What is most likely?', options: ['The application is slow', 'Something is silently dropping the traffic, or the host is unreachable', 'The server refused the connection with RST', 'DNS failed'], answerIndex: 1, explanation: 'No SYN-ACK and no RST means nothing answered.' },
            { question: 'The handshake takes 5 ms but the server sends its first response 3 seconds after the request. Where is the delay?', options: ['The network', 'The server or application', 'DNS', 'The client\'s cable'], answerIndex: 1, explanation: 'A fast handshake and a slow first response point to the application.' },
            { question: 'Which patterns indicate packet loss? Select all that apply.', options: ['Retransmissions', 'Duplicate ACKs followed by a fast retransmission', '"Previous segment not captured" at the receiver', 'A quick SYN-ACK'], answerIndexes: [0, 1, 2], explanation: 'Each shows a segment that did not arrive in time.' },
        ] },
    { slug: 'dns-and-tls-in-captures', title: 'DNS and TLS in captures', minutes: 30, sources: ['rfc-1034', 'rfc-8446', 'wireshark-dfref'],
        objectives: ['Read DNS queries, answers and response times', 'Read the TLS Client Hello and Server Hello', 'Explain what encrypted traffic still reveals'],
        checks: [
            { question: 'Where do you find the real negotiated version in a TLS 1.3 handshake?', options: ['The record header', 'The Server Hello supported_versions extension', 'The DNS answer', 'The TCP window'], answerIndex: 1, explanation: 'The record header says TLS 1.2 for compatibility.' },
            { question: 'Which TLS field shows the server name the client asked for?', options: ['Cipher suite', 'SNI (server_name extension) in the Client Hello', 'Session ID', 'Change Cipher Spec'], answerIndex: 1, explanation: 'The SNI is readable in the Client Hello.' },
            { question: 'What can you learn from encrypted HTTPS traffic without decryption? Select all that apply.', options: ['Who talked to whom and when', 'The server name (usually)', 'Handshake timing and retransmissions', 'The full URL and form data'], answerIndexes: [0, 1, 2], explanation: 'Content stays encrypted; metadata and timing remain.' },
        ] },
    { slug: 'documenting-packet-evidence', title: 'Documenting packet evidence', minutes: 25, sources: ['nist-sp-800-86', 'nist-sp-800-61r3'],
        objectives: ['Separate facts from interpretation', 'Write a repeatable findings report', 'State limitations and confidence'],
        checks: [
            { question: 'Which is a fact rather than an interpretation?', options: ['"The firewall is probably blocking it"', '"Frames 21–24 are SYNs to 10.10.0.16:443 with no reply captured"', '"The server is badly configured"', '"The user is impatient"'], answerIndex: 1, explanation: 'Facts are what the capture shows, with frame numbers.' },
            { question: 'What belongs in the scope section of a findings report? Select all that apply.', options: ['File name and SHA-256 hash', 'Capture point and time (UTC)', 'Any capture filter used', 'Personal data copied from packets'], answerIndexes: [0, 1, 2], explanation: 'Describe personal data rather than copying it.' },
            { question: 'Why state the limitations of a capture?', options: ['To make the report longer', 'Readers need to know what the capture cannot show, such as encrypted content or a single capture point', 'It is a legal requirement in every country', 'To avoid giving recommendations'], answerIndex: 1, explanation: 'Limitations set the confidence of your conclusions.' },
        ] },
];

const R = (id, label) => ({ id, label });
// Accepted spellings of "SYN set, ACK clear" (answers are lower-cased and space-collapsed before comparing).
const synFilters = [];
for (const op of ['&&', 'and']) for (const eq of ['==', ' == ']) synFilters.push(`tcp.flags.syn${eq}1 ${op} tcp.flags.ack${eq}0`);
synFilters.push('tcp.flags == 0x002', 'tcp.flags == 0x02', 'tcp.flags==0x002', 'tcp.flags==0x02');

const lab = {
    slug: 'portal-capture-analysis',
    title: 'Why is the portal slow? Analyse a capture',
    difficulty: 'intermediate',
    labType: 'packet_analysis',
    summary: 'Download a synthetic capture (or use the packet list in the browser), measure the round-trip time, read the TLS handshake, find packet loss and an unanswered server, and write a findings report.',
    minutes: 60,
    moduleSlug: 'traffic-analysis',
    passPercent: 80,
    content: {
        objectives: ['Measure round-trip time from a TCP handshake', 'Read the TLS Client Hello and Server Hello', 'Recognise loss, duplicate ACKs and fast retransmission', 'Find a server that never answers and explain the delay', 'Write a repeatable findings report'],
        scenario: 'Loans officers in Abuja say the Kanem portal takes "forever" to load. The network team captured traffic on one officer\'s laptop (10.40.9.21) while she opened the portal on Tuesday 10 March 2026. Your job is to find out why, and write it up.',
        scope: 'Kanem Financial Services and all data are fictional. The capture is synthetic: it was generated by a script for this lab, not recorded from a real network.',
        instructions: `1. Download the capture from the **Capture file** document and open it in Wireshark, **or** use the **PacketList** tab, which shows the same ${cap.packets.length} frames.\n2. Answer tasks 1–8.\n3. Write the findings report in task 9. It is saved to your portfolio.\n\nYou need 80% to pass.`,
        simulationNotice: 'This is a simulation. The capture is synthetic and contains no real traffic or personal data.',
        workspace: { kind: 'evidence',
            datasets: [{ name: 'PacketList', description: 'Wireshark-style packet list for the capture. Times are seconds since the first frame.', columns: ['No', 'Time', 'Source', 'Destination', 'Protocol', 'Length', 'Info'], rows: cap.packets }],
            documents: [
                { title: 'Capture file', format: 'markdown', body: `**[Download ${PCAP_FILE}](/content/network-security/${PCAP_FILE})** (${cap.file.length.toLocaleString('en-GB')} bytes, libpcap format, ${cap.packets.length} frames)\n\n| | |\n| --- | --- |\n| SHA-256 | \`${cap.sha256}\` |\n| Captured on | Laptop KAN-LT-121 (10.40.9.21), Abuja staff zone |\n| First frame | 2026-03-10 09:14:00.512300 UTC |\n| Capture filter | \`host 10.40.9.21\` |\n| Captured by | Network team, ticket INC-2207, with the user's manager's approval |\n\nCheck the hash after downloading. The Info column in the PacketList tab follows Wireshark's style; your Wireshark version may word some lines slightly differently or number duplicate ACKs differently.` },
                { title: 'Recent changes (from the change log)', format: 'markdown', body: '| Change | Date | Summary |\n| --- | --- | --- |\n| CHG-3318 | 6 March 2026 | `static.kanem.example` (images, scripts and styles for the portal) migrated to new server 10.10.0.16; DNS updated |\n| CHG-3320 | 7 March 2026 | WAN link Abuja–Lagos: provider maintenance, no change expected |' },
            ] },
        tasks: [
            { id: 'rtt', points: 1, prompt: 'What is the round-trip time to the portal, in **milliseconds**, measured from the handshake?', hints: [`Compare the times of frames ${F.syn} and ${F.synack}.`], input: { type: 'number', unit: 'ms' } },
            { id: 'sni', points: 1, prompt: 'Which server name (SNI) does the client send in the Client Hello?', hints: [`Frame ${F.ch}.`], input: { type: 'text', placeholder: 'e.g. host.example' } },
            { id: 'tls', points: 1, prompt: 'Which TLS version was negotiated?', hints: ['Lesson 4: don\'t trust the record header.'], input: { type: 'choice', options: [R('13', 'TLS 1.3'), R('12', 'TLS 1.2, because the record header says 0x0303'), R('10', 'TLS 1.0')] } },
            { id: 'loss', points: 1, prompt: `What happened in frames ${F.gap}–${F.retrans + 1}?`, hints: ['What did the client receive first, and what did it ask for?'], input: { type: 'choice', options: [R('fastretrans', 'One server segment was lost before reaching the laptop; the client sent duplicate ACKs and the server fast-retransmitted it about 39 ms later'), R('reset', 'The server reset the connection'), R('client', 'The client retransmitted its request three times'), R('dns', 'A DNS failure interrupted the session')] } },
            { id: 'unanswered', points: 1, prompt: 'Which destination never answered?', hints: ['Filter for connection attempts.'], input: { type: 'choice', options: [R('static', '10.10.0.16:443 (static.kanem.example)'), R('portal', '10.10.0.15:443 (portal.kanem.example)'), R('resolver', '10.40.8.53:53 (the DNS resolver)')] } },
            { id: 'synretrans', points: 1, prompt: 'How many times was the SYN to that destination **retransmitted** (not counting the first attempt)?', hints: ['Count the [TCP Retransmission] lines.'], input: { type: 'number' } },
            { id: 'filter', points: 1, prompt: 'Write a Wireshark **display filter** that shows only TCP connection attempts (SYN set, ACK not set).', hints: ['Lesson 2 lists it.'], input: { type: 'text', placeholder: 'display filter' } },
            { id: 'cause', points: 2, prompt: 'What best explains why the portal feels slow?', hints: ['Compare how long each problem cost.'], input: { type: 'choice', options: [R('static', 'The page waits more than 7 seconds for static.kanem.example, whose new server (migrated in CHG-3318) never answers: probably a firewall rule or route missing for 10.10.0.16'), R('loss', 'The single lost segment, which delayed the page by about 39 ms'), R('rtt', 'The 38 ms round-trip time to the portal'), R('tls', 'TLS 1.3 is too slow')] } },
            { id: 'report', points: 2, prompt: 'Write the **findings report** for the network manager: summary, scope (file, hash, capture point, filter), findings with frame numbers and measured values, interpretation and confidence, limitations, and recommendations.', hints: ['Lesson 5 gives the structure; separate facts from interpretation.'], input: { type: 'deliverable', title: 'Portal slowness: packet capture findings', competency: 'Analysing packet captures and reporting findings', minWords: 140, rubric: ['Summarises the cause and the fix in a few sentences', 'Records the file, hash, capture point, time and filter', 'Gives findings with frame numbers and measured values (RTT, retransmissions, delays)', 'Separates facts from interpretation and states confidence and limitations', 'Recommends specific checks and fixes (firewall policy and routes for 10.10.0.16)'], template: 'Summary:\n\nScope:\n\nFindings (frames, times, values):\n\nInterpretation and confidence:\n\nLimitations:\n\nRecommendations:\n' } },
        ],
        reflection: ['Where else would you capture to confirm where the SYNs are dropped?', 'Why is the lost segment not the main problem, even though Wireshark highlights it?', 'What would you change in the migration process so CHG-3318 could not cause this again?', 'What would you leave out of the report to protect the user\'s privacy?'],
    },
    validator: {
        tasks: {
            rtt: { type: 'number', value: 38, tolerance: 2, feedback: { correct: 'Right: 0.040600 − 0.002600 = 38 ms.', incorrect: `Subtract the time of frame ${F.syn} from frame ${F.synack} and convert to milliseconds.` } },
            sni: { type: 'text', accept: ['portal.kanem.example'], feedback: { correct: 'Right.', incorrect: 'Read the server_name extension in the Client Hello.' } },
            tls: { type: 'choice', correct: '13', feedback: { correct: 'Right: the Server Hello selects 0x0304 in supported_versions.', incorrect: 'TLS 1.3 records still carry 0x0303 for compatibility; check the Server Hello extensions.' } },
            loss: { type: 'choice', correct: 'fastretrans', feedback: { correct: 'Right.', incorrect: 'The client saw a gap, repeated its acknowledgement three times, and the server resent the missing segment.' } },
            unanswered: { type: 'choice', correct: 'static', feedback: { correct: 'Right.', incorrect: 'Look for SYNs that never get a SYN-ACK or RST.' } },
            synretrans: { type: 'number', value: 3, tolerance: 0, feedback: { correct: 'Right: at about +1, +3 and +7 seconds.', incorrect: 'Count only the retransmissions, not the first SYN.' } },
            filter: { type: 'text', accept: synFilters, feedback: { correct: 'Right.', incorrect: 'Combine tcp.flags.syn == 1 with tcp.flags.ack == 0.' } },
            cause: { type: 'choice', correct: 'static', feedback: { correct: 'Right: seven seconds of waiting outweighs everything else.', incorrect: 'Compare the size of each delay. The loss cost about 39 ms; the unanswered server cost more than 7 s.' } },
            report: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'the unanswered server', any: ['10.10.0.16', 'static.kanem'] },
                { label: 'the round-trip time', any: ['rtt', 'round-trip', 'round trip', '38'] },
                { label: 'the retransmissions', any: ['retransmi'] },
                { label: 'firewall or routing as the next check', any: ['firewall', 'route', 'routing', 'rule'] },
                { label: 'the capture hash', any: ['sha-256', 'sha256', 'hash'] },
            ], feedback: { correct: 'Report complete. Compare it with the rubric and the debrief.', incorrect: 'Your report is not complete yet.' } },
        },
        debrief: `## Worked solution\n\n**RTT:** frame ${F.syn} (0.002600) to frame ${F.synack} (0.040600) = **38 ms**, normal for the Abuja–Lagos WAN.\n\n**TLS:** the Client Hello (frame ${F.ch}) carries SNI **portal.kanem.example** and offers TLS 1.3 and 1.2; the Server Hello (frame ${F.sh}) selects **TLS 1.3** (0x0304) in supported_versions, although the record header says 0x0303.\n\n**Loss:** frame ${F.gap} arrives with a gap ("previous segment not captured"); the client sends three duplicate ACKs (frames ${F.dup1}, ${F.dup1 + 2}, ${F.dup1 + 4}) and the server fast-retransmits the missing 1,400 bytes in frame ${F.retrans}, about 39 ms later. One lost segment, recovered quickly: worth watching, not the cause.\n\n**Unanswered server:** \`static.kanem.example\` resolves to **10.10.0.16** (frames ${F.dns2q}–${F.dns2r}). The client's SYN to 10.10.0.16:443 (frame ${F.ssyn}) is retransmitted **3 times** (frames ${F.ssyn2}–${F.ssyn4}, at about +1, +3 and +7 s) with no SYN-ACK or RST. Filter: \`tcp.flags.syn == 1 && tcp.flags.ack == 0\`.\n\n**Cause:** the page waits more than 7 seconds for its images, scripts and styles from the server migrated in CHG-3318. Silence (no RST) suggests traffic is being **dropped**, most likely by a firewall rule or a missing route for the new address. Confidence: medium; confirm with the firewall logs.\n\n**Limitations:** one capture point (the laptop); content is encrypted; the capture cannot show where the SYNs are dropped.\n\n**Recommendations:** check firewall policy and routing from the Abuja staff zone (10.40.8.0/22) to 10.10.0.16:443; capture on the server side to confirm whether SYNs arrive; add a post-migration connectivity test to the change process; keep watching for loss on the WAN.\n\n## What you have practised\n\nMeasuring, reading and explaining real protocol behaviour, then reporting it so others can repeat it: core skills for network engineers and analysts.\n\n**Next:** module 9 brings these skills into network incident response.`,
    },
};

replaceModule(p.data, 'traffic-analysis', {
    slug: 'traffic-analysis',
    title: 'Network Traffic Analysis with Wireshark',
    purpose: 'When logs run out, packets tell the story. This module covers capturing safely and lawfully, navigating Wireshark and writing display filters, reading TCP behaviour, DNS and TLS in captures, and documenting packet evidence. You finish by analysing a downloadable synthetic capture and writing a findings report.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written report.',
    prerequisiteKnowledge: 'Module 1 (TCP, UDP, DNS and TLS basics).',
    objectives: ['Capture traffic with authority, a clear purpose and minimal scope', 'Use Wireshark display filters and statistics to find relevant packets', 'Measure RTT and recognise TCP problems', 'Read DNS and the TLS handshake in captures', 'Write repeatable findings reports'],
    keyConcepts: ['Capture vs display filters', 'SPAN, TAP and host capture', 'Evidence handling', 'Conversations and Follow Stream', 'Round-trip time', 'Retransmission, duplicate ACK, RST', 'DNS response time', 'Client Hello, SNI, Server Hello', 'Facts vs interpretation'],
    assessmentCriteria: ['Measures and reads protocol behaviour correctly', 'Identifies the cause of a performance problem', 'Writes a correct display filter', 'Writes a complete, repeatable report'],
    instructorNotes: `Estimated time: 2.5 hours of lessons plus 1 hour of lab. The capture (public/content/network-security/${PCAP_FILE}) is produced by database/content/authoring/pcap-kanem-portal.cjs, which checks all checksums. It has not yet been opened in Wireshark by a reviewer: please confirm that the Info text and frame numbering match your Wireshark version before teaching.`,
    contentStatus: 'complete',
    lessons,
});
p.data.labs = p.data.labs.filter((l) => l.slug !== 'packet-and-dns-analysis');
replaceLab(p.data, 'portal-capture-analysis', lab);

const C = 'traffic-analysis';
replaceQuestions(p.data, 'net-pcap-', [
    single('net-pcap-01', C, 'foundation', 'Capture lawfully', 'What should you confirm first before capturing traffic?', ['Disk space', '*That you are authorised to capture on that network for that purpose', 'The Wireshark theme', 'The packet size'], 'Authority and purpose come first.'),
    single('net-pcap-02', C, 'foundation', 'Use filters', 'Which kind of filter permanently limits what is recorded?', ['*A capture filter', 'A display filter', 'A colouring rule', 'A column'], 'Excluded packets are never saved.'),
    single('net-pcap-03', C, 'intermediate', 'Choose a capture point', 'Which capture point gives the most faithful copy of a link?', ['A host capture', 'A switch mirror port under heavy load', '*A network TAP', 'A DNS log'], 'Mirror ports can drop packets under load.'),
    single('net-pcap-04', C, 'foundation', 'Write display filters', 'Which display filter shows only DNS "no such name" responses?', ['dns.qry.type == 1', '*dns.flags.rcode == 3', 'udp.port == 80', 'tcp.flags.reset == 1'], 'Response code 3 is NXDOMAIN.'),
    single('net-pcap-05', C, 'foundation', 'Use Wireshark tools', 'Which Wireshark feature shows one TCP conversation in order?', ['Protocol Hierarchy', '*Follow TCP Stream', 'Endpoints', 'Capture Options'], 'Follow Stream reassembles the conversation.'),
    single('net-pcap-06', C, 'intermediate', 'Measure RTT', 'SYN at 0.002600 s, SYN-ACK at 0.040600 s. What is the RTT?', ['4 ms', '*38 ms', '0.38 s', '43 ms'], '0.0406 − 0.0026 = 0.038 s.'),
    single('net-pcap-07', C, 'intermediate', 'Interpret TCP', 'A SYN is answered immediately by an RST from the server\'s address. What does this usually mean?', ['The packet was lost', '*Nothing is listening on that port, or a firewall is rejecting', 'TLS failed', 'The application is slow'], 'An RST is an active refusal.'),
    single('net-pcap-08', C, 'intermediate', 'Interpret TCP', 'Why does a single lost SYN add about a second before the retry?', ['DNS caching', '*TCP waits for its retransmission timeout, which doubles on each retry', 'TLS renegotiation', 'The window is zero'], 'RFC 6298 describes the timer and back-off.'),
    multiple('net-pcap-09', C, 'intermediate', 'Recognise loss', 'Which Wireshark annotations point to packet loss? Select all that apply.', ['*TCP Retransmission', '*TCP Dup ACK', '*TCP Previous segment not captured', 'TCP Window Update'], 'Each indicates data that did not arrive in time.'),
    single('net-pcap-10', C, 'intermediate', 'Read TLS', 'A TLS 1.3 record header shows 0x0303. Which version was negotiated?', ['TLS 1.2, as the header says', '*Check the Server Hello supported_versions extension; TLS 1.3 uses 0x0303 in headers for compatibility', 'SSL 3.0', 'It cannot be known'], 'The negotiated version is in supported_versions.'),
    single('net-pcap-11', C, 'foundation', 'Read TLS', 'Which part of HTTPS traffic is usually readable without decryption?', ['The URL path', 'Form data', '*The server name (SNI) in the Client Hello', 'Cookies'], 'Content is encrypted; SNI and timing are visible.'),
    single('net-pcap-12', C, 'intermediate', 'Diagnose performance', 'The handshake is fast but the first response takes 3 s. Where is the delay?', ['The network', '*The server or application', 'The capture filter', 'DNS'], 'A slow first response after a fast handshake points to the application.'),
    single('net-pcap-13', C, 'foundation', 'Report findings', 'Which makes a findings report repeatable by a colleague?', ['Screenshots only', '*The file hash, filters used and frame numbers for each finding', 'A long description of Wireshark', 'Personal data copied from packets'], 'Others must be able to see the same frames.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/traffic-analysis/reading-a-capture.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 8 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions; capture', cap.packets.length, 'frames, sha256', cap.sha256);
