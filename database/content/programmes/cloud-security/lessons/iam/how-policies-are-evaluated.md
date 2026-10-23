You cannot review access you cannot read. This lesson shows how each provider expresses permissions and, most importantly, how it **decides** when several rules apply. The lab's policy simulator uses the AWS model, so we start there.

## Anatomy of an AWS IAM policy

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "ReadInvoices",
      "Effect": "Allow",
      "Action": ["s3:GetObject"],
      "Resource": "arn:aws:s3:::kanem-invoices/*"
    }
  ]
}
```

| Element | Meaning |
| --- | --- |
| `Effect` | `Allow` or `Deny` |
| `Action` | API operations, as `service:Operation`; wildcards such as `s3:Get*` or `s3:*` are allowed |
| `Resource` | What the actions apply to, identified by ARN (Amazon Resource Name); wildcards allowed |
| `Condition` | Optional extra requirements, such as MFA present, a source network or a resource tag |
| `Sid` | An optional label for the statement |

`"Version": "2012-10-17"` is the policy *language* version, not a date you should update.

## How the decision is made

For a request inside one AWS account, the logic documented by AWS is, simplified:

1. **By default, everything is denied** (implicit deny).
2. If **any applicable policy explicitly denies** the request, it is denied. Nothing can override an explicit deny.
3. Otherwise, if an applicable policy **allows** it, it is allowed, provided that other policy types in play (organisation SCPs, permissions boundaries, session policies) also permit it.
4. If nothing allows it, it stays denied.

```text
                     ┌─ any explicit Deny matches? ── yes ──► DENY
request ─► evaluate ─┤
                     └─ no ─► any Allow matches? ── yes ──► ALLOW*
                                                  └─ no ──► DENY (implicit)
   * if organisation guardrails and boundaries also allow it
```

### Walkthrough

Policy attached to `invoice-app`:

```json
{ "Statement": [
  { "Effect": "Allow", "Action": "s3:*", "Resource": "arn:aws:s3:::kanem-invoices/*" },
  { "Effect": "Deny",  "Action": "s3:DeleteObject", "Resource": "arn:aws:s3:::kanem-invoices/*" }
]}
```

| Request | Matching statements | Decision |
| --- | --- | --- |
| `s3:GetObject` on `kanem-invoices/a.pdf` | Allow | **Allowed** |
| `s3:DeleteObject` on `kanem-invoices/a.pdf` | Allow **and** Deny | **Denied** (explicit deny wins) |
| `s3:GetObject` on `kanem-payroll/b.csv` | none | **Denied** (implicit) |
| `s3:PutBucketPolicy` on the bucket `arn:aws:s3:::kanem-invoices` | none: the Allow's resource `kanem-invoices/*` covers objects, not the bucket itself | **Denied** (implicit) |

The last row is a classic: `arn:aws:s3:::kanem-invoices/*` means *objects in* the bucket. Bucket-level actions use the bucket ARN `arn:aws:s3:::kanem-invoices`.

## Azure RBAC and Google Cloud IAM

The other two providers use **role-based** models built from the same idea:

| | Azure RBAC | Google Cloud IAM |
| --- | --- | --- |
| Building block | **Role assignment** = security principal + role definition + scope | **Allow policy binding** = principals + role, attached to a resource |
| Where it applies | Scope: management group → subscription → resource group → resource; assignments are inherited downwards | Resource hierarchy: organisation → folders → projects → resources; bindings are inherited downwards |
| Built-in broad roles | Owner, Contributor, Reader, User Access Administrator | Basic roles Owner, Editor, Viewer (far broader than most jobs need) |
| Explicit deny | Deny assignments take precedence over role assignments; they are created by Azure features (for example deployment stacks), not written directly like roles | Deny policies are checked before allow policies |

Both are **additive**: a principal's effective access is the union of everything granted to it, its groups and the scopes above. That makes *inheritance* the thing to check: a Contributor role assigned on the subscription quietly applies to every resource group in it.

:::tip Reading access in practice
Each provider has tools that answer "who can do what" better than reading every policy by hand: AWS IAM policy simulator and IAM Access Analyzer, Azure "Check access" and access reviews, Google Cloud Policy Analyzer and Policy Troubleshooter. Use them, but understand the model first so you can tell when the answer looks wrong.
:::

:::takeaway
Read every policy as Effect + Action + Resource (+ Condition). In AWS, explicit deny beats allow and anything not allowed is denied. In Azure and Google Cloud, roles are granted at a scope and inherited downwards, so always check what sits above the resource.
:::
