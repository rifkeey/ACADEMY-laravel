The single most important idea in cloud security: **the provider secures the cloud; you secure what you put in it and how you configure it.** Every major provider publishes its own version of this shared responsibility model. The split moves with the service model.

## Who does what

| Responsibility | On-premises | IaaS | PaaS | SaaS |
| --- | --- | --- | --- | --- |
| Physical data centre, hardware | You | Provider | Provider | Provider |
| Host and hypervisor | You | Provider | Provider | Provider |
| Network infrastructure | You | Provider (you configure virtual networks and rules) | Provider (you configure access) | Provider |
| Guest operating system | You | **You** | Provider | Provider |
| Runtime, middleware, database engine | You | **You** | Provider (you choose versions and settings) | Provider |
| Application code | You | **You** | **You** | Provider |
| Configuration of the service | You | **You** | **You** | **You** (the settings the app exposes) |
| Identities and access | You | **You** | **You** | **You** |
| Data: classification, protection, retention | You | **You** | **You** | **You** |
| Devices used to access the service | You | **You** | **You** | **You** |

Read the bottom rows carefully: **identities, data and configuration are always yours**, even in SaaS. Microsoft's shared-responsibility page makes the same point: some responsibilities, such as your data, accounts and devices, always stay with the customer whatever the model.

## Provider wording differs

| Provider | How they describe it |
| --- | --- |
| AWS | "Security **of** the cloud" (AWS) versus "security **in** the cloud" (customer) |
| Microsoft Azure | A responsibility matrix by service type, highlighting that information, data, devices, accounts and identities always remain the customer's |
| Google Cloud | Shared responsibility plus **"shared fate"**: Google aims to help customers succeed with secure defaults, blueprints and guidance, while the customer still owns its configuration |

The models agree on the principle and differ in detail. For a real service, read the provider's documentation for **that specific service**, because two PaaS offerings from the same provider can split backups, encryption keys or patching differently.

## Shared does not mean "someone else's"

Many responsibilities are genuinely **shared**:

- **Patching a managed database**: the provider releases and often applies minor updates; you choose maintenance windows and plan major version upgrades.
- **Backups on PaaS**: the provider may take automatic backups; you set retention, protect them from deletion and **test restoring** them.
- **Encryption**: the provider offers encryption at rest, often on by default; you decide who can use the keys and whether to manage your own keys.
- **SaaS security settings**: the provider builds MFA, sharing controls and audit logs; you must switch them on and use them.

## Where breaches actually happen

Most publicly reported cloud incidents involve the **customer's side** of the model (this course does not quote a percentage, because published figures vary in method): storage made public by mistake, stolen credentials without MFA, over-powerful identities, unpatched VMs exposed to the internet, logging switched off. That is not because providers are perfect, but because the customer side is larger, changes faster and is configured by many different people.

:::reveal Back to the board's question
"The provider looks after security **of** the cloud: the buildings, hardware and the platforms they run. We remain responsible for **who** can access our systems, **what data** we put there and how it is protected, and **how we configure** every service. For the reporting VM we also patch the operating system; for the invoice database the provider patches the engine but we control access, backups and encryption keys; for email the provider runs everything except our users, settings and data."
:::

:::takeaway
The provider secures the infrastructure; the customer always owns identities, data and configuration. The further you move from IaaS towards SaaS, the less of the stack you run, but never none of it. Check the provider's responsibility documentation for each specific service.
:::
