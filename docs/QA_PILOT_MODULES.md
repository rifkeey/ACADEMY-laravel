# QA report: pilot modules

Date: 23 October 2026. Scope: Network Security module 3 (Firewalls and Network Access Control) and Cloud Security module 2 (Cloud Identity and Access Management), plus the platform components added for them.

Status key: **Tested** = checked by an automated test that was run and passed, or by hand in the browser as stated. **Reviewed** = checked by reading only. **Not tested** = not yet checked.

## Contents delivered

| | Network module 3 | Cloud module 2 |
| --- | --- | --- |
| Lessons | 5 (≈ 2.5 h): firewall types; rule order and default deny; least privilege and egress; logging, review and change; network access control | 5 (≈ 2.75 h): identities and authn/authz; policy evaluation across AWS/Azure/GCP; least privilege in practice; workload identities; privileged access and escalation |
| In-lesson checks | 15 (single, multi-select, code-reading; feedback per option on some) | 15 |
| Diagram | Kanem head-office zone diagram (SVG with text alternative) | Text diagrams of the request and decision flow |
| Lab | 8 tasks, 90 min, pass 80%: rule review (multi, choice, prediction), risk mapping (grid), rule-set rebuild tested against 22 hidden connections, written change request | 7 tasks, 90 min, pass 80%: permission review, escalation and evasion paths, evidence interpretation, credential choice, policy written in the simulator and tested against 15 hidden requests, written access-review finding |
| Independent challenge | Revealable advanced challenge in the lab instructions | Revealable advanced challenge (protective explicit deny) |
| Debrief / worked solution | Sent only after a pass | Sent only after a pass |
| Reflection questions | 5 | 5 |
| Exam questions added | 14 (category `firewalls`) | 14 (category `iam`) |
| Portfolio deliverable | Firewall change request | Access-review finding |

## Technical accuracy

| Check | Result |
| --- | --- |
| All 35 sources cited by the pilot lessons return HTTP 200 | **Tested**, 23 Oct 2026 |
| Page titles match the cited technique/standard (ATT&CK T1021.001, T1046, T1048, T1071, T1078.004, T1098, T1190, T1548.005, T1572; IEEE 802.1X-2020; CIS Controls v8.1; CSA CCM; NIST SP 800-210; AWS/Azure/Google pages) | **Tested** |
| Control identifiers (SP 800-53 SC-7, SC-7(5), AC-4, AC-6, CM-3; CIS Controls 12 and 13) used as described | **Reviewed** against my knowledge of the documents; the catalogues themselves were not re-read in full |
| Azure deny assignments described as created by Azure features, not written like roles | **Tested** against the Microsoft page title/topic, corrected during QA |
| NIST SP 800-41 Rev. 1 and SP 800-144 flagged as older guidance | Done in lessons and source notes |
| Lab worked solutions actually pass their graders; an empty submission fails; no starter query solves a task | **Tested** (`ContentIntegrityTest`) |
| AWS-style policy evaluation in the simulator (explicit deny wins, implicit deny, wildcards, case rules for actions vs resources) | **Tested** (`TeachingComponentsTest`). The simulator deliberately rejects Condition, NotAction, NotResource and Principal, so learners are never misled |

## Learner experience

| Check | Result |
| --- | --- |
| All 10 pilot lessons render over HTTP with every teaching block converted and no byte-order mark | **Tested** (`HttpPagesTest`) |
| Lab pages contain no answer keys, hidden tests, rubric checks or worked solutions | **Tested** (`HttpPagesTest`) |
| Debrief returned only when the lab is passed | **Tested** (`HttpPagesTest`) |
| Portfolio shows the learner's own deliverables only | **Tested** (`HttpPagesTest`) |
| Callouts, reveal blocks and code-based checks render in the browser | **Tested** in the browser (DOM inspection) |
| Multi-select check marks the right options | **Tested** in the browser |
| Feedback text appearing after an answer (`x-show`) | **Not tested in this session**: the app window was hidden, which pauses browser animation frames, so Alpine could not update visibility. The same mechanism worked in earlier sessions. Re-check with the window visible |
| IAM lab: starter policy prefilled, deliverable template and rubric shown; grader feedback names the first failing request | **Tested** in the browser via the real endpoint |
| No horizontal scrolling at 375 px on pilot lessons, labs and portfolio | **Tested** after fixing an overflow caused by a wide code block inside a knowledge-check fieldset |
| Keyboard use, screen-reader announcements | **Reviewed** (native controls, labels, `role="status"`, `<details>`); not tested with a screen reader |

## Defects found and fixed during QA

1. Lesson pages sent a byte-order mark before `<!doctype html>` (two view files): fixed, test added.
2. Knowledge checks with code blocks overflowed on phones (fieldset minimum width): fixed in lessons, labs and exams.
3. The portfolio could show an older attempt when two were submitted in the same second: fixed (ordering by id).
4. The lab debrief (worked solution) was first stored in learner-visible content: moved to the server-only answer key.
5. An example access key in a lesson resembled a real AWS key format: masked.

## Known limitations

- Deliverables are checked for completeness (length and required points), not for quality. Learners self-assess against the rubric; there is no instructor marking queue yet.
- Module completion is enforced through the course requirements (all lessons marked complete + labs passed + exam). Lesson completion is learner-declared; labs are graded on the server.
- Neither exam can be published yet: each bank needs about 120 approved questions (12 per module) for a balanced 40-question draw. Each currently has 14.
- The other 18 modules are still outlines or starter lessons (see `docs/NETWORK_CLOUD_PLAN.md`).

## Batch 1 (23 October 2026): Network module 1 and Cloud module 1

| | Network module 1: Networking Foundations | Cloud module 1: Cloud Computing and Shared Responsibility |
| --- | --- | --- |
| Lessons | 5 (≈ 3 h): layer models; IP addressing and subnetting (with refresher); switching, ARP and routing; TCP/UDP and ports; DHCP, DNS, NAT and TLS | 5 (≈ 2.5 h): NIST definition and models; regions, accounts and hierarchy; shared responsibility by model; governance and adoption risks; seven misconceptions |
| In-lesson checks | 16 | 15 |
| Lab | *Trace the packet path* (9 tasks, 60 min, pass 80%): subnet maths, host and router forwarding decisions, root-cause diagnosis from command output, separating an unrelated symptom, ordered packet path, written ticket resolution | *Who secures what?* (7 tasks, 60 min, pass 80%): 40-cell responsibility grid across IaaS/PaaS/SaaS/serverless, incident attribution, attestation scope, account structure, written CFO briefing |
| Exam questions added | 13 (category `foundations`) | 13 (category `fundamentals`) |
| Sources | 13 RFCs and the IANA port registry added; all URLs HTTP 200 with matching titles on 23 Oct 2026 | 8 provider pages added (shared responsibility, regions, hierarchy, multi-account); all HTTP 200 with matching titles |

**Tests:** the HTTP test now covers every module marked `complete` in both programmes (lessons render with all teaching blocks converted; lab pages hold no answer keys, hidden tests, grid answers or worked solutions; debrief only after a pass). Full suite: 44 tests passing.

**Accuracy notes:** the lab's fault was designed so the symptoms are consistent (printer reachable on the same /24, gateway unreachable, Windows "Destination host unreachable" reported by the host itself). Statements about provider responsibilities follow the providers' own pages; one unsupported phrasing ("overwhelmingly") was replaced by a qualitative statement with a note that the course does not quote a percentage. Legal references (Nigeria Data Protection Act 2023, NDPC) are pointers, not legal advice.

**Still not checked in the browser:** answer feedback appearing on screen (Alpine `x-show`), because the app window was hidden during testing.

## Batch 2 (23 October 2026): Network module 4 and Cloud module 4

| | Network module 4: Segmentation and Zero Trust | Cloud module 4: Data Protection and Cryptography |
| --- | --- | --- |
| Lessons | 5: zones and DMZs; VLANs and enforcement; microsegmentation; Zero Trust Architecture (NIST SP 800-207, CISA ZTMM); management plane and validation | 5: classification and discovery; encryption and key management; secrets management; preventing public storage access; backup and recovery |
| In-lesson checks | 15 | 15 |
| Lab | *Segment the Lagos data centre* (6 tasks, pass 80%): zone placement, policy tested against 24 hidden flows, three access decisions, written rationale | *Remediate an exposed loan-documents bucket* (7 tasks, pass 80%): classification, configuration causes, response order, prevention, written remediation plan |
| Exam questions | 13 (`segmentation`) | 13 (`data`) |
| Sources | 4 added (SP 800-207A, SP 800-215, CISA ZTMM, IEEE 802.1Q); all HTTP 200 with matching titles | 12 added (SP 800-57 Pt 1 r5, KMS/Key Vault/Cloud KMS, secrets managers, public-access prevention ×3, Object Lock, Macie, ATT&CK T1530); all HTTP 200 with matching titles |

**Tests:** 44 passing (899 assertions); worked solutions pass, empty submissions fail, lab pages hold no answer keys or solutions, and every lesson renders.

**Accuracy notes:** the data-protection lab states the NDPA 2023 regulator-notification duty (72 hours for notifiable breaches) as a pointer for the DPO, not legal advice. Have a lawyer confirm it before publication.

**Written at a high level by request:** attack and misconfiguration topics cover risks, detection and fixes only. Lessons stopped by the safety check, and drafts awaiting review, are listed in `docs/NETWORK_CLOUD_PLAN.md` section 6.

## Batch 3 (23 October 2026): Cloud module 5

Cloud module 5, *Cloud Logging, Monitoring and Detection*: 5 lessons (audit logs; identity and sign-in events; centralising and protecting logs; managed detection, SIEM and prioritisation; gaps and detection engineering), 15 checks, a logging-coverage assessment lab (grid across four accounts, storage, retention, prioritisation and a written design) and 13 exam questions (`logging`). 8 sources added (CloudTrail and integrity validation, Azure activity log, Entra sign-in logs, Google Cloud Audit Logs, GuardDuty, Defender for Cloud, Security Command Center); all HTTP 200 with matching titles. Written at a high level (risks, detection and fixes). Tests: 44 passing (932 assertions).

Network module 5 lessons are drafted but unpublished; its module definition, lab and questions were stopped by the safety check (see the plan, section 6).

## Batch 4 (23 October 2026): Cloud module 6 and Network module 6

| | Cloud module 6: Workload and Compute Security | Network module 6: Secure Remote Access, VPNs and Network Cryptography |
| --- | --- | --- |
| Lessons | 5: VM hardening and patching; container image security; Kubernetes basics; serverless; runtime protection and vulnerability management | 5: choosing remote access; IPsec; TLS and certificates; secure admin protocols and Wi-Fi; MFA, conditional access and troubleshooting |
| In-lesson checks | 15 | 15 |
| Lab | *Review three ways of running the reporting service* (6 tasks, pass 80%) | *Diagnose and secure Kanem's remote access* (7 tasks, pass 80%) |
| Exam questions | 13 (`workloads`) | 13 (`remote-access`) |
| Sources | 6 added (SP 800-190, SP 800-40r4, CIS Kubernetes, Kubernetes security and Pod Security Standards, Lambda security) | 8 added (SP 800-52r2, SP 800-153, WPA3, RFC 4253, RFC 5280, SP 800-63B, CISA phishing-resistant MFA, Entra Conditional Access) |

All new URLs returned HTTP 200 with matching titles (the CISA item is a PDF). Written at a high level. No content was stopped in this batch. Tests: 44 passing (998 assertions). Consistency fix: the Kano branch uses 10.60.0.0/16 so it does not overlap the cloud invoice network.

## Batch 5 (23 October 2026): Cloud module 7 and Network module 7

| | Cloud module 7: Security Posture and Configuration Management | Network module 7: DNS, DHCP and Web Protocol Security |
| --- | --- | --- |
| Lessons | 5: misconfiguration and CSPM; baselines and policy as code; infrastructure-as-code security; drift and continuous validation; prioritising remediation | 5: securing DNS; DNS logs as evidence; DHCP security; web traffic, proxies and email authentication; joining evidence and escalating |
| In-lesson checks | 15 | 15 |
| Lab | *Prioritise Kanem's posture findings* (6 tasks, pass 80%) | *Triage DNS, DHCP and email evidence at Kanem* (7 tasks, pass 80%) |
| Exam questions | 13 (`posture`) | 13 (`protocol-security`) |
| Sources | 6 added (SP 800-128, Security Hub, AWS Config, Azure Policy, Google Cloud organization policy, OPA) | 11 added (RFC 4033, 7858, 8484, 7208, 6376, 9989; SP 800-81 Rev. 3; SP 800-177 Rev. 1; CISA Protective DNS; ATT&CK T1071.004 and T1568) |

All new URLs returned HTTP 200 with matching titles. Two sources were updated while checking: NIST SP 800-81-2 has been withdrawn and replaced by **SP 800-81 Rev. 3** (March 2026), and DMARC's RFC 7489 is obsoleted by **RFC 9989**. The lessons cite the current versions. Network module 7 teaches DNS indicators for detection only; lab domains use the reserved `.example` name. No content was stopped in this batch. Tests: 44 passing (1,064 assertions).

**Question banks:** Network 66, Cloud 79 (about 120 each are needed before the exams can be published).

## Batch 6 (23 October 2026): Cloud module 8 and Network module 8

| | Cloud module 8: Cloud Application, API and DevSecOps Security | Network module 8: Network Traffic Analysis with Wireshark |
| --- | --- | --- |
| Lessons | 5: API security and the OWASP API Top 10; securing CI/CD pipelines; dependencies, images and SBOMs; security testing in the pipeline; software supply chain | 5: capturing safely and lawfully; Wireshark navigation and display filters; TCP behaviour; DNS and TLS in captures; documenting packet evidence |
| In-lesson checks | 15 | 15 |
| Lab | *Review and fix the loans API pipeline* (7 tasks, pass 80%) | *Why is the portal slow? Analyse a capture* (9 tasks, pass 80%), with a **downloadable synthetic PCAP** and the same frames in the browser |
| Exam questions | 13 (`devsecops`) | 13 (`traffic-analysis`) |
| Sources | 12 added (OWASP API Top 10 2023, OWASP Top 10:2025, ASVS, OWASP CI/CD Top 10, GitHub Actions secure use and OIDC, CISA SBOM and KEV, SP 800-218, SP 800-204D, SLSA v1.1, Sigstore) | 6 added (Wireshark display filter reference and filter syntax, pcap-filter, RFC 6298, SP 800-86, pcap Internet-Draft) |

All new URLs returned HTTP 200 with matching titles. SSDF v1.2 (SP 800-218 Rev. 1) is still a draft, so v1.1 is cited and the draft is mentioned.

**Cloud module order now matches the plan:** workloads (6) before posture (7), the new DevSecOps module as 8, cloud incident response as 9, capstone as 10. The old `governance-resilience` starter module (one short lesson) was removed; resilience and governance are covered in the capstone, as planned.

**The capture:** `public/content/network-security/kanem-portal-slow.pcap` (27 frames, 9,305 bytes) is produced by `database/content/authoring/pcap-kanem-portal.cjs`, which is deterministic and re-checks every IPv4, TCP and UDP checksum. A new test checks that the published file's hash and frame count match the lab. Served locally with HTTP 200 and `application/vnd.tcpdump.pcap`. **Not yet opened in Wireshark** (not installed on the build machine): a reviewer should confirm the Info text and duplicate-ACK numbering match their Wireshark version.

The old starter lab `packet-and-dns-analysis` was replaced by the capture lab; its DNS-tunnelling task is now covered in Network module 7. An admin test that created a source called `cisa-kev` now uses its own key, because `cisa-kev` is in the register. No content was stopped in this batch. Tests: 45 passing (1,129 assertions).

**Question banks:** Network 79, Cloud 92 (about 120 each are needed before the exams can be published).
