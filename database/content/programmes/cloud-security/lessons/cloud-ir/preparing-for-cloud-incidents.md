:::scenario 02:14 on a Wednesday
At 02:14 an alert fires: audit logging has been stopped in Kanem's production cloud account. Nobody on call has ever handled a cloud incident. Whether the next hour goes well depends almost entirely on what was prepared beforehand. This module covers preparing for, investigating and recovering from cloud incidents.
:::

NIST SP 800-61 Rev. 3, *Incident Response Recommendations and Considerations for Cybersecurity Risk Management* (2025), places incident response inside the NIST Cybersecurity Framework 2.0. **Preparation** (Govern, Identify, Protect) is as important as **response** (Detect, Respond, Recover).

## What is different in the cloud

| Difference | Consequence |
| --- | --- |
| Everything is an API call | The control-plane **audit log** is the primary evidence; without it you are blind |
| Identities, not networks, are the perimeter | Most incidents start with a stolen credential or token (module 2) |
| Resources are created and destroyed in seconds | Evidence can disappear; automation can spread a mistake fast |
| Shared responsibility | The provider investigates its layers; you investigate yours, using the logs and tools it gives you |
| Global reach | Activity in unused regions is both a risk and a useful signal |

## Prepare before anything happens

| Area | Preparation |
| --- | --- |
| **Logging** | Organisation-wide audit trail, data-access logs for sensitive storage, sign-in logs, all sent to a separate, locked-down log account (module 5) |
| **Responder access** | A pre-approved **incident response role** in every account: read access to everything, ability to quarantine, protected by phishing-resistant MFA, tested regularly. Break-glass accounts sealed and monitored |
| **Playbooks** | Step-by-step guides for the likely cases: leaked access key, compromised user, public storage, suspicious compute, logging disabled |
| **Isolation tools** | A "quarantine" security group with no rules, a deny-all policy that can be attached to an identity, a forensics account to copy evidence into |
| **Contacts** | Provider support plan and how to open a security case; legal; data protection officer; communications; key suppliers |
| **Practice** | Tabletop exercises and game days using synthetic scenarios like the one in this module's lab |

The major providers publish their own incident response guides (for example the AWS Security Incident Response Guide and Microsoft's incident response overview for Azure). They describe provider-specific tools; the principles are the same.

## Roles in an incident

| Role | Does |
| --- | --- |
| **Incident lead** | Decides, prioritises, keeps the timeline |
| **Investigators** | Query logs, scope the incident, preserve evidence |
| **Cloud platform engineers** | Carry out containment and recovery changes |
| **Communications** | Updates for management, staff and, if needed, customers |
| **Legal and data protection** | Decide on notification duties (for Kanem, the Nigeria Data Protection Act 2023 and NDPC guidance) |

:::takeaway
Cloud incident response is won in preparation: complete, protected logs; a tested responder role; playbooks; isolation tools; known contacts; and practice. Plan around identities and the audit log, because that is where cloud incidents start and where the evidence is.
:::
