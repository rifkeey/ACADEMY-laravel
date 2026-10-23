// Cloud Security, module 3: Cloud Network Security.
const fs = require('fs');
const path = require('path');
const { load, save, upsertSources, replaceModule, replaceLab, replaceQuestions, single, multiple } = require('./lib.cjs');

const CHECKED = '2026-10-23';
const o = 'official_standard', v = 'vendor_documentation';
const doc = (key, title, publisher, url, topic, supports) => ({ key, title, publisher, url, topic, sourceType: v, versionNote: 'Provider documentation, current; features change often.', supports });
upsertSources([
    doc('aws-vpc-how-it-works', 'How Amazon VPC works', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/userguide/how-it-works.html', 'Cloud networking', 'Subnets, route tables, internet and NAT gateways.'),
    doc('azure-vnet-overview', 'What is Azure Virtual Network?', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/virtual-network/virtual-networks-overview', 'Cloud networking', 'Azure virtual network concepts.'),
    doc('gcp-vpc-networks', 'VPC networks', 'Google Cloud', 'https://cloud.google.com/vpc/docs/vpc', 'Cloud networking', 'Global VPC networks, subnets and routes.'),
    doc('aws-security-groups', 'Control traffic to your AWS resources using security groups', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/userguide/vpc-security-groups.html', 'Cloud network filtering', 'Stateful security groups.'),
    doc('aws-network-acls', 'Control subnet traffic with network access control lists', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/userguide/vpc-network-acls.html', 'Cloud network filtering', 'Stateless network ACLs.'),
    doc('azure-nsg', 'Azure network security groups overview', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/virtual-network/network-security-groups-overview', 'Cloud network filtering', 'NSG rules, priorities and defaults.'),
    doc('azure-asg', 'Azure application security groups overview', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/virtual-network/application-security-groups', 'Cloud network filtering', 'Grouping workloads in NSG rules.'),
    doc('gcp-vpc-firewall', 'VPC firewall rules', 'Google Cloud', 'https://cloud.google.com/firewall/docs/firewalls', 'Cloud network filtering', 'Firewall rules, targets and implied rules.'),
    doc('gcp-firewall-policies', 'Firewall policies and rules', 'Google Cloud', 'https://cloud.google.com/firewall/docs/firewall-policies-overview', 'Cloud network filtering', 'Hierarchical and network firewall policies.'),
    doc('aws-reachability-analyzer', 'What is Reachability Analyzer?', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/reachability/what-is-reachability-analyzer.html', 'Network validation', 'Analysing reachability without sending traffic.'),
    doc('azure-ip-flow-verify', 'IP flow verify overview (Azure Network Watcher)', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/network-watcher/ip-flow-verify-overview', 'Network validation', 'Checking whether NSG rules allow a flow.'),
    doc('gcp-connectivity-tests', 'Connectivity Tests overview', 'Google Cloud', 'https://cloud.google.com/network-intelligence-center/docs/connectivity-tests/concepts/overview', 'Network validation', 'Analysing network paths and configuration.'),
    doc('aws-privatelink', 'What is AWS PrivateLink?', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/privatelink/what-is-privatelink.html', 'Private connectivity', 'Interface endpoints for services.'),
    doc('azure-private-endpoint', 'What is a private endpoint? (Azure Private Link)', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/private-link/private-endpoint-overview', 'Private connectivity', 'Private IP addresses for Azure services.'),
    doc('gcp-private-service-connect', 'Private Service Connect', 'Google Cloud', 'https://cloud.google.com/vpc/docs/private-service-connect', 'Private connectivity', 'Private access to Google APIs and services.'),
    doc('aws-vpc-connectivity-options', 'Amazon VPC connectivity options (whitepaper)', 'Amazon Web Services', 'https://docs.aws.amazon.com/whitepapers/latest/aws-vpc-connectivity-options/introduction.html', 'Hybrid connectivity', 'VPN, Direct Connect and transit options.'),
    doc('azure-hub-spoke', 'Hub-spoke network topology in Azure', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/architecture/networking/architecture/hub-spoke', 'Hybrid connectivity', 'Hub-and-spoke design with central inspection.'),
    doc('aws-vpc-flow-logs', 'Logging IP traffic using VPC Flow Logs', 'Amazon Web Services', 'https://docs.aws.amazon.com/vpc/latest/userguide/flow-logs.html', 'Flow logs', 'Flow log records, fields and limitations.'),
    doc('azure-vnet-flow-logs', 'Virtual network flow logs (Azure Network Watcher)', 'Microsoft', 'https://learn.microsoft.com/en-us/azure/network-watcher/vnet-flow-logs-overview', 'Flow logs', 'Azure flow logging.'),
    doc('gcp-vpc-flow-logs', 'VPC Flow Logs', 'Google Cloud', 'https://cloud.google.com/vpc/docs/flow-logs', 'Flow logs', 'Sampled flow logs per subnet.'),
    { key: 'mcsb-v2-network', title: 'Microsoft cloud security benchmark v2: Network Security', publisher: 'Microsoft', url: 'https://learn.microsoft.com/en-us/security/benchmark/azure/mcsb-v2-network-security', topic: 'Cloud network controls', sourceType: o, versionNote: 'Benchmark v2; check for updates.', supports: 'Network security control recommendations across clouds.' },
], CHECKED);

const p = load('cloud-security');

const lessons = [
    { slug: 'virtual-networks-and-routing', title: 'Virtual networks and routing', minutes: 30, sources: ['aws-vpc-how-it-works', 'azure-vnet-overview', 'gcp-vpc-networks', 'mcsb-v2-network'],
        objectives: ['Map virtual network concepts across AWS, Azure and Google Cloud', 'Explain what makes a subnet public', 'Design a three-tier layout with private application and database tiers'],
        checks: [
            { question: 'In AWS, what makes a subnet public?', options: ['Its name', 'A route to an internet gateway in its route table', 'A NAT gateway', 'Encryption'], answerIndex: 1, explanation: 'Routes, not labels, decide exposure.' },
            { question: 'Where should the invoice database sit?', options: ['In a public subnet with a public IP', 'In a private subnet with no route to the internet', 'In the load balancer subnet', 'On the internet gateway'], answerIndex: 1, explanation: 'Only the front door is internet-facing.' },
            { question: 'Why does cloud network security depend on identity too?', options: ['It does not', 'Anyone with the right permissions can change routes or add public IPs with an API call', 'Identities encrypt packets', 'Firewalls need passwords'], answerIndex: 1, explanation: 'Protect network changes with IAM and guardrails.' },
        ] },
    { slug: 'security-groups-and-network-acls', title: 'Security groups, network ACLs and cloud firewall rules', minutes: 35, sources: ['aws-security-groups', 'aws-network-acls', 'azure-nsg', 'azure-asg', 'gcp-vpc-firewall', 'gcp-firewall-policies', 'aws-reachability-analyzer', 'azure-ip-flow-verify', 'gcp-connectivity-tests'],
        objectives: ['Compare stateful and stateless filters across providers', 'Write group-to-group rules per tier', 'Use a coarse second layer as a guardrail and validate reachability'],
        checks: [
            { question: 'An AWS network ACL allows inbound TCP 443 but the website still fails. What is the likely cause?', options: ['TLS is broken', 'Network ACLs are stateless, so return traffic to the client\'s high ports must also be allowed', 'Security groups block HTTPS by default', 'DNS'], answerIndex: 1, explanation: 'Stateless rules need both directions.' },
            { question: 'What should the database security group allow?', options: ['TCP 5432 from 0.0.0.0/0', 'TCP 5432 from the app tier\'s security group only', 'All traffic from the VPC', 'SSH from the internet'], answerIndex: 1, explanation: 'Reference groups, not the whole internet.' },
            { question: 'Which tools check whether a path is open without sending traffic? Select all that apply.', options: ['AWS Reachability Analyzer', 'Azure Network Watcher IP flow verify', 'Google Cloud Connectivity Tests', 'A spreadsheet of guesses'], answerIndexes: [0, 1, 2], explanation: 'Use them to validate changes.' },
        ] },
    { slug: 'private-connectivity-and-egress', title: 'Private connectivity and egress control', minutes: 30, sources: ['aws-privatelink', 'azure-private-endpoint', 'gcp-private-service-connect'],
        objectives: ['Reach provider services privately', 'Restrict egress to named needs', 'Avoid public IPs and administer through identity-based session services'],
        checks: [
            { question: 'What do private endpoints allow you to do next?', options: ['Turn off encryption', 'Disable public access to the service and limit use through endpoint policies', 'Remove IAM', 'Open SSH'], answerIndex: 1, explanation: 'Remove the public path.' },
            { question: 'How should administrators reach private servers?', options: ['Public SSH from anywhere', 'Identity-based session services such as Session Manager, Azure Bastion or IAP', 'A shared password over RDP', 'By giving servers public IPs'], answerIndex: 1, explanation: 'Identity-based, logged access.' },
            { question: 'After creating a private endpoint, an application still uses the public endpoint. What should you check first?', options: ['CPU usage', 'DNS resolution from inside the network', 'Disk size', 'The billing account'], answerIndex: 1, explanation: 'Private endpoints usually rely on DNS.' },
        ] },
    { slug: 'hybrid-connectivity', title: 'Hybrid connectivity and hub-and-spoke', minutes: 30, sources: ['aws-vpc-connectivity-options', 'azure-hub-spoke', 'nist-sp-800-207'],
        objectives: ['Compare VPNs and dedicated links', 'Design a hub with central inspection', 'Apply least privilege and zero trust across the hybrid boundary'],
        checks: [
            { question: 'Are dedicated private links (Direct Connect, ExpressRoute, Interconnect) encrypted by default at the network layer?', options: ['Yes, always', 'No; decide explicitly whether extra encryption is needed', 'Only on weekends', 'They cannot be encrypted'], answerIndex: 1, explanation: 'Private is not the same as encrypted.' },
            { question: 'Why route spokes through a hub?', options: ['To slow traffic', 'So traffic is inspected and logged centrally', 'To avoid IP addressing', 'Providers require it'], answerIndex: 1, explanation: 'Central policy and visibility.' },
            { question: 'Traffic arrives from on-premises over the private link. Should the cloud trust it?', options: ['Yes, the link is private', 'No; apply the same least-privilege rules and authentication as between any zones', 'Only on port 22', 'Only if it is encrypted'], answerIndex: 1, explanation: 'The link is plumbing, not permission.' },
        ] },
    { slug: 'flow-logs-and-monitoring', title: 'Flow logs and network monitoring', minutes: 30, sources: ['aws-vpc-flow-logs', 'azure-vnet-flow-logs', 'gcp-vpc-flow-logs'],
        objectives: ['Read a flow log record', 'Use flow logs for investigation, detection and validating segmentation', 'Know their blind spots and make them useful'],
        checks: [
            { question: 'A flow log shows ACCEPT from 198.51.100.77 to the database on 5432. What does it prove?', options: ['Data was stolen', 'The network accepted a connection from the internet to the database: an exposure to investigate', 'The database is encrypted', 'Nothing'], answerIndex: 1, explanation: 'It shows the connection, not what happened over it.' },
            { question: 'What do flow logs not show? Select all that apply.', options: ['Payload content', 'Which user or role made the connection', 'Some platform traffic, such as provider DNS on AWS', 'Source and destination addresses'], answerIndexes: [0, 1, 2], explanation: 'Combine with audit and application logs.' },
            { question: 'Which alert is most valuable from flow logs?', options: ['Every accepted connection', 'Accepted inbound connections from the internet to private tiers', 'All rejected connections from the internet', 'Traffic inside the app tier'], answerIndex: 1, explanation: 'Alert on the few patterns that matter.' },
        ] },
];

const R = (id, label) => ({ id, label });
const current = '```text\nSubnet subnet-db-a (10.50.30.0/24)    route table: rt-public\n  10.50.0.0/16   local\n  0.0.0.0/0      igw-0a1b2c\n\nDatabase invoice-db   publicly accessible: YES   public IP: 203.0.113.60\n\nSecurity group sg-db (attached to invoice-db)\n  inbound   TCP 5432   0.0.0.0/0          "temp - migration tool"\n  inbound   TCP 22     0.0.0.0/0          "admin"\n  outbound  all        0.0.0.0/0\n\nSecurity group sg-app (attached to app servers)\n  inbound   TCP 8080   0.0.0.0/0\n  outbound  all        0.0.0.0/0\n\nSecurity group sg-lb (load balancer)\n  inbound   TCP 443    0.0.0.0/0\n\nNetwork ACL (all subnets): default, allow all in and out\nApp servers reach the invoice storage bucket through the NAT gateway (public endpoint).\nVPC flow logs: enabled for subnet-db-a only (enabled yesterday).\n```';
const flowlog = '```text\nsrcaddr        dstaddr      srcport dstport protocol packets bytes  action\n198.51.100.77  10.50.30.12  51544   5432    6        5       312    ACCEPT\n198.51.100.77  10.50.30.12  51545   5432    6        41      18220  ACCEPT\n203.0.113.200  10.50.30.12  40112   22      6        3       180    ACCEPT\n10.50.20.15    10.50.30.12  33010   5432    6        120     98400  ACCEPT\n192.0.2.31     10.50.30.12  61002   3389    6        1       44     REJECT\n```\n*(Synthetic.)* 10.50.20.15 is an invoice app server. No address outside 10.50.0.0/16 is expected to reach the database.';

const lab = {
    slug: 'invoice-network-redesign',
    title: 'Fix the invoice app\'s exposed network',
    difficulty: 'intermediate',
    labType: 'architecture_review',
    summary: 'Find why the invoice database answers from the internet, read the flow log evidence, design tier-by-tier rules, private connectivity and egress, choose validation steps and write the redesign.',
    minutes: 60,
    moduleSlug: 'cloud-networking',
    passPercent: 80,
    content: {
        objectives: ['Explain an exposure from routes, settings and rules', 'Read flow log evidence', 'Design group-to-group rules per tier', 'Choose private connectivity, egress and guardrails', 'Write a redesign with validation'],
        scenario: 'Kanem Financial Services\' (fictional) security scan reports that the **invoice database answers on TCP 5432 from the internet**. The configuration and yesterday\'s flow logs for the database subnet are in the workspace.',
        scope: 'Work from the documents. The configuration uses AWS terms; the same ideas apply on Azure and Google Cloud. All data is synthetic.',
        instructions: '1. Read both documents.\n2. Answer tasks 1–7.\n3. Write the redesign in task 8. It is saved to your portfolio.\n\nYou need 80% to pass.',
        simulationNotice: 'This is a simulation. All data is synthetic.',
        workspace: { kind: 'documents', documents: [
            { title: 'Current configuration (extract)', format: 'markdown', body: current },
            { title: 'Flow log extract: subnet-db-a, last 24 hours', format: 'markdown', body: flowlog },
        ] },
        tasks: [
            { id: 'cause', points: 2, prompt: 'Which settings together expose the database to the internet? Select all that apply.', hints: ['Lesson 1 (routes) and lesson 2 (rules).'], input: { type: 'multi', options: [R('route', 'subnet-db-a\'s route table has a route to the internet gateway'), R('public', 'The database is set to publicly accessible, with a public IP'), R('sg', 'sg-db allows TCP 5432 from 0.0.0.0/0'), R('nat', 'There is a NAT gateway in the VPC'), R('lb', 'The load balancer accepts TCP 443 from the internet'), R('flowlogs', 'Flow logs are enabled on subnet-db-a')] } },
            { id: 'evidence', points: 1, prompt: 'What does the flow log show, and what should happen next?', hints: ['Which rows come from outside 10.50.0.0/16, and what was the action?'], input: { type: 'choice', options: [R('incident', 'Two internet addresses were accepted on 5432 and 22, one with about 18 KB of traffic; treat it as a possible incident: check the database and SSH logs for logins, rotate credentials and close the exposure now'), R('fine', 'Nothing to worry about: the database is encrypted'), R('rejected', 'All internet connections were rejected'), R('app', 'Only the app server connected')] } },
            { id: 'rules', points: 2, prompt: 'Choose the allowed source for each inbound rule in the target design.', hints: ['Reference groups, not addresses; one internet-facing rule.'], input: { type: 'grid', rows: [R('lb443', 'Load balancer: TCP 443'), R('app8080', 'App servers: TCP 8080'), R('db5432', 'Database: TCP 5432'), R('admin', 'Administration of app servers and database')], columns: [{ id: 'src', label: 'Allowed from', options: [R('internet', '0.0.0.0/0 (internet)'), R('sglb', 'sg-lb only'), R('sgapp', 'sg-app only'), R('vpc', 'The whole VPC 10.50.0.0/16'), R('none', 'No inbound rule: identity-based session service instead')] }] } },
            { id: 'placement', points: 1, prompt: 'Where should the database live?', hints: ['Lesson 1 three-tier layout.'], input: { type: 'choice', options: [R('private', 'Private database subnets in two zones with no route to the internet or NAT, publicly accessible set to No'), R('public', 'Keep it in subnet-db-a but rely on the security group'), R('lb', 'Behind the internet-facing load balancer'), R('nat', 'In a subnet routed through the NAT gateway with a public IP')] } },
            { id: 'guardrail', points: 1, prompt: 'Which second layer best backs up the security groups?', hints: ['Coarse, stateless, deny-only for dangerous ports.'], input: { type: 'choice', options: [R('nacl', 'A network ACL on the database and app subnets denying inbound 22, 3389 and 5432 from outside 10.50.0.0/16, plus a posture rule and pipeline check that block 0.0.0.0/0 on those ports'), R('allowall', 'Keep the default allow-all network ACL'), R('denyall', 'A network ACL denying all traffic everywhere'), R('none', 'Nothing; security groups are enough')] } },
            { id: 'private', points: 1, prompt: 'How should the app servers reach the invoice storage bucket and the internet?', hints: ['Lesson 3.'], input: { type: 'choice', options: [R('endpoint', 'A VPC endpoint for storage with an endpoint policy limited to the invoice bucket, and the bucket policy requiring that endpoint; other egress only to named destinations through the NAT and an egress firewall'), R('nat', 'Keep using the public endpoint through NAT with all outbound allowed'), R('publicip', 'Give app servers public IPs'), R('none', 'Block all access to storage')] } },
            { id: 'validate', points: 2, prompt: 'Which steps validate the redesign? Select all that apply.', hints: ['Lessons 2 and 5.'], input: { type: 'multi', options: [R('reach', 'Run Reachability Analyzer from the internet gateway to the database: expect "not reachable"'), R('flow', 'Keep flow logs on all subnets and alert on any accepted connection from outside 10.50.0.0/16 to the app or database tiers'), R('posture', 'Confirm the posture rule flags a test security group that opens 5432 to 0.0.0.0/0'), R('scanother', 'Scan other companies\' cloud addresses to compare'), R('disablelogs', 'Turn off flow logs once the fix is in to save cost')] } },
            { id: 'redesign', points: 2, prompt: 'Write the **redesign** for the invoice team: what was wrong, the immediate actions, the target network (subnets, routes, rules per tier, administration, private connectivity, egress), guardrails, and how you will validate it.', hints: ['Include the flow log evidence and the incident follow-up.'], input: { type: 'deliverable', title: 'Invoice app network redesign', competency: 'Securing cloud network architecture', minWords: 160, rubric: ['Explains the exposure from routes, the public setting and sg-db', 'Treats the accepted internet connections as a possible incident with immediate actions', 'Target three-tier design with private subnets and group-to-group rules', 'Identity-based administration, private endpoints and controlled egress', 'Guardrails and validation steps'], template: 'What was wrong:\n\nImmediate actions:\n\nTarget design:\n\nGuardrails:\n\nValidation:\n' } },
        ],
        reflection: ['Which single setting, if it had been blocked by a guardrail, would have prevented the exposure?', 'Why is "temporary" in a rule description a warning sign?', 'What can the flow log not tell you about the 18 KB connection?', 'How would the design differ on Azure or Google Cloud?'],
    },
    validator: {
        tasks: {
            cause: { type: 'multi', correct: ['route', 'public', 'sg'], feedback: { correct: 'Right: route, public setting and rule together.', incorrect: 'Exposure needs a path (route), an address (public IP) and permission (rule). The NAT gateway, load balancer and flow logs are not causes.' } },
            evidence: { type: 'choice', correct: 'incident', feedback: { correct: 'Right.', incorrect: 'Rows 1–3 are accepted connections from outside the VPC.' } },
            rules: { type: 'grid', minCorrectRatio: 0.75, cells: { lb443: { src: ['internet'] }, app8080: { src: ['sglb'] }, db5432: { src: ['sgapp'] }, admin: { src: ['none'] } }, feedback: { correct: 'Your rules match.', incorrect: 'Each tier accepts only from the tier in front of it; administration goes through identity.' } },
            placement: { type: 'choice', correct: 'private', feedback: { correct: 'Right.', incorrect: 'The database needs no path to or from the internet.' } },
            guardrail: { type: 'choice', correct: 'nacl', feedback: { correct: 'Right.', incorrect: 'A coarse deny for dangerous ports catches security-group mistakes without breaking everything.' } },
            private: { type: 'choice', correct: 'endpoint', feedback: { correct: 'Right.', incorrect: 'Keep service traffic private and egress to named needs.' } },
            validate: { type: 'multi', correct: ['reach', 'flow', 'posture'], feedback: { correct: 'Right.', incorrect: 'Never test systems you don\'t own; keep the logs that would show a regression.' } },
            redesign: { type: 'deliverable', minWords: 160, mustMention: [
                { label: 'the public route or setting', any: ['internet gateway', 'route', 'publicly accessible', 'public ip'] },
                { label: 'the security group rules', any: ['sg-db', 'sg-app', 'security group'] },
                { label: 'the possible incident', any: ['incident', 'rotate', 'credential', 'logins'] },
                { label: 'private endpoints or egress', any: ['endpoint', 'egress'] },
                { label: 'validation', any: ['reachability', 'validat', 'test'] },
            ], feedback: { correct: 'Redesign complete. Compare it with the rubric and the debrief.', incorrect: 'Your redesign is not complete yet.' } },
        },
        debrief: '## Worked solution\n\n**Why it was exposed:** subnet-db-a routes 0.0.0.0/0 to the internet gateway, the database is publicly accessible with a public IP, and sg-db allows 5432 (and 22) from anywhere. All three were needed; removing any one would have closed the path.\n\n**Evidence:** 198.51.100.77 connected twice on 5432, once moving about 18 KB, and 203.0.113.200 connected on 22. Treat this as a possible incident: check database and host logs for successful logins, rotate the database and host credentials, and preserve the flow logs (module 9).\n\n**Immediate actions (today):** remove the 0.0.0.0/0 rules from sg-db; set publicly accessible to No; then move the database.\n\n**Target design:**\n\n| Tier | Subnets | Route to internet | Inbound |\n| --- | --- | --- | --- |\n| Load balancer | Public, 2 zones | Internet gateway | 443 from 0.0.0.0/0 |\n| App | Private, 2 zones | NAT gateway (named egress only) | 8080 from sg-lb |\n| Database | Private, 2 zones | None | 5432 from sg-app |\n\nAdministration through Session Manager (no SSH rules). A storage gateway endpoint with an endpoint policy for the invoice bucket, and a bucket policy requiring it. Egress through an egress firewall allowing named destinations.\n\n**Guardrails:** network ACL deny for 22, 3389 and 5432 from outside the VPC; a posture rule for 0.0.0.0/0 on administration and database ports; a pipeline policy blocking such rules and publicly accessible databases; an organisation policy limiting public IPs.\n\n**Validation:** Reachability Analyzer from the internet gateway to the database returns not reachable; flow logs on all subnets with an alert for accepted external connections to private tiers; the posture rule fires on a test group.\n\n## What you have practised\n\nReading cloud network configuration and evidence, and redesigning it in layers: the daily work of a cloud security engineer.\n\n**Next:** module 4 covers data protection.',
    },
};

replaceModule(p.data, 'cloud-networking', {
    slug: 'cloud-networking',
    title: 'Cloud Network Security',
    purpose: 'Cloud networks are software-defined but follow familiar rules. This module covers virtual networks and routing, security groups, network ACLs and cloud firewall rules across the three major providers, private connectivity and egress, hybrid connectivity, and flow logs. You finish by fixing an exposed database network and writing the redesign.\n\n**To complete this module:** finish all five lessons and pass the lab (80%), including the written redesign.',
    prerequisiteKnowledge: 'Modules 1 and 2; Network Security modules 1, 3 and 4 help.',
    objectives: ['Design virtual networks with private application and database tiers', 'Write stateful and stateless filtering rules across providers', 'Reach provider services privately and control egress', 'Design hybrid connectivity with central inspection', 'Use flow logs for detection and investigation'],
    keyConcepts: ['VPC, VNet and VPC network', 'Public and private subnets', 'Security groups, NSGs and VPC firewall rules', 'Network ACLs and hierarchical policies', 'Group-to-group rules', 'Private endpoints', 'Egress control', 'Hub-and-spoke', 'Flow logs', 'Reachability analysis'],
    assessmentCriteria: ['Explains exposure from routes, settings and rules', 'Designs tiered rules correctly', 'Chooses private connectivity and guardrails', 'Writes a complete redesign with validation'],
    instructorNotes: 'Estimated time: 2.5 hours of lessons plus 1 hour of lab. Provider features change often; check the linked documentation before teaching details.',
    contentStatus: 'complete',
    lessons,
});
replaceLab(p.data, 'invoice-network-redesign', lab);

const C = 'networking';
replaceQuestions(p.data, 'cld-net-', [
    single('cld-net-01', C, 'foundation', 'Design networks', 'Which makes an AWS subnet public?', ['A NAT gateway', '*A route to an internet gateway', 'A security group', 'Its CIDR range'], 'Routes decide exposure.'),
    single('cld-net-02', C, 'foundation', 'Design networks', 'Which tier should normally be the only internet-facing one?', ['The database', '*The load balancer or API gateway', 'The app servers', 'The log store'], 'Keep everything else private.'),
    single('cld-net-03', C, 'intermediate', 'Compare filters', 'What is the key difference between a security group and a network ACL in AWS?', ['There is none', '*Security groups are stateful; network ACLs are stateless and evaluated in order', 'Network ACLs attach to instances', 'Security groups only deny'], 'Stateless rules need return traffic allowed.'),
    single('cld-net-04', C, 'intermediate', 'Write rules', 'Which source is best for the database\'s inbound 5432 rule?', ['0.0.0.0/0', 'The whole VPC', '*The app tier\'s security group', 'The load balancer\'s public IP'], 'Reference groups, not ranges.'),
    single('cld-net-05', C, 'foundation', 'Know defaults', 'What do Google Cloud\'s implied VPC firewall rules do?', ['Allow all ingress', '*Allow all egress and deny all ingress', 'Deny all egress', 'Nothing'], 'Know your platform\'s defaults.'),
    single('cld-net-06', C, 'intermediate', 'Group workloads', 'Which Azure feature lets NSG rules refer to groups of workloads instead of IP addresses?', ['Azure Bastion', '*Application security groups', 'ExpressRoute', 'Private DNS'], 'ASGs group network interfaces.'),
    single('cld-net-07', C, 'intermediate', 'Validate reachability', 'Which tool analyses whether an AWS path is open without sending traffic?', ['GuardDuty', '*Reachability Analyzer', 'Macie', 'CloudTrail'], 'Azure IP flow verify and Google Connectivity Tests are equivalents.'),
    single('cld-net-08', C, 'intermediate', 'Use private connectivity', 'What is the main security benefit of a private endpoint for storage?', ['Faster uploads', '*Service traffic stays private and public access can be disabled or restricted to the endpoint', 'Free storage', 'No need for IAM'], 'Remove the public path.'),
    single('cld-net-09', C, 'foundation', 'Administer securely', 'What should replace public SSH access to cloud servers?', ['A longer password', '*An identity-based session service with logging', 'Telnet', 'A shared key on a wiki'], 'Session Manager, Azure Bastion or IAP.'),
    single('cld-net-10', C, 'intermediate', 'Control egress', 'Which egress design is best for app servers that call two partner APIs?', ['Allow all outbound', '*Allow only those destinations through an egress firewall or proxy', 'Give each server a public IP', 'Block DNS'], 'Egress to named needs.'),
    single('cld-net-11', C, 'intermediate', 'Design hybrid networks', 'Why share only the routes each side needs over a hybrid link?', ['It is faster', '*So a dev network cannot reach production on-premises systems', 'Providers charge per route', 'It is required by BGP'], 'Least privilege applies to routing.'),
    multiple('cld-net-12', C, 'intermediate', 'Use flow logs', 'What can flow logs help with? Select all that apply.', ['*Investigating which hosts an instance talked to', '*Detecting internet connections to private tiers', '*Validating that closed paths stay closed', 'Reading the SQL queries sent to a database'], 'Flows have no payload content.'),
    single('cld-net-13', C, 'foundation', 'Read flow logs', 'A flow record shows action REJECT. What does it mean?', ['Data was stolen', '*The network\'s rules did not allow the connection', 'The record is invalid', 'The connection succeeded'], 'REJECT means the filters blocked it.'),
]);

save(p);
const old = path.join(__dirname, '../programmes/cloud-security/lessons/cloud-networking/virtual-network-design.md');
if (fs.existsSync(old)) fs.unlinkSync(old);
console.log('cloud module 3 applied:', lessons.length, 'lessons,', p.data.labs.length, 'labs,', p.data.questions.length, 'questions');
