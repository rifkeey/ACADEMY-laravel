The hardest problem in detection is not finding attacks. It is finding them **without drowning in alerts that are not attacks**. This lesson gives you the vocabulary to measure and discuss alert quality.

## Four possible outcomes

|  | Alert raised | No alert |
| --- | --- | --- |
| **Real malicious activity** | **True positive (TP)**: the detection worked | **False negative (FN)**: a missed attack, the most dangerous outcome |
| **Benign activity** | **False positive (FP)**: wasted effort and lost trust in the tool | **True negative (TN)**: correctly quiet |

A fifth category is useful in practice: the **benign true positive**. The rule fired correctly on what it was designed to detect, but the activity was authorised (the vulnerability scanner scanning, an administrator using a remote-management tool). It is not a mistake in the rule; it is a gap in context.

## Why alerts are mostly noise

Malicious traffic is rare compared with normal traffic. Even a rule that is right 99% of the time on benign traffic, applied to millions of connections, produces thousands of false positives for every real attack. This is called the **base-rate problem**, and it is why tuning (lesson 4) matters more than adding more rules.

The human cost has a name: **alert fatigue**. When analysts see hundreds of false positives, they start to skim, and the real attack is closed with the rest.

## Measuring a detection

You don't need advanced statistics, but you do need numbers:

| Measure | Question | How to estimate |
| --- | --- | --- |
| **Volume** | How many alerts per day does this rule produce? | Count |
| **True-positive rate** of the alerts | Of the alerts closed last month, how many were real or worth investigating? | Closed alerts by disposition |
| **Coverage** | Which attacker behaviours do we detect at all? | Map detections to ATT&CK techniques |
| **Time to triage** | How long does an analyst need to decide? | From ticket timestamps |

Recording a **disposition** (true positive, benign true positive, false positive) with a short reason when closing every alert is what makes these numbers possible.

## Missed attacks are invisible

False positives are visible and irritating. **False negatives are silent**: you only learn about them later, from an incident. Ways to find them before an attacker does:

- **Coverage mapping**: which ATT&CK techniques relevant to your environment have no detection?
- **Testing**: replaying known-safe test traffic or using authorised testing tools to confirm a detection fires.
- **Retrospective review**: after every incident, ask which alerts should have fired earlier.

:::tip Tune for precision, test for coverage
A detection programme improves in two directions at once: removing noise from rules that fire too often (precision) and adding or testing rules for behaviours nobody detects (coverage). Doing only the first makes the SOC quieter, not safer.
:::

:::takeaway
Classify every alert outcome (true positive, benign true positive, false positive), record it when closing alerts, and measure volume, accuracy and coverage. Because attacks are rare, small error rates create large noise, and the most dangerous errors are the silent false negatives.
:::
