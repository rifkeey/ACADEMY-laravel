Before you can secure a cloud environment you need to know where things physically run and how they are organised. Both decisions are hard to change later, and both have direct security consequences.

## Where things run: regions and zones

| Concept | AWS | Azure | Google Cloud |
| --- | --- | --- | --- |
| Geographic area with its own data centres | Region (e.g. `eu-west-1`) | Region | Region (e.g. `europe-west1`) |
| Isolated location inside a region (separate power, cooling, network) | Availability Zone | Availability zone (in regions that support zones) | Zone |

Spreading a workload across **zones** protects it from a single data-centre failure. Spreading across **regions** protects against a regional outage, but costs more and raises data-location questions.

**Data location is a legal question as well as a technical one.** Personal data of Kanem's customers is subject to Nigerian data-protection law (the Nigeria Data Protection Act 2023, supervised by the Nigeria Data Protection Commission) and to Central Bank requirements, which affect where data may be stored and how it may be transferred abroad. Choosing a region is therefore a decision for security, legal and compliance together, not just for the engineer creating the resource. Organisation policies (lesson 4) can then *enforce* the approved regions.

## How things are organised: the resource hierarchy

Every provider lets you nest containers for resources. Permissions and policies attached high in the hierarchy are **inherited** below.

```text
AWS                          Azure                           Google Cloud
Organization                 Microsoft Entra tenant          Organization
 └─ Organizational units      └─ Management groups            └─ Folders
     └─ Accounts                  └─ Subscriptions                └─ Projects
         └─ Resources                 └─ Resource groups              └─ Resources
                                          └─ Resources
```

The unit that matters most for security is the **account / subscription / project**:

- It is a strong **isolation boundary**: identities, networks and resources in one do not see another by default.
- It defines the **blast radius**: an attacker or a mistake in the development account should not reach production data.
- It is where **billing, quotas and many guardrails** are applied.

Provider guidance (for example AWS's *Organizing Your AWS Environment Using Multiple Accounts* and Microsoft's Cloud Adoption Framework) recommends separating workloads and environments into different accounts or subscriptions rather than putting everything in one.

## Walkthrough: Kanem's first design

| Option | Layout | Problem / benefit |
| --- | --- | --- |
| A | One account: dev, test and production invoice app, plus the reporting VM, all together | A developer with broad rights in "dev" has the same rights over production. One leaked key exposes everything |
| B | Separate accounts per environment (invoice-dev, invoice-prod), a shared logging account, and a security account, grouped into organisational units | Production can have stricter guardrails; logs are kept where workload administrators cannot delete them; blast radius is limited |

Option B is more work to set up, but it is the basis of the "landing zone" designs you will build in the capstone.

:::tip Name and tag everything
Consistent names and tags (`owner`, `environment`, `data-classification`, `cost-centre`) on accounts and resources are what let you answer "who owns this public bucket?" at 2 a.m. They also feed cost reports and attribute-based access control.
:::

:::takeaway
Choose regions with legal and compliance input, use zones for resilience, and separate environments and workloads into different accounts, subscriptions or projects so that a mistake or compromise in one stays there.
:::
