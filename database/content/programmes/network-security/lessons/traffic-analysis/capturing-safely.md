:::scenario "The portal is slow"
Kanem's loans officers in Abuja say the internal portal takes ages to load. Logs show nothing wrong. When logs and dashboards run out, network engineers and analysts turn to **packet captures**: the exact record of what crossed the wire. This module teaches you to capture safely, read captures in Wireshark and write up what you find.
:::

A packet capture records every frame seen on an interface, including headers and, for unencrypted traffic, the content. That makes it powerful evidence, and also sensitive data.

## Before you capture: permission and privacy

| Question | Why it matters |
| --- | --- |
| **Am I authorised?** | Capture only on networks and systems you are permitted to monitor, under Kanem's policy and with the owner's agreement. Capturing other people's traffic without authority can be unlawful |
| **What personal data might I collect?** | Captures can contain names, account numbers, session cookies and, on unencrypted protocols, passwords. Treat them under Kanem's data protection rules |
| **What is the minimum I need?** | Capture only the hosts, ports and time window needed to answer the question |
| **Where will the file be stored, and for how long?** | Store captures in a restricted location, encrypt them, and delete them when the case is closed |

## Where to capture

| Point | Sees | Notes |
| --- | --- | --- |
| **On the host** (Wireshark, `tcpdump`) | That host's traffic | Easiest; affected by the host's own problems |
| **Switch mirror port (SPAN)** | Copies of traffic from chosen ports or VLANs | Can drop packets under load |
| **Network TAP** | A full copy of a link | Most faithful; needs hardware |
| **Cloud traffic mirroring** | Copies of virtual network traffic | Provider feature; costs and limits apply |

Capture as close as possible to where the problem appears, and sometimes at **both ends** to see where packets are lost.

## Capture filters versus display filters

- A **capture filter** (BPF syntax, for example `host 10.40.9.21 and port 443`) decides what is **recorded**. Anything excluded is gone for good.
- A **display filter** (Wireshark syntax, for example `ip.addr == 10.40.9.21 && tcp.port == 443`) only hides packets on screen; you can change it at any time.

Use capture filters to respect privacy and limit file size. Use display filters to explore.

## Handling captures as evidence

If a capture may support an incident or disciplinary case (module 9):

1. Record **who** captured, **where**, **when** (UTC) and **why**.
2. Calculate a **hash** (for example SHA-256) of the file straight away and record it.
3. Work on a **copy**; keep the original read-only.
4. Log every transfer of the file (chain of custody).

NIST SP 800-86, *Guide to Integrating Forensic Techniques into Incident Response*, explains these practices in more depth.

:::takeaway
Capture only with authority, for a clear purpose, with the minimum scope. Choose the capture point deliberately, use capture filters to limit what is recorded, protect captures as sensitive data, and hash and log them if they may become evidence.
:::
