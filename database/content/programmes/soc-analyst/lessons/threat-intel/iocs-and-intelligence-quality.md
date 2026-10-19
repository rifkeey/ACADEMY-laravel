## What threat intelligence is for

Threat intelligence is information about threats that has been analysed so that someone can make a better decision. Raw feeds of IP addresses are *data*; intelligence explains who uses them, how, against whom, and how confident we are.

Three levels are commonly distinguished:

- **Strategic**: trends and actor motivations for executives (for example, ransomware groups targeting West African financial services).
- **Operational**: campaigns and their tactics, relevant to planning defences and hunts.
- **Tactical**: indicators and specific techniques, used directly in detection and blocking.

## Indicators of compromise (IOCs)

| Indicator type | Example | Lifetime |
| --- | --- | --- |
| File hash (SHA-256) | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` (this is the hash of an empty file, a classic false IOC) | Long, but trivial for an attacker to change |
| IP address | `198.51.100.77` | Short: infrastructure is rented and recycled |
| Domain | `cdn-update-check.example` | Medium |
| URL | `https://files.example/inv-2026.zip` | Short to medium |
| Email sender / subject | `Subject: Outstanding invoice March` | Short |

The **Pyramid of Pain** (David Bianco) captures a key idea: indicators at the bottom (hashes, IPs) are easy for an attacker to change; behaviours at the top (tools and TTPs) are painful to change. Detections built on behaviour last longer than detections built on indicators.

## Judging intelligence quality

Ask of every indicator:

1. **Source**: who reported it, and how reliable have they been?
2. **Context**: what campaign or actor, and what role did the indicator play (C2, delivery, exfiltration)?
3. **Confidence**: is it confirmed, assessed or speculative?
4. **Age**: when was it observed? IP indicators older than a few weeks are often reassigned to innocent customers.
5. **Relevance**: does it target our sector, region or technology?

## IOC hygiene

- Give every indicator an **expiry date** in your platform. Old IPs cause false positives on innocent cloud tenants.
- Never block shared infrastructure (a major CDN or cloud provider range) based on a single indicator.
- Record **sightings**: an IOC that matched in your environment is far more important than one that did not.
- Watch for defanged formats in reports (`hxxps://files[.]example`) and do not "re-fang" them in places where they might be clicked.

## Pivoting

From one indicator you can often find more: the domain resolves to an IP; other domains resolve to the same IP (passive DNS); a file hash's sandbox report lists the URLs it contacts. Each pivot should be recorded with its confidence, because infrastructure sharing can lead you to unrelated activity.
