DHCP gives every device its address, gateway and DNS servers (module 1). Whoever answers DHCP requests decides where devices send their traffic, so DHCP must be protected, and its logs connect IP addresses to real devices.

## Risks to manage

| Risk | Effect |
| --- | --- |
| **Unauthorised DHCP server** (often a misconfigured home router plugged into the network) | Devices receive the wrong gateway or DNS server, causing outages or misdirected traffic |
| **Address exhaustion** | Legitimate devices can't get addresses |
| **Unknown devices** | Anything plugged in gets network configuration automatically |

## Controls

| Control | How it helps |
| --- | --- |
| **DHCP snooping** on switches | Only ports connected to the real DHCP servers may send DHCP replies; replies from other ports are dropped |
| **Network access control** (module 3) | Devices must authenticate before they get onto a VLAN at all |
| **Scope design** | Separate scopes per VLAN, sized for need, with short leases on guest networks |
| **Reservations** for servers and infrastructure | Predictable addresses for important systems |
| **Monitoring** | Alerts for scope exhaustion and for DHCP replies from unexpected sources |
| **Redundancy** | Two DHCP servers or failover, so the service doesn't become a single point of failure |

DHCP snooping also supports other switch protections (for example ARP inspection) because it builds a table of which address was given to which device on which port.

## DHCP logs as evidence

| Question | DHCP logs answer |
| --- | --- |
| Which device had IP 10.20.7.44 at 03:30 on 9 March? | The lease: MAC address, hostname, start and end time |
| When did this unknown device first appear? | Its first lease |
| Is this the same device as yesterday? | Matching MAC and hostname across leases |

Because addresses change hands, **an IP address in a log means nothing without the time** and the lease history. Keep DHCP logs as long as the logs they help explain (firewall, DNS, proxy).

:::takeaway
Protect DHCP with snooping, network access control, sensible scopes, reservations, monitoring and redundancy, and keep lease logs: they are how you turn an IP address at a point in time into a specific device.
:::
