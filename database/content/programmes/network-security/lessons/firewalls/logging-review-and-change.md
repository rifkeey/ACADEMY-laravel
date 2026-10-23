A firewall rule set is not something you design once. Applications come and go, vendors need access, people make "quick fixes". Without logging, regular review and controlled change, every rule set drifts towards "allow everything". This lesson covers the operational side that keeps a policy honest.

## What to log

| Log | Why it matters |
| --- | --- |
| **Denied connections**, especially on the default deny | Reveals scanning (ATT&CK T1046), malware trying to call out, and misconfigured applications |
| **Allowed connections across sensitive boundaries** (into servers, out of servers, into management) | Lets investigators reconstruct what an attacker reached |
| **Administrative actions on the firewall itself** (logins, rule changes) | Shows who changed the policy and when; the firewall is a high-value target |
| **System events** (failover, high CPU, dropped logs) | A firewall that silently stops logging is a blind spot |

A typical connection log entry carries enough to answer *who talked to whom, how, and what the firewall decided*:

```text
2026-03-04T02:13:41Z FW-HQ-01 action=deny rule=12 proto=tcp
  src=10.10.0.14:49822 dst=203.0.113.77:4444 zone_src=servers zone_dst=internet
  bytes=0 reason=policy-deny
```

Send firewall logs to a central log platform or SIEM with accurate time (NTP) on every device. NIST SP 800-92 is the long-standing reference for log management; the Network Security Monitoring module goes deeper.

:::warning Logging off on the default deny
Some teams disable logging on the final deny rule "because it's noisy". Those are exactly the events that show an attacker probing your network. Tune the noise (for example, by not logging known-harmless broadcast traffic) instead of switching the evidence off.
:::

## Reviewing the rule set

Review on a schedule (many organisations do it quarterly for critical firewalls) and after major changes. For each rule ask:

1. **Is it still needed?** Check the owner and the ticket. Rules for decommissioned systems should go.
2. **Is it being used?** Hit counters over a meaningful period (for example 90 days) identify unused rules. An unused allow rule is pure risk.
3. **Is it as narrow as it can be?** Any `any`? Could the source or port be smaller?
4. **Is it in the right place?** Shadowed and redundant rules (lesson 2).
5. **Is it logged?** At least the sensitive boundaries and the default deny.
6. **Does it match the design?** For example, nothing from user or guest zones into management.

Record the outcome of the review (rule kept, changed or removed, and why). The record is evidence for audits and, more importantly, for the next reviewer.

## Change management

Firewall changes can break the business (blocking a payment flow) or expose it (opening the wrong port). Treat them like any other production change. NIST SP 800-53 Rev. 5 groups this under configuration change control (CM-3): changes are proposed, reviewed, approved, tested, documented and recorded.

A good firewall change request answers:

| Section | Content |
| --- | --- |
| Summary | What is changing, in one sentence |
| Rules affected | Rule numbers and before/after definitions |
| Business justification | The need, the owner, the ticket |
| Risk of not changing | Why this matters now |
| Business impact | Who or what could be affected, and when the change will happen |
| Testing and validation | How you will confirm it works and nothing else broke (test connections, hit counters, monitoring) |
| Rollback plan | Exactly how to reverse it if something goes wrong |
| Approval | Who approved it |

:::tip Removing a risky rule that something depends on
A broad rule with many hits cannot just be deleted. Use the logs to see which sources, destinations and ports actually use it, write narrow rules for the legitimate traffic, place them above the broad rule, then disable (not delete) the broad rule for an agreed period with logging on. If nothing legitimate breaks, remove it. This is how the lab's "temporary vendor rule" should be retired.
:::

## Standards that ask for this

- **CIS Critical Security Controls v8.1**: Control 12 (Network Infrastructure Management) covers keeping network infrastructure securely configured and documented; Control 13 (Network Monitoring and Defense) covers collecting and alerting on network traffic logs.
- **NIST SP 800-53 Rev. 5**: SC-7 (boundary protection), CM-3 (configuration change control) and the AU family (event logging and review).

These frameworks tell you *what* good looks like; the review checklist above is *how* you do it on a real firewall.

:::takeaway
Log the denies and the sensitive allows, review every rule against an owner, its usage and the design, and change rules through a documented process with testing and a rollback plan.
:::
