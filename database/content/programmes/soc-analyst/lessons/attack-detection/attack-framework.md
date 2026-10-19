## A shared language for adversary behaviour

MITRE ATT&CK is a publicly available knowledge base of adversary tactics and techniques, based on real-world observations. It gives defenders a common vocabulary: instead of "the attacker did something with scheduled tasks", you can write *T1053.005 Scheduled Task/Job: Scheduled Task* and every reader knows precisely what you mean.

ATT&CK is updated regularly and is versioned. When you map detections or report findings, record the ATT&CK version you used, because technique IDs and names occasionally change.

## Tactics, techniques and procedures

- **Tactic**: the adversary's *goal* at a stage, such as Initial Access, Persistence, Privilege Escalation, Credential Access, Lateral Movement or Exfiltration.
- **Technique**: *how* they achieve the goal, for example T1110 Brute Force. Many techniques have **sub-techniques** (T1110.003 Password Spraying).
- **Procedure**: the specific implementation observed, such as "used a list of 3 seasonal passwords against all accounts in the directory from a VPS".

Procedures are what you see in logs. Techniques are how you generalise from them to detection coverage.

## Mapping evidence to ATT&CK

Take the timeline from the previous module and map each step:

| Observed behaviour | Tactic | Technique |
| --- | --- | --- |
| Many accounts tried with few passwords | Credential Access | T1110.003 Password Spraying |
| Successful sign-in with the guessed password | Initial Access / Persistence | T1078 Valid Accounts |
| Inbox forwarding rule to an external address | Collection | T1114.003 Email Collection: Email Forwarding Rule |
| Bulk download from SharePoint | Collection | T1213 Data from Information Repositories |

Mapping is a judgement. Check the technique's description and detection guidance on the ATT&CK site rather than relying on its name alone.

## Coverage and gaps

ATT&CK is also a planning tool. For each relevant technique, ask:

1. Do we collect the data that would reveal it?
2. Do we have a detection for it?
3. Has that detection been **tested**?

A coverage map that only records "we have a rule" overstates protection. Distinguish *data available*, *detection exists* and *detection validated*. Prioritise techniques used by threat groups relevant to your sector and region rather than trying to cover the whole matrix evenly.

## Cautions

- ATT&CK describes behaviours; it is not a maturity score. "We cover 70% of ATT&CK" is not a meaningful statement on its own.
- Some techniques overlap heavily with legitimate administration (for example PowerShell). Detection there relies on context, not on the technique alone.
