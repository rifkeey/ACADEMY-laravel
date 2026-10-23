Most cloud incidents begin with a valid credential in the wrong hands: an access key committed to a repository, a password reused from another breach, a session token copied from a compromised laptop. The attacker then uses ordinary, legitimate API calls. MITRE ATT&CK's cloud matrix lists Valid Accounts: Cloud Accounts (T1078.004) among the most common initial access techniques.

## Signs that an identity is compromised

| Signal | Example |
| --- | --- |
| New source | Calls from an IP address or network never seen for this identity |
| New credential type | A developer who normally signs in through single sign-on suddenly uses a long-lived access key |
| Unusual time or region | Activity at 02:00, or in a region Kanem never uses |
| "Who am I?" and listing calls | Identity checks and listing users, roles and buckets, typical of someone learning the environment |
| Bursts of access-denied errors | Someone testing what the identity is allowed to do |
| Identity and policy changes | New users, keys or roles (next lesson) |
| Logging changes | Trails stopped or deleted |

Managed detection services (module 5) raise many of these automatically, but you still need to confirm them in the audit log.

## Scoping: what did this identity do?

1. List **every action** by the identity in the window, from **every** source IP, including failures.
2. Separate the **suspicious source** from the user's normal activity (Kanem's NAT addresses, the SSO session).
3. Find **new identities or credentials** it created, and scope those too.
4. Check **data access** logs for reads and downloads (lesson 4).
5. Find the **source of the leak**: repository, pipeline, laptop or phishing (module 8 explains how keys leak through pipelines).

## Containment for a leaked credential

Contain completely and quickly, but preserve evidence first where it costs little:

| Step | Why |
| --- | --- |
| Export or confirm the audit log for the period | Containment changes will add events; you want the clean record |
| **Deactivate** (don't delete yet) the leaked key and any keys the attacker created | Stops use immediately, keeps the record |
| **Revoke active sessions** for the identity and any role it assumed | Temporary credentials remain valid until revoked or expired |
| Attach a **deny-all** policy to attacker-created identities | Stops them while you investigate |
| **Restore logging** and protect it with an organisation policy | Visibility first |
| Reset the user's password and MFA if the account itself may be affected | Covers more than the key |
| Rotate any secrets the identity could read | Assume they were copied |

:::warning Don't tip your hand too early, but don't wait either
In a small incident, contain at once. In a larger one, the incident lead may scope first so that every attacker foothold is removed at the same moment; removing one key while the attacker holds another just warns them. Make that decision deliberately and quickly.
:::

:::takeaway
Treat new sources, new credential types, listing calls, access-denied bursts and logging changes as signs of compromise. Scope everything the identity did and created, then contain completely: deactivate keys, revoke sessions, block attacker-created identities, restore logging and rotate secrets.
:::
