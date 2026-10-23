Logs that sit in the same account as the workload can be changed or deleted by anyone who controls that account, including an attacker. Centralising and protecting logs is what makes them trustworthy evidence.

## A log archive outside the workload accounts

The common design (part of most landing-zone guidance) is a **dedicated logging account, subscription or project**:

```text
invoice-prod ──┐
invoice-dev  ──┼── audit, sign-in, flow and resource logs ──► log-archive (separate account)
shared-tools ──┘                                               │  write-only for sources
                                                               │  read for the security team
                                                               └─► SIEM
```

- Workload administrators can **send** logs but cannot **read, change or delete** the archive.
- The security team can read; very few people can administer it.
- Organisation-level guardrails stop anyone switching off logging in the workload accounts (module 1, lesson 4).

## Retention

Keep logs long enough to investigate incidents that are discovered late (often weeks or months after they start) and to meet legal, regulatory and contractual requirements. Agree the periods with legal and compliance, then use **storage tiers and lifecycle rules** to keep older logs cheaply rather than deleting them early.

## Integrity

| Measure | Purpose |
| --- | --- |
| **Immutable storage** (write-once retention) for the archive | Logs cannot be altered or deleted during the retention period |
| **Integrity validation** (for example, CloudTrail log file integrity validation) | Proves log files have not been modified since delivery |
| **Restricted access and alerts** on the archive | Any attempt to change logging configuration or read the archive unusually is itself an event |
| **Synchronised time** (UTC everywhere) | Events from different sources can be put in the right order |

## Cost and noise

Logging everything at full detail forever is expensive. Decide deliberately:

- **Always**: management events, identity events, logging-configuration changes, key usage for restricted keys.
- **For sensitive resources**: data access, flow logs.
- **Sampled or short retention**: very high-volume, low-value logs.

Record these decisions; an auditor or investigator will ask why a source was not kept.

:::takeaway
Send all logs to a separate, locked-down archive that workload administrators cannot alter, keep them as long as investigations and regulations need, protect their integrity with immutable storage and validation, and decide deliberately what to keep at what cost.
:::
