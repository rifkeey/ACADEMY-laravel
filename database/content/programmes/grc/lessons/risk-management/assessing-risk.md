## The building blocks

- **Asset**: anything of value: information, systems, people, reputation.
- **Threat**: a potential cause of harm (a criminal group, a flood, a careless insider).
- **Vulnerability**: a weakness a threat could exploit (no MFA, an untested backup).
- **Impact**: the harm if the risk occurs.
- **Likelihood**: how probable it is within a defined period.

A risk statement links them: *"Because staff share the core banking admin password (vulnerability), a phishing attacker (threat) could gain administrative access to customer accounts (asset), causing fraud losses and regulatory sanction (impact)."*

## Rating risk

Many organisations use a 5×5 matrix and multiply likelihood by impact to get a score from 1 to 25. This is simple and widely understood, but remember that it is ordinal: a score of 20 is not "twice as risky" as 10. Define every rating in plain, measurable terms, in money, customer numbers or regulatory consequences, so that two assessors reach similar answers. NIST SP 800-30 Rev. 1 describes qualitative, semi-quantitative and quantitative approaches in detail.

## Inherent and residual risk

**Inherent risk** is the rating before considering current controls. **Residual risk** is the rating after them. The gap shows what your controls are worth, and it is only real if the controls are proven to work.

## Treatment options

| Option | Meaning | Example |
| --- | --- | --- |
| Treat | Reduce likelihood or impact with controls | Enforce MFA |
| Transfer | Shift financial impact to another party | Cyber insurance, contractual liability |
| Avoid | Stop the activity | Retire an unsupported system |
| Accept | Consciously tolerate, within appetite | Minor, low-value risks |

Acceptance must be a recorded decision by someone with authority, with a review date. It is not the same as ignoring a risk. Transfer moves money, not accountability: a breach at your supplier is still your customers' data.
