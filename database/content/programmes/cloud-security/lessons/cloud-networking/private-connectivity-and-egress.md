Putting a server in a private subnet is only half the job. Cloud workloads constantly talk to **provider services** (storage, key management, databases, queues) and to the **internet** (updates, partner APIs). This lesson covers how to make those conversations private and controlled.

## Provider services have public endpoints

By default, an application in a private subnet that reads from object storage or calls the key management service reaches a **public service endpoint**, usually through a NAT gateway. The traffic is encrypted and authenticated, but it leaves your private network, needs an outbound internet path, and the service itself is reachable from anywhere by anyone with valid credentials.

## Private endpoints

All three providers can place a private entry point for a service **inside your network**:

| Provider | Feature | What it does |
| --- | --- | --- |
| AWS | **VPC endpoints** (gateway endpoints for S3 and DynamoDB; interface endpoints powered by **AWS PrivateLink** for many services) | Traffic to the service stays on the AWS network; no internet gateway or NAT needed |
| Azure | **Private endpoints** (Azure Private Link) | The service gets a private IP address in your VNet; public network access to it can then be disabled |
| Google Cloud | **Private Service Connect**, Private Google Access | Reach Google APIs and published services from private IP addresses |

Two security wins follow:

1. **Remove the public path.** Many services can then be set to reject requests that don't come through the private endpoint (for example, an Azure storage account with public network access disabled, or an S3 bucket policy that only allows access through a specific VPC endpoint).
2. **Control who uses the endpoint.** Endpoint policies (on AWS) can limit which buckets or actions can be reached through it, reducing the chance of data being copied to an attacker's own storage account through your network.

## Controlling egress

Cloud workloads should not have unrestricted outbound internet access (the same principle as egress filtering in the Network Security course).

| Need | Better pattern |
| --- | --- |
| OS and package updates | Private repository mirrors or a filtering proxy; or a NAT gateway with tight security rules and logging |
| Calls to provider services | Private endpoints (no internet needed) |
| Calls to a few partner APIs | A cloud firewall or proxy that allows only named destinations (domain-based rules where supported) |
| Nothing at all (databases) | No route to the internet, no NAT |

Managed **cloud firewalls** (AWS Network Firewall, Azure Firewall, Google Cloud NGFW) can inspect and filter traffic centrally, typically in a hub network that spoke networks route through (lesson 4).

## Avoid public IP addresses

Give public IP addresses only to resources that must accept internet connections, which in a well-designed application is usually just the load balancer or API gateway. Administrators can reach private servers through provider session services (AWS Systems Manager Session Manager, Azure Bastion, Google Cloud Identity-Aware Proxy TCP forwarding) instead of public SSH or RDP, with identity-based access and session logging. Many organisations enforce "no public IPs except approved types" with organisation-level guardrails.

:::tip Check the DNS
Private endpoints usually work by making the service's normal name resolve to a private address inside your network. If DNS is not configured correctly, applications silently keep using the public endpoint. Test name resolution from inside the network after creating an endpoint.
:::

:::takeaway
Keep traffic to provider services private with VPC endpoints, Private Link or Private Service Connect, then disable public access where the service allows it. Restrict egress to named needs, avoid public IPs except at the front door, and use identity-based session services for administration.
:::
