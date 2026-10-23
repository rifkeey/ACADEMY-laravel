With hundreds or thousands of findings, the question is not "what is wrong?" but **"what do we fix first, and how do we show progress?"** This lesson gives you a defensible way to prioritise and report.

## Prioritise by risk, not by count or tool severity

Tool severity is a starting point. Real priority combines it with context:

| Factor | Higher priority when… |
| --- | --- |
| **Exposure** | The resource is reachable from the internet or shared externally |
| **Data sensitivity** | It holds confidential or restricted data (module 4 classification) |
| **Environment** | Production rather than development or sandbox |
| **Privilege** | The finding involves administrative identities or permissions |
| **Exploitability** | There is a known, practical way to abuse it |
| **Control weakening** | The finding disables a safety net (logging off, guardrail removed) |

A practical scheme: **critical** = exposure or control-weakening on production with sensitive data; **high** = sensitive data or privileged identity without exposure; **medium/low** = the rest. Write the scheme down so priorities are consistent and explainable.

## Remediation plan structure

| Element | Content |
| --- | --- |
| **Now (days)** | Critical findings, with named owners |
| **Next (weeks)** | High findings, grouped by fix (one module change may fix 200 findings) |
| **Prevent** | Guardrails or IaC module fixes that stop each class of finding from recurring |
| **Accept** | Findings deliberately accepted, with risk owner and review date |
| **Measure** | How progress will be reported |

**Group by root cause**: if 300 findings come from one insecure IaC module, fixing that module (and redeploying) is one task, not 300.

## Mapping to compliance

Posture findings usually map to frameworks (CIS Benchmarks, provider benchmarks, the CSA Cloud Controls Matrix, ISO/IEC 27001 controls). Mapping helps you:

- report posture in the language auditors and regulators use;
- show coverage of a framework's requirements;
- avoid duplicated work when several frameworks ask for the same control.

Remember that **a compliant score is not the same as being secure**: frameworks set minimums, and a high score can hide one critical exposure. Report the critical items explicitly, not only percentages.

## Showing progress

Useful measures for the CISO and the board:

- number of **critical and high** findings over time (not all findings);
- **time to remediate** by severity;
- **recurrence**: findings that come back after being fixed (a sign of missing prevention);
- **coverage**: share of accounts and resources monitored.

:::takeaway
Prioritise findings by exposure, data sensitivity, environment, privilege, exploitability and control weakening; group fixes by root cause; prevent recurrence with guardrails and secure modules; accept risks explicitly; map to frameworks for reporting, but always report critical items, not just scores.
:::
