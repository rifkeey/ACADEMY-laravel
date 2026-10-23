:::scenario The invoice app's network
The invoice application from module 2 runs in an AWS virtual network built by the project team in a hurry. A routine scan by Kanem's security team reports that **the invoice database answers on TCP 5432 from the internet**. "But it's in the cloud, behind AWS," says the developer. This module explains how cloud networks really work, why that database is exposed, and how to design it properly.
:::

Every major cloud gives you a **software-defined private network** in which your resources live. The names differ; the concepts are the same as the networks in the Network Security course, with a few cloud-specific twists.

## The building blocks

| Concept | AWS | Azure | Google Cloud |
| --- | --- | --- | --- |
| Private network | VPC (regional) | Virtual network, VNet (regional) | VPC network (**global**, with regional subnets) |
| Subdivision | Subnet (in one Availability Zone) | Subnet | Subnet (regional) |
| Routing | Route tables attached to subnets | Route tables (user-defined routes) | Routes on the VPC network |
| Path to the internet | Internet gateway | Default outbound access / public IPs, NAT gateway | Default internet gateway route, Cloud NAT |
| Outbound only for private resources | NAT gateway | NAT gateway | Cloud NAT |

You choose the address range (for example `10.50.0.0/16` for the invoice VPC), split it into subnets and decide where each subnet's traffic can go. Plan the ranges so they **don't overlap** with on-premises networks or other cloud networks you may need to connect later (lesson 4).

## What makes a subnet "public"

In AWS the definition is precise and worth memorising: a subnet is **public** if its route table has a route to an **internet gateway**. A resource in that subnet with a public IP address can then be reached from the internet, subject to its security rules.

```text
Route table rt-public  (attached to subnet-web-a, subnet-web-b)
Destination      Target
10.50.0.0/16     local
0.0.0.0/0        igw-0a1b2c          ← internet gateway: this makes the subnet public

Route table rt-private (attached to subnet-app-a, subnet-db-a …)
Destination      Target
10.50.0.0/16     local
0.0.0.0/0        nat-0d4e5f          ← NAT gateway: outbound only, no inbound from the internet
```

A **private** subnet has no route to the internet gateway. Its resources can reach out through a NAT gateway (if there is one) but cannot be reached from the internet directly.

## The classic three-tier layout

```text
                 internet
                    │
        ┌───────────▼────────────┐   public subnets (2 AZs)
        │  load balancer (443)   │
        └───────────┬────────────┘
                    │ 8080
        ┌───────────▼────────────┐   private app subnets (2 AZs)
        │  invoice app servers   │──► NAT gateway ──► internet (updates, APIs)
        └───────────┬────────────┘
                    │ 5432
        ┌───────────▼────────────┐   private database subnets (2 AZs)
        │  invoice database      │   no route to the internet at all
        └────────────────────────┘
```

Only the load balancer is internet-facing. The application servers have no public IPs; the database has no path to or from the internet.

:::warning "Publicly accessible" databases
Managed database services often have a setting that gives the database a public endpoint. Combined with a public subnet and a permissive rule, the database answers the whole internet, which is exactly the finding in the scenario. Keep databases in private subnets, with public access switched off.
:::

## Cloud differences that matter for security

- **Everything is an API call.** Anyone with the right permissions can add a route to the internet gateway or attach a public IP in seconds, so network security depends on identity and guardrails as much as on design.
- **Some services live outside your network.** Object storage, many managed databases and serverless APIs have **public service endpoints** by default. Reaching them privately needs specific features (lesson 3).
- **Defaults are permissive in places.** For example, Google Cloud's implied firewall rules allow all egress, and AWS's default security group allows all outbound traffic. Know the defaults of your platform.

:::takeaway
Cloud networks are software-defined but follow familiar rules: address ranges, subnets and route tables. A subnet is public because of its routes. Put only internet-facing components in public subnets, keep application and database tiers private, and remember that network changes are API calls protected by identity.
:::
