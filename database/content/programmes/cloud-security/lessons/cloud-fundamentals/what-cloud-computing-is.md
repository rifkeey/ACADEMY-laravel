:::scenario The board's question
Kanem Financial Services' board has approved moving three systems to the cloud: a reporting server, the invoice application and staff email. One director asks: *"If it's in the cloud, the provider looks after security, doesn't it?"* By the end of this module you will be able to answer that precisely, for each of the three systems.
:::

"Cloud" is used loosely in sales conversations. Security work needs precision, because **who is responsible for what depends entirely on what kind of cloud service you are using**. The standard vocabulary comes from NIST SP 800-145, *The NIST Definition of Cloud Computing*.

## Five essential characteristics

NIST defines cloud computing by five characteristics. If a service lacks them, it is hosting, not cloud in this sense.

| Characteristic | Meaning | Security consequence |
| --- | --- | --- |
| **On-demand self-service** | You create resources yourself, through a console or API, without calling the provider | Anyone with the right permissions can create (or expose) resources in seconds, so identity and guardrails matter |
| **Broad network access** | Services are reached over the network from many kinds of device | Management interfaces are reachable from the internet unless you restrict them |
| **Resource pooling** | The provider serves many customers from shared infrastructure (multi-tenancy) | Isolation between tenants is the provider's job; trust depends on their controls and attestations |
| **Rapid elasticity** | Capacity scales up and down quickly | Assets appear and disappear constantly, so inventory and logging must be automatic |
| **Measured service** | Usage is metered and billed | Unexpected cost spikes are a security signal (for example, crypto-mining on stolen credentials) |

## Three service models

| Model | You get | You still manage | Kanem example |
| --- | --- | --- | --- |
| **IaaS** (Infrastructure as a Service) | Virtual machines, networks, storage | The guest operating system and everything above it | The reporting server on a cloud VM |
| **PaaS** (Platform as a Service) | A managed platform: databases, application hosting, functions | Your code, data, configuration and access | The invoice database on a managed database service |
| **SaaS** (Software as a Service) | A complete application | Your users, data, and the settings the application offers | Staff email and documents |

Many services sit between these labels. **Serverless functions** (AWS Lambda, Azure Functions, Google Cloud Run functions) are usually treated as PaaS: the provider runs everything except your code and its configuration. **Containers** on a managed Kubernetes service are a mix: the provider runs the control plane, you are responsible for the workloads, images and much of the configuration.

## Four deployment models

| Model | Who uses the infrastructure | Notes |
| --- | --- | --- |
| **Public cloud** | Many organisations, on a provider's infrastructure | AWS, Azure, Google Cloud and many others |
| **Private cloud** | One organisation (on its premises or hosted for it) | Cloud characteristics, single tenant |
| **Community cloud** | A group of organisations with shared concerns | For example, a sector-specific platform |
| **Hybrid cloud** | A combination, connected so data and applications can move | Typical for banks: some systems stay on-premises, others move |

Kanem will run a **hybrid** model: core banking stays in its Lagos data centre, connected to public-cloud workloads.

:::note SP 800-145's age
SP 800-145 was published in 2011. Its definitions are still the common vocabulary (they appear in contracts, audits and the CSA guidance), but the market has moved on: serverless, managed Kubernetes and AI services blur the service-model lines. Use the definitions to frame questions, then read each provider's own responsibility documentation for the specific service.
:::

:::takeaway
Name the service model before you discuss security. The five characteristics explain why cloud security is driven by identity, automation and configuration; the service model tells you how much of the stack is yours to secure.
:::
