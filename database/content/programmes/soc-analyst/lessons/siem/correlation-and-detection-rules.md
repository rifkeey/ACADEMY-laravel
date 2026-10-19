## From search to detection

A detection rule is a saved query plus scheduling and alerting logic. It runs on a schedule (for example every 5 minutes over the last 10 minutes of data), and creates an alert when its results meet a condition.

Every rule should be documented with:

- **Purpose**: the behaviour it detects, in one sentence.
- **Data sources**: the tables or log types it needs.
- **Logic**: the query and thresholds.
- **ATT&CK mapping**: the technique or techniques it covers.
- **Known false positives**: what legitimate activity looks similar.
- **Triage guidance**: the first three things an analyst should check.
- **Owner and review date**.

## Types of detection logic

| Type | Description | Example |
| --- | --- | --- |
| Atomic / signature | A single event matches known-bad attributes | A process hash matches a known ransomware sample |
| Threshold | A count exceeds a limit within a time window | More than 20 failed logons for one account in 10 minutes |
| Correlation | Different events in sequence or combination | Many failures followed by a success from the same IP |
| Anomaly / baseline | Behaviour deviates from the entity's history | A user downloads 50× their normal data volume |

Correlation rules are usually higher fidelity than threshold rules because they describe attacker *progress*, not just noise. For example, "failed logons" alone is noisy; "failed logons followed by a successful logon from the same source, followed by a new inbox forwarding rule" is a strong indicator of account takeover.

## Writing a correlation rule in plain language first

Before writing a query, write the logic as sentences:

```text
For each source IP in the last 1 hour:
  count failed sign-ins (distinct target users)
  find any successful sign-in from that same IP
Alert when: distinct failed users >= 10 AND at least one success exists
Output: source IP, list of targeted users, the successful user, first and last timestamps
```

This makes peer review possible, and the query becomes a translation exercise rather than an act of invention.

## Windows of time

Two windows matter. The **lookback** (how far back each run searches) must exceed the **frequency** (how often the rule runs) plus expected ingestion delay, or events will slip between runs. A rule that runs every 5 minutes with a 5-minute lookback will miss events that arrive 3 minutes late. Overlapping windows can create duplicate alerts, so SIEMs provide suppression or grouping settings to handle that.
