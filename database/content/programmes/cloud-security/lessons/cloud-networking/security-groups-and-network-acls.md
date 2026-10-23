Routes decide where traffic **can** go. Filtering rules decide what is **allowed**. Each provider offers a stateful filter attached close to the workload, and most offer a second, coarser layer. Getting these right is what would have kept the invoice database off the internet.

## Stateful and stateless filtering

The idea is the same as on-premises firewalls (Network module 3):

| | Stateful | Stateless |
| --- | --- | --- |
| Return traffic | Allowed automatically for connections you allowed | Must be allowed by its own rule, including the client's high-numbered ports |
| Typical use | Main workload filtering | Coarse subnet-level guardrails |
| Mistakes | Over-broad sources such as `0.0.0.0/0` | Forgetting return traffic, so legitimate connections break |

## How the providers compare

| | AWS | Azure | Google Cloud |
| --- | --- | --- | --- |
| **Main workload filter** | **Security group** (stateful), attached to network interfaces; allow rules only | **Network security group (NSG)** (stateful), attached to subnets or network interfaces; allow and deny rules with priorities | **VPC firewall rules** (stateful), applied to instances by network tag or service account; allow and deny rules with priorities |
| **Grouping workloads** | Reference another security group as a source | **Application security groups** as sources and destinations | Target tags or **service accounts** |
| **Second layer** | **Network ACL** (stateless), per subnet, numbered allow and deny rules, evaluated in order | Azure Firewall or a network virtual appliance for central inspection | **Hierarchical firewall policies** at organisation or folder level |
| **Central inspection** | AWS Network Firewall, or appliances through a gateway load balancer | Azure Firewall | Cloud Next Generation Firewall |
| **Defaults to know** | Default security group allows all outbound; network ACLs allow all by default | Default rules allow traffic within the virtual network and outbound to the internet | Implied rules allow all egress and deny all ingress |

Provider features change often; check each provider's current documentation before relying on a detail.

## Designing rules for the invoice app

Reference **groups, not addresses**, so rules follow workloads as they scale:

| Rule set | Inbound allowed | From |
| --- | --- | --- |
| `sg-lb` (load balancer) | TCP 443 | `0.0.0.0/0` (the only internet-facing rule) |
| `sg-app` (app servers) | TCP 8080 | `sg-lb` only |
| `sg-db` (database) | TCP 5432 | `sg-app` only |
| Administration | None | Use a session manager or bastion service through identity (lesson 3), not open SSH or RDP |

Outbound: allow the app tier only what it needs (the database, approved APIs through egress controls in lesson 3); restrict the database tier's outbound to almost nothing.

Use the second layer as a **guardrail**, not the main control: for example, a network ACL or hierarchical policy that denies inbound administration ports from the internet to every subnet, so a mistaken security group can't expose them.

## Common mistakes

| Mistake | Fix |
| --- | --- |
| `0.0.0.0/0` on database, SSH or RDP ports | Group references; remove direct administration from the internet |
| One shared "allow-all-internal" group used everywhere | Separate groups per tier and purpose |
| Rules added for a test and never removed | Owner and expiry in the description; regular review (module 7 posture checks) |
| Relying only on the stateless layer | Stateful workload filters first; stateless as a coarse backstop |
| Unrestricted outbound | Egress rules and central egress inspection |

## Checking that rules do what you think

Each provider has a tool that analyses reachability without sending real traffic, for example AWS Reachability Analyzer, Azure Network Watcher (IP flow verify) and Google Cloud Connectivity Tests. Use them to validate changes, and use posture management (module 7) to flag risky rules continuously. Policy-as-code checks in the pipeline (module 8) can block a template that opens a database port to the internet before it is ever deployed.

:::takeaway
Use the provider's stateful workload filter as the main control, written as group-to-group rules per tier with one internet-facing rule at the load balancer. Add a coarse second layer as a guardrail, know each platform's defaults, avoid open administration ports and allow-all groups, and validate reachability with the provider's tools and posture checks.
:::
