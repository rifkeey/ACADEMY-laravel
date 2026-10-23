# Network Security and Cloud Security: audit and build plan

Working record for building out the two programmes. Updated as each phase completes; the status table at the end is the source of truth for what is finished.

## 1. Audit (23 October 2026)

### Stack

Laravel 13 / PHP 8.3 / MySQL 8, Blade components, Alpine.js (CSP build), Tailwind 4. No Livewire. Content is authored in `database/content/programmes/<dir>/` (`programme.json` plus one Markdown file per lesson) and loaded by `CatalogueSeeder` (insert-if-missing); after go-live it is maintained in **Admin**.

### What the two programmes contain today

| | Network Security | Cloud Security |
| --- | --- | --- |
| Modules | 9, all "starter" | 10, all "starter" |
| Lessons | 9 (one ~300-word lesson per module) | 10 (same) |
| Knowledge checks | 0 | 0 |
| Labs | 2 (firewall rule builder, packet/DNS analysis) | 2 (IAM policy review, audit-log KQL) |
| Question bank | 0 questions, exam unpublished | 0 questions, exam unpublished |
| Enrolment | Closed | Closed |

Nobody is enrolled and nothing is deployed, so the content files can be restructured safely. No migration of learner data is needed.

### What the platform already supports

| Capability | Status |
| --- | --- |
| Lessons in Markdown (GitHub-flavoured: headings, tables, code blocks, links, images from this site) | Yes, rendered with raw HTML escaped |
| Per-lesson objectives, estimated time, references from the source register | Yes |
| Knowledge checks inside lessons | Single-answer only, instant feedback with explanation, not graded |
| Labs: scenario, objectives, instructions, workspace (datasets explorer, KQL console, documents), hints, drafts, server grading, attempt history | Yes |
| Lab task types | text, number, choice, multi-select, KQL query, firewall rule builder, grid (matching / rating / ordering) |
| Final exam: bank of approved questions, 40 drawn and balanced by category, 75% pass, server timer and grading, explanations afterwards | Yes |
| Completion: all required lessons + all required labs + exam pass, then certificate and badge | Yes |
| Admin editing of programmes, modules, lessons, labs (JSON), questions, exams, sources | Yes |
| Versioning (learners stay on the version they enrolled in) | Yes |

### Gaps against the master prompt, and what will be done

| Gap | Decision |
| --- | --- |
| No callouts, scenario boxes or **revealable** hints/solutions in lessons | **Add**: safe Markdown blocks `:::note`, `:::tip`, `:::warning`, `:::scenario`, `:::takeaway`, `:::reveal Title` (collapsible). Inner text is still Markdown with HTML escaped |
| Knowledge checks are single-answer only, no per-option feedback | **Add**: multi-select checks and optional feedback per option |
| No written deliverables (reports, corrected policies, change requests) and no portfolio | **Add**: lab task type `deliverable` (written answer with a rubric, a minimum length and optional required terms checked automatically) and a learner **Portfolio** page listing their submitted deliverables. The automatic check confirms completeness only; quality is judged by the learner against the rubric. This is stated in the UI |
| No IAM policy exercise that can be graded objectively | **Add**: task type `iam-policy`, a policy evaluator (explicit deny wins, otherwise an allow is needed, wildcards in actions and resources) tested against hidden access requests, like the firewall builder |
| Diagrams | Inline SVG files in `public/content/<programme>/` referenced from Markdown, with alt text; text diagrams in code blocks where clearer |
| Downloadable lab files (e.g. PCAP for Wireshark) | **Add** in the packet-analysis module: generated synthetic `.pcap` files served from `public/labs/`, with a reproducible generator script |
| Module completion criteria | Already enforced at course level (lessons + labs + exam). Each module will state its criteria in the module purpose text; lesson "mark complete" stays learner-driven, but labs are graded server-side, so a module can't be finished by clicking through |
| Instructor-graded practical assessment | **Not built**. Capstones use deliverables + rubric self-assessment and objective lab tasks. An instructor review queue is a documented follow-up |
| Real cyber range / live cloud accounts | **Not used.** All labs run in the browser on synthetic data, are labelled as simulations, and cost nothing. Optional free-tier exercises may be described as extensions, clearly marked |

## 2. Course design decisions

- **Continuing case study:** *Kanem Financial Services* (fictional): head office in Lagos, branches in Abuja and Kano, a data centre, remote staff, and workloads moving to the cloud. Problems found in early modules come back later (e.g. the over-permissive firewall rule from Network module 3 is the way in during the module 9 incident).
- **Lesson pattern:** short sections, a realistic example, a diagram/config/log, in-lesson checks, a `:::takeaway`, references. Not every lesson is identical.
- **Module pattern:** introduction scenario → 4–6 lessons → a graded lab (guided tasks + independent challenge + written deliverable) → reflection questions → module questions added to the exam bank.
- **Difficulty:** each lab has a core path and an `Advanced challenge` reveal block; refreshers (subnetting, Linux commands, identity basics) are optional lessons.
- **Exam:** existing rules kept: 40 questions from the bank, balanced by module category, 75% pass, 3 attempts, 24-hour cooldown. Target ≥ 12 approved questions per module (≥ 120 per programme) before an exam is published.

## 3. Curriculum outlines

### Network Security Professional (10 modules, ~70 hours)

| # | Module (slug) | Lessons | Lab / challenge | Main standards |
| --- | --- | --- | --- | --- |
| 1 | Networking Foundations for Security (`networking-foundations`) | Models and encapsulation · IP addressing, subnetting and CIDR · Switching, ARP and routing · TCP, UDP and ports · DNS, DHCP, NAT and TLS basics | Trace the packet path between two hosts across a diagram and fix a connectivity fault | RFC 9293, RFC 1034, NIST CSF 2.0 |
| 2 | Network Threats and Attack Surfaces (`network-threats`) | Reconnaissance · Spoofing, poisoning and MITM · DoS · Credential attacks and lateral movement · C2 and DNS abuse | Triage synthetic flow and auth telemetry; map behaviours to ATT&CK | MITRE ATT&CK, CIS Controls 13 |
| 3 | **Firewalls and Network Access Control** (`firewalls`) — **pilot** | Stateless vs stateful filtering · Rule order, shadowing and default deny · Least-privilege and egress rules · Logging, review and change control · Network access control (802.1X, NAC) | Review a flawed rule set, rebuild it in the rule engine, write the change request | NIST SP 800-41r1, SP 800-53r5 (SC-7, AC-4, CM-3), CIS Controls 12/13, IEEE 802.1X |
| 4 | Segmentation and Zero Trust (`segmentation-zero-trust`) | Zones, VLANs and DMZ · East-west vs north-south · Microsegmentation · Zero Trust Architecture · Management-plane separation | Design segmentation for Kanem; validate with test flows | NIST SP 800-207, CIS 12 |
| 5 | IDS, IPS and Network Detection (`ids-ips`) | Signatures vs anomalies · Alert quality and tuning · Placement and telemetry · Encrypted traffic limits | Triage IDS alerts, separate benign from suspicious, propose tuning | NIST SP 800-94, CIS 13 |
| 6 | Secure Remote Access, VPNs and Network Cryptography (`remote-access`) | VPN types · IPsec · TLS and certificates · Secure admin protocols · Wi-Fi security · MFA and conditional access | Diagnose a remote-access failure from logs; recommend a secure config | NIST SP 800-77r1, SP 800-207 |
| 7 | DNS, DHCP and Web Protocol Security (`protocol-security`) | DNS security and tunnelling indicators · DHCP threats · HTTP and TLS metadata · Proxies and DNS filtering | Investigate DNS/HTTP metadata and decide whether to escalate | RFC 1034, RFC 9110 |
| 8 | Traffic Analysis with Wireshark (`traffic-analysis`) | Capturing safely · Display filters · TCP behaviour · DNS and TLS in captures · Documenting evidence | **Downloadable synthetic PCAP**; answer questions, write findings report | Wireshark User's Guide, RFC 9293 |
| 9 | Network Security Monitoring and Incident Response (`network-ir`) | Log sources and flow data · Correlation in a SIEM · Triage and escalation · Containment and evidence · Reporting | Multi-stage incident across firewall, DNS, flow and IDS data (KQL) | NIST SP 800-61r3, SP 800-92 |
| 10 | Network Security Architecture and Capstone (`network-capstone`) | Threat modelling · Resilience · Hardening and assessment · Risk prioritisation · Documentation | Capstone: architecture, policy, segmentation rationale, monitoring plan, risk register, validation checklist | NIST CSF 2.0, SP 800-30r1, SP 800-115 |

### Cloud Security Professional (10 modules, ~75 hours)

| # | Module (slug) | Lessons | Lab / challenge | Main standards |
| --- | --- | --- | --- | --- |
| 1 | Cloud Computing and Shared Responsibility (`cloud-fundamentals`) | Service and deployment models · Regions, accounts, subscriptions, projects · Shared responsibility by model · Governance and misconceptions | Assign responsibilities across four architectures | NIST SP 800-145, SP 800-144, provider shared-responsibility pages |
| 2 | **Cloud Identity and Access Management** (`iam`) — **pilot** | Identities, authentication and authorisation · How policies are evaluated · Least privilege in practice · Workload identities and temporary credentials · Privileged access and escalation paths | Review an over-permissive policy, write a least-privilege policy in the policy simulator, document the escalation risk | NIST SP 800-210, AWS IAM docs, Azure RBAC, Google Cloud IAM, CSA CCM (IAM), ATT&CK T1078.004/T1098/T1548.005 |
| 3 | Cloud Network Security (`cloud-networking`) | Virtual networks and routing · Security groups vs network ACLs · Private connectivity · Flow logs · Hybrid and zero trust | Redesign an exposed architecture; validate rules | MCSB Network Security, AWS VPC docs |
| 4 | Cloud Data Protection and Cryptography (`data-protection`) | Classification · Encryption and key management · Secrets · Storage permissions · Backup and recovery | Storage exposure investigation and remediation plan | NIST SP 800-57 (ref.), CSA CCM (DSP, CEK) |
| 5 | Cloud Logging, Monitoring and Detection (`logging-monitoring`) | Audit logs · Identity events · Flow and storage logs · Centralisation, retention, integrity · Detection engineering | Reconstruct a timeline from audit, identity, network and config logs (KQL) | NIST SP 800-92 |
| 6 | Cloud Workload and Compute Security (`workloads`) | VM hardening and patching · Containers and images · Kubernetes basics · Serverless · Runtime protection | Review an insecure workload deployment | CIS Benchmarks, CIS Kubernetes |
| 7 | Cloud Security Posture and Configuration (`posture`) | Misconfiguration patterns · CSPM · Baselines and policy as code · IaC security · Drift | Rank synthetic findings into a remediation plan | CIS Benchmarks, CSA CCM |
| 8 | Cloud Application, API and DevSecOps Security (`devsecops`) | API authn/z · CI/CD security · Dependency and image scanning · IaC review · Supply chain | Fix an insecure pipeline definition | OWASP Top 10, OWASP API Security |
| 9 | Cloud Incident Response and Threat Hunting (`cloud-ir`) | Preparation · Credential compromise · Privilege escalation and persistence · Exposure and data access · Evidence and recovery | Investigate a simulated compromise; incident report | NIST SP 800-61r3, ATT&CK Cloud |
| 10 | Cloud Security Architecture and Capstone (`cloud-capstone`) | Landing zones · Identity governance · Logging and key architecture · Resilience and guardrails · Cost/security trade-offs | Capstone: full secure design with threat model, control mapping, risk register, roadmap, validation checklist | AWS Well-Architected Security Pillar, Microsoft CAF, NIST CSF 2.0 |

## 4. Source register

New sources are added to `database/content/sources.json` with the date checked. All URLs cited in the pilot modules were requested on 23 October 2026 and returned HTTP 200; page titles were confirmed for ATT&CK techniques, IEEE 802.1X-2020, CIS Controls v8.1, CSA CCM and NIST SP 800-210. A 200 response confirms the page exists, not that every statement was re-read.

Notes for reviewers:
- NIST SP 800-41 Rev. 1 dates from 2009. Its principles (default deny, policy management) are still sound, but it predates cloud and next-generation firewalls, so lessons say so.
- ISO/IEC 27001/27017/27018 are paid standards: cited by title only, never quoted.
- Provider documentation changes often. Lessons teach the shared concepts and link to the provider pages instead of copying console steps.

## 5. Status

| Phase | Status |
| --- | --- |
| 1 Audit | Done (this document) |
| 2 Curriculum outlines | Done (section 3) |
| 3 Source register | Pilot sources verified (35 cited, all HTTP 200 on 23 Oct 2026); the rest per module as it is written |
| 4 Reusable components | Done: teaching blocks, multi-select checks with per-option feedback, `iam-policy` and `deliverable` lab tasks, post-pass debrief, reflection questions, Portfolio page |
| 5 Pilot modules (Network 3, Cloud 2) | Done, see `docs/QA_PILOT_MODULES.md`. Awaiting review before the remaining modules are written in the same pattern |
| 6 Remaining modules | In progress. Done: Network 1, 3, 4; Cloud 1, 2, 4. Partly done: Network 2 (1 lesson), Cloud 3 (lessons awaiting review, see section 6). Next: Network 5–10, Cloud 5–10 |
| 7 Integration testing | Not started |
| 8 Final review | Not started |

## 6. Lessons needing a human author or reviewer

An automated safety check stopped content generation four times while these lessons were being written. They are left for AMANIQ's subject-matter experts. Topics are listed at a high level only.

| Module | Lesson | Status |
| --- | --- | --- |
| Network 2 — Network Threats | Spoofing, poisoning and adversary-in-the-middle (risks, detection and mitigations) | **Not written**: please author |
| Cloud 3 — Cloud Network Security | Security groups, network ACLs and cloud firewall rules compared across AWS, Azure and Google Cloud | **Not written**: please author |
| Network 5 — IDS, IPS and Network Detection | Module definition, in-lesson checks, lab (alert triage and tuning) and exam questions | **Not written**: stopped while being generated; please author. The five lessons are **drafted but unpublished** (`lessons/ids-ips/*.md` except the old `network-detection-basics.md`); please review |
| Cloud 3 — Cloud Network Security | Private connectivity and egress; hybrid connectivity; flow logs and monitoring | **Drafted but unpublished** (`lessons/cloud-networking/*.md`, not yet listed in `programme.json`). Please review before they are linked into the module |

Also written and published but worth a reviewer's eye, since they discuss attacker behaviour: Network 2 lesson *Reconnaissance and scanning* (detection-focused; file present, not yet linked into the module).

Remaining Network 2 topics (denial of service, credential attacks and lateral movement, command-and-control and DNS abuse) will be written at a high level (risks, detection and fixes only); any that are stopped will be added to this table.
