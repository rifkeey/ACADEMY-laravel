Triage turns an alert into a decision. Tuning turns many decisions into a better detection. Done together, they are how Monday's 400 alerts become a manageable queue.

## A triage routine

For every alert, answer these questions in order and write the answers down:

1. **What does the rule detect, and what would a true positive look like?** Read the rule description before the data.
2. **Who is involved?** Look up the source and destination: system, owner, zone, criticality (address plan, asset inventory).
3. **Is this expected?** Is the source an authorised scanner, a monitoring system, a known business application? Is there a change ticket?
4. **What else happened around it?** Check flow logs, DNS, authentication and endpoint data for the same hosts in the same time window.
5. **Decision**: true positive (escalate), benign true positive (document why it is authorised), or false positive (document why the rule was wrong).

| Disposition | Next step |
| --- | --- |
| True positive | Escalate to incident handling with the evidence gathered (module 9) |
| Benign true positive | Close with the reason; consider a tuning exception for that specific source and rule |
| False positive | Close with the reason; propose a rule change |
| Can't decide | Escalate with what you found; don't close it as "nothing" |

## Tuning without creating blind spots

Tuning reduces noise. Done badly, it also removes the alerts you needed. Prefer the **narrowest** change that fixes the problem:

| Technique | Example | Risk if done badly |
| --- | --- | --- |
| **Scoped exception** | Suppress "internal port scan" only when the source is the vulnerability scanner 10.99.0.20 during its scheduled window | Scope too wide → an attacker on another host is also suppressed |
| **Thresholds** | Alert on 20 failed connections per minute, not 1 | Threshold too high → slow, quiet activity is missed |
| **Context enrichment** | Raise severity when the destination is in the cards zone | None, if asset data is accurate |
| **Rewriting the rule** | Make the pattern more specific | Needs testing against known-good and known-bad examples |
| **Disabling** | Turn the rule off | Creates a blind spot: only with a documented reason and a replacement or accepted risk |

Every tuning change should be **documented** (what, why, who approved, review date) and **reviewed**: exceptions for a scanner that has since moved are a classic way to lose visibility.

## Walkthrough: three alerts

| Alert | Evidence | Decision |
| --- | --- | --- |
| Internal port scan from 10.99.0.20 to the server zone, 02:00 | 10.99.0.20 is the vulnerability scanner; change ticket CHG-310 schedules it nightly 01:30–03:30 | Benign true positive → scoped exception for that source, rule and window |
| Outbound connection to newly registered domain from 10.20.5.12 | Laptop of a marketing user; domain belongs to a new partner announced last week | Benign; add the partner domain to the allow-list after verifying ownership |
| Internal port scan from 10.20.7.44, 02:55 | VPN pool address; no ticket; same host has repeated outbound connections to an unknown external address every five minutes | **True positive** → escalate |

The rule that fired for the first and third alerts is the same. Only context separates them, which is why the exception must be scoped to the scanner, not to the rule.

:::takeaway
Triage each alert with a fixed set of questions, record the disposition and reason, and escalate when in doubt. Tune with the narrowest change that removes the noise, document and review every exception, and never disable a rule without accepting the blind spot explicitly.
:::
