Some permissions are dangerous not because of what they do directly, but because they let an identity **give itself more**. An attacker who compromises a modest identity will look for exactly these. This lesson teaches you to spot them and to protect the access that really is privileged.

## Privilege escalation: permissions about permissions

| Pattern | AWS example | Azure example | Google Cloud example |
| --- | --- | --- | --- |
| Change your own (or anyone's) permissions | `iam:AttachUserPolicy`, `iam:PutRolePolicy`, `iam:CreatePolicyVersion` | `Microsoft.Authorization/roleAssignments/write` (held by Owner and User Access Administrator) | `setIamPolicy` on a project or resource |
| Act as a more powerful identity | `sts:AssumeRole` on an admin role | Using credentials of a more privileged service principal | `iam.serviceAccounts.actAs` / creating access tokens for a privileged service account |
| Hand a powerful identity to something you control | `iam:PassRole` on `*` plus permission to launch EC2 or Lambda | Assigning a privileged managed identity to a VM you can log in to | Attaching a privileged service account to a VM or function you deploy |
| Create new credentials for someone else | `iam:CreateAccessKey`, `iam:CreateLoginProfile` | Adding a secret to another application's registration | Creating keys for another service account |

### Walkthrough: why `iam:PassRole` on `*` matters

The invoice application's identity has these two permissions:

```json
{ "Effect": "Allow", "Action": "iam:PassRole", "Resource": "*" }
{ "Effect": "Allow", "Action": "ec2:*", "Resource": "*" }
```

On their own they look like "can launch servers". Together, an attacker who controls the application can:

1. Launch a new EC2 instance (`ec2:RunInstances`)…
2. …and **pass it any role in the account**, including an administrator role (`iam:PassRole` on `*`)…
3. …then log in to that instance and use the administrator role's credentials.

The application never had administrator rights, but it could *create* something that did. This is why `iam:PassRole` should always be limited to the specific roles a workload legitimately passes. MITRE ATT&CK tracks these behaviours, for example T1098 (Account Manipulation) and T1548.005 (Abuse Elevation Control Mechanism: Temporary Elevated Cloud Access).

## Protecting privileged access

| Practice | What it means |
| --- | --- |
| **Few standing administrators** | Most people have no admin rights day to day |
| **Just-in-time (JIT) elevation** | Admin rights are activated for a limited time, with a reason and sometimes approval: for example Microsoft Entra Privileged Identity Management (a licensed feature), or time-bound role assumption in AWS |
| **Phishing-resistant MFA** | Required for every privileged role |
| **Break-glass accounts** | Two emergency accounts with long, stored-offline credentials, excluded from normal controls that could lock everyone out, and **monitored**: any sign-in raises an alert |
| **Guardrails above accounts** | Organisation-level policies (AWS SCPs, Azure Policy, Google Cloud organisation policies) that even account administrators cannot override, for example "nobody may disable audit logging" |
| **Alert on privilege changes** | Policy attachments, new role assignments, new access keys and role-trust changes are high-value audit events (module 5) |

:::warning The contractor admin account
Kanem's `contractor-admin` from lesson 1 is a standing Owner/administrator with no end date. Even if the contractor is trustworthy, the account is a target: its password or session could be stolen. The fix is a named account with JIT elevation for specific tasks, an expiry date, and an access review when the contract ends.
:::

:::reveal Advanced challenge: find the escalation path
An identity has: `iam:CreatePolicyVersion` on `arn:aws:iam::111122223333:policy/invoice-app-policy` and `s3:GetObject` on `kanem-invoices/*`. The policy `invoice-app-policy` is attached to this same identity. How can it escalate?

*Answer:* `iam:CreatePolicyVersion` lets it publish a new version of its own policy (and set it as default) containing `"Action": "*", "Resource": "*"`. Any permission that lets an identity edit a policy attached to itself is equivalent to full control.
:::

:::takeaway
Treat permissions that create identities, change permissions, pass roles or impersonate others as administrative, whatever the role is called. Keep standing admin access rare, elevate just in time, protect break-glass accounts, and alert on every privilege change.
:::
