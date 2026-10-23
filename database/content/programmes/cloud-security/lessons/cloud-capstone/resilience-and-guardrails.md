A secure design keeps working when things go wrong, whether that is an outage, a mistake or an attack, and stops the most dangerous mistakes from being possible at all.

## Resilience

| Question | Design answer for Kanem Pay |
| --- | --- |
| **What must keep running?** | Payments authorisation and the customer app; statements and analytics can wait |
| **How much downtime and data loss are acceptable?** | Agree a **recovery time objective (RTO)** and **recovery point objective (RPO)** per service with the business, for example payments RTO 1 hour, RPO 5 minutes |
| **Zones and regions** | Run payments across multiple availability zones in one region; a second region only if the RTO and regulators require it |
| **Backups** | Automated, encrypted, copied to a separate backup account with immutability, so a compromised production account can't delete them (module 4) |
| **Recovery from attack** | Rebuild from infrastructure as code into a clean account; restore data from immutable backups taken before the incident (module 9) |
| **Testing** | Restore tests every quarter; a game day each year that simulates losing a zone and a compromised account |

NIST SP 800-34 Rev. 1 covers contingency planning, and SP 800-160 Vol. 2 Rev. 1 describes designing for **cyber resiliency**: anticipating, withstanding, recovering from and adapting to attacks. The AWS Well-Architected Reliability Pillar gives provider-specific practices.

## Guardrails

Guardrails are controls applied centrally so every account gets them automatically (module 7).

| Type | Examples | Implemented as |
| --- | --- | --- |
| **Preventive** | Deny disabling audit logs; deny regions Kanem doesn't use; deny public storage; deny creating IAM users with keys; require encryption | Organisation policies (SCPs, Azure Policy deny, Google organisation policies) |
| **Detective** | Alert on public resources, unencrypted volumes, security groups open to the internet, root or break-glass use | Configuration rules, CSPM, SIEM detections |
| **Corrective** | Automatically re-apply public-access blocks; quarantine non-compliant resources | Automated remediation functions |
| **Pipeline** | IaC scanning and policy checks block insecure changes before deployment | Policy as code in CI/CD (module 8) |

Good guardrails are **few, clear and well-tested**: each one blocks something genuinely dangerous, has a documented exception process, and is checked by an automated test so a policy change can't silently remove it.

:::takeaway
Agree RTO and RPO per service, use zones (and regions only where needed), keep immutable backups in a separate account, plan recovery from attack, and test it. Apply preventive, detective, corrective and pipeline guardrails centrally, keep them few and clear, and test them like code.
:::
