:::scenario Kanem Pay
Kanem is launching **Kanem Pay**, a mobile payments service built entirely in the cloud. The board wants it designed securely from the start, not fixed after the first audit. In this capstone you bring together everything from modules 1–9 into one secure design, with a threat model, control mapping, risk register, roadmap and validation checklist.
:::

A **landing zone** is the pre-built foundation that every new cloud workload lands in: account structure, identity, networking, logging and guardrails, set up once and reused. All three major providers describe one: AWS multi-account guidance and Control Tower, Microsoft's Cloud Adoption Framework (CAF) Azure landing zones, and Google Cloud's landing zone design.

## Why separate accounts

An account (AWS), subscription (Azure) or project (Google Cloud) is the strongest boundary most teams can use easily (module 1). Separating workloads limits the **blast radius** of a mistake or a compromise, makes permissions simpler, and makes costs clear.

## A typical structure

| Organisational unit | Accounts | Purpose |
| --- | --- | --- |
| **Security** | Log archive; security tooling | Organisation-wide audit logs kept where workload administrators can't change them (module 5); detection, CSPM and incident response tools (modules 7 and 9) |
| **Infrastructure** | Shared networking; shared services | Transit routing, DNS, egress inspection, private connectivity (module 3); CI/CD tooling |
| **Workloads, production** | One account per major application, for example Kanem Pay prod | Real customer data; tightest guardrails |
| **Workloads, non-production** | Test and staging accounts | Synthetic data only; similar guardrails |
| **Sandbox** | Developer experiments | No connection to production; spending limits; regular clean-up |
| **Suspended** | Accounts being closed | Deny-all policy |

The **management account** (or root management group or organisation node) is used only to manage the organisation, never to run workloads.

## What the landing zone provides to every account

| Capability | Example |
| --- | --- |
| Identity | Single sign-on from the corporate identity provider, permission sets or roles per job, no IAM users (module 2) |
| Logging | Organisation trail and data-access logs for sensitive storage, delivered to the log archive (module 5) |
| Guardrails | Organisation policies denying risky actions; detective rules for drift (modules 2 and 7) |
| Networking | Standard network layout from approved address ranges, attached to shared networking (module 3) |
| Security services | Threat detection and posture management enabled automatically (modules 5 and 7) |
| Vending | New accounts created through a pipeline with all of the above applied, not by hand |

:::tip Start small, but start structured
A landing zone does not have to be complete on day one. Starting with the security OU, a log archive, SSO and a few guardrails, and adding the rest as needed, is far easier than splitting a crowded single account later.
:::

:::takeaway
Build on a landing zone: an organisation structure with separate security, infrastructure, production, non-production and sandbox accounts; a management account used only for management; and identity, logging, guardrails, networking and security services applied automatically to every new account.
:::
