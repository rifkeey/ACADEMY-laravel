Triage decides how serious an event is and what happens next. NIST SP 800-61 Rev. 3 frames incident response as part of overall cybersecurity risk management; in practice, triage is where that risk judgement is made, quickly and with incomplete information.

## From event to incident

| Term | Meaning |
| --- | --- |
| **Event** | Anything observable: a log line, an alert |
| **Alert** | An event a detection flagged for attention |
| **Incident** | An event or series of events that threatens confidentiality, integrity or availability and needs a coordinated response |

Declaring an incident starts the formal process: an incident lead, a record, regular updates. Declaring early is cheap; declaring late is expensive.

## A severity matrix

| | Low impact (one non-critical system) | Medium (one important system or limited data) | High (critical systems, restricted data, many users) |
| --- | --- | --- | --- |
| **Confirmed** | Medium | High | Critical |
| **Likely** | Low | Medium | High |
| **Possible** | Low | Low | Medium |

Raise severity when the host is internet-facing, holds restricted data, has administrative access, or when there are signs that data is leaving.

## The first 30 minutes

1. **Confirm** the key facts from at least two sources (for example IDS alert and flow records).
2. **Scope** roughly: which hosts, which accounts, which time window, is it still happening?
3. **Classify and set severity** using the matrix.
4. **Escalate** with a clear summary (Network module 7, lesson 5): what, who, when, evidence, why it matters, what is unknown.
5. **Recommend** immediate containment if harm is ongoing (next lesson).

## Roles and communication

| Role | Responsibility |
| --- | --- |
| Incident lead | Decisions, priorities, timeline |
| Network team | Firewall, routing and NAC changes |
| System owners | Server knowledge, credential resets |
| SOC analysts | Investigation and monitoring |
| Management and communications | Business decisions, internal and external messages |
| Legal and DPO | Notification duties |

Keep a single incident record with a timeline of findings and decisions. Share sensitive details only with people who need them; schemes such as the **Traffic Light Protocol** (TLP), maintained by FIRST, label how far information may be shared.

:::takeaway
Triage confirms facts from more than one source, scopes roughly, sets severity from confidence and impact, and escalates with a clear summary. Declare incidents early, run them through a lead and a single record, and control who sees sensitive details.
:::
