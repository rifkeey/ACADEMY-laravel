## What "cloud" means precisely

NIST SP 800-145 defines cloud computing by five characteristics: on-demand self-service, broad network access, resource pooling, rapid elasticity and measured service. It describes three service models and four deployment models.

| Service model | You manage | Provider manages | Examples |
| --- | --- | --- | --- |
| IaaS | OS, runtime, applications, data, identities | Physical hardware, hypervisor, data centre | Virtual machines, block storage |
| PaaS | Applications, data, identities, configuration | OS and runtime as well | Managed databases, app platforms |
| SaaS | Data, users and configuration | Almost everything else | Email, CRM, collaboration suites |

Deployment models: public, private, community and hybrid cloud.

## Shared responsibility

Every major provider publishes a **shared responsibility model**. The provider secures the cloud itself; the customer secures what they put *in* it. The split shifts with the service model, but some responsibilities **always** remain with the customer:

- **Identities and access**: who can do what.
- **Data**: classification, access policies and, often, encryption choices.
- **Configuration**: of every service you enable.

Most cloud breaches are not failures of the provider's infrastructure. They are customer-side misconfigurations, weak identity controls or leaked credentials.

## Common cloud security risks

1. Over-privileged identities and long-lived access keys.
2. Publicly exposed storage or databases.
3. Missing or disabled audit logging.
4. Secrets embedded in code or images.
5. Unpatched internet-facing workloads.
6. Insecure management access, such as SSH open to the whole internet.
7. Lack of an inventory: resources nobody knows they own.

## Cloud-neutral first

This programme teaches principles first, then shows labelled provider-specific examples. Names differ (AWS IAM roles, Azure managed identities, Google Cloud service accounts) but the security questions are the same: who is this identity, what can it do, and how would we know if it were misused?
