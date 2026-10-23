Even with IaC and guardrails, the real environment slowly diverges from the intended one. **Drift** is that divergence. **Continuous validation** is how you notice and correct it.

## Where drift comes from

| Source | Example |
| --- | --- |
| Manual console changes | An engineer opens a rule "for five minutes" during an incident |
| Emergency fixes not written back into code | The fix works; the template still has the old setting |
| Changes by other tools or teams | A monitoring tool adds its own permissions |
| Provider changes | New services or features with defaults nobody reviewed |
| Resources created outside the pipeline | A test environment built by hand |

## Detecting drift

| Method | What it shows |
| --- | --- |
| **IaC drift detection** (plan or drift features of IaC tools) | Differences between the code and the deployed resources |
| **Configuration history** (for example AWS Config, Azure Resource Graph change history, Google Cloud Asset Inventory) | What changed, when and by whom |
| **CSPM findings** | Resources that no longer meet the baseline |
| **Audit logs** (module 5) | Changes made outside the pipeline identity |

A strong signal: a production change made by **any identity other than the deployment pipeline**. Alerting on it makes console changes visible immediately.

## Responding to drift

1. **Find out why.** An emergency change may have been right; the code may need updating.
2. **Choose the source of truth.** Either revert the resource to match the code, or update the code to match a justified change.
3. **Record it**, with the reason, in the change history.
4. **Fix the cause.** If drift keeps happening in one area, the process or the permissions need changing.

## Continuous control validation

Beyond configuration, check that controls actually **work**:

- Test that preventive guardrails block what they should (for example, try creating a public bucket in a test account and confirm it is denied).
- Test that detections fire (module 5).
- Test restores (module 4).
- Re-test after major changes and on a schedule.

Record the results: they are your evidence that the control environment is effective, for management and for auditors.

:::takeaway
Drift is inevitable; notice it through IaC drift detection, configuration history, CSPM and audit logs (especially changes by identities other than the pipeline). Decide the source of truth, record the outcome, fix recurring causes, and regularly test that guardrails, detections and restores really work.
:::
