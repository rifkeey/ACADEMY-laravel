## IDS and IPS

An **intrusion detection system (IDS)** inspects traffic and raises alerts. An **intrusion prevention system (IPS)** sits inline and can block traffic as well. Open-source examples include Suricata and Snort; Zeek produces rich network metadata logs rather than traditional alerts. NIST SP 800-94 covers IDPS concepts and deployment.

## Detection approaches

| Approach | How it works | Strength | Weakness |
| --- | --- | --- | --- |
| Signature-based | Matches known patterns | Precise for known threats | Misses new or modified attacks |
| Anomaly / behavioural | Compares traffic to a baseline | Can find unknown threats | More false positives; needs tuning |
| Protocol analysis | Checks traffic follows protocol rules | Catches malformed or evasive traffic | Limited to understood protocols |

## Interpreting an alert

An IDS alert usually includes the rule or signature, source and destination, ports, time, and sometimes a packet excerpt. Before acting, ask:

1. What does the signature actually detect? Read its documentation or rule text.
2. Is the target vulnerable? An exploit attempt against a system that does not run the targeted software is noise, though the scanning itself may still be worth noting.
3. Did the attack succeed? Look for follow-on activity from the target.
4. Is the source internal? Internal sources may be compromised hosts or legitimate scanners.

## Placement and blind spots

Sensors only see traffic that passes them. Common blind spots: east-west traffic between servers on the same segment, encrypted traffic (unless decrypted at a proxy), cloud workloads and remote users who do not route through the corporate network.

## Tuning

Tune noisy signatures by understanding why they fire. Suppress for specific, documented sources rather than disabling rules entirely, and review suppressions periodically, exactly as you would SIEM exclusions.
