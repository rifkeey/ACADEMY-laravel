## Why SOCs measure

Metrics show whether the SOC is improving, where effort is wasted, and what management should fund. Poorly chosen metrics, however, create perverse incentives. A team measured only on "alerts closed per hour" will close alerts quickly and badly.

## Useful operational metrics

| Metric | What it tells you | Caution |
| --- | --- | --- |
| Mean / median time to acknowledge (MTTA) | Queue health and staffing | Median resists outliers better than mean |
| Mean time to detect (MTTD) | Gap between intrusion start and detection | Requires post-incident analysis to know the start time |
| Mean time to respond / contain (MTTR) | Speed of effective action | Define precisely when the clock stops |
| Alert volume per rule | Noise sources | High volume is not inherently bad if precision is high |
| False-positive rate per rule | Tuning priorities | Only meaningful if closure reasons are recorded consistently |
| Detection coverage (validated) | Techniques with tested detections | Not "rules exist"; tested rules |
| Escalation rate | Tier 1 effectiveness and rule quality | Very low rates can mean under-escalation |

## False-positive analysis

Monthly, list the top ten rules by false-positive volume. For each, record the dominant false-positive cause and the tuning action. This turns analyst frustration into an engineering backlog and usually reclaims hours of analyst time a week.

## Reporting to different audiences

- **Analysts and engineers**: technical detail, queries, indicators, specific tuning recommendations.
- **IT and system owners**: what is affected, what they must do, by when.
- **Management**: business impact, risk, trends, decisions needed. No raw log lines.
- **Executives and boards**: risk in business terms, comparison with appetite, resource asks.

## The incident report

A professional incident report usually contains:

1. **Executive summary**: what happened, impact, current status, key recommendations, all on one page.
2. **Scope and timeline**: systems, accounts and data affected; a condensed timeline.
3. **Technical analysis**: how the attack worked, with ATT&CK mapping and evidence references.
4. **Response actions**: containment, eradication and recovery steps, with times.
5. **Root cause and contributing factors**: including control gaps.
6. **Recommendations**: prioritised, each with an owner and target date.
7. **Appendices**: indicators, queries and the evidence register.

Write the executive summary **last**, once you know exactly what the evidence supports.
