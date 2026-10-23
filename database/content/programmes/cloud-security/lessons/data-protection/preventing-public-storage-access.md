Accidental public access to cloud storage is one of the most frequently reported causes of cloud data exposure. The good news: all major providers now offer strong preventive settings, and most of the work is switching them on and keeping them on.

## How storage access is decided (high level)

Access to an object depends on several layers that must all agree:

| Layer | Examples |
| --- | --- |
| Identity permissions | IAM policies, role assignments (module 2) |
| Resource policies | Bucket policies, container access levels |
| Preventive guardrails | Account- or organisation-level "block public access" settings and policies |
| Network restrictions | Private endpoints, "public network access disabled" (module 3) |
| Encryption keys | Permission to use a customer-managed key (lesson 2) |

Exposure happens when a **resource policy** grants access to everyone and **no guardrail** stops it.

## Preventive settings to switch on

| Provider | Setting | Effect |
| --- | --- | --- |
| AWS | **S3 Block Public Access** at the account level (and bucket level) | Overrides policies and ACLs that would grant public access |
| Azure | **Disallow anonymous (public) blob access** on storage accounts | Containers cannot be made publicly readable |
| Google Cloud | **Public access prevention** on buckets, enforceable by organisation policy | Prevents granting access to all users |

Then **enforce them from above**: organisation-level policies (AWS SCPs, Azure Policy, Google organisation policy constraints) so that teams cannot switch them off without an approved exception.

## When data must be shared

Legitimate sharing has safer patterns than a public bucket:

- **Time-limited signed links** (pre-signed URLs, shared access signatures, signed URLs) for a specific object, expiring quickly, and logged.
- **A content delivery service in front of a private bucket** for genuinely public website content.
- **Separate public and private stores**, so a public website bucket never holds customer documents.

## Monitor and respond

- **Posture checks** (module 7) continuously look for any storage that is publicly accessible and raise findings.
- **Data-access logging** for confidential and restricted stores records who read what; ATT&CK lists *Data from Cloud Storage* (T1530) as an adversary technique, and these logs are how you would detect or scope it.
- If exposure is found: **block public access first**, **preserve the logs**, establish **what was accessed and by whom**, and involve the **data protection officer and legal** early, because notification deadlines may apply.

:::takeaway
Turn on each provider's public-access prevention, enforce it with organisation policies, share data through time-limited signed links or delivery services instead of public buckets, and log access to sensitive stores so any exposure can be detected and scoped.
:::
