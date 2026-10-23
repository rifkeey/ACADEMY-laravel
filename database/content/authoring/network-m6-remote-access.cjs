// Network Security, module 6: Secure Remote Access, VPNs and Network Cryptography.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard';
upsertSources([
    { key: 'nist-sp-800-52r2', title: 'SP 800-52 Rev. 2: Guidelines for the Selection, Configuration, and Use of TLS Implementations', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/52/r2/final', topic: 'TLS configuration', sourceType: o, versionNote: 'Revision 2, August 2019.', supports: 'TLS versions, cipher suites and certificate guidance.' },
    { key: 'nist-sp-800-153', title: 'SP 800-153: Guidelines for Securing Wireless Local Area Networks (WLANs)', publisher: 'NIST', url: 'https://csrc.nist.gov/pubs/sp/800/153/final', topic: 'Wi-Fi security', sourceType: o, versionNote: 'Final, February 2012. Predates WPA3; principles still apply.', supports: 'WLAN security configuration and monitoring.' },
    { key: 'wifi-alliance-security', title: 'Wi-Fi security (WPA3)', publisher: 'Wi-Fi Alliance', url: 'https://www.wi-fi.org/discover-wi-fi/security', topic: 'Wi-Fi security', sourceType: o, versionNote: 'Live page.', supports: 'WPA3 Personal and Enterprise.' },
    { key: 'rfc-4253', title: 'RFC 4253: The Secure Shell (SSH) Transport Layer Protocol', publisher: 'IETF (RFC Editor)', url: 'https://www.rfc-editor.org/rfc/rfc4253', topic: 'Protocol standard', sourceType: o, versionNote: 'RFC 4253; check for updating RFCs.', supports: 'SSH transport security.' },
    { key: 'rfc-5280', title: 'RFC 5280: Internet X.509 Public Key Infrastructure Certificate and CRL Profile', publisher: 'IETF (RFC Editor)', url: 'https://www.rfc-editor.org/rfc/rfc5280', topic: 'Protocol standard', sourceType: o, versionNote: 'RFC 5280; check for updating RFCs.', supports: 'Certificate format and path validation.' },
    { key: 'nist-sp-800-63b', title: 'SP 800-63B: Digital Identity Guidelines, Authentication and Authenticator Management', publisher: 'NIST', url: 'https://pages.nist.gov/800-63-4/sp800-63b.html', topic: 'Authentication', sourceType: o, versionNote: 'Revision 4 web edition; check the page for status.', supports: 'Authenticator types, phishing resistance and assurance levels.' },
    { key: 'cisa-phishing-resistant-mfa', title: 'Implementing Phishing-Resistant MFA (fact sheet)', publisher: 'CISA', url: 'https://www.cisa.gov/sites/default/files/publications/fact-sheet-implementing-phishing-resistant-mfa-508c.pdf', topic: 'MFA', sourceType: o, versionNote: 'PDF fact sheet.', supports: 'Prioritising phishing-resistant MFA.' },
    { key: 'entra-conditional-access', title: 'Microsoft Entra Conditional Access overview', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/entra/identity/conditional-access/overview', topic: 'Conditional access', sourceType: 'vendor_documentation', versionNote: 'Microsoft Learn, current.', supports: 'Signal-based access policies (example product).' },
], CHECKED);

const p = load('network-security');

const lessons = [
    { slug: 'remote-access-options', title: 'Choosing remote access: VPNs, ZTNA and published applications', minutes: 25, sources: ['nist-sp-800-77r1', 'nist-sp-800-207'],
        objectives: ['Compare site-to-site VPN, remote-access VPN, ZTNA, published applications and administration services', 'Match a remote-access method to a business need'],
        checks: [
            { question: 'Loans officers need only the loans application from their tablets. What is the best fit?', options: ['A full remote-access VPN', 'The application published securely with MFA and device checks', 'A site-to-site VPN to each tablet', 'An open port on the firewall'], answerIndex: 1, explanation: 'Give access to the one application needed, not the whole network.' },
            { question: 'Which connection is best served by a site-to-site IPsec VPN?', options: ['A contractor\'s laptop', 'A branch office network to head office', 'A visitor\'s phone', 'A public website'], answerIndex: 1, explanation: 'Site-to-site VPNs join whole networks through gateways.' },
            { question: 'Why is the trend from VPNs towards application-level access?', options: ['VPNs are illegal', 'It limits what a compromised account or device can reach', 'It removes authentication', 'It is always cheaper'], answerIndex: 1, explanation: 'Network-level access exposes far more than most users need.' },
        ] },
    { slug: 'ipsec-fundamentals', title: 'IPsec fundamentals', minutes: 30, sources: ['nist-sp-800-77r1'],
        objectives: ['Describe what IPsec provides and its two negotiation phases', 'Identify common causes of tunnel failure', 'Recommend secure IPsec configuration choices'],
        checks: [
            { question: 'A tunnel fails because each side defines the other\'s subnets differently. Which setting is mismatched?', options: ['The encryption key length', 'The traffic selectors', 'The DNS server', 'The MTU'], answerIndex: 1, explanation: 'Traffic selectors must mirror each other on both ends.' },
            { question: 'Which are recommended IPsec configuration choices? Select all that apply.', options: ['IKEv2', 'Certificates or long, unique pre-shared keys', 'Perfect forward secrecy', 'Legacy algorithms for compatibility'], answerIndexes: [0, 1, 2], explanation: 'Disable legacy options and follow current guidance.' },
            { question: 'Why should branch traffic still pass firewall policy after leaving the tunnel?', options: ['To slow it down', 'Encryption protects transit, not what the traffic may reach', 'Tunnels cannot carry TCP', 'It is a vendor requirement'], answerIndex: 1, explanation: 'A compromised branch device must not gain open access because the link is encrypted.' },
        ] },
    { slug: 'tls-and-certificates', title: 'TLS and certificate management', minutes: 30, sources: ['nist-sp-800-52r2', 'rfc-5280', 'rfc-8446'],
        objectives: ['Configure TLS versions and suites to current guidance', 'Explain certificate validation', 'Run certificate management with inventory, renewal and key protection'],
        checks: [
            { question: 'Which TLS versions should normally be enabled?', options: ['SSL 3.0 and TLS 1.0', 'TLS 1.2 and TLS 1.3', 'All versions', 'Only TLS 1.1'], answerIndex: 1, explanation: 'Older versions are deprecated.' },
            { question: 'Which checks does a client make on a server certificate? Select all that apply.', options: ['Trusted issuer chain', 'Name matches the requested host', 'Within its validity dates', 'The server\'s colour scheme'], answerIndexes: [0, 1, 2], explanation: 'Chain, name, dates and revocation status.' },
            { question: 'What most often causes certificate-related outages?', options: ['Broken cryptography', 'Expired certificates nobody tracked', 'Too many certificates', 'TLS 1.3'], answerIndex: 1, explanation: 'Inventory, owners, automated renewal and alerts prevent expiry outages.' },
        ] },
    { slug: 'secure-admin-protocols-and-wifi', title: 'Secure administration protocols and Wi-Fi', minutes: 30, sources: ['rfc-4253', 'nist-sp-800-153', 'wifi-alliance-security', 'ieee-8021x'],
        objectives: ['Choose secure protocols for administration and file transfer', 'Design staff, guest and device Wi-Fi securely'],
        checks: [
            { question: 'Which protocols are acceptable for administration? Select all that apply.', options: ['SSH', 'HTTPS', 'Telnet', 'SNMPv3'], answerIndexes: [0, 1, 3], explanation: 'Telnet sends credentials in clear text.' },
            { question: 'What is the recommended authentication for staff Wi-Fi?', options: ['A shared password printed on the wall', 'WPA3- or WPA2-Enterprise with 802.1X', 'Open network with a portal', 'MAC address lists only'], answerIndex: 1, explanation: 'Enterprise mode authenticates each user or device individually.' },
            { question: 'How should guest Wi-Fi be designed?', options: ['On the staff VLAN', 'Isolated from internal zones, internet only, with client isolation', 'With access to printers and servers', 'Without any firewall'], answerIndex: 1, explanation: 'Guests are untrusted and must not reach internal systems.' },
        ] },
    { slug: 'mfa-conditional-access-and-troubleshooting', title: 'MFA, conditional access and troubleshooting remote access', minutes: 35, sources: ['nist-sp-800-63b', 'cisa-phishing-resistant-mfa', 'entra-conditional-access'],
        objectives: ['Compare MFA methods by phishing resistance', 'Design conditional access policies from signals', 'Troubleshoot remote access layer by layer without weakening controls'],
        checks: [
            { question: 'Which MFA method resists phishing best?', options: ['SMS codes', 'Authenticator app codes', 'FIDO2 security keys or platform authenticators', 'Security questions'], answerIndex: 2, explanation: 'FIDO2/WebAuthn is bound to the genuine site.' },
            { question: 'A user\'s VPN sign-in is blocked because the device is not compliant. What is the right fix?', options: ['Exempt the user from the policy permanently', 'Bring the device into compliance (or provide a compliant device)', 'Disable conditional access', 'Share another user\'s account'], answerIndex: 1, explanation: 'Fix the root cause instead of weakening the control.' },
            { question: 'Put the first troubleshooting steps in order: (A) check authentication logs, (B) confirm the gateway is reachable, (C) check routes and firewall for the destination.', options: ['A, B, C', 'B, A, C', 'C, A, B', 'C, B, A'], answerIndex: 1, explanation: 'Reachability, then authentication and policy, then routing and access.' },
        ] },
];

const R = (id, label) => ({ id, label });

const lab = {
    slug: 'remote-access-diagnosis',
    title: 'Diagnose and secure Kanem\'s remote access',
    difficulty: 'intermediate',
    labType: 'troubleshooting',
    summary: 'Use gateway, sign-in and certificate evidence to diagnose two remote-access failures, choose secure fixes rather than shortcuts, and recommend Kanem\'s remote-access configuration.',
    minutes: 60,
    moduleSlug: 'remote-access',
    passPercent: 80,
    content: {
        objectives: ['Diagnose a site-to-site tunnel failure from gateway logs', 'Diagnose a user remote-access failure from sign-in logs', 'Choose fixes that keep controls intact', 'Recommend a secure remote-access configuration'],
        scenario: 'Monday morning at Kanem Financial Services (fictional): the **Kano branch** tunnel is down after a weekend router replacement, and a **loans officer** cannot sign in from her tablet. The service desk has collected the evidence in the workspace.',
        scope: 'Work from the documents. All data is synthetic.',
        instructions: '1. Read the three documents.\n2. Answer tasks 1–6.\n3. Write the recommendation in task 7. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic.',
        workspace: { kind: 'documents', documents: [
            { title: 'Head-office VPN gateway log: Kano tunnel', format: 'code', language: 'text', body: '2026-03-16T07:02:11Z  peer=kano-gw  IKE_SA established (IKEv2, certificate authentication OK)\n2026-03-16T07:02:11Z  peer=kano-gw  CHILD_SA proposal received: local 10.60.0.0/16 remote 10.10.0.0/16\n2026-03-16T07:02:11Z  peer=kano-gw  CHILD_SA failed: traffic selectors unacceptable (expected local 10.10.0.0/16 remote 10.60.8.0/22)\n2026-03-16T07:02:41Z  peer=kano-gw  retrying...' },
            { title: 'Identity provider sign-in log: loans officer', format: 'code', language: 'text', body: '2026-03-16T08:15:40Z  user=f.bello  app=loans-portal  result=failure\n  authentication: password OK, MFA (security key) OK\n  conditional access: policy "Loans app requires compliant device" -> BLOCK\n  device: KAN-TAB-031  compliance: not compliant (OS update overdue 21 days)' },
            { title: 'Certificate inventory (extract)', format: 'markdown', body: '| Certificate | Used by | Expires | Owner |\n| --- | --- | --- | --- |\n| kano-gw | Kano VPN gateway | 2027-02-01 | Network team |\n| loans-portal.kanem.example | Loans portal | **2026-03-22** | Digital channels |\n| vpn.kanem.example | Remote-access VPN | 2026-11-30 | Network team |' },
        ] },
        tasks: [
            { id: 'tunnel', points: 2, prompt: 'Why is the Kano tunnel down?', hints: ['Which phase failed, and what does the error say?'], input: { type: 'choice', options: [R('selectors', 'The new Kano router proposes the wrong local subnet (10.60.0.0/16 instead of 10.60.8.0/22): a traffic-selector mismatch in the Child SA'), R('cert', 'The Kano certificate has expired'), R('ike', 'IKE authentication failed'), R('isp', 'The internet link is down')] } },
            { id: 'tunnelfix', points: 1, prompt: 'What is the correct fix?', hints: ['Fix the side that changed.'], input: { type: 'choice', options: [R('fixkano', 'Correct the local subnet on the Kano router to 10.60.8.0/22 to match the agreed configuration'), R('any', 'Change head office to accept any subnets from Kano'), R('psk', 'Switch to a short pre-shared key')] } },
            { id: 'user', points: 1, prompt: 'Why can\'t the loans officer sign in?', hints: ['Read the conditional access line.'], input: { type: 'choice', options: [R('compliance', 'Her tablet is not compliant (overdue OS update), so conditional access blocks the loans app'), R('password', 'Wrong password'), R('mfa', 'Her security key failed')] } },
            { id: 'userfix', points: 2, prompt: 'Which fixes are acceptable? Select all that apply.', hints: ['Lesson 5: don\'t weaken controls.'], input: { type: 'multi', options: [R('update', 'Install the OS update so the tablet becomes compliant'), R('loan', 'Provide a compliant spare device for today'), R('exempt', 'Permanently exempt her from the policy'), R('disable', 'Disable the compliance policy for the loans team')] } },
            { id: 'cert', points: 1, prompt: 'What else in the evidence needs action this week?', hints: ['Look at the expiry dates.'], input: { type: 'choice', options: [R('portal', 'Renew the loans-portal certificate before 22 March'), R('vpn', 'Renew vpn.kanem.example today'), R('nothing', 'Nothing')] } },
            { id: 'methods', points: 2, prompt: 'Choose the remote-access method for each need.', hints: ['Lesson 1.'], input: { type: 'grid', rows: [R('branch', 'Kano branch network to head office'), R('tablet', 'Loans officers to the loans app'), R('admin', 'IT administrators to infrastructure'), R('contractor', 'Software contractor to one application')], columns: [{ id: 'method', label: 'Method', options: [R('s2s', 'Site-to-site IPsec VPN'), R('app', 'Published application with MFA and device checks'), R('mgmt', 'VPN to the management zone with phishing-resistant MFA, then jump host'), R('ztna', 'ZTNA, time-limited')] }] } },
            { id: 'recommendation', points: 2, prompt: 'Write the **recommendation** to the IT manager: what failed and why, the fixes applied, and the remote-access configuration Kanem should standardise on (methods, MFA, conditional access, tunnel settings, certificate management).', hints: ['Explain why the shortcuts were rejected.'], input: { type: 'deliverable', title: 'Remote-access diagnosis and recommendation', competency: 'Diagnosing remote-access failures and recommending secure configuration', minWords: 140, rubric: ['Explains both failures and their root causes from the evidence', 'Describes fixes that keep controls intact, and why shortcuts were rejected', 'Recommends methods per need, phishing-resistant MFA and conditional access', 'Includes tunnel configuration standards (IKEv2, certificates, matched selectors) and change checks', 'Addresses certificate inventory and renewal'], template: 'What failed and why:\n\nFixes applied:\n\nShortcuts rejected and why:\n\nRecommended standard configuration:\n\nCertificate management:\n' } },
        ],
        reflection: ['How would a configuration check before the router replacement have prevented the outage?', 'What would you tell a manager who asks to exempt their team from device compliance?', 'Which certificate expiry would hurt most, and how would you be warned?', 'What would you log to prove remote access is working securely?'],
    },
    validator: {
        tasks: {
            tunnel: { type: 'choice', correct: 'selectors', feedback: { correct: 'Right: IKE succeeded; the Child SA failed on traffic selectors.', incorrect: 'IKE (phase 1) succeeded with certificates. Read the Child SA error.' } },
            tunnelfix: { type: 'choice', correct: 'fixkano', feedback: { correct: 'Right.', incorrect: 'Accepting any subnets widens the tunnel; weak keys weaken authentication. Fix the changed side.' } },
            user: { type: 'choice', correct: 'compliance', feedback: { correct: 'Right.', incorrect: 'Password and MFA succeeded; conditional access blocked the device.' } },
            userfix: { type: 'multi', correct: ['update', 'loan'], feedback: { correct: 'Right: fix the device, or provide a compliant one.', incorrect: 'Exemptions and disabling policies trade a ticket for a lasting weakness.' } },
            cert: { type: 'choice', correct: 'portal', feedback: { correct: 'Right: it expires in six days.', incorrect: 'Check every expiry date against today, 16 March 2026.' } },
            methods: { type: 'grid', minCorrectRatio: 0.75, cells: { branch: { method: ['s2s'] }, tablet: { method: ['app'] }, admin: { method: ['mgmt'] }, contractor: { method: ['ztna'] } }, feedback: { correct: 'Your choices match.', incorrect: 'Match each need to the narrowest suitable access.' } },
            recommendation: { type: 'deliverable', minWords: 140, mustMention: [
                { label: 'the traffic-selector mismatch', any: ['selector', 'subnet'] },
                { label: 'device compliance or conditional access', any: ['complian', 'conditional access'] },
                { label: 'MFA', any: ['mfa', 'multi-factor', 'security key', 'fido'] },
                { label: 'certificate renewal', any: ['certificate', 'renew', 'expir'] },
            ], feedback: { correct: 'Recommendation complete. Compare it with the rubric and the debrief.', incorrect: 'Your recommendation is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Kano tunnel:** IKE (phase 1) succeeded with certificates; the Child SA failed because the replacement router proposes local subnet 10.60.0.0/16 while head office expects 10.60.8.0/22. Fix the Kano router to the agreed selectors; do not widen head office to accept anything.\n\n**Loans officer:** password and security key succeeded; conditional access blocked the tablet because its OS update is 21 days overdue. Install the update (or issue a compliant spare); do not exempt her or disable the policy.\n\n**This week:** renew the loans-portal certificate before 22 March.\n\n**Methods:** branch, site-to-site IPsec; tablets, published application with MFA and device checks; administrators, VPN to the management zone with phishing-resistant MFA and the jump host; contractor, time-limited ZTNA.\n\n**Standard configuration to recommend:** IKEv2 with certificate authentication, current algorithms and forward secrecy, documented and matched traffic selectors checked before any router change; phishing-resistant MFA for administrators and remote access; conditional access requiring compliant devices for sensitive apps; a certificate inventory with owners, automated renewal and alerts 30 days before expiry.\n\n## What you have practised\n\nLayered troubleshooting with evidence, and the discipline of fixing root causes without weakening security: core skills for network and identity teams.\n\n**Next:** module 7 looks at DNS, DHCP and web protocols.',
    },
};

replaceModule(p.data, 'remote-access', {
    slug: 'remote-access',
    title: 'Secure Remote Access, VPNs and Network Cryptography',
    purpose: 'Kanem\'s staff and branches connect from networks it does not control. This module covers choosing remote-access methods, IPsec VPNs, TLS and certificates, secure administration protocols and Wi-Fi, and MFA, conditional access and troubleshooting. You finish by diagnosing two real-looking failures and recommending a secure standard.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written recommendation.',
    prerequisiteKnowledge: 'Modules 1, 3 and 4.',
    objectives: ['Choose remote-access methods appropriate to each need', 'Explain IPsec negotiation and diagnose tunnel failures', 'Configure TLS and manage certificates', 'Select secure administration protocols and design Wi-Fi securely', 'Apply phishing-resistant MFA and conditional access', 'Troubleshoot remote access without weakening controls'],
    keyConcepts: ['Site-to-site and remote-access VPN', 'ZTNA', 'IKEv2, SAs and traffic selectors', 'TLS configuration and certificate validation', 'Certificate lifecycle', 'SSH, SFTP, SNMPv3', 'WPA3 and 802.1X', 'Phishing-resistant MFA', 'Conditional access'],
    assessmentCriteria: ['Diagnoses tunnel and sign-in failures from evidence', 'Chooses secure fixes', 'Matches access methods to needs', 'Writes a complete recommendation'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'remote-access-diagnosis', lab);

const C = 'remote-access';
replaceQuestions(p.data, 'net-ra-', [
    single('net-ra-01', C, 'foundation', 'Choose remote access', 'Which method gives a user access to specific applications rather than the whole network?', ['Site-to-site VPN', 'Full-tunnel remote-access VPN', '*Zero trust network access (ZTNA)', 'A flat network'], 'ZTNA decides access per application and session.'),
    single('net-ra-02', C, 'foundation', 'Explain IPsec', 'Which protocol negotiates IPsec security associations?', ['DHCP', '*IKE (IKEv2)', 'SNMP', 'ARP'], 'IKE authenticates peers and negotiates keys and parameters.'),
    single('net-ra-03', C, 'intermediate', 'Troubleshoot tunnels', 'IKE succeeds but the Child SA fails with "traffic selectors unacceptable". What is wrong?', ['The certificate expired', '*The subnets defined for the tunnel do not match on both sides', 'The internet link is down', 'DNS is misconfigured'], 'Traffic selectors must mirror each other.'),
    single('net-ra-04', C, 'intermediate', 'Configure IPsec', 'What does perfect forward secrecy protect against?', ['Expired certificates', '*Exposure of past traffic if a long-term key is later compromised', 'Packet loss', 'DNS spoofing'], 'Session keys are not derivable from the long-term key.'),
    single('net-ra-05', C, 'foundation', 'Configure TLS', 'Which TLS versions should normally be enabled today?', ['SSL 3.0', 'TLS 1.0 and 1.1', '*TLS 1.2 and 1.3', 'All available versions'], 'Older versions are deprecated.'),
    multiple('net-ra-06', C, 'intermediate', 'Validate certificates', 'Which checks are part of certificate validation? Select all that apply.', ['*The chain leads to a trusted issuer', '*The name matches the requested host', '*The certificate is within its validity period', 'The server responds quickly'], 'Revocation status is also checked where available.'),
    single('net-ra-07', C, 'foundation', 'Manage certificates', 'What best prevents outages from expired certificates?', ['Longer passwords', '*An inventory with owners, automated renewal and expiry alerts', 'Disabling TLS', 'Using self-signed certificates everywhere'], 'Most certificate incidents are operational.'),
    single('net-ra-08', C, 'foundation', 'Choose admin protocols', 'Which is the secure replacement for Telnet?', ['FTP', '*SSH', 'HTTP', 'SNMPv1'], 'SSH encrypts and authenticates the session.'),
    single('net-ra-09', C, 'intermediate', 'Secure Wi-Fi', 'What is the recommended security mode for staff Wi-Fi?', ['WEP', 'Open network', '*WPA3- or WPA2-Enterprise with 802.1X', 'WPA2-Personal with a shared password'], 'Enterprise mode authenticates each user or device.'),
    single('net-ra-10', C, 'intermediate', 'Compare MFA methods', 'Which MFA method is considered phishing-resistant?', ['SMS codes', 'Push approval without number matching', '*FIDO2/WebAuthn security keys', 'Email codes'], 'They are bound to the genuine site.'),
    single('net-ra-11', C, 'intermediate', 'Design conditional access', 'Which conditional access policy best protects a sensitive application?', ['Allow all sign-ins', '*Require phishing-resistant MFA and a compliant device', 'Block everyone', 'Require a longer username'], 'Combining identity strength and device health limits misuse.'),
    single('net-ra-12', C, 'intermediate', 'Troubleshoot securely', 'A user is blocked because their device is non-compliant. What is the best response?', ['Exempt them permanently', '*Bring the device into compliance or provide a compliant device', 'Disable the policy', 'Share credentials'], 'Fix the root cause rather than weakening controls.'),
    single('net-ra-13', C, 'foundation', 'Apply mutual TLS', 'What does mutual TLS add over standard TLS?', ['Faster connections', '*The client also authenticates with a certificate', 'No encryption', 'Shorter certificates'], 'Both ends prove their identity.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/network-security/lessons/remote-access/vpn-fundamentals.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('network module 6 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
