"Least privilege" sounds abstract until you have to write the rule. This lesson turns business requirements into firewall rules that allow exactly what is needed, in both directions, and explains why the outbound direction matters as much as the inbound one.

![Diagram of the Kanem head-office network: a central firewall FW-HQ-01 connects six zones: internet, dmz, users, guest, servers and management.](/content/network-security/kanem-hq-zones.svg)

*Kanem Financial Services head office (fictional). All traffic between zones passes through FW-HQ-01, which is used throughout this module and its lab.*

## Start from requirements, not from the firewall

A rule should exist because a **named business need** requires it. Before touching the firewall, write the need down as a row in a simple flow matrix:

| # | Who (source) | Needs to reach (destination) | Service | Why | Owner |
| --- | --- | --- | --- | --- | --- |
| R1 | internet | dmz web server | TCP 443 | Public website and loan application form | Digital channels |
| R2 | users | servers (core banking app) | TCP 443 | Staff use the core banking application | Operations |
| R3 | dmz web server | servers (database) | TCP 5432 | Website reads loan products | Digital channels |
| R4 | management | servers | TCP 22 | Server administration | IT infrastructure |

Each row then becomes one rule, and every rule can be traced back to a row. When a row's need ends, its rule goes too.

## The four questions for every allow rule

1. **Source**: is it the smallest group of systems that needs access? A zone, a subnet, a named host group, or a user group on an identity-aware firewall.
2. **Destination**: is it the specific system, not "servers" or "any"?
3. **Service**: is it the specific protocol and port, not "any"?
4. **Justification**: does it have an owner, a reason and, for temporary rules, an expiry date?

The word `any` in an allow rule is where most excessive permissions hide. Sometimes it is genuinely right (the public website really does accept connections from any internet address), but it should always be a deliberate choice.

:::warning "Temporary" rules
`ALLOW any → dmz any any — temp, vendor troubleshooting` is one of the most common findings in firewall reviews. It was needed for an afternoon and is still there two years later, exposing every port on every DMZ server to the internet. If you must add a temporary rule, give it an expiry date, a ticket and an owner, and check that it was removed.
:::

## Inbound is only half the policy

Many organisations filter inbound traffic carefully and allow almost anything outbound ("users and servers may go anywhere"). That is a gift to attackers, because the next steps after gaining a foothold are outbound:

| Attacker goal | Typical traffic | ATT&CK |
| --- | --- | --- |
| Talk to a command-and-control server | HTTPS, DNS or unusual ports to an attacker's server | T1071 Application Layer Protocol |
| Hide a channel inside another protocol | DNS or HTTPS tunnels | T1572 Protocol Tunneling |
| Steal data | Uploads over FTP, SMB, or anything allowed | T1048 Exfiltration Over Alternative Protocol |

**Egress filtering** limits which zones may start outbound connections and on which services:

- **Servers rarely need the internet.** They need DNS (ideally only to your internal resolvers, which then query the internet) and access to patch repositories, preferably through a proxy. "Servers → internet any any" turns every compromised server into a perfect exfiltration point.
- **Users need web access** (TCP 443, possibly 80) through a proxy or secure web gateway, not arbitrary ports. A laptop that suddenly connects out on TCP 4444 should hit a logged deny.
- **Guests need the internet and nothing else**: guest Wi-Fi should never reach internal zones.
- **Management traffic flows outwards from the management zone**, never into it from user zones.

Egress rules also produce valuable evidence. A logged deny for `servers → internet TCP 8443` at 03:00 is often the first sign of compromise.

## Walkthrough: from requirement to rule

Requirement: *IT support staff must be able to take remote control of user laptops with Remote Desktop.*

| Draft | Problem |
| --- | --- |
| `ALLOW users → any tcp 3389` | Wrong direction (support connects *to* users), and lets every laptop open RDP to every server and to the internet: an ideal lateral-movement path (ATT&CK T1021.001) |
| `ALLOW any → users tcp 3389` | Lets any zone, including guest and DMZ, reach laptop RDP |
| `ALLOW management → users tcp 3389` | ✔ Only the management zone, only RDP, only towards users |

If the firewall is identity-aware, you could tighten it further: only members of the "IT Support" group, from management workstations.

:::reveal Advanced challenge: what would you ask before writing R3?
- Which *exact* host is the web server and which is the database? Use those, not whole zones.
- Does the web server need write access to the database, or only read? (That is a database permission, not a firewall rule, but the conversation should happen.)
- Should the connection be encrypted (TLS to PostgreSQL)?
- What should be logged, and who reviews those logs?
:::

:::takeaway
Every allow rule should trace back to a written need with an owner. Filter outbound as carefully as inbound: limiting egress is what stops a single compromised host from becoming a data breach.
:::
