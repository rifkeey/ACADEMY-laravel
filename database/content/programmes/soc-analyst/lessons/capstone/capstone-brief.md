## Capstone overview

The capstone brings together everything in this programme. You will act as the on-shift SOC analyst for **Kanem Logistics Ltd**, a fictional freight company with offices in Lagos and Kano. Kanem's environment, users, IP addresses and incidents are entirely synthetic; any resemblance to a real organisation is coincidental.

## What you will do

1. **Build and test detection logic** (Lab 4) for a behaviour seen in the capstone incident.
2. **Investigate the multi-stage incident** (Lab 6) using sign-in, process, network and DNS telemetry in the KQL workspace.
3. **Document evidence and response actions** as you go, using the case-note structure from Module 9.
4. **Produce a final incident report** in the structure described in Module 9.

## Assessment criteria

The capstone lab is validated automatically on the findings that can be checked objectively:

- the compromised account and the attacker's source IP address;
- the persistence mechanism used;
- the exfiltration destination;
- the ATT&CK tactics represented in the intrusion;
- appropriate severity and containment priorities.

Your written report is not graded by the platform. We strongly recommend that you write it anyway and review it against the checklist below. Your report is the artefact you can show an employer (with the fictional scenario clearly labelled).

## Report checklist

- [ ] One-page executive summary written for a non-technical reader
- [ ] Timeline in UTC with evidence references
- [ ] ATT&CK mapping table with technique IDs and the ATT&CK version used
- [ ] Scope: accounts, hosts and data affected, and what was **not** affected
- [ ] Containment, eradication and recovery actions with timestamps
- [ ] Root cause and contributing control gaps
- [ ] Prioritised recommendations with owners
- [ ] Appendix: indicators of compromise and the KQL queries you used

## Ground rules

All capstone activity takes place inside the AMANIQ simulated environment. Never run the techniques or queries you learn against systems you do not own or are not explicitly authorised to test. See the Acceptable Use Policy for cybersecurity labs.
