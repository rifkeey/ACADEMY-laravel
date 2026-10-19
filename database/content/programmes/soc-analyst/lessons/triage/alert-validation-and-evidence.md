## The triage mindset

Triage is a disciplined decision under time pressure: *is this alert real, how serious is it, and what happens next?* Good triage is fast because it follows the same sequence every time, not because the analyst guesses well.

## A repeatable triage sequence

1. **Read the alert definition.** What behaviour is the rule designed to detect? What are its known false positives? If the rule has triage guidance, follow it.
2. **Identify the entities.** User, host, IP address, process, file hash, URL, cloud resource. Every later step pivots on these.
3. **Validate the trigger.** Open the raw events that caused the alert. Do they really show the behaviour described? Rules can match on malformed or partial data.
4. **Gather context.** Who is the user and what is their role? Is the host a server or a laptop? Is there a change ticket? Has this entity alerted before?
5. **Look left and right in time.** What happened on the same host or account in the 30 minutes before and after?
6. **Decide and document.** Classify the outcome, set severity, and either close with a reason or escalate.

## What counts as evidence

Evidence is information you can point to and someone else can verify. For each conclusion, record:

- the **query or tool** used;
- the **time range** searched;
- the **result** (including "no results");
- your **interpretation**, labelled as interpretation.

```text
14:05 UTC – Queried SigninLogs for IPAddress == "203.0.113.45", 2026-03-04 00:00–06:00 UTC.
          Result: 212 failures across 15 users, 1 success (f.manager@example.org, 02:14:09).
          Interpretation: password spraying with one successful compromise.
```

Separating facts from interpretation makes your work reviewable. If a later finding changes the interpretation, the facts remain valid.

## Preserve before you change

Some response actions destroy evidence. Rebooting a host erases memory; reimaging erases the disk; deleting a malicious email removes the headers you needed. Before containment that alters a system, confirm with your incident lead whether evidence must be collected first. NIST SP 800-61 Rev. 3 frames incident response within overall cybersecurity risk management. Its emphasis on preparation and documented procedures is exactly what prevents evidence being lost in a hurry.

## Common triage traps

- **Anchoring**: deciding early that it is a false positive and only looking for confirming evidence.
- **Alert-only scope**: investigating only the entity in the alert, when the attacker has already moved elsewhere.
- **Trusting a single source**: an EDR "remediated" status does not prove the attacker's access has ended.
- **Silent closure**: closing without a reason, which makes tuning impossible.
