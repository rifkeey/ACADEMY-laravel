## Misconfiguration at scale

A cloud environment may contain thousands of resources created by many teams. Manual review cannot keep up. **Cloud security posture management (CSPM)** tools continuously compare resource configurations with a baseline and report deviations. Providers offer native options (for example AWS Security Hub, Microsoft Defender for Cloud, Google Security Command Center), and independent tools exist.

## Baselines

A baseline defines what "secure" means for each resource type. Good starting points:

- **CIS Benchmarks** for each major cloud provider;
- the provider's own security benchmark, such as the Microsoft cloud security benchmark (Azure-specific);
- your organisation's policies and regulatory requirements.

Adopt baselines deliberately: some recommendations will not fit your environment, so record justified exceptions rather than ignoring findings.

## Prevent, then detect

- **Preventive guardrails**: organisation-level policies that make certain configurations impossible, such as denying public storage or restricting regions.
- **Policy as code**: checking infrastructure templates (Terraform, Bicep, CloudFormation) for misconfigurations before deployment.
- **Detective controls**: CSPM scans and alerts for anything that slips through.

## Prioritising remediation

CSPM tools often report hundreds of findings. Prioritise by:

1. **Exposure**: is the resource reachable from the internet?
2. **Privilege**: does it involve powerful identities?
3. **Data sensitivity**: does it hold restricted data?
4. **Exploitability**: is there a known, actively exploited weakness?
5. **Combination**: a public VM with a critical vulnerability and an over-privileged role is far worse than any one of those alone.

Track findings to closure with owners and target dates, and measure the trend over time rather than chasing a perfect score.
