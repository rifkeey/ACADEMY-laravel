An intruder who expects to lose a stolen key will try to keep access another way, and to gain more permissions than the key had. In cloud environments both show up as **identity and policy changes** in the audit log. This lesson covers what to look for and how to remove it, not how it is done.

## Changes to look for

| Category | Audit log events (names vary by provider) | ATT&CK |
| --- | --- | --- |
| **New identities** | Create user, create service account, create role | T1136.003 Create Cloud Account |
| **New credentials** | Create access key, create service account key, add a client secret or certificate to an app registration | T1098 Account Manipulation |
| **New trust** | Change a role's trust policy to allow another account, add a federated identity provider | T1098 |
| **More permissions** | Attach an administrator policy, add a role assignment at subscription or organisation level, add a member to a privileged group | T1098, T1548.005 |
| **Weakened controls** | Stop or delete audit trails, change log destinations, disable detection services | T1685.002 Disable or Modify Cloud Log (formerly T1562.008) |
| **Data opened up** | Storage policy made public, snapshots or images shared with another account | T1530 (data from cloud storage) |

## Hunting for them

Ask focused questions of the audit log for the incident window, and then for the weeks before it:

| Question | Approach |
| --- | --- |
| Which identities, keys and roles were created, and by whom? | Filter on create events; compare creators with the identities in scope |
| Did anything gain administrator-level permissions? | Filter on policy attachments and role assignments; look for broad policies |
| Did any trust policy or identity provider change? | These are rare in normal operations, so every one deserves review |
| Was any logging or detection changed? | Any change outside the platform team's pipeline is suspicious (module 7 drift alerts) |
| Was anything shared outside the organisation? | Storage policies, snapshot permissions, resource shares |

## Removing persistence

1. **Inventory** every change found, with time, identity and source IP.
2. **Block** attacker-created identities with a deny-all policy straight away.
3. **Preserve**: note their configuration (policies, keys, trust) in the case record.
4. **Remove** them, revert policy and trust changes, and remove external shares, all together once the scope is clear.
5. **Prevent recurrence**: organisation policies that stop anyone outside the pipeline from creating users or disabling logs (modules 2 and 7), alerts on every identity creation in production.
6. **Verify**: re-run the hunt after removal; nothing should remain.

:::takeaway
Persistence and privilege gain appear as identity, credential, trust, permission and logging changes in the audit log. Hunt for them in and before the incident window, block attacker-created identities at once, remove everything together once scoped, and add guardrails and alerts so the same changes cannot happen silently again.
:::
