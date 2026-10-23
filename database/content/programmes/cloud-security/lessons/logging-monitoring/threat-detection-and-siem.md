Logs only help if someone, or something, looks at them. Cloud providers offer managed detection services; a SIEM brings everything together; and alert prioritisation decides what an analyst sees first.

## Provider-managed detection

| Provider | Service | What it does (high level) |
| --- | --- | --- |
| AWS | **Amazon GuardDuty** | Analyses sources such as CloudTrail, VPC flow logs and DNS logs to produce security findings |
| Microsoft | **Microsoft Defender for Cloud** | Posture recommendations and threat protection across Azure and connected environments |
| Google Cloud | **Security Command Center** | Asset inventory, posture findings and threat detection for the organisation |

These services are quick to enable and maintained by the provider. Enable them across the organisation from a central security account, and route their findings to the same place as everything else.

## Why a SIEM as well?

A **SIEM** (security information and event management system) collects logs and findings from cloud providers, on-premises systems, identity providers and endpoints, so analysts can:

- **correlate** events across sources (a sign-in from an unusual location, followed by a permission change, followed by unusual data access);
- run **detections you write** for your own environment;
- keep **one queue** of alerts, with case management and history;
- **search** months of data during an investigation.

Many organisations use a cloud-native SIEM (for example Microsoft Sentinel) or another platform; the concepts are the same.

## Prioritising alerts

Not all findings are equal. A simple, explainable scoring helps:

| Factor | Raises priority when… |
| --- | --- |
| **Asset criticality** | The resource holds restricted data or is production |
| **Identity privilege** | The identity involved is an administrator or has broad permissions |
| **Confidence** | The detection is precise, or several signals agree |
| **Stage** | The activity suggests impact (data access, deletion, disabling logging) rather than early reconnaissance |
| **Exposure** | The resource is internet-facing |

Publish the scheme so analysts and stakeholders understand why one alert is handled before another.

## From alert to response

Each alert type should have a short **runbook**: what to check first, who owns the resource, what containment options exist, and when to escalate to incident response (module 9). Runbooks make response consistent at 3 a.m.

:::takeaway
Enable each provider's managed threat detection organisation-wide, bring its findings and your logs into a SIEM to correlate across sources, prioritise alerts with an explainable scheme based on asset, identity, confidence, stage and exposure, and give each alert type a runbook.
:::
