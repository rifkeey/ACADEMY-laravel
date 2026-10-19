## Two kinds of cloud logs

- **Control-plane (management) logs** record API calls that create, change or delete resources and identities: who did what, from where, and whether it succeeded. Examples: AWS CloudTrail, Azure Activity Log, Google Cloud Audit Logs (Admin Activity).
- **Data-plane logs** record access to the data or service itself, such as reads from a storage bucket or queries to a database. They are often **off by default** and may cost extra.

A typical audit record contains the time, the identity (and how it authenticated), the source IP, the API action, the target resource, the region and the result.

## Centralise

Send logs from every account, subscription or project to a **central, separate logging account** that workload teams cannot modify. Protect it with immutability or object-lock features where available. Attackers commonly try to disable logging; centralisation and alerts on logging changes defeat that.

## Detections worth having early

- Audit logging stopped, deleted or modified.
- Root or global-administrator use.
- New access keys created for existing users.
- Policies changed to grant public or cross-account access.
- Activity in regions you never use.
- Sign-ins without MFA for privileged users.
- Bursts of `AccessDenied` from one identity, often an attacker exploring what stolen credentials allow.

These can feed a SIEM such as Microsoft Sentinel, where KQL queries like those in the SOC programme apply directly.

## Retention and evidence

Decide retention from legal, regulatory and investigative needs. Many intrusions are discovered months after they begin, so keep control-plane logs long enough to investigate them (commonly a year or more in cheaper archive storage). Record the integrity protections you use, so evidence can be relied on later.
