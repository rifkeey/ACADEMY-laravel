:::scenario The invoice application
Kanem Financial Services is moving its invoice application to the cloud. In the first week, the project team creates eleven user accounts, three access keys and a "temporary" administrator account for a contractor. Nobody can say exactly who can do what. Your job in this module is to fix that, starting with the vocabulary every cloud uses.
:::

On-premises, the network was the main boundary: if you were inside, you were mostly trusted. In the cloud, **every action is an API call**, made by an **identity**, and checked against **policy**. Creating a server, reading a file and deleting a database all go through the same front door. That is why identity is often called the new perimeter, and why most serious cloud incidents involve stolen or over-powerful identities (ATT&CK T1078.004, Valid Accounts: Cloud Accounts).

## Who can make a request?

Every cloud has the same kinds of *principals* (things that can act), with different names:

| Kind of principal | AWS | Microsoft Azure / Entra ID | Google Cloud |
| --- | --- | --- | --- |
| A person | IAM user, or (better) a federated user via IAM Identity Center | Entra ID user | Google account or federated identity |
| A group of people | IAM group / Identity Center group | Entra ID group | Google group |
| A workload (application, VM, function) | IAM role assumed by the service | Managed identity or service principal | Service account |
| The all-powerful account | Root user of the AWS account | Global Administrator; subscription Owner | Organisation administrator; project Owner |

The table hides real differences (for example, an AWS IAM role is not a "user" at all: it is a set of permissions that someone or something temporarily *assumes*). But the questions you ask are the same in every provider.

## Authentication and authorisation

Two separate questions are answered on every request:

1. **Authentication: who are you?** Proven with a password plus MFA, a certificate, a signed token from an identity provider, or a cryptographically signed API request using access keys or short-lived credentials.
2. **Authorisation: are you allowed to do this, to this resource, right now?** Decided by evaluating policies (lesson 2).

```text
API request: "DeleteObject kanem-invoices/2026/03/inv-1042.pdf"
  1 Authentication  → signed by role invoice-app-prod, session valid until 14:32 ✔
  2 Authorisation   → policies attached to invoice-app-prod allow s3:DeleteObject? ✘ implicit deny
  Result            → AccessDenied (and an audit-log entry)
```

Mixing the two up causes real mistakes. MFA strengthens **authentication**; it does nothing if the authenticated user is **authorised** to do far too much.

## Human access: federate, don't duplicate

Good practice in all three providers is the same:

- **Use one identity provider** (for Kanem, its corporate directory) and **single sign-on** into the cloud, instead of separate cloud passwords for each person. Leavers then lose cloud access when their corporate account is disabled.
- **Require MFA for everyone**, and phishing-resistant MFA (security keys, platform authenticators) for administrators.
- **Grant access to groups, not individuals**, so access follows the job role.
- **Lock away the all-powerful account**: the AWS root user, Global Administrator and organisation administrator should be used only for the few tasks that need them, protected with strong MFA and alerting on every sign-in.

:::warning Long-lived access keys
AWS access keys, Azure client secrets and Google service-account keys are passwords that never expire unless you rotate them, and they often end up in code repositories, laptops and CI logs. Provider guidance (for example AWS's IAM security best practices) is to prefer temporary credentials (lesson 4) and to remove keys that are not needed.
:::

## Walkthrough: classify the invoice project's identities

| Identity | What it is | Concern |
| --- | --- | --- |
| `adebayo.o`, `chika.n` … (8 users) | Developers with cloud passwords | Should sign in via SSO; check MFA |
| `ci-deployer` with an access key | Pipeline identity | Long-lived key stored in the CI tool; replace with federation |
| `invoice-app` access key in a config file | Workload | Should be a role / managed identity / service account with no stored key |
| `contractor-admin` | Human, administrator | Temporary admin with no end date; needs just-in-time access and an expiry |

:::takeaway
In the cloud every action is an authenticated API call checked against policy. Keep the two questions separate: authentication proves who is asking, authorisation decides what they may do. Federate people, use groups, and avoid long-lived keys.
:::
