:::scenario Saturday morning
On Saturday at 09:00 the SOC's weekly review finds a large overnight data transfer from a DMZ server to an unfamiliar internet address. The firewall logs show nothing at all for that server. This module covers how network evidence is collected, correlated and acted on, and why gaps like this one matter. In the lab you investigate that night.
:::

Network security monitoring brings together evidence from many devices. Each one answers different questions, and each has blind spots.

## The main network evidence sources

| Source | Answers | Blind spots |
| --- | --- | --- |
| **Firewall logs** (module 3) | Which rule allowed or denied a connection | Only rules with **logging enabled**; allowed traffic is often not logged to save space |
| **Flow records** (NetFlow, IPFIX, cloud flow logs) | Who talked to whom, on which port, for how long, how many bytes each way | No content; sampled flows can miss short connections |
| **IDS/IPS alerts** (module 5) | Known patterns and policy violations | Only what the rules recognise; encrypted content |
| **DNS logs** (module 7) | Which names each host looked up | Hosts that bypass the internal resolvers |
| **DHCP and VPN logs** | Which device or user had an address at a time | Static addresses |
| **Proxy logs** (module 7) | Which user visited which site | Traffic that does not use the proxy |
| **Packet captures** (module 8) | Exactly what crossed the wire | Short time windows; storage cost; privacy |

## Flow records in more detail

A flow record summarises a conversation: source and destination address and port, protocol, start time, duration, and packets and bytes in each direction. Routers, switches and firewalls export them using protocols such as **IPFIX** (RFC 7011) or NetFlow; cloud providers offer flow logs (Cloud module 3).

Flow data is cheap to keep for months, so it is often the only evidence left when an incident is found late. It is particularly good at:

- showing **volume**: a server that normally sends megabytes suddenly sending gigabytes;
- showing **new conversations**: a DMZ host talking to the server zone on a port it never used before;
- filling gaps where firewall rules were not logging.

## Making evidence usable

| Practice | Why |
| --- | --- |
| **Time synchronisation** (NTP) on every device, and UTC in logs | Events from different sources can be lined up |
| **Logging on every allow rule** that crosses a trust boundary | Otherwise the most important connections leave no firewall record |
| **Central collection** in a SIEM or log platform | One place to search; logs survive if a device is compromised |
| **Retention** long enough for late discovery (often 12 months for flows and firewall logs) | Many incidents are found weeks later |
| **Asset inventory** with owners and zones | Turns an IP address into a system and a person to call |

CISA and partner agencies' guidance *Best Practices for Event Logging and Threat Detection* (2024) and NIST SP 800-92 describe log management programmes in more depth.

:::takeaway
Combine firewall, flow, IDS, DNS, DHCP, VPN and proxy evidence, knowing each one's blind spots. Flow records are cheap, long-lived and often decisive. Synchronise clocks, log every boundary-crossing allow rule, collect centrally and keep evidence long enough for late discovery.
:::
