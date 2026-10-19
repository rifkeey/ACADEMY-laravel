## The assessment you will perform

The capstone asks you to assess a fictional organisation from end to end. Real engagements follow a similar method, whether performed by an internal GRC team or an external consultant.

## 1. Agree the engagement

Write a one-page **terms of reference**: objective (for example "assess information security risk to the mobile lending service and recommend a 12-month plan"), scope, stakeholders, sources of information, timeline and deliverables. Agree the risk criteria and the board's appetite statement at the start; without them, ratings cannot be challenged or defended.

## 2. Gather context

- **Documents**: policies, network and data-flow diagrams, previous audit reports, incident history, supplier list, obligations.
- **Interviews**: business owners (what matters and what would hurt), IT (how things really work), compliance (what must be met).
- **Technical data**: asset inventory, vulnerability scan summaries, MFA coverage, backup reports.

Record each fact with its source. Interview notes such as "IT manager, 4 March: backups never test-restored" are evidence you will cite later.

## 3. Identify and rate risks

Write ten to fifteen specific scenarios. Rate inherent and residual risk against the agreed criteria, documenting your reasoning for every rating. Where you are uncertain, say so and give the range. Have a colleague rate two or three scenarios independently; large differences show where your criteria are unclear.

## 4. Assess controls

For the controls that matter to your top risks, assess design and, where evidence allows, operation. Map them to CSF 2.0 Categories and CIS Controls so that the organisation can report consistently. Note where the mapping is partial.

## 5. Build the remediation plan

| Priority | Action | Risks addressed | Owner | Effort and cost | Horizon |
| --- | --- | --- | --- | --- | --- |
| 1 | MFA for all administrators and remote access | R1, R6 | IT manager | Low; licences owned | 30 days |
| 2 | Quarterly restore testing for core systems | R2 | IT manager | Medium | 90 days |
| 3 | Supplier contract renegotiation (breach notice, audit rights) | R4 | Head of operations | Medium; legal time | 6 months |

Order actions by risk reduction per unit of effort, respect dependencies (you cannot monitor logs you do not collect), and include target residual ratings so progress can be measured.

## 6. Report

Use the executive reporting structure from the previous lesson: headline, risk position, decisions required, plan, appendix. Separate facts, assessments and recommendations so readers can see which is which.

## Quality checks before you submit

- Does every high rating trace to evidence?
- Does every recommendation trace to a risk?
- Is every risk owned by a business person, not "IT"?
- Would a sceptical board member understand the headline in thirty seconds?
- Have you avoided promising certainty you do not have?
