## Identity is the new perimeter

In the cloud, almost every action is an API call authorised by an identity. Controlling identities is therefore the most important cloud security control.

## Building blocks (cloud-neutral)

- **Principals**: human users, groups, and workload identities (roles, managed identities, service accounts).
- **Policies**: documents that allow or deny **actions** on **resources**, sometimes under **conditions**.
- **Federation**: users sign in through a central identity provider (single sign-on) rather than having separate cloud passwords.
- **Temporary credentials**: short-lived tokens issued when a role is assumed, preferred over long-lived access keys.

## Reading a policy (AWS example, provider-specific)

```json
{
  "Effect": "Allow",
  "Action": "s3:GetObject",
  "Resource": "arn:aws:s3:::kanem-invoices/*"
}
```

This allows reading objects in one bucket, and nothing else. Compare it with `"Action": "s3:*", "Resource": "*"`, which allows every storage action on every bucket, including deleting data and changing bucket policies.

## Least privilege in practice

1. Start from what the identity **needs**, not from a broad managed policy.
2. Avoid wildcards in actions and resources unless justified.
3. Scope by resource and, where supported, by conditions (source network, required MFA, tags).
4. Review unused permissions using provider tools that report last-used access.
5. Separate duties: the identity that deploys code should not also manage audit logging.

## Privilege-escalation paths

Some permissions let an identity gain more privileges than it was given, for example the ability to attach policies to itself, create access keys for other users, or pass a powerful role to a service it launches (`iam:PassRole` in AWS). Treat these as highly privileged.

## Protecting privileged access

- Require **MFA** for all human users, and phishing-resistant MFA for administrators.
- Avoid using the root or global-administrator account day to day; protect it with hardware MFA and alerts.
- Prefer just-in-time elevation for administrative tasks.
- Eliminate long-lived access keys where possible; rotate and monitor those that remain.
