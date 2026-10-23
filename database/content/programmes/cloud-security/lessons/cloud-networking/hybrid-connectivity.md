Kanem's core banking stays in its Lagos data centre while the invoice application runs in the cloud. Hybrid connectivity joins the two, and with it, joins their risks: a weakness on one side can become a path into the other.

## Ways to connect

| Option | AWS | Azure | Google Cloud | Characteristics |
| --- | --- | --- | --- | --- |
| Site-to-site VPN | Site-to-Site VPN | VPN Gateway | Cloud VPN | Encrypted IPsec tunnels over the internet; quick to set up |
| Dedicated private link | Direct Connect | ExpressRoute | Cloud Interconnect | Private circuits through a provider partner; predictable bandwidth; **not encrypted by default** at the network layer |

For dedicated links, decide explicitly whether traffic needs extra encryption (for example IPsec over the link, or MACsec where supported). "Private" means it doesn't cross the public internet, not that nobody else could ever see it.

## Hub-and-spoke designs

As the number of cloud networks grows, connecting each to every other (and each to on-premises) becomes unmanageable. The common pattern is a **hub**:

```text
                     on-premises (Lagos DC)
                              │ VPN / dedicated link
                     ┌────────▼────────┐
                     │   hub network   │  shared services: firewall, DNS, egress, connectivity
                     └──┬──────┬─────┬─┘
                        │      │     │
                  invoice-prod  invoice-dev  shared-tools    ← spokes
```

Spokes reach on-premises and each other **only through the hub**, where a cloud firewall enforces policy and logs traffic. Providers offer managed building blocks for this (AWS Transit Gateway, Azure Virtual WAN or hub-and-spoke VNets, Google Network Connectivity Center).

## Security decisions in hybrid networks

| Decision | Good practice |
| --- | --- |
| What routes are shared | Advertise only the networks each side needs; don't give a dev spoke a route to production on-premises systems |
| Where traffic is inspected | At the hub firewall, both directions |
| Address planning | No overlapping ranges, so rules and logs stay unambiguous |
| Trust | The cloud should not trust on-premises traffic just because it arrives over the private link, and vice versa: apply the same least-privilege rules as between any zones |
| Resilience | Two tunnels or links, ideally in different locations, with monitoring |
| DNS | A clear design for which side resolves which names (private endpoint names especially) |

## Zero trust across the hybrid boundary

The network link is plumbing, not permission. Applications on both sides should still authenticate users and workloads (Cloud Security module 2) and encrypt in transit, so that a compromise on one side does not translate into automatic access on the other. This is the zero trust principle from Network Security module 4 applied across environments.

:::takeaway
Connect on-premises and cloud with VPNs or dedicated links, route spokes through a hub where traffic is inspected and logged, share only the routes each side needs, and never treat the private link itself as a reason to trust traffic.
:::
