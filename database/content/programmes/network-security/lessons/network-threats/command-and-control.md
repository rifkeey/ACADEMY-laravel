Once malicious software is running inside a network, it usually needs to **talk to its operator**: to receive instructions and send back results. MITRE ATT&CK calls this **Command and Control** (C2), tactic TA0011. Because the traffic has to leave your network, C2 is one of the best chances defenders have to spot an intrusion, even when they missed how it started.

## How C2 tries to blend in

| Approach (ATT&CK) | Why it is hard to spot |
| --- | --- |
| **Web protocols** (T1071.001) | HTTPS to the internet is the most common traffic of all |
| **DNS** (T1071.004) | DNS is allowed almost everywhere and rarely inspected (module 7) |
| **Protocol tunnelling** (T1572) | Data hidden inside another allowed protocol |
| **Dynamic resolution** (T1568) | Changing domain names or addresses so blocklists fall behind |
| **Encrypted channels** (T1573) | Content can't be inspected without decryption |
| **Web services** (T1102) | Using legitimate cloud and collaboration services as a relay |

## The patterns that give it away

Even when content is hidden, behaviour often isn't:

| Pattern | What to look for | Benign look-alikes |
| --- | --- | --- |
| **Beaconing** | Connections to the same destination at regular intervals, often with similar sizes | Update checkers, monitoring agents, chat apps |
| **New or rare destinations** | Domains first seen recently, or contacted by only one host in the organisation | New SaaS tools or partners |
| **DNS anomalies** | Long random-looking subdomains, high volumes of TXT queries, many failed lookups (module 7) | Some security and CDN services |
| **Odd timing and volume** | Activity at night from a workstation, or steady upload greater than download | Backups, sync clients |
| **Mismatches** | A non-browser process making web requests, unusual TLS client fingerprints, direct connections to IP addresses with no DNS lookup | Some legitimate software |
| **Bypassing controls** | Connections that skip the proxy or internal resolvers | Misconfigured devices |

No single pattern proves C2. Several together on one host (for example regular beacons to a three-day-old domain, from a laptop, at 02:00, bypassing the proxy) should be escalated (module 7, lesson 5).

## Making C2 hard and visible

| Control | Effect |
| --- | --- |
| **Egress filtering**: default deny outbound, only through proxies and resolvers (module 3) | C2 must use the routes you watch |
| **Proxy or secure web gateway with categorisation** | Newly registered and uncategorised domains can be blocked or flagged |
| **Protective DNS** and internal-only resolvers (module 7) | Known-bad domains blocked; DNS logged |
| **Selective TLS inspection** where justified (module 5) | Content-based detection for high-risk categories |
| **Endpoint detection** | Ties network connections to the process that made them |
| **Detections for beaconing and rare destinations** in the SIEM (module 9) | Finds behaviour that signatures miss |
| **Threat intelligence feeds** | Known C2 infrastructure blocked, with the limit that it changes quickly |

:::takeaway
C2 has to leave your network, so watch the exits. Expect it to hide in web and DNS traffic, use encryption and change destinations. Look for beaconing, rare new destinations, DNS anomalies, odd timing and volume, mismatches and control bypass, and escalate when several line up. Force outbound traffic through proxies and resolvers you monitor.
:::
