DNS logs record which names every device looked up. Because most connections start with a lookup, they are one of the most useful sources for spotting compromise, and they stay useful when traffic itself is encrypted.

## What DNS logs contain

| Field | Example |
| --- | --- |
| Time | 2026-03-09T03:31:12Z |
| Client | 10.20.7.44 |
| Query name | portal.kanem.example |
| Query type | A, AAAA, TXT, MX … |
| Response | 10.10.0.15, or an error such as NXDOMAIN (name does not exist) |

## Indicators worth investigating

These patterns are **reasons to look closer**, not proof. Each has benign explanations.

| Indicator | Why it can matter | Common benign causes |
| --- | --- | --- |
| Lookups for **newly registered** or **rare** domains | Attackers often use fresh infrastructure | New partners, new SaaS tools |
| **Regular, machine-like** lookups to the same domain | Automated check-ins by unwanted software | Update checkers, monitoring agents |
| **Unusually long or random-looking** subdomains, or unusual volumes of TXT queries to one domain | Data may be encoded into DNS traffic (DNS used as a tunnel; ATT&CK T1071.004 and T1572 describe these behaviours) | Some security and content-delivery services use long names |
| Many **NXDOMAIN** responses from one host | Software trying many generated names to find its server (ATT&CK T1568, Dynamic Resolution) | Misconfigured applications, typos in scripts |
| Hosts querying **external resolvers directly** | Bypassing internal logging and filtering | Misconfigured devices, personal apps |

## Turning indicators into a decision

Ask the same questions as for any alert (module 5):

1. **Which host and user?** Asset owner, zone, criticality.
2. **What else is that host doing?** Firewall and proxy logs for the same time; endpoint alerts.
3. **Is the domain known?** Ownership, age, reputation, whether colleagues recognise it.
4. **Is it new behaviour?** Has this host done it before?
5. **Decide**: benign (document), suspicious (escalate with evidence), or unclear (escalate).

One indicator on its own rarely justifies escalation. **Several indicators on the same host** (long random subdomains, plus regular timing, plus a new domain, plus unusual outbound traffic) usually do.

## Making DNS logging useful

- Log at the **internal resolvers**, including client address and response.
- Keep logs long enough to look back after an incident is discovered.
- Feed them to the SIEM and correlate with DHCP leases (to identify the device) and firewall logs.
- Make sure devices actually **use** the internal resolvers (previous lesson), or the logs will be incomplete.

:::takeaway
DNS logs show what every device looked up and stay useful when traffic is encrypted. Treat new or rare domains, machine-like regularity, long random subdomains, bursts of failed lookups and direct use of outside resolvers as reasons to investigate; escalate when several indicators line up on one host.
:::
