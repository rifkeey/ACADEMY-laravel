:::scenario The data-centre redesign
Kanem Financial Services is rebuilding its Lagos data centre network. Today the public website, the core banking application, its databases, the card-payment switch and the IT admin workstations all sit in one large server VLAN. The CIO has one requirement: *"If one system is compromised, it must not take the rest with it."* This module gives you the design tools to meet it.
:::

In a **flat network** every device can reach every other device. One compromised laptop or web server can then reach databases, payment systems and administration interfaces directly. **Segmentation** divides the network into zones with controlled boundaries, limiting the **blast radius** of any single compromise.

## Zones and trust boundaries

A **zone** groups systems with similar trust levels and security requirements. A **trust boundary** is every point where traffic passes between zones, and every boundary needs an enforcement point (firewall, ACL, proxy) with logging.

| Zone | Contents | Typical policy |
| --- | --- | --- |
| Internet | Everyone | Untrusted; inbound only to published services |
| DMZ | Internet-facing systems (website, mail relay, API gateway) | Reachable from the internet on specific ports; very narrow access inwards |
| Users | Staff laptops and desktops | Web access outbound; specific internal applications only |
| Application | Internal application servers | Reachable from users and the DMZ only on application ports |
| Database | Databases | Reachable **only** from the application servers that use them |
| Restricted / high-value | Card-payment systems, HSMs, core ledger | Smallest possible set of flows, strongest monitoring |
| Management | Admin workstations, jump hosts, monitoring | The only zone that may administer infrastructure |
| Guest / IoT | Visitors, cameras, building systems | Internet only, or only their own controllers |

## North–south and east–west

- **North–south** traffic crosses the perimeter: internet ↔ DMZ, users ↔ internet. Traditional perimeter firewalls focus here.
- **East–west** traffic moves *inside* the organisation: users ↔ servers, server ↔ server. In modern data centres it is most of the traffic, and it is the path attackers use for lateral movement after their first foothold.

A design that filters only north–south leaves the inside flat. Good segmentation controls both.

## DMZ designs

A **DMZ** holds systems that must accept connections from untrusted networks, so that a compromise there does not give direct access inside.

```text
Single firewall, three legs                 Two firewalls ("back-to-back")
internet ─┐                                  internet ── FW-A ── DMZ ── FW-B ── internal
          FW ── DMZ
internal ─┘                                  Different administrators or even vendors
                                             can run FW-A and FW-B
```

Whichever topology you use, the DMZ rules matter more than the shape:

- Internet → DMZ: only the published services (e.g. TCP 443 to the web server).
- DMZ → internal: only the specific back-end calls each DMZ system needs (the web front end calls the core banking API on one port, **never** the database directly).
- DMZ → internet: usually nothing initiated, or only named update sources through a proxy.
- Internal → DMZ: administration only from the management zone.

## Trust is not binary

Zones express **relative** trust, not "trusted inside, untrusted outside". A staff laptop is less trusted than a database server even though both are "internal": laptops browse the web, open email attachments and travel. Design boundaries around **what each system does and what it holds**, not around physical location.

:::tip Start from data and flows
Before drawing zones, list the systems that hold or process the most sensitive data (customer records, card data, the ledger) and the **flows** each needs. Zones then follow naturally: group systems with the same exposure and the same partners, and put the high-value ones behind the most boundaries.
:::

:::takeaway
Segmentation limits blast radius. Group systems into zones by trust and function, put an enforcement point with logging at every boundary, control east–west traffic as well as north–south, and keep the DMZ's paths inward as narrow as the business allows.
:::
