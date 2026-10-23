Module 2 covered how cloud permissions work. Identity **governance** is how an organisation keeps them right over time: who gets access, who approves it, how long it lasts, and how it is reviewed.

## Principles for Kanem Pay

| Principle | In practice |
| --- | --- |
| **One identity per person** | Everyone signs in through the corporate identity provider with phishing-resistant MFA; no local cloud users |
| **Access by role** | Permission sets or roles defined per job (developer, operator, auditor, incident responder), not per person |
| **Least privilege by default** | Developers have read access in production; changes go through the pipeline |
| **Just-in-time privilege** | Administrative roles are requested, approved and time-limited, for example through privileged identity management (module 2) |
| **No long-lived keys** | Workloads use instance roles, managed identities or workload identity federation; pipelines use OIDC (module 8) |
| **Separation of duties** | The person who writes a change is not the person who approves it; security tooling administrators cannot change the log archive |
| **Break-glass** | Two sealed emergency accounts with hardware keys, monitored and tested quarterly |

## The joiner, mover, leaver process

| Event | Control |
| --- | --- |
| **Joiner** | Access granted from the HR record and role, approved by the manager |
| **Mover** | Old access removed when the new is granted; no accumulation |
| **Leaver** | Identity disabled in the identity provider on the last day, which removes cloud access everywhere at once |

## Access reviews

| Review | Frequency | Who |
| --- | --- | --- |
| Privileged role assignments | Monthly | Security and system owners |
| All production access | Quarterly | System owners |
| Unused permissions and identities | Continuously, using access analysis tools (module 2) | Platform team |
| Third-party and cross-account access | Quarterly | Security |

Reviews only work if reviewers can see what each role actually allows and when it was last used, and if unused access is removed by default rather than kept "just in case".

## Measuring identity health

- Number of identities with standing administrator access (target: only break-glass).
- Long-lived access keys in existence (target: zero for people).
- Percentage of sign-ins with phishing-resistant MFA.
- Time from leaver's last day to access removal.
- Unused permissions removed per quarter.

:::takeaway
Govern identity with one SSO identity per person, roles per job, least privilege, just-in-time administration, no long-lived keys, separation of duties and tested break-glass accounts. Run joiner-mover-leaver from HR records, review access on a schedule, and track a few identity health measures.
:::
