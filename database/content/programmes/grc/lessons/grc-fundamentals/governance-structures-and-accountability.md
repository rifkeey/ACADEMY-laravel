## Who decides, who does, who checks

Security fails quietly when nobody is sure who owns a decision. Governance structures exist to answer three questions for every important security matter: **who decides**, **who does the work**, and **who checks that it was done well**.

## Typical governance bodies

| Body | Role in security governance |
| --- | --- |
| Board of directors | Sets risk appetite, approves top-level policy, holds management to account. Accountable in law and to regulators. |
| Board risk or audit committee | Receives detailed risk and assurance reports; challenges management on behalf of the board. |
| Executive management | Owns business risks, funds controls, approves exceptions within delegated authority. |
| Security steering committee | Cross-functional forum (IT, operations, legal, HR, business units) that prioritises security work and resolves conflicts. |
| CISO or head of security | Advises, sets standards, runs the security programme and reports on risk. Usually not the owner of business risks. |
| Internal audit | Provides independent assurance to the board that controls work. |

Small organisations combine these roles. A 40-person microfinance company may have no steering committee, but someone must still approve policy, someone must own each risk, and someone independent should occasionally check.

## Accountability versus responsibility

- **Accountable**: the single person who answers for the outcome and has authority to decide. There should be exactly one.
- **Responsible**: the people who do the work. There can be several.

A **RACI** matrix (Responsible, Accountable, Consulted, Informed) makes this explicit:

| Activity | CEO | CISO | IT manager | HR |
| --- | --- | --- | --- | --- |
| Approve access-control policy | A | R | C | C |
| Operate joiner-mover-leaver process | I | C | R | A |
| Accept a high residual risk | A | C | I | I |

Two common mistakes: making the CISO accountable for risks the business creates and controls, and giving one activity several "A"s so that nobody really owns it.

## Segregation of duties

No single person should be able to complete a sensitive action end to end without a check. The person who requests a payment should not approve it; the administrator who grants privileged access should not also review it. Where staff numbers make full segregation impossible, use compensating controls such as independent log review.

## Delegated authority

Governance documents should state who may approve what. For example: risks rated 15 or above may only be accepted by the CEO and reported to the board; risks rated 8 to 14 may be accepted by a director. Without delegated authority, either everything escalates to the board or junior staff quietly accept serious risks.
