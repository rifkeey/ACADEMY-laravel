## Flat networks fail badly

In a flat network, any device can reach any other. One compromised laptop can then scan and connect to servers, databases and administrative interfaces. Segmentation limits that **blast radius**.

## Zones and trust boundaries

A **zone** groups systems with similar trust and security requirements. Typical zones:

| Zone | Contents | Typical policy |
| --- | --- | --- |
| Internet | Untrusted | Inbound only to published services |
| DMZ | Public-facing servers | Reachable from the internet on specific ports; very limited access inward |
| Users | Staff devices | Outbound web; specific internal applications only |
| Servers | Internal applications, databases | Reachable only from the zones that need them |
| Management | Admin workstations, jump hosts | The only zone allowed to administer infrastructure |
| Guest / IoT | Visitors, printers, cameras | Internet only, isolated from everything else |

A **trust boundary** is any point where traffic passes between zones. Every boundary needs a control (firewall, ACL, proxy) and logging.

## Implementing segmentation

- **VLANs** separate broadcast domains at layer 2; a router or firewall controls traffic between them. VLANs alone do not enforce security.
- **Firewalls** or layer-3 ACLs enforce policy between segments.
- **Microsegmentation** applies policy per workload, often in virtualised or cloud environments.

## Least privilege and zero trust

Allow only the specific flows a business process needs: source zone, destination, protocol and port. NIST SP 800-207 describes **zero trust architecture**, which goes further: no implicit trust based on network location, with every access request authenticated and authorised. Segmentation remains valuable within zero trust, because it limits what a compromised identity or device can reach.

## Design questions

1. What are the crown-jewel systems, and which zones may reach them?
2. Where do administrators connect from?
3. What can a compromised user device reach right now?
4. Is every boundary logged?
