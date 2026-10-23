:::scenario 400 alerts before lunch
Kanem's new network sensor went live on Monday. By lunchtime it had raised 400 alerts. The SOC lead asks you two questions: "Which of these matter?" and "How do we make tomorrow better than today?" This module gives you the answers: how network detection works, why it is noisy, and how to make it useful.
:::

Firewalls decide what is **allowed**. Intrusion detection asks a different question: **of the traffic we allow (and some we block), what looks like an attack or a compromise?** NIST SP 800-94, *Guide to Intrusion Detection and Prevention Systems*, describes the technologies; this lesson covers the ideas you need to work with any of them.

## Detection methods

| Method | How it decides | Strengths | Weaknesses |
| --- | --- | --- | --- |
| **Signature-based** | Matches traffic against patterns of known threats | Precise for known threats; easy to explain an alert | Misses new or modified threats; needs constant updates |
| **Anomaly-based** | Compares traffic with a learned or defined baseline of "normal" | Can catch the unknown | Baselines are hard to get right; more false positives; alerts are harder to explain |
| **Stateful protocol analysis** | Checks that protocols behave as their specifications say | Catches protocol misuse and evasion attempts | Needs deep protocol knowledge; resource-hungry |

Real products combine them. Open-source examples you will meet in many SOCs: **Suricata** (signature-based detection and prevention with protocol parsing) and **Zeek** (a network monitoring framework that writes detailed protocol logs and supports scripted detection).

## Detection or prevention?

| | IDS (detection) | IPS (prevention) |
| --- | --- | --- |
| Placement | Receives a **copy** of traffic (from a mirror port or network tap) | Sits **inline**: traffic passes through it |
| Action | Alerts | Alerts **and** can block, reset or drop traffic |
| Risk of a mistake | A false positive wastes analyst time | A false positive **blocks legitimate business traffic** |
| Failure mode | If it fails, traffic still flows (unmonitored) | Must be designed to fail open or closed deliberately |

Many organisations run prevention only for high-confidence signatures and detection for everything else.

## Network and host

| | Network-based (NIDS/NIPS) | Host-based (HIDS, endpoint detection) |
| --- | --- | --- |
| Sees | Traffic on the monitored links | Processes, files, logins and connections on one device |
| Blind to | Encrypted content; traffic that never crosses the sensor | Other devices; the network as a whole |

They complement each other. Network detection is valuable precisely because it does not rely on the compromised device telling the truth about itself.

## What an alert contains

```text
2026-03-10T09:14:22Z  sensor=NS-HQ-01  severity=medium
signature="Policy: outbound connection to newly registered domain"
src=10.20.5.12:51022  dst=203.0.113.9:443  proto=TCP  app=tls
sni=update-checker.example  action=alert
```

*(Synthetic and illustrative.)* An alert is a **hypothesis**: "this might be bad". Everything in lessons 2 and 4 is about deciding whether it is.

:::takeaway
Network detection inspects allowed (and blocked) traffic for signs of attack using signatures, anomalies and protocol analysis. IDS watches a copy and alerts; IPS sits inline and can block, so its mistakes cost more. Combine network and host detection, and treat every alert as a hypothesis to test.
:::
