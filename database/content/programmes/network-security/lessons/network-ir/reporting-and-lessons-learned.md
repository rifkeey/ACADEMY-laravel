An incident is not finished when the intruder is out. The report records what happened for management, auditors and regulators, and the review turns the incident into improvements.

## The incident report

| Section | Contents |
| --- | --- |
| **Executive summary** | Half a page in plain language: what happened, impact, current status, key actions |
| **Timeline** | UTC times from first evidence to closure, each with its source |
| **Scope and impact** | Systems, accounts and data affected; volume of data; business impact |
| **Root cause** | How the intruder got in and why the controls didn't stop them |
| **Response** | Detection, containment, eradication and recovery actions, with times |
| **Evidence** | What was preserved, where, with hashes |
| **Gaps and unknowns** | What the evidence could not show, and why |
| **Recommendations** | Each with an owner, priority and date |

Write facts precisely and separate them from interpretation, as in module 8. Use the same times and names throughout.

## Metrics worth tracking

| Metric | Meaning |
| --- | --- |
| **Time to detect** | From the first malicious activity to the first alert or report |
| **Time to contain** | From detection to the harm being stopped |
| **Time to recover** | From detection to normal service |
| **Dwell time** | How long the intruder was present before detection |

Watch the trend over many incidents rather than judging one.

## The lessons-learned review

Hold it within two weeks, **blameless**: the aim is to improve systems, not to punish people.

1. What happened, and when did we know?
2. What worked well?
3. What slowed us down or nearly went wrong?
4. Which controls failed or were missing?
5. What will we change? (Owner, date.)

Typical outcomes link back to earlier modules: retire temporary firewall rules (module 3), log every boundary-crossing allow rule, add DMZ-to-server-zone detections (module 5), protect DNS (module 7), and practise with exercises.

:::takeaway
Report with a clear summary, a sourced UTC timeline, scope and impact, root cause, response, evidence, honest gaps and owned recommendations. Track time to detect, contain and recover, and run blameless reviews that end in specific, owned changes.
:::
