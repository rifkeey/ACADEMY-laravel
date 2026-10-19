## Precise language matters

Analysts use three words that are often confused. Using them precisely makes reports clearer and metrics meaningful.

- **Event**: any observable occurrence in a system or network. A user signing in, a file being created, a DNS query. Most events are normal.
- **Alert**: a notification that one or more events matched a detection condition and deserve human attention. An alert is a *hypothesis* that something may be wrong.
- **Incident**: an occurrence that actually or imminently jeopardises the confidentiality, integrity or availability of information or systems, or violates security policy. An incident is a *confirmed* problem that requires response.

A single incident may involve thousands of events and several alerts. Many alerts never become incidents.

## Alert outcomes

When you finish triaging an alert, classify the outcome:

| Outcome | Meaning | Example |
| --- | --- | --- |
| True positive | The rule detected what it was designed to detect, and it is malicious | Password spraying from an external IP |
| Benign true positive | The rule detected the behaviour correctly, but it is authorised | IT admin running PsExec during a maintenance window |
| False positive | The rule fired, but the behaviour it targets did not occur | A "malware download" alert caused by a security vendor's update file |
| False negative | Malicious activity occurred and no alert fired | Discovered later during hunting |

False negatives are the most dangerous category, because nobody sees them. Hunting and detection testing exist to reduce them.

## Telemetry: the raw material

Detection is only as good as the data you collect. The main telemetry families are:

- **Identity**: sign-in logs, MFA events, directory changes.
- **Endpoint**: process creation, network connections per process, file and registry changes (from EDR or tools such as Sysmon).
- **Network**: firewall logs, DNS logs, proxy logs, flow records, IDS alerts.
- **Cloud and SaaS**: control-plane audit logs, storage access logs, application audit trails.
- **Application**: web server access logs, application error logs, database audit logs.

A recurring analyst question is "do we even log that?" Know your coverage before you rely on the absence of evidence.

## Context turns events into evidence

The same event can be harmless or alarming depending on context. A sign-in from Lagos is normal for a Lagos-based employee and suspicious for an account that signed in from London ten minutes earlier. Context comes from asset inventories, HR data (joiners and leavers), change calendars and threat intelligence. Collecting that context is part of the analyst's job, not an optional extra.
