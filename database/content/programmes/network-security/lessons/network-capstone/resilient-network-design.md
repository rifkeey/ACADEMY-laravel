A secure network must also keep working. Outages, failed changes and attacks all test the same thing: whether critical services survive the loss of a component.

## Find the single points of failure

| Component | Kanem today | Resilient design |
| --- | --- | --- |
| Internet link | One provider | Two providers, different physical routes, with failover tested |
| Perimeter firewall | One FW-HQ-01 | A high-availability pair, configuration synchronised |
| Core switching | One core switch | Redundant core with link aggregation |
| Branch links | One VPN tunnel each | Primary and backup tunnels, ideally over different providers |
| DNS and DHCP | One server each | Two of each (module 7) |
| Identity services | One domain controller per site | At least two, in different locations |
| Management access | Only from the head-office management zone | A secured out-of-band path for when the main network is down |

## Set recovery objectives

As in Cloud module 10, agree **RTO** (how fast) and **RPO** (how much data loss) with the business for each critical service, then design to meet them. NIST SP 800-34 Rev. 1 describes business impact analysis and contingency planning.

## Resilience against attack

NIST SP 800-160 Vol. 2 Rev. 1 describes cyber resiliency as the ability to **anticipate, withstand, recover from and adapt to** attacks. In network terms:

| Goal | Network practice |
| --- | --- |
| **Anticipate** | Threat model; monitoring; tested incident playbooks (module 9) |
| **Withstand** | Segmentation so one compromised zone does not take down others (module 4); rate limiting and DDoS protection for internet services |
| **Recover** | Configuration backups of every device, stored off-device; documented rebuild procedures; spare hardware or contracts |
| **Adapt** | Lessons-learned reviews that change the design |

## Configuration management

- Back up device configurations automatically after every change, and keep versions.
- Use templates and a configuration standard per device type (lesson 3).
- Make changes through change control with tested rollback (module 3).
- Compare running configurations against the approved baseline regularly, and alert on drift.

:::warning Failover you have never tested is not failover
Schedule failover tests for links, firewalls and DNS at least once a year, in a maintenance window, with a rollback plan.
:::

:::takeaway
Remove single points of failure in links, firewalls, core switching, DNS, DHCP and identity; set RTO and RPO per service; design to anticipate, withstand, recover and adapt; back up and baseline every device configuration; and test failover regularly.
:::
