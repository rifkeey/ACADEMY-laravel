Everyone agrees with least privilege. The difficulty is doing it without breaking the application or drowning in requests. This lesson gives you a repeatable method.

## Step 1: write down what the identity actually does

Start from the workload or job, not from a policy you found online:

| Need (invoice-app) | Service operation | Resource |
| --- | --- | --- |
| Read invoice PDFs | `s3:GetObject` | objects in `kanem-invoices` |
| Store newly generated invoices | `s3:PutObject` | objects under `kanem-invoices/generated/` |
| Decrypt the invoices | `kms:Decrypt` | the invoice KMS key only |
| Write application logs | `logs:CreateLogStream`, `logs:PutLogEvents` | log group `/kanem/invoice-app` |

Four rows, four statements. Anything else the identity can do is excess.

## Step 2: avoid the three wildcard traps

| Pattern | Why it is dangerous |
| --- | --- |
| `"Action": "*"` or a service wildcard like `"s3:*"` | Includes destructive and administrative operations (delete, change bucket policy, disable encryption) |
| `"Resource": "*"` | Applies to every resource of that type in the account, including ones created next year |
| Broad built-in roles (AWS `AdministratorAccess`, Azure Owner/Contributor at subscription scope, Google basic Editor role) | Convenient for a pilot, far beyond what a job needs |

Wildcards are not always wrong. `ec2:Describe*` on `*` (read-only inventory) is a common, low-risk pattern, and some actions do not support resource-level scoping at all. The point is to *choose* them, document why, and keep them away from write and administrative actions.

## Step 3: scope with conditions and tags where you can

Conditions narrow a grant further, for example:

- only when the request comes from Kanem's network or VPC endpoint;
- only when MFA was used (for humans);
- only for resources tagged `project = invoices` (**attribute-based access control**, ABAC).

ABAC scales well (new resources inherit access through their tags) but depends on tags being correct and protected: if users can change tags, they can change their access.

:::note RBAC and ABAC
**Role-based access control (RBAC)** grants permissions through roles tied to job functions ("Invoice operators"). **Attribute-based access control (ABAC)** decides using attributes of the user, resource and request (department, tag, time). NIST SP 800-210 discusses access control models for cloud systems. Most organisations use RBAC as the foundation and add ABAC conditions where they reduce the number of roles.
:::

## Step 4: use evidence to remove what is unused

Provider tools show which permissions are actually used:

- AWS: last-accessed information in IAM, and IAM Access Analyzer (unused access findings, external access findings, and generating a policy from CloudTrail activity).
- Azure: access reviews and sign-in/activity logs in Entra ID.
- Google Cloud: role recommendations from the IAM recommender based on observed usage.

A good routine: grant the documented needs, monitor for a period, remove what was never used.

## Step 5: separate duties and review regularly

- The identity that **deploys** an application should not be able to **disable its audit logs** or **change its own permissions**.
- Production and development should be separate accounts / subscriptions / projects, so a developer's broad access in development does not reach production data.
- Review privileged and human access on a schedule (quarterly is common for privileged roles), with the access owner confirming each grant.

:::reveal Worked example: least-privilege policy for the four needs above
```json
{
  "Version": "2012-10-17",
  "Statement": [
    { "Sid": "ReadInvoices", "Effect": "Allow", "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::kanem-invoices/*" },
    { "Sid": "WriteGenerated", "Effect": "Allow", "Action": "s3:PutObject",
      "Resource": "arn:aws:s3:::kanem-invoices/generated/*" },
    { "Sid": "DecryptInvoices", "Effect": "Allow", "Action": "kms:Decrypt",
      "Resource": "arn:aws:kms:eu-west-1:111122223333:key/0000aaaa-11bb-22cc-33dd-444455556666" },
    { "Sid": "WriteLogs", "Effect": "Allow",
      "Action": ["logs:CreateLogStream", "logs:PutLogEvents"],
      "Resource": "arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/invoice-app:*" }
  ]
}
```
The lab asks you to write a policy like this one for a slightly different set of needs, then tests it against requests you cannot see.
:::

:::takeaway
Least privilege is a method: list the needs, grant exactly those actions on exactly those resources, narrow with conditions, remove what evidence shows is unused, and separate duties so no single identity can both act and hide the action.
:::
