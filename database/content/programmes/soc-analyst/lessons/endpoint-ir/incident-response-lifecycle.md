## A structured response

Incident response turns a confirmed incident into a controlled outcome. NIST SP 800-61 Rev. 3 (2025) organises incident response around the NIST Cybersecurity Framework 2.0 Functions: preparation activities sit under **Govern, Identify and Protect**; detection and analysis under **Detect**; and the response and recovery work under **Respond** and **Recover**. The earlier Rev. 2 described a four-phase life cycle (preparation; detection and analysis; containment, eradication and recovery; post-incident activity) that you will still see in many organisations' plans. The concepts below apply to both.

## Containment

Containment limits damage while you investigate. Choose a strategy based on the threat and the evidence you need:

| Action | Effect | Trade-off |
| --- | --- | --- |
| Isolate host via EDR | Cuts network access except to the EDR console | Fast; preserves disk and memory; user loses work |
| Disable account / revoke sessions | Stops use of stolen credentials | May disrupt business; attacker may have other accounts |
| Block IP or domain | Interrupts C2 | Easy for attacker to change infrastructure |
| Reset credentials | Invalidates stolen passwords | Must include service accounts and tokens; do it after scoping |
| Segment network | Stops lateral movement | Requires network team; broad impact |

**Scope before you reset.** If an attacker holds five accounts and you reset one, they learn they have been discovered and keep the other four. Coordinate containment actions so that they happen together where possible.

## Eradication

Remove the attacker's footholds: malware, persistence mechanisms, unauthorised accounts, malicious mail rules, OAuth applications, and the vulnerability or misconfiguration that let them in. Use the persistence checklist from the previous lesson systematically.

## Recovery

Restore systems to normal operation from known-good sources, monitor closely for re-entry, and only declare recovery complete when monitoring shows no further malicious activity for an agreed period. Backups must be verified as clean before restoration.

## Post-incident activity

Within a short time of closure, hold a blameless lessons-learned review:

- What happened, and when did we detect it relative to when it started?
- What worked well?
- Which detection, logging, process or control gaps did we find?
- Which actions, with owners and dates, will close them?

The value of incident response is realised in this step. Organisations that skip it tend to experience the same incident again.

## Legal and regulatory considerations

Some incidents trigger legal duties, such as notifying a data protection regulator or affected individuals within a defined period, or reporting to a sector regulator. In Nigeria, the Nigeria Data Protection Act 2023 includes breach-notification obligations overseen by the Nigeria Data Protection Commission. Analysts do not make these decisions, but they must escalate promptly and preserve the evidence that legal and compliance teams will need.
