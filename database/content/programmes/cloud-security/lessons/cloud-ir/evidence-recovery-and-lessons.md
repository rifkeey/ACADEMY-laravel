Cloud resources can be created, changed and deleted in seconds, which makes evidence fragile and recovery fast. Both need planning.

## Preserving evidence

| Evidence | How to preserve it |
| --- | --- |
| Audit, data-access and sign-in logs | Confirm they are in the protected log account; export the incident window; record hashes |
| A suspicious virtual machine | **Don't terminate it.** Isolate it (quarantine security group, remove its role), take **disk snapshots**, capture memory if your tools allow, tag it as evidence |
| A container or function | Keep the image and version; export its logs; record its configuration and permissions |
| Identity configuration | Export policies, keys (metadata only), trust relationships and group memberships of everything in scope |
| Storage | Record policies and object versions; enable object lock or retention if deletion is a risk |

Copy evidence into a separate **forensics account** that the affected account cannot reach, and keep a chain-of-custody record (who, what, when, hash), as in Network module 8.

## Recovering

| Principle | Practice |
| --- | --- |
| **Rebuild, don't repair** | Redeploy workloads from known-good infrastructure-as-code and images (modules 6–8), rather than cleaning a compromised server |
| **Restore data from trusted backups** | Use immutable or cross-account backups (module 4), checked to be from before the incident |
| **Rotate everything in scope** | Keys, passwords, secrets, certificates the identities could reach |
| **Close the way in** | Fix the root cause: remove long-lived keys, enforce MFA, fix the pipeline |
| **Watch closely** | Extra alerting on the affected identities and resources for weeks afterwards |

## Threat hunting

Threat hunting is proactive: you start from a hypothesis rather than an alert. Good hypotheses come from incidents like this one:

- "Other long-lived access keys may have leaked the same way" → list all keys older than 90 days and their last use.
- "The intruder may have tested other accounts" → search all accounts for calls from the incident's source addresses.
- "Logging may have been changed elsewhere without anyone noticing" → list all logging configuration changes in the last six months.

## Lessons learned

Within two weeks, hold a **blameless** review: what happened, what went well, what didn't, and which actions will stop it happening again. Each action needs an owner and a date. Track metrics such as time to detect, time to contain and time to recover.

:::takeaway
Preserve before you change: protected logs, snapshots of isolated machines, exported identity configuration, all copied to a forensics account with a chain of custody. Recover by rebuilding from known-good code and backups, rotating everything in scope and fixing the root cause. Turn each incident into hunting hypotheses and owned improvement actions.
:::
