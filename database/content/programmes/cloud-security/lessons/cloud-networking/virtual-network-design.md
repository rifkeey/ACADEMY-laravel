## Virtual networks

Every major cloud lets you define private virtual networks (AWS VPC, Azure VNet, Google Cloud VPC) divided into subnets. The same zone thinking used on-premises applies: public-facing components in public subnets, application and data tiers in private subnets with no direct internet route.

## Two layers of filtering

| Control | Scope | Stateful? | Typical use |
| --- | --- | --- | --- |
| Security group / network security group | Attached to an instance or interface | Yes | Per-workload allow rules |
| Network ACL / subnet-level rules | Applied to a subnet | Often stateless | Coarse subnet guardrails |

Write security group rules that reference **other security groups** where supported ("the app tier may reach the database tier on 5432") rather than wide IP ranges.

## Common exposure mistakes

- SSH (22) or RDP (3389) open to `0.0.0.0/0`.
- Databases with public IP addresses.
- "Temporary" wide rules that became permanent.
- Default networks left in place with permissive defaults.

## Private access to services

**Private endpoints** (or private links) let workloads reach managed services such as storage and databases over the provider's private network, without traversing the internet. Combined with service policies that deny public access, they greatly reduce data-exposure risk.

## Secure administrative access

Avoid exposing management ports at all. Options include provider-managed bastion services, session managers that broker access through the identity system, or a VPN into a management subnet. Each should log sessions and require MFA.

## Egress control

Restrict outbound internet access from private subnets to what is needed (through NAT gateways or egress firewalls with allow-lists). Unrestricted egress makes data exfiltration and command-and-control trivial.

## Zero trust in the cloud

Network controls are necessary but insufficient. NIST SP 800-207's zero trust principles apply naturally to cloud: authenticate and authorise every request, and treat network location as just one signal.
