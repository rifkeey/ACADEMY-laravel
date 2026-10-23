Traditional segmentation draws boundaries around **networks**. **Microsegmentation** draws them around **workloads**: each server, virtual machine or container gets its own policy, wherever it runs and whatever its IP address.

## Why go finer?

| Coarse zones | Microsegmentation |
| --- | --- |
| "Application zone may reach database zone on 1433" | "**Loans API** servers may reach the **loans database** on 1433; nothing else may" |
| A compromised app server can reach every database in the zone | A compromised app server reaches only its own database |
| Rules follow IP ranges | Rules follow **labels** (application, environment, role) |
| Changing the network changes the policy | Workloads keep their policy when they move |

It is especially valuable in virtualised and cloud data centres, where workloads are created and moved constantly and most traffic is east–west.

## How it is implemented

| Approach | Where policy is enforced | Examples |
| --- | --- | --- |
| **Host-based agents** | On each workload's own firewall, managed centrally | Endpoint and workload protection platforms, centrally managed host firewalls |
| **Hypervisor / software-defined networking** | In the virtual switch of each host | Distributed firewalls in virtualisation platforms |
| **Cloud-native controls** | In the cloud provider's network layer | Security groups and network security groups attached to each workload (Cloud Security module 3) |
| **Service mesh / identity-based** | Between services, using workload identities and mutual TLS | Kubernetes service meshes |

All of them express rules in terms of **what a workload is** (labels or identity) rather than **where it is** (IP address), which is a step towards zero trust.

## A practical rollout

Microsegmentation fails when it is switched on before anyone knows which flows are legitimate. A safer sequence:

1. **Inventory and label** workloads: application, environment (prod/test), tier (web/app/db), data sensitivity.
2. **Observe** traffic for a few weeks (flow logs, agent visibility) and build a map of who talks to whom.
3. **Write policy from the map**, review it with application owners, and remove flows nobody can justify.
4. **Enforce in monitoring mode** first: log what would be blocked.
5. **Enforce for real**, starting with the highest-value applications (card payments, core ledger).
6. **Keep it current**: new applications need labels and policy before they go live.

:::warning Don't break the business
Applications often have dependencies nobody documented: a nightly batch job, a licence server, a monitoring probe. Observing before enforcing, and enforcing in stages, is what separates a successful microsegmentation project from an outage.
:::

## Worked example: the card-payment application

| Flow | Allowed? | Why |
| --- | --- | --- |
| Core banking app → card switch API, TCP 9443 | ✔ | Documented payment flow |
| Card switch → card processor (internet), TCP 443 | ✔ | Required to authorise payments |
| Card switch ↔ other card switch nodes, cluster port | ✔ | Cluster replication |
| Any user laptop → card switch | ✘ | No business need |
| Other application servers → card switch | ✘ | Only core banking calls it |
| Card switch → internet, any other destination | ✘ | Limits exfiltration |
| Management jump host → card switch, SSH | ✔ | Administration, logged |

Seven rows, each with an owner and a reason. That table is the policy, regardless of which product enforces it.

:::takeaway
Microsegmentation applies policy per workload, using labels or identities instead of IP addresses, and closes the east–west gap inside zones. Inventory and observe before you enforce, start with the most valuable systems, and treat the flow table as the policy.
:::
