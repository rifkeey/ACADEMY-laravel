:::scenario Working from anywhere
Kanem's loans officers now visit customers across Abuja and Kano with tablets, IT administrators support branches from home, and two branches connect to head office over the internet. Every one of these connections crosses networks Kanem does not control. This module covers how to make them secure, and how to fix them when they fail.
:::

Remote access connects people and sites to resources across untrusted networks. Choosing the right method is the first security decision.

## The main options

| Option | Connects | Typical technology | Security character |
| --- | --- | --- | --- |
| **Site-to-site VPN** | Whole networks (branch ↔ head office, head office ↔ cloud) | IPsec | Always-on tunnel between gateways; users notice nothing |
| **Remote-access VPN** | One device to the corporate network | IPsec or TLS-based VPN clients | Device joins the network; access depends on what the network allows |
| **Zero trust network access (ZTNA)** | One user and device to specific applications | Identity-aware proxies and connectors | Per-application, per-session decisions (Network Security module 4) |
| **Published applications** | Users to a web application directly | HTTPS with strong authentication | No network access at all; only the application |
| **Remote administration services** | Administrators to servers | Bastions, provider session services | Narrow, logged, identity-based |

The trend is from network-level access (VPN) towards application-level access (ZTNA, published applications), because it limits what a compromised account or device can reach. VPNs remain essential for site-to-site links and many legacy applications.

## Questions to ask before choosing

1. **Who or what needs access?** Staff, contractors, partners, devices, sites?
2. **To what?** A few applications, or the whole network?
3. **From which devices?** Managed and compliant, or personal?
4. **How strong must authentication be?** Phishing-resistant MFA for anything sensitive.
5. **What must be logged?** Every connection, with user, device and time.

## Kanem's remote-access design

| Need | Choice | Why |
| --- | --- | --- |
| Abuja and Kano branches to head office | Site-to-site IPsec VPN, two tunnels per branch | Always-on, encrypted, resilient |
| Loans officers' tablets to the loans application | Published application with MFA and device compliance | They need one application, not the network |
| IT administrators to infrastructure | VPN with MFA to the management zone only, then the jump host | Administrative access stays in the management plane |
| Contractor support | ZTNA to the specific application, time-limited | Least privilege for third parties |

:::takeaway
Match the remote-access method to the need: site-to-site VPNs for networks, application-level access for most users, narrow identity-based paths for administrators and contractors. Strong authentication, device checks and logging apply to all of them.
:::
