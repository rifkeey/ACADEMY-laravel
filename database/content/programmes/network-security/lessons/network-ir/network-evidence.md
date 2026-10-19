## The network as a witness

Endpoints can be tampered with by an intruder; network evidence collected elsewhere is harder to erase. Network data helps answer: which hosts did the attacker touch, which external systems did they talk to, and how much data left?

## Sources of network evidence

| Source | What it shows | Retention |
| --- | --- | --- |
| Firewall logs | Allowed and denied connections | Often weeks to months |
| Flow records (NetFlow, IPFIX, VPC flow logs) | Who talked to whom, when, how much | Months, compact |
| DNS logs | Names resolved by each client | Varies; very valuable |
| Proxy logs | URLs, user, bytes | Varies |
| IDS/NSM logs (e.g. Zeek) | Protocol metadata, alerts | Varies |
| Full packet capture | Complete content | Hours to days; expensive |

## Suspicious traffic patterns

- **Beaconing**: regular connections to the same destination with similar sizes.
- **Unusual volumes**: a workstation uploading hundreds of megabytes to a new domain.
- **New external destinations**: first-seen domains or IPs contacted by only one host.
- **Unusual protocols or ports**: DNS to external resolvers, SSH from a finance laptop.

## Lateral movement indicators

Lateral movement is the attacker spreading from the first host to others. Network indicators include:

- workstation-to-workstation SMB (TCP 445) or RDP (TCP 3389);
- remote management protocols (WinRM, TCP 5985/5986) from non-admin hosts;
- one host authenticating to many others in a short time;
- connections from user zones to management interfaces.

MITRE ATT&CK's Lateral Movement tactic lists techniques and their data sources.

## Collecting evidence

Preserve relevant logs before they roll over: export the time window of interest, record hashes, and note the source and time zone. If you start a new packet capture during an incident, capture at a point that sees the suspect traffic, and document when and where.

## Containment at the network layer

Network containment options include blocking destinations at the firewall or DNS, isolating a segment, or moving a host to a quarantine VLAN. Coordinate with endpoint and identity containment, so the attacker does not simply switch to another path.
