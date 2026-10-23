Zones are a design. **VLANs, subnets, firewalls and host controls** are how you build them. This lesson explains what each mechanism actually enforces, because the most common segmentation mistake is believing a VLAN is a security boundary on its own.

## VLANs separate; they do not filter

A VLAN (IEEE 802.1Q) splits one switched network into separate layer-2 broadcast domains. Devices in VLAN 20 cannot send frames directly to devices in VLAN 30.

But traffic **between** VLANs must pass through a layer-3 device, and that device decides:

| Inter-VLAN routing on… | What happens |
| --- | --- |
| A core switch with routing enabled and no ACLs | Every VLAN can reach every other VLAN: the network is segmented on paper and flat in practice |
| A core switch with ACLs | Basic stateless filtering between VLANs (module 3, lesson 1) |
| A firewall | Stateful, logged, reviewable policy between zones: the usual choice for important boundaries |

So a VLAN design needs a matching **enforcement design**: which device routes between these VLANs, and what policy it applies.

## Map VLANs to subnets to zones

Keep the mapping one-to-one where you can. It makes rules, logs and investigations readable:

| Zone | VLAN | Subnet | Gateway / enforcement |
| --- | --- | --- | --- |
| DMZ | 110 | 10.30.0.0/24 | Firewall FW-DC-01 |
| Application | 120 | 10.31.0.0/24 | Firewall FW-DC-01 |
| Database | 130 | 10.32.0.0/24 | Firewall FW-DC-01 |
| Cards (restricted) | 140 | 10.33.0.0/26 | Firewall FW-DC-01, separate rule section |
| Management | 199 | 10.99.0.0/24 | Firewall FW-DC-01 |

An analyst who sees `10.32.0.14` in a log can then tell immediately that it is a database.

## Switch hygiene matters

Layer-2 misconfigurations can let traffic cross VLAN boundaries that the design says are separate. Standard hardening, which the CIS Benchmarks for network devices also cover:

- Disable **automatic trunk negotiation** on access ports; configure trunks explicitly and only where needed.
- Allow on each trunk **only the VLANs it must carry**.
- Use an **unused VLAN as the native VLAN** on trunks, and don't put users in VLAN 1.
- **Shut down unused ports** and assign them to an isolated VLAN.
- Use **802.1X** (module 3) so devices land in the right VLAN automatically.

## Inside a zone: the east–west gap

Firewalls between zones do nothing for traffic **within** a zone. If ten database servers share VLAN 130, a compromised database can talk to the other nine freely. Options to close that gap:

| Control | How it works | Trade-off |
| --- | --- | --- |
| **Host-based firewalls** | Each server only accepts the connections it needs (Windows Defender Firewall, `nftables`) | Must be centrally managed or it drifts |
| **Private VLANs / port isolation** | Hosts in the same VLAN can reach the gateway but not each other | Good for groups of similar hosts (e.g. kiosks); less flexible |
| **Smaller segments** | Split the zone (e.g. one subnet per application) | More rules to manage |
| **Microsegmentation** (next lesson) | Policy per workload, independent of network layout | Needs good inventory and tooling |

## How segmentation is enforced, end to end

```text
laptop (VLAN 20)  ──►  core switch: routes to firewall only  ──►  FW-DC-01: policy users→application tcp 443
                                                                       │ logged allow
                                                                       ▼
                                                     app server (VLAN 120): host firewall accepts 443 only
```

Two independent layers (network firewall and host firewall) mean a single misconfiguration does not open the path.

:::takeaway
VLANs create separate layer-2 networks; the device that routes between them is what enforces security. Route important boundaries through a firewall, keep VLANs, subnets and zones aligned, harden switches against VLAN leakage, and add host or microsegmentation controls for east–west traffic inside a zone.
:::
