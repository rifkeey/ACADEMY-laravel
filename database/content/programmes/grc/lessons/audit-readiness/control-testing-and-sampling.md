## Kinds of audit

| Type | Performed by | Example |
| --- | --- | --- |
| First-party (internal) | The organisation's own auditors | ISMS internal audit; internal audit review of access control |
| Second-party | A customer or partner auditing a supplier | A bank auditing its outsourced IT provider |
| Third-party | An independent body | Certification audit; independent assurance report; regulatory inspection |

Whatever the type, auditors follow the same logic: understand the control, decide how to test it, gather evidence, and reach a conclusion.

## Walkthroughs

A **walkthrough** follows one transaction from start to finish through the control: for example, one new joiner from HR request to account creation. It confirms the auditor understands how the control actually works, which is often different from what the procedure document says.

## Testing techniques

| Technique | What the auditor does | Strength |
| --- | --- | --- |
| Inquiry | Asks people how the control works | Weakest on its own; always corroborate |
| Observation | Watches the control being performed | Shows it at one moment only |
| Inspection | Examines records, configurations, tickets, logs | Strong when records are system-generated |
| Re-performance | Independently performs the control and compares results | Strongest; e.g. recompute which leavers still have accounts |

Good tests combine techniques: inquiry to understand, inspection to confirm, re-performance for key controls.

## Design versus operating effectiveness

- **Design**: if performed as described, would the control reduce the risk? A quarterly access review that only covers email accounts is poorly designed if the risk is core banking access.
- **Operating**: was it actually performed, correctly, throughout the period? Test this with samples.

A control can be well designed and never operated, or operated faithfully but badly designed. Both are findings, of different kinds.

## Sampling

Auditors rarely test every item. They test a **sample** from a **population** and draw conclusions about the whole.

1. **Confirm the population is complete.** Get the list of all joiners from the HR system, not from IT's ticket queue; otherwise the joiners who never got a ticket are invisible.
2. **Choose a sample size** appropriate to the control's frequency and risk. A quarterly control might be tested on two of four instances; a daily control on 20 to 40 days; a control applied to hundreds of transactions on a sample of 25 or more.
3. **Select without bias**: random or systematic selection across the whole period, not the first ten.
4. **Evaluate exceptions.** Ask whether each exception is an isolated error or a sign of a systemic weakness.

## Reaching a conclusion

Typical conclusions: *effective*; *effective with exceptions* (minor, isolated deviations); *design deficiency*; *operating deficiency*. State the basis: "Of 25 sampled joiners, 3 were granted access before approval; we conclude the control did not operate effectively during the period."

## Preparing as the auditee

Know your populations, keep evidence where you can retrieve it quickly, run your own sample tests before the auditor does, and answer questions precisely. Volunteering unrelated documents slows audits down; withholding relevant ones damages trust.
