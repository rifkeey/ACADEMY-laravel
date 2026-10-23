// Network Security, module 1: Networking Foundations for Security.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const rfc = (n, title, supports) => ({ key: `rfc-${n}`, title: `RFC ${n}: ${title}`, publisher: 'IETF (RFC Editor)', url: `https://www.rfc-editor.org/rfc/rfc${n}`, topic: 'Protocol standard', sourceType: 'official_standard', versionNote: `RFC ${n}; check the RFC page for any updating RFCs.`, supports });
upsertSources([
    rfc(791, 'Internet Protocol', 'IPv4 header fields and TTL.'),
    rfc(1918, 'Address Allocation for Private Internets', 'Private IPv4 ranges.'),
    rfc(4632, 'Classless Inter-domain Routing (CIDR)', 'Prefix notation and aggregation.'),
    rfc(8200, 'Internet Protocol, Version 6 (IPv6) Specification', 'IPv6 basics.'),
    rfc(4291, 'IP Version 6 Addressing Architecture', 'IPv6 address types, link-local, /64.'),
    rfc(826, 'An Ethernet Address Resolution Protocol', 'ARP and why it has no authentication.'),
    rfc(4861, 'Neighbor Discovery for IP version 6 (IPv6)', 'IPv6 neighbour discovery.'),
    rfc(768, 'User Datagram Protocol', 'UDP behaviour.'),
    rfc(2131, 'Dynamic Host Configuration Protocol', 'DHCP exchange and options.'),
    rfc(8446, 'The Transport Layer Security (TLS) Protocol Version 1.3', 'TLS goals and handshake.'),
    rfc(3022, 'Traditional IP Network Address Translator (Traditional NAT)', 'NAT and port translation.'),
    rfc(6335, 'IANA Procedures for the Management of the Service Name and Transport Protocol Port Number Registry', 'Port ranges.'),
    { key: 'iana-ports', title: 'Service Name and Transport Protocol Port Number Registry', publisher: 'IANA', url: 'https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.xhtml', topic: 'Port assignments', sourceType: 'official_standard', versionNote: 'Live registry.', supports: 'Well-known and registered ports.' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'models-and-encapsulation', title: 'Layer models and encapsulation for security work', minutes: 30, sources: ['nist-csf-2', 'rfc-791'],
        objectives: ['Map protocols and security controls to OSI and TCP/IP layers', 'Describe how encapsulation wraps a request in headers', 'Use the layer model to choose where to start troubleshooting'],
        checks: [
            { question: 'A stateful firewall allows TCP 443 to a web server. At which layer does an SQL injection attack against that site happen, and can the firewall stop it?', options: ['Layer 3; yes', 'Layer 4; yes', 'Layer 7; no, the firewall only enforces addresses and ports', 'Layer 2; no'], answerIndex: 2, explanation: 'Injection happens inside HTTP at the application layer. A control can only enforce what it can see.' },
            { question: 'As a packet crosses two routers on its way to a server, which header normally changes at each hop?', options: ['The IP destination address', 'The Ethernet (layer 2) header', 'The TCP destination port', 'The HTTP request'], answerIndex: 1, explanation: 'Layer 2 only describes the next link, so it is rebuilt at every hop. IP addresses stay the same unless NAT rewrites them.' },
            { question: 'Which devices or controls inspect layer 7 content? Select all that apply.', options: ['Web application firewall', 'Layer 2 switch', 'Application-aware (next-generation) firewall', 'Router doing basic forwarding'], answerIndexes: [0, 2], explanation: 'WAFs and application-aware firewalls read application data; switches and plain routers work at layers 2 and 3.' },
        ] },
    { slug: 'ip-addressing-and-subnetting', title: 'IP addressing, subnetting and CIDR', minutes: 40, sources: ['rfc-4632', 'rfc-1918', 'rfc-8200', 'rfc-4291'],
        objectives: ['Calculate the network, broadcast and host range of an IPv4 prefix', 'Decide whether two hosts are on the same subnet', 'Recognise private, link-local and documentation addresses, and key IPv6 differences'],
        checks: [
            { question: 'What is the network address of 10.40.9.77/22?', options: ['10.40.9.0', '10.40.8.0', '10.40.0.0', '10.40.10.0'], answerIndex: 1, explanation: '/22 leaves 6 network bits in the third octet: block size 4, so 9 falls in the block starting at 8.' },
            { question: 'How many usable host addresses does a /26 provide?', options: ['64', '62', '30', '126'], answerIndex: 1, explanation: '6 host bits = 64 addresses, minus the network and broadcast addresses.' },
            { question: 'A laptop shows the address 169.254.20.11. What is the most likely explanation?', options: ['It is on the guest network', 'It failed to get an address from DHCP and assigned itself a link-local address', 'It is connected over IPv6', 'It is the default gateway'], answerIndex: 1, explanation: '169.254.0.0/16 is link-local; Windows and macOS use it when DHCP does not answer.' },
            { question: 'Which statements about IPv6 are true? Select all that apply.', options: ['Interfaces have link-local addresses even if nobody configured IPv6', 'A standard LAN prefix is /64', 'NAT is required for IPv6 hosts to be secure', 'Firewalls and monitoring must cover IPv6 as well as IPv4'], answerIndexes: [0, 1, 3], explanation: 'IPv6 is often active by default. Filtering, not NAT, provides protection.' },
        ] },
    { slug: 'switching-arp-and-routing', title: 'Switching, ARP and routing', minutes: 35, sources: ['rfc-826', 'rfc-4861', 'rfc-791'],
        objectives: ['Explain how switches, VLANs and ARP deliver traffic inside a subnet', 'Predict a host\'s next hop from its address, mask and gateway', 'Apply longest-prefix matching to a routing table', 'Identify the trust assumptions attackers abuse at layers 2 and 3'],
        checks: [
            { question: 'Why can ARP be abused to intercept traffic on a LAN?', options: ['ARP is encrypted with a weak cipher', 'ARP replies are not authenticated, so any device can claim to own an IP address', 'ARP only works on Wi-Fi', 'ARP tables never expire'], answerIndex: 1, explanation: 'Hosts believe any ARP reply, which enables ARP spoofing and man-in-the-middle attacks.' },
            { question: 'A router has routes for 10.0.0.0/8 (VPN), 10.10.0.0/16 (data-centre link) and 0.0.0.0/0 (internet). Which route is used for 10.10.0.15?', options: ['0.0.0.0/0', '10.0.0.0/8', '10.10.0.0/16', 'All three, load-balanced'], answerIndex: 2, explanation: 'The longest (most specific) matching prefix wins.' },
            { question: 'A host is 10.40.9.77 with mask 255.255.255.0. Where does it send a packet for 10.40.10.5?', options: ['Directly, after an ARP request for 10.40.10.5', 'To its default gateway', 'Nowhere: it is the broadcast address', 'To the DNS server'], answerIndex: 1, explanation: 'With a /24 mask, 10.40.10.5 is not on the host\'s subnet, so it goes to the gateway (even though on a /22 it would be local).' },
        ] },
    { slug: 'tcp-udp-and-ports', title: 'TCP, UDP and ports', minutes: 35, sources: ['rfc-9293', 'rfc-768', 'rfc-6335', 'iana-ports'],
        objectives: ['Describe the TCP handshake, key flags and connection states', 'Interpret open, closed and filtered port responses', 'Explain why UDP is easy to spoof and abuse', 'Spot unusual ports and connection patterns in host and log output'],
        checks: [
            { question: 'A scanner sends a SYN to TCP 3389 and receives an RST. What does that mean?', options: ['The port is open', 'The port is closed but the host is reachable', 'A firewall silently dropped the packet', 'The host is down'], answerIndex: 1, explanation: 'SYN-ACK means open, RST means closed, silence usually means filtered.' },
            { question: 'Why are UDP services used in reflection and amplification denial-of-service attacks?', options: ['UDP is always encrypted', 'UDP has no handshake, so the source address can be spoofed and replies sent to a victim', 'UDP packets are larger', 'Firewalls cannot see UDP'], answerIndex: 1, explanation: 'Without a handshake, a spoofed request sends the (often larger) reply to the victim.' },
            { question: 'A laptop shows SYN-SENT to 198.51.100.200:4444. Why is that worth investigating?', options: ['4444 is the standard HTTPS port', 'An outbound attempt to an unusual port with no business purpose can indicate malware', 'SYN-SENT means the connection succeeded', 'It is normal DNS traffic'], answerIndex: 1, explanation: 'Unusual outbound ports are classic signs of command-and-control or tooling; egress filtering should log them.' },
        ] },
    { slug: 'dns-dhcp-nat-and-tls', title: 'DHCP, DNS, NAT and TLS: the services behind every connection', minutes: 40, sources: ['rfc-2131', 'rfc-1034', 'rfc-3022', 'rfc-8446'],
        objectives: ['Explain what DHCP, DNS, NAT/PAT and TLS each contribute to a connection', 'Identify how each service can be abused', 'Name the logs each service produces and what they prove'],
        checks: [
            { question: 'A website reports abuse from Kanem\'s single public address at 14:02. What do you need to identify the internal host?', options: ['The website\'s access logs', 'The edge firewall\'s NAT/PAT translation logs for 14:02', 'The DHCP scope options', 'The TLS certificate'], answerIndex: 1, explanation: 'With PAT, many hosts share one public address; only the translation logs map it back to an internal host and port.' },
            { question: 'A rogue DHCP server appears on the staff network. What can it do? Select all that apply.', options: ['Give hosts a malicious default gateway', 'Give hosts a malicious DNS server', 'Read traffic already encrypted with TLS', 'Cause IP address conflicts and outages'], answerIndexes: [0, 1, 3], explanation: 'DHCP controls gateway and DNS settings, which can redirect traffic. It cannot decrypt TLS by itself.' },
            { question: 'What can a network sensor normally still see about a TLS 1.3 connection without decrypting it?', options: ['The HTTP URLs and form data', 'IP addresses, ports, timing, volume, and usually the server name (SNI)', 'Nothing at all', 'The user\'s password'], answerIndex: 1, explanation: 'TLS hides content but not metadata. Encrypted Client Hello can also hide the server name.' },
        ] },
];

const ipconfig = [
    'C:\\> ipconfig /all   (LN-PC-14, Abuja branch, 3 March 2026 10:42)',
    '',
    'Ethernet adapter Ethernet:',
    '   DHCP Enabled. . . . . . . . . . . : No',
    '   IPv4 Address. . . . . . . . . . . : 10.40.9.77',
    '   Subnet Mask . . . . . . . . . . . : 255.255.255.0',
    '   Default Gateway . . . . . . . . . : 10.40.9.1',
    '   DNS Servers . . . . . . . . . . . : 10.10.0.53',
    '',
    'C:\\> ping 10.40.9.30          (loans printer)',
    'Reply from 10.40.9.30: bytes=32 time<1ms TTL=64   (4 of 4 replies)',
    '',
    'C:\\> ping 10.40.9.1',
    'Reply from 10.40.9.77: Destination host unreachable.   (4 times)',
    '',
    'C:\\> ping 10.10.0.15          (core banking server)',
    'Reply from 10.40.9.77: Destination host unreachable.   (4 times)',
    '',
    'C:\\> tracert -d 10.10.0.15',
    '  1  10.40.9.77  reports: Destination host unreachable.',
].join('\n');

const lab = {
    slug: 'packet-path-troubleshooting',
    title: 'Trace the packet path: the Abuja branch connectivity ticket',
    difficulty: 'foundation',
    labType: 'troubleshooting',
    summary: 'Use the address plan, host output and routing table to work out exactly how a laptop should reach the core banking server, find why it can\'t, and write the ticket resolution.',
    minutes: 60,
    moduleSlug: 'networking-foundations',
    passPercent: 80,
    content: {
        objectives: ['Calculate subnet boundaries from an address and prefix', 'Predict a host\'s forwarding decision and a router\'s route choice', 'Diagnose a layer-3 fault from evidence rather than guesses', 'Separate the root cause from unrelated symptoms'],
        scenario: 'A ticket from the **Abuja branch** of Kanem Financial Services (fictional): *"Loans officer cannot open the core banking app. Printer works fine. Internet is slow."* The laptop is **LN-PC-14**. You can\'t visit the branch, but the branch IT officer has run some commands for you and sent the documents in the workspace.',
        scope: 'Work only from the documents. All addresses and outputs are synthetic. The core banking application is `corebanking.kanem.example` at 10.10.0.15 in the Lagos data centre, reached over the branch VPN.',
        instructions: '1. Read the address plan first, then the laptop output and the router\'s routing table.\n2. Answer tasks 1–8.\n3. Write your ticket resolution in task 9. It is saved to your portfolio.\n\nYou need 80% to pass.\n\n:::reveal Advanced challenge (optional)\nThe laptop also has the wrong mask. Even after the gateway is fixed, name one Abuja staff address that LN-PC-14 would wrongly send to the router instead of delivering directly, and explain why that is inefficient but still works. Add it to your resolution.\n:::',
        simulationNotice: 'This is a simulation. All data is synthetic and generated by AMANIQ Academy for training. No real systems, people or organisations are involved.',
        workspace: { kind: 'documents', documents: [
            { title: 'Abuja branch address plan', format: 'markdown', body: '| VLAN | Purpose | Subnet | Gateway | Addressing |\n| --- | --- | --- | --- | --- |\n| 20 | Staff laptops and the legacy loans printer | 10.40.8.0/22 | 10.40.8.1 | DHCP (printer reserved at 10.40.9.30) |\n| 30 | Other printers | 10.40.12.0/24 | 10.40.12.1 | DHCP reservations |\n| 99 | Network management | 10.40.15.0/27 | 10.40.15.1 | Static |\n\nDHCP for VLAN 20 hands out: mask 255.255.252.0, gateway 10.40.8.1, DNS 10.10.0.53 (Lagos).\n\n*Note from the branch IT officer: "A contractor re-imaged some laptops last week."*' },
            { title: 'LN-PC-14: commands run by the branch IT officer', format: 'code', language: 'text', body: ipconfig },
            { title: 'Branch router R-ABJ-01: routing table', format: 'code', language: 'text', body: 'Destination        Next hop         Interface    Notes\n10.40.8.0/22       directly conn.   Gi0/1.20     Abuja staff (VLAN 20)\n10.40.12.0/24      directly conn.   Gi0/1.30     Abuja printers (VLAN 30)\n10.40.15.0/27      directly conn.   Gi0/1.99     Management (VLAN 99)\n10.0.0.0/8         172.31.0.1       Tunnel1      VPN to Lagos data centre\n0.0.0.0/0          198.51.100.1     Gi0/0        Internet (default route)' },
            { title: 'Branch internet link: utilisation, 3 March 2026', format: 'markdown', body: '| Time | Utilisation | Top talker |\n| --- | --- | --- |\n| 09:00 | 34% | web browsing |\n| 10:00 | 96% | 10.40.12.40 (scan-to-cloud copier) uploading a 40 GB archive |\n| 11:00 | 97% | same |\n\nThe VPN to Lagos runs over the same link but is prioritised (QoS).' },
        ] },
        tasks: [
            { id: 'hosts', points: 1, prompt: 'How many usable host addresses are there in the Abuja staff subnet 10.40.8.0/22?', hints: ['2^(32 − 22) − 2.'], input: { type: 'number' } },
            { id: 'network', points: 1, prompt: 'What is the network address of 10.40.9.77 with the **correct** prefix, /22?', hints: ['Lesson 2: block size in the third octet is 4.'], input: { type: 'text', placeholder: 'e.g. 10.0.0.0' } },
            { id: 'same', points: 1, prompt: 'With the correct /22 prefix, is 10.40.11.200 on the same subnet as 10.40.9.77?', hints: ['Find the range of 10.40.8.0/22.'], input: { type: 'choice', options: [{ id: 'yes', label: 'Yes' }, { id: 'no', label: 'No' }] } },
            { id: 'decision', points: 1, prompt: 'With its **current** configuration, what does LN-PC-14 do with a packet for 10.10.0.15?', hints: ['Is 10.10.0.15 on its /24? If not, where do off-subnet packets go?'], input: { type: 'choice', options: [{ id: 'direct', label: 'Delivers it directly after an ARP request for 10.10.0.15' }, { id: 'gw', label: 'Sends it to its configured default gateway, 10.40.9.1' }, { id: 'dns', label: 'Sends it to the DNS server 10.10.0.53' }, { id: 'router', label: 'Sends it to the real branch router, 10.40.8.1' }] } },
            { id: 'cause', points: 2, prompt: 'What is the **root cause** of the ticket?', hints: ['Why does pinging 10.40.9.1 fail?', 'Compare the laptop\'s settings with what DHCP would have given it.'], input: { type: 'choice', options: [{ id: 'static', label: 'The laptop has a manual (static) configuration with a default gateway, 10.40.9.1, that does not exist, so nothing off its subnet is reachable' }, { id: 'server', label: 'The core banking server is down' }, { id: 'vpn', label: 'The VPN tunnel to Lagos is down' }, { id: 'bandwidth', label: 'The internet link is saturated, so the application times out' }] } },
            { id: 'order', points: 2, prompt: 'After the fix, put the steps of LN-PC-14 opening the core banking app in order.', hints: ['The DNS server is in Lagos too: what must the laptop do before it can send anything off its subnet?'], input: { type: 'grid', rows: [
                { id: 'arp', label: 'Laptop resolves the gateway 10.40.8.1 to a MAC address with ARP' },
                { id: 'dns', label: 'Laptop asks the DNS resolver 10.10.0.53 for corebanking.kanem.example' },
                { id: 'syn', label: 'Laptop sends a TCP SYN to 10.10.0.15 port 443' },
                { id: 'route', label: 'Branch router forwards the SYN into the VPN tunnel' },
                { id: 'tls', label: 'TLS handshake: the server presents its certificate' },
            ], columns: [{ id: 'step', label: 'Step', options: ['1', '2', '3', '4', '5'].map((n) => ({ id: n, label: n })) }] } },
            { id: 'route', points: 1, prompt: 'Which route does R-ABJ-01 use for traffic to 10.10.0.15, and why?', hints: ['Longest prefix match.'], input: { type: 'choice', options: [{ id: 'vpn', label: '10.0.0.0/8 via Tunnel1, because it is more specific than the default route' }, { id: 'default', label: '0.0.0.0/0, because the default route always wins' }, { id: 'staff', label: '10.40.8.0/22, because the laptop is in it' }] } },
            { id: 'slow', points: 1, prompt: 'What does the evidence say about "internet is slow"?', hints: ['Look at the top talker and the note about the VPN.'], input: { type: 'choice', options: [{ id: 'separate', label: 'It is a separate capacity problem (a copier uploading 40 GB) to raise with the branch, not the cause of the core banking failure' }, { id: 'cause', label: 'It is the cause: fix the bandwidth and core banking will work' }, { id: 'attack', label: 'It proves a denial-of-service attack is under way' }] } },
            { id: 'resolution', points: 2, prompt: 'Write the **ticket resolution** for the service desk: root cause, evidence, fix, how you validated it, and how to prevent a repeat.', hints: ['Name the wrong settings and the right ones.', 'How will the branch officer prove it works? Which commands, which results?'], input: { type: 'deliverable', title: 'Ticket resolution', competency: 'Diagnosing a network fault from evidence and documenting the fix and its validation', minWords: 120, rubric: ['States the root cause precisely (static configuration, non-existent gateway, wrong mask)', 'Cites the evidence (ping results, ipconfig values, address plan)', 'Gives the fix (re-enable DHCP, or correct gateway 10.40.8.1 and mask /22)', 'Describes validation (ipconfig, ping gateway, nslookup, open the app, tracert path)', 'Separates the unrelated bandwidth issue and suggests prevention (image standards, checking re-imaged laptops)'], template: 'Root cause:\n\nEvidence:\n\nFix applied:\n\nValidation:\n\nOther findings:\n\nPrevention:\n' } },
        ],
        reflection: ['Which single piece of evidence pointed you to layer 3 rather than the server or the VPN?', 'Why was "printer works" such a useful clue?', 'How would you have found this faster if you could only ask the user one question?', 'What would you check on the other laptops the contractor re-imaged?'],
    },
    validator: {
        tasks: {
            hosts: { type: 'number', value: 1022, feedback: { correct: 'Right: 2^10 − 2.', incorrect: 'A /22 has 10 host bits: 1,024 addresses minus the network and broadcast addresses.' } },
            network: { type: 'text', accept: ['10.40.8.0', '10.40.8.0/22'], feedback: { correct: 'Right.', incorrect: 'Block size 4 in the third octet: 9 is in the block that starts at 8.' } },
            same: { type: 'choice', correct: 'yes', feedback: { correct: 'Right: 10.40.8.0/22 runs from 10.40.8.0 to 10.40.11.255.', incorrect: 'The /22 covers third-octet values 8 to 11.' } },
            decision: { type: 'choice', correct: 'gw', feedback: { correct: 'Right: off-subnet traffic goes to the configured gateway, even if that gateway is wrong.', incorrect: 'The host uses its own address, mask and configured gateway, not the real router.' } },
            cause: { type: 'choice', correct: 'static', feedback: { correct: 'Right: the configured gateway does not exist, so every off-subnet packet fails.', incorrect: 'The laptop cannot even reach its own gateway (Destination host unreachable from itself). Look at its configuration.' } },
            order: { type: 'grid', minCorrectRatio: 0.8, cells: { arp: { step: ['1'] }, dns: { step: ['2'] }, syn: { step: ['3'] }, route: { step: ['4'] }, tls: { step: ['5'] } }, feedback: { correct: 'Your sequence matches.', incorrect: 'The DNS server is off the subnet, so the gateway\'s MAC is needed first; name resolution comes before the connection.' } },
            route: { type: 'choice', correct: 'vpn', feedback: { correct: 'Right: the most specific matching route wins.', incorrect: 'Routers choose the longest matching prefix.' } },
            slow: { type: 'choice', correct: 'separate', feedback: { correct: 'Right: a real issue, but not this ticket\'s cause, and the VPN is prioritised anyway.', incorrect: 'Even a saturated link would not make the laptop unable to reach its own gateway.' } },
            resolution: { type: 'deliverable', minWords: 120, mustMention: [
                { label: 'the gateway (10.40.8.1 or the wrong 10.40.9.1)', any: ['gateway', '10.40.8.1', '10.40.9.1'] },
                { label: 'DHCP or the correct static settings', any: ['dhcp', '255.255.252.0', '/22'] },
                { label: 'how you validated the fix', any: ['ping', 'tracert', 'traceroute', 'nslookup', 'validat', 'test', 'verified'] },
            ], feedback: { correct: 'Resolution complete. Compare it with the rubric and the debrief.', incorrect: 'Your resolution is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Root cause:** LN-PC-14 was set up manually (DHCP disabled) by the contractor with gateway **10.40.9.1**, which does not exist, and mask **255.255.255.0** instead of /22. The printer at 10.40.9.30 is inside the laptop\'s /24, so the laptop reaches it directly with ARP: that is why "printer works". Everything off its subnet, including the DNS server and core banking, goes to 10.40.9.1, which never answers ARP, so Windows reports *Destination host unreachable* from the laptop itself.\n\n**Fix:** re-enable DHCP so the laptop receives 10.40.9.77/22 (or a new lease), gateway 10.40.8.1 and DNS 10.10.0.53.\n\n**Validate:** `ipconfig` shows DHCP enabled, mask 255.255.252.0 and gateway 10.40.8.1; `ping 10.40.8.1` replies; `nslookup corebanking.kanem.example` returns 10.10.0.15; `tracert -d 10.10.0.15` goes 10.40.8.1 → 172.31.0.1 → … → 10.10.0.15; the user opens the app.\n\n**Separate issue:** the copier at 10.40.12.40 saturating the internet link explains "internet is slow" but not this failure (the VPN is prioritised). Raise it with the branch: schedule large uploads out of hours or rate-limit them.\n\n**Advanced challenge:** with a /24 mask, any staff address outside 10.40.9.0/24 (for example 10.40.10.5) looks remote, so the laptop sends it to the router, which forwards it straight back into VLAN 20. It works, but it adds a hop and load on the router.\n\n### Sequence\n1 ARP for the gateway → 2 DNS query → 3 TCP SYN → 4 router forwards over the VPN (10.0.0.0/8 is more specific than the default route) → 5 TLS handshake.\n\n## What you have practised\n\nStructured troubleshooting by layer, subnet arithmetic, host forwarding decisions and longest-prefix routing: the foundation for reading firewall rules, segmentation designs and incident evidence in the rest of the course. Keeping the bandwidth symptom separate from the root cause is exactly the discipline incident responders need.\n\n**Next:** module 2 uses the same packet-level thinking to recognise attacks.',
    },
};

replaceModule(p.data, 'networking-foundations', {
    slug: 'networking-foundations',
    title: 'Networking Foundations for Security',
    purpose: 'A branch ticket says the core banking app is unreachable but the printer works. Before you can secure a network you need to be able to explain exactly how packets move through it. This module covers layer models, IP addressing and subnetting, switching, ARP and routing, TCP and UDP, and the DHCP, DNS, NAT and TLS services behind every connection, always with the security consequences in view. You finish by solving the ticket.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written ticket resolution.',
    prerequisiteKnowledge: 'Basic computer literacy and comfort with the command line. No prior networking knowledge is assumed; lesson 2 includes a subnetting refresher.',
    objectives: ['Map protocols and security controls to network layers', 'Calculate subnet boundaries and decide whether hosts share a subnet', 'Explain host forwarding decisions, ARP and longest-prefix routing', 'Interpret TCP and UDP behaviour, ports and connection states', 'Describe how DHCP, DNS, NAT/PAT and TLS work, how each is abused, and which logs each produces', 'Troubleshoot a connectivity fault from evidence and document the fix'],
    keyConcepts: ['OSI and TCP/IP models', 'Encapsulation', 'CIDR and subnetting', 'Private, link-local and IPv6 addressing', 'MAC tables, VLANs and ARP', 'Default gateway and routing tables', 'TCP handshake, flags and states', 'UDP', 'DHCP, DNS, NAT/PAT, TLS'],
    assessmentCriteria: ['Correctly calculates subnet values', 'Predicts host and router forwarding decisions', 'Identifies the root cause of a connectivity fault from evidence', 'Writes a clear resolution with validation steps'],
    instructorNotes: 'Estimated time: 3 hours of lessons plus 1 hour of lab. Learners with networking experience can test out by attempting the lab first.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'packet-path-troubleshooting', lab);

const C = 'foundations';
replaceQuestions(p.data, 'net-fnd-', [
    single('net-fnd-01', C, 'foundation', 'Map controls to layers', 'At which OSI layer does a switch make its forwarding decisions?', ['Layer 1', '*Layer 2', 'Layer 3', 'Layer 7'], 'Switches forward frames using MAC addresses (layer 2).'),
    single('net-fnd-02', C, 'foundation', 'Map controls to layers', 'Which control operates mainly at layer 7?', ['A VLAN', 'A router ACL on IP addresses', '*A web application firewall', '802.1X port authentication'], 'WAFs inspect HTTP requests, which are application-layer data.'),
    single('net-fnd-03', C, 'foundation', 'Calculate subnets', 'What is the broadcast address of 192.168.20.64/26?', ['192.168.20.255', '*192.168.20.127', '192.168.20.65', '192.168.20.128'], 'A /26 has 64 addresses: .64 to .127, so .127 is the broadcast.'),
    single('net-fnd-04', C, 'intermediate', 'Calculate subnets', 'Which address is NOT in 10.40.8.0/22?', ['10.40.8.1', '10.40.10.200', '10.40.11.254', '*10.40.12.1'], '10.40.8.0/22 covers 10.40.8.0 to 10.40.11.255.'),
    single('net-fnd-05', C, 'foundation', 'Recognise address types', 'Which range is private under RFC 1918?', ['8.8.0.0/16', '*172.16.0.0/12', '169.254.0.0/16', '203.0.113.0/24'], '10/8, 172.16/12 and 192.168/16 are the RFC 1918 private ranges. 169.254/16 is link-local and 203.0.113/24 is for documentation.'),
    single('net-fnd-06', C, 'intermediate', 'Explain ARP risks', 'What makes ARP spoofing possible?', ['ARP uses TCP', '*Hosts accept ARP replies without authentication', 'ARP only works across routers', 'ARP tables are stored on the DNS server'], 'Any device on the LAN can claim an IP address, redirecting traffic through itself.'),
    single('net-fnd-07', C, 'intermediate', 'Predict routing decisions', 'Routes: 0.0.0.0/0 → ISP; 10.0.0.0/8 → VPN; 10.10.0.0/24 → DC link. Which next hop is used for 10.10.0.200?', ['ISP', 'VPN', '*DC link', 'Dropped'], 'Longest prefix match: /24 is the most specific route that contains the address.'),
    single('net-fnd-08', C, 'foundation', 'Interpret TCP behaviour', 'In a TCP scan, which response shows that a port is open?', ['RST', '*SYN-ACK', 'No response', 'ICMP time exceeded'], 'An open port answers the SYN with SYN-ACK. RST means closed; silence usually means filtered.'),
    multiple('net-fnd-09', C, 'intermediate', 'Explain UDP abuse', 'Why is UDP attractive for amplification attacks? Select all that apply.', ['*No handshake, so source addresses are easy to spoof', '*Some UDP services send replies much larger than the request', 'UDP is always encrypted', 'UDP cannot be filtered by firewalls'], 'Spoofable sources plus large responses let attackers aim traffic at a victim.'),
    single('net-fnd-10', C, 'foundation', 'Explain DHCP risks', 'Which switch feature helps stop a rogue DHCP server?', ['Spanning tree', '*DHCP snooping', 'Jumbo frames', 'Link aggregation'], 'DHCP snooping only allows DHCP server replies from trusted ports.'),
    single('net-fnd-11', C, 'intermediate', 'Use NAT evidence', 'Why is NAT not a security control in itself?', ['NAT encrypts traffic', '*It translates addresses; the firewall policy is what decides which traffic is allowed', 'NAT blocks all outbound traffic', 'NAT authenticates users'], 'Translation hides internal addresses but does not enforce policy.'),
    single('net-fnd-12', C, 'foundation', 'Describe TLS', 'Which property does a valid server certificate give a TLS connection?', ['Faster loading', '*Authentication of the server\'s identity', 'Hiding the IP addresses of both ends', 'Compression'], 'The certificate proves the server is the one named; encryption protects the content.'),
    single('net-fnd-13', C, 'intermediate', 'Troubleshoot by layer', 'A laptop can reach a printer on its own subnet but nothing on other subnets. Where should you look first?', ['The application server\'s logs', '*The laptop\'s default gateway and subnet mask', 'The TLS certificate', 'The internet link speed'], 'Local delivery works, so layers 1–2 are fine; reaching other networks depends on the gateway and mask.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/networking-foundations/models-and-addressing.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 1 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
