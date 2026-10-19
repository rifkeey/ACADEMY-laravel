## Alert quality is a security control

A SOC that receives more alerts than it can triage is effectively blind: analysts start skimming, closing in bulk, or ignoring entire rule families. Alert quality therefore directly affects security outcomes.

Useful measures of alert quality:

- **Precision**: of the alerts a rule produced, what fraction were true positives (including benign true positives)?
- **Volume**: alerts per day per rule.
- **Triage time**: median minutes to classify an alert from this rule.
- **Actionability**: did the alert lead to an action, or only to closure?

## Tuning techniques

Tune in this order, from safest to riskiest:

1. **Fix the logic.** Many false positives come from a rule that matches more than intended, for example a `contains "admin"` that also matches `badminton`.
2. **Add context instead of exclusions.** Join with an asset list so that "server process spawned a shell" ignores build servers that legitimately do so, but only those listed hosts.
3. **Adjust thresholds** using real distributions. Look at what normal looks like over 30 days before choosing "more than 20".
4. **Add narrow exclusions** with an owner, a reason and an expiry date.
5. **Lower severity or convert to a hunting query** if the rule is valuable but cannot be made precise enough for real-time alerting.

## The danger of broad exclusions

An exclusion is a permanent blind spot. Compare:

```kql
// Too broad: anything from the IT subnet is now invisible
| where SourceIP !startswith "10.20.9."

// Narrow: one scanner, one behaviour, documented
| where not(SourceIP == "10.20.9.15" and ProcessName == "nessusd")
```

Attackers who compromise an IT administrator's workstation benefit enormously from the first version. Record every exclusion in the rule documentation with a review date.

## Testing detections

Test a rule against two datasets before enabling it:

- **Known-bad**: synthetic or replayed malicious events. The rule must fire.
- **Known-good**: a representative sample of normal activity. The rule should stay quiet.

You will do exactly this in Lab 4, where your detection must return every malicious event in a synthetic dataset and none of the benign ones.
