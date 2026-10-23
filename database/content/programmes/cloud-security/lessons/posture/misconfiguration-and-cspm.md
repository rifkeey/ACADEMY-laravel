:::scenario 1,240 findings
Kanem has switched on its cloud providers' posture-management services across all accounts. The first report lists **1,240 findings**. The CISO asks: "Which of these actually matter, and how do we stop new ones appearing faster than we fix the old ones?" This module answers both questions.
:::

Most cloud security problems are **configuration** problems: a setting that should have been on, a permission that should have been narrower, a resource that should not have been public. Because the cloud changes constantly, configuration has to be checked **continuously**, not once a year.

## Common misconfiguration categories

Each module so far has shown one category. Together they are what posture tools look for:

| Category | Examples | Module |
| --- | --- | --- |
| Identity | Unused credentials, broad roles, no MFA on privileged users | 2 |
| Network exposure | Resources reachable from the internet that should be private | 3 |
| Data | Public storage, missing encryption settings, no backups | 4 |
| Logging | Audit logging off or not centralised, short retention | 5 |
| Workloads | Outdated images, unpatched VMs, privileged containers | 6 |

## Cloud security posture management (CSPM)

**CSPM** tools inventory resources across accounts and compare their configuration with security rules, producing findings. Each provider offers one:

| Provider | Service |
| --- | --- |
| AWS | AWS Security Hub (with AWS Config rules) |
| Microsoft | Microsoft Defender for Cloud |
| Google Cloud | Security Command Center |

Third-party tools cover several clouds in one view. Whatever the tool, findings are typically mapped to **benchmarks and standards** (for example CIS Benchmarks, provider security benchmarks, or compliance frameworks), so you can report posture against a recognised baseline.

## What a finding contains

| Field | Example |
| --- | --- |
| Resource | `kanem-loan-docs` storage bucket, account loans-prod |
| Rule | Storage should block public access |
| Severity | High |
| Standard mapping | CIS benchmark control; Kanem baseline DP-01 |
| Status | New / in progress / resolved / accepted risk |
| Owner | From the resource's `owner` tag |

The **owner** field is the most important for getting things fixed, which is why tagging (module 1) matters so much.

## The first report is always overwhelming

A first scan lists years of accumulated drift. Don't try to fix everything at once:

1. **Confirm coverage**: all accounts and regions are in scope.
2. **Sort by real risk** (lesson 5), not by count.
3. **Fix the top risks**, then **prevent recurrence** with guardrails (lesson 2) so the same findings don't come back.
4. **Accept or exempt** consciously what you won't fix, with an owner and review date.

:::takeaway
Cloud security is largely configuration security, and configuration must be checked continuously. CSPM tools inventory resources, compare them with benchmark-mapped rules and raise findings with owners. Start by confirming coverage, then fix by risk and prevent recurrence.
:::
