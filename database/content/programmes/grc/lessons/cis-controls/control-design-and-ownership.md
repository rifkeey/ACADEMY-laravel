## What a control is

A **control** is any measure that modifies risk: a policy, a process, a technical setting, a physical barrier, a training programme. A good control is described precisely enough that someone else could check whether it is working.

## Describing a control

A complete control description answers:

| Element | Example: privileged access review |
| --- | --- |
| Objective | Ensure only authorised staff retain administrator rights to core banking |
| Risk(s) treated | R1 unauthorised access via privileged accounts |
| Activity | IT manager exports the admin group membership; each line manager confirms or revokes access |
| Frequency | Quarterly, and on every role change |
| Owner | Head of IT operations (accountable) |
| Operator | IT administrator (performs the export and removals) |
| Evidence | Signed review record, export file with date, tickets for removals |

## Classifying controls

By **purpose**:

- **Preventive**: stops an event (MFA, firewall rules, approval workflows).
- **Detective**: finds an event (log monitoring, reconciliations, access reviews).
- **Corrective**: limits damage and restores (backups and restore procedures, incident response).

By **nature**: administrative (policies, training), technical (configurations, software), physical (locks, guards). Some frameworks also distinguish **deterrent** and **compensating** controls.

A balanced design uses several types. Preventive controls fail eventually; detective controls tell you when they have.

## Manual versus automated

Automated controls operate consistently and produce evidence automatically, but they can be misconfigured in ways nobody notices. Manual controls are flexible but depend on people remembering and doing them properly. When a manual control is performed late or skipped, the evidence trail usually shows it.

## Owners and operators

The **owner** is accountable that the control exists, is designed properly and works. The **operator** performs it. Separating them supports segregation of duties: the administrator who grants access should not be the only person reviewing it.

## Compensating controls

When a required control cannot be implemented, a compensating control reduces the same risk in another way. Example: a legacy application cannot enforce MFA, so access is restricted to a jump server that does, and all sessions are recorded and reviewed weekly. A compensating control must address the same risk to a similar degree and be documented, approved and reviewed, usually through the exception process.

## Defining evidence before you need it

Decide, when you design a control, what evidence it will produce and where it will be kept. Retrofitting evidence the week before an audit is expensive and often impossible. Prefer system-generated, timestamped evidence that covers the whole period over screenshots taken on the day.
