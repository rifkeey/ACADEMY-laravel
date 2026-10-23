A review always finds more problems than there is money or time to fix. Risk assessment decides the order, and lets the board make informed choices about what to fund and what to accept.

## Risk in plain terms

NIST SP 800-30 Rev. 1, *Guide for Conducting Risk Assessments*, describes risk as a combination of **likelihood** (how probable it is that a threat exploits a weakness) and **impact** (how much harm it would cause). Many organisations use a simple matrix:

| Likelihood ↓ / Impact → | Low | Medium | High |
| --- | --- | --- | --- |
| **High** | Medium | High | Critical |
| **Medium** | Low | Medium | High |
| **Low** | Low | Low | Medium |

## Judging likelihood and impact

| Raises likelihood | Raises impact |
| --- | --- |
| Reachable from the internet or untrusted zones | Crown-jewel systems (core banking, finance data, identity) |
| Known, easily used weakness | Personal or payment data (regulatory consequences) |
| Weak or no authentication | Many users or branches affected |
| No monitoring, so misuse goes unseen | Long recovery time |
| Seen in real incidents (including Kanem's own) | Reputational damage |

Be consistent: write down what "high" means for Kanem so different assessors score the same way.

## The risk register

| Field | Example |
| --- | --- |
| ID and title | NR-01 Temporary vendor rule exposes the DMZ |
| Description | Rule 2 allows any source to any DMZ port; logging off |
| Likelihood / impact / rating | High / High / Critical |
| Owner | Network manager |
| Treatment | Reduce: remove rule 2; vendor access through ZTNA (module 6) |
| Target date | Within 2 weeks |
| Residual risk | Low |

## Building the roadmap

| Horizon | What goes in it |
| --- | --- |
| **Now (0–30 days)** | Critical risks and cheap, high-value quick wins: remove dangerous rules, turn on logging, block management access from user zones |
| **Next (1–6 months)** | High risks needing projects: segmentation of the server zone, NAC, redundant firewall pair, DNS controls |
| **Later (6–18 months)** | Larger changes: zero trust access for remote users and vendors, microsegmentation of crown jewels, full resilience |

Dependencies matter: logging and monitoring often need to come before major segmentation changes, so you can see what breaks.

## Accepting risk

Some risks will be accepted. Acceptance must be **explicit**: a named owner with authority, the reason, any compensating controls, and a review date. "We didn't get round to it" is not acceptance.

:::takeaway
Score risks by likelihood and impact on a consistent matrix, record them in a register with owners, treatments, dates and residual risk, and turn them into a now/next/later roadmap that respects dependencies. Accept risks only explicitly, with an owner, reason and review date.
:::
