A design review needs a structured way to find what could go wrong, and an honest way to decide what is worth spending money on. Threat modelling and explicit trade-offs provide both.

## Threat modelling in four questions

The Threat Modeling Manifesto summarises the process in four questions:

1. **What are we working on?** Draw the system: users, components, data stores, trust boundaries and data flows.
2. **What can go wrong?** Work through each element and flow systematically.
3. **What are we going to do about it?** Choose controls, accept, transfer or avoid each risk.
4. **Did we do a good enough job?** Review the model and revisit it when the design changes.

## STRIDE as a prompt

STRIDE, used in Microsoft's threat modelling guidance, gives six categories to ask about for each element:

| Category | Question | Typical control |
| --- | --- | --- |
| **S**poofing | Can someone pretend to be a user or service? | Strong authentication, workload identities |
| **T**ampering | Can data or code be changed? | Integrity checks, signing, least-privilege write access |
| **R**epudiation | Can someone deny doing something? | Audit logs that users can't change |
| **I**nformation disclosure | Can data leak? | Encryption, access control, no public storage |
| **D**enial of service | Can it be made unavailable? | Rate limits, zones, autoscaling, DDoS protection |
| **E**levation of privilege | Can someone gain more rights? | Least privilege, guardrails, no wildcard policies |

For Kanem Pay, the important trust boundaries are: the internet to the API gateway; the API to the payments database; the pipeline to production; partner banks to the partner API; and staff to the cloud console.

## Making trade-offs explicit

Security, cost, speed and usability pull against each other. Write down the decision, the options and the reasoning, for example as an **architecture decision record (ADR)**:

| Decision | Option A | Option B | Chosen and why |
| --- | --- | --- | --- |
| Disaster recovery for payments | Second region, active-active (high cost, complex) | Multi-zone in one region with tested restore to a second region within 4 hours | B: meets the agreed RTO at a fraction of the cost; revisit if the regulator requires more |
| Keys | External hardware key store | Customer-managed keys in the cloud service | Customer-managed keys: no regulatory requirement for external keys |
| Data-access logging | Every bucket | Only buckets with personal or payment data | Sensitive buckets: most of the value at a fraction of the cost |

The provider Well-Architected frameworks (AWS Security and Cost Optimization pillars, Microsoft Azure Well-Architected security guidance) offer review questions that help structure these conversations.

## A validation checklist

Every design claim should be checkable. For example:

- [ ] No IAM users with access keys exist in any account (query identity reports).
- [ ] A test attempt to stop the organisation trail is denied.
- [ ] A test bucket made public is blocked or remediated within 15 minutes.
- [ ] A restore of the payments database to the recovery account succeeds within the RTO.
- [ ] The partner API rejects a request for another partner's data.

:::takeaway
Threat-model with four questions and STRIDE across the trust boundaries, record trade-offs explicitly with options and reasons, use Well-Architected questions to structure the review, and back every design claim with a check that can be run.
:::
