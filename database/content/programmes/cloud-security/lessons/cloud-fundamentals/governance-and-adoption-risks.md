Self-service is the cloud's great strength: a developer can create a database in a minute. It is also its biggest governance risk: so can anyone else with the right permissions, without asking security, finance or legal. **Cloud governance** is how an organisation keeps that speed while staying in control.

## The risks of moving fast

| Risk | What it looks like | Example at Kanem |
| --- | --- | --- |
| **Shadow IT** | Teams open their own cloud accounts with company data | Marketing stores customer lists in a personal SaaS account |
| **Misconfiguration** | Insecure settings created in a hurry | A storage bucket made public "just for testing" |
| **Identity sprawl** | Users, keys and roles nobody owns | The contractor admin account from module 2 |
| **No visibility** | No central logs, no asset inventory | Nobody can say how many VMs exist |
| **Data location** | Data stored in an unapproved region | Customer data copied to a region outside approved jurisdictions |
| **Skills gap** | Teams apply on-premises habits to cloud services | Opening "any → any" in a security group to make something work |
| **Cost surprises** | Uncontrolled spending, or attackers using stolen credentials for mining | A five-figure bill from GPU instances nobody launched |
| **Lock-in and exit** | Hard to move away from a provider | No tested plan to retrieve data if the contract ends |

## Governance in practice: decide, then enforce

Good governance has two halves.

**1. Decide and write down** (people and process):
- Who may create accounts, subscriptions and projects, and who owns each one.
- Approved regions and services, and the data classifications allowed in each.
- Minimum security baselines (MFA, logging on, no public storage without approval).
- How exceptions are requested, approved and reviewed.

**2. Enforce automatically** (guardrails): cloud platforms can enforce rules **above** the accounts, so even an account administrator cannot break them:

| Guardrail type | AWS | Azure | Google Cloud |
| --- | --- | --- | --- |
| Prevent actions organisation-wide | Service control policies (SCPs) | Azure Policy (deny effect) at management group level | Organisation policy constraints |
| Detect drift and misconfiguration | AWS Config rules, Security Hub | Azure Policy (audit), Microsoft Defender for Cloud | Security Command Center |
| Central audit logs | Organisation CloudTrail to a log-archive account | Activity logs and diagnostic settings to a central workspace | Organisation-level Cloud Audit Logs to a central project |

Typical first guardrails: block unapproved regions, block disabling audit logging, require encryption, block public access to storage unless an exception exists. Module 7 (posture management) and the capstone build these out.

## Frameworks that help

- **NIST CSF 2.0** added a **Govern** function: organisational context, risk strategy, roles, policy and oversight. It applies directly to deciding how cloud is adopted.
- The **CSA Cloud Controls Matrix (CCM)** maps cloud-specific control objectives across domains such as governance, identity, logging and data security, and is commonly used to assess providers and your own environment.
- Provider frameworks (AWS Well-Architected, Microsoft Cloud Adoption Framework, Google Cloud Architecture Framework) turn these into concrete designs.

:::tip Start small, but start before the first workload
A one-page cloud policy, an account structure, central logging and three or four preventive guardrails in place **before** the first production workload is worth more than a 50-page standard written after an incident.
:::

:::takeaway
Cloud governance is deciding who may do what, where and with which data, then enforcing the most important rules automatically with guardrails above the accounts. Do it before the first production workload, not after the first incident.
:::
