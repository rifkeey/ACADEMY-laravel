:::scenario 02:55 on a Monday
Kanem's SIEM shows a burst of firewall denies from **10.20.7.44**, an address in the remote-access VPN pool, towards the server zone: dozens of different ports in under five minutes. Is this an IT administrator testing something, a misconfigured application, or an attacker mapping the network? This module teaches you to tell the difference, and to connect what you see on the wire to what the adversary is trying to achieve.
:::

Before attacking, adversaries need to know **what exists and what is reachable**. MITRE ATT&CK records this as reconnaissance and discovery: *Active Scanning* (T1595) when it targets you from outside, and *Network Service Discovery* (T1046) once the attacker is inside. Defenders who understand scanning can spot it early, often before any damage is done.

## Passive and active reconnaissance

| Type | What the attacker does | Do you see it? |
| --- | --- | --- |
| **Passive** | Searches public sources: DNS records, certificate transparency logs, job adverts naming your technologies, staff profiles, leaked credentials | Rarely. It never touches your network |
| **Active, external** | Probes your public addresses for open ports and services (T1595) | Yes: in perimeter firewall and IDS logs, usually as constant background noise from the internet |
| **Active, internal** | From a compromised device, maps internal hosts and services (T1046) | Yes, and it is **highly significant**: internal scanning from a user device is rarely legitimate |

Internet-facing systems are scanned constantly by researchers, search engines for exposed devices and attackers alike. Internal scanning is different: it means something inside your network is behaving like an attacker.

## What scanning looks like in logs

Two shapes are worth memorising:

| Pattern | Shape | Example in firewall logs |
| --- | --- | --- |
| **Port scan** (vertical) | One source → **one** destination, **many ports** | `10.20.7.44 → 10.10.0.20` on 21, 22, 23, 80, 135, 139, 443, 445, 1433, 3306, 3389 … within seconds |
| **Host sweep** (horizontal) | One source → **many** destinations, **one or few ports** | `10.20.7.44 → 10.10.0.11 … 10.10.0.30` on 445 |

```text
2026-03-09T02:55:04Z deny  tcp 10.20.7.44:51101 → 10.10.0.20:21
2026-03-09T02:55:04Z deny  tcp 10.20.7.44:51102 → 10.10.0.20:22
2026-03-09T02:55:05Z deny  tcp 10.20.7.44:51103 → 10.10.0.20:23
2026-03-09T02:55:05Z allow tcp 10.20.7.44:51108 → 10.10.0.20:443
2026-03-09T02:55:05Z deny  tcp 10.20.7.44:51109 → 10.10.0.20:445
...
```

*(Synthetic.)* Notice the **sequential source ports**, the **sub-second timing** and the spread of destination ports with no business pattern. In a capture you would also see the TCP signatures from module 1: SYNs answered by SYN-ACK (open), RST (closed) or nothing (filtered).

## Detecting it

The question to ask the data is simple: **how many distinct ports (or hosts) did each source touch in a short window?**

```kusto
FirewallLogs
| where Action == "deny"
| summarize Ports = dcount(DstPort), Hosts = dcount(DstIp) by SrcIp
| where Ports >= 10 or Hosts >= 20
```

Tune the thresholds to your environment, and keep an **allow-list of authorised scanners** (the vulnerability scanner, the IT monitoring system) so their activity is expected and documented. Anything else that crosses the threshold from an internal address deserves triage.

:::warning Scanning is authorised activity too
Vulnerability scanning is a normal security control (NIST SP 800-115 covers authorised testing). The difference between a security test and an attack is **authorisation**: a scope, a schedule, a named owner. Never scan systems you are not authorised to test, including in this course: all scanning here is done on synthetic data.
:::

## Reducing what reconnaissance reveals

- **Shrink the attack surface**: expose only the services that must be public, and nothing on management ports.
- **Default deny with logging** (module 3) turns scans into evidence.
- **Segment** (module 4) so a compromised laptop cannot even reach most server ports.
- **Watch for internal scanning** specifically: it is a high-value, low-noise detection.

:::takeaway
Reconnaissance comes first in almost every attack. Port scans are one source to many ports; sweeps are one source to many hosts. Internal scanning from a user device is rarely legitimate, so count distinct ports and hosts per source and investigate anything unexplained.
:::
