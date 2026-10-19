## Severity is not priority

**Severity** describes how harmful an incident is or could be. **Priority** describes the order in which you work on things. They usually align, but not always: a medium-severity issue on a system that processes payroll tonight may be worked before a high-severity issue on an isolated test server.

## A practical severity model

Most organisations define four or five levels. A reasonable starting point:

| Severity | Typical criteria | Initial response target |
| --- | --- | --- |
| Critical | Active compromise of critical systems or sensitive data; ransomware spreading; confirmed data exfiltration | Immediate, 24×7, incident commander assigned |
| High | Confirmed compromise of a single host or account; likely spread | Within 1 hour |
| Medium | Suspicious activity with plausible malicious explanation; policy violation with limited impact | Same business day |
| Low | Informational; hardening opportunity; failed attack with no impact | Within agreed backlog window |

The response targets above are illustrative. Your organisation's incident response plan and any contractual obligations define the real values.

## Factors that raise or lower severity

Ask these questions in order:

1. **Is it confirmed?** Unconfirmed alerts rarely justify the top severity.
2. **What asset is involved?** Domain controllers, identity providers, payment systems and backups weigh heavily.
3. **What data is at risk?** Personal data, financial data and credentials raise severity and may trigger legal reporting duties.
4. **Is it spreading or persistent?** Lateral movement or established persistence indicates an active intruder.
5. **What is the business impact now?** Outages and fraud in progress demand immediate action.

## Escalation

Escalation is not failure. It is the mechanism that brings the right authority and expertise to a problem. Escalate when:

- the severity exceeds what you are authorised to handle;
- containment requires an action you are not permitted to take (for example disabling a privileged account);
- legal, regulatory or reputational consequences are possible;
- you are stuck. Time-boxing investigations prevents a single analyst from silently holding a critical case.

A good escalation message is short and complete:

```text
SEVERITY: High (proposed)
WHAT: Successful sign-in to f.manager@example.org after 212 failed attempts from 203.0.113.45
WHEN: 2026-03-04 02:14 UTC (first failure 01:52 UTC)
SCOPE SO FAR: 1 account confirmed; 14 other accounts targeted, no other successes found
ACTIONS TAKEN: Sessions revoked, password reset requested via service desk ticket INC-4471
NEED: Approval to block 203.0.113.0/24 at the perimeter; finance manager contact
```

Notice the structure: severity, facts, scope, actions, and the specific decision you need. Decision-makers should never have to read a wall of log lines to understand what you are asking for.
