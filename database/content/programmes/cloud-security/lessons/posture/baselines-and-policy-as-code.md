Finding misconfigurations after they happen is useful. Preventing them is better. **Configuration baselines** say what "secure" means; **policy as code** enforces it automatically.

## Configuration baselines

A baseline is a documented set of required settings for a type of resource. NIST SP 800-128 describes security-focused configuration management: establish baselines, control changes, and monitor for deviations.

Example extract from Kanem's cloud baseline:

| ID | Resource | Requirement |
| --- | --- | --- |
| ID-01 | All accounts | MFA required for all human users |
| LG-01 | All accounts | Organisation audit trail on, delivered to the log archive |
| NW-01 | Databases | No public endpoint |
| DP-01 | Storage | Public access blocked |
| DP-02 | Restricted data stores | Customer-managed key; data-access logging on |
| WL-01 | Container namespaces | Restricted Pod Security Standard unless an exception exists |

Base the baseline on recognised benchmarks (CIS, provider benchmarks), then adapt it to your risk and document why.

## Preventive, detective and corrective controls

| Type | What it does | Examples |
| --- | --- | --- |
| **Preventive** | Stops a non-compliant change from happening | Organisation policies (AWS SCPs, Azure Policy *deny*, Google Cloud organisation policy constraints); checks in deployment pipelines |
| **Detective** | Finds non-compliance after it happens | CSPM findings, AWS Config rules, Azure Policy *audit* |
| **Corrective** | Fixes it automatically | Automated remediation (for example re-enabling a public access block), used carefully |

Use **preventive** controls for the few settings that must never be wrong (logging off, public restricted data, unapproved regions), **detective** controls for everything in the baseline, and **corrective** automation only where fixing automatically cannot break a service.

## Policy as code

**Policy as code** expresses rules in machine-readable files, stored in version control, reviewed like software and evaluated automatically. Options include each provider's native policy languages and general-purpose engines such as **Open Policy Agent**.

Benefits:

- **Consistency**: the same rule is applied everywhere.
- **Review and history**: every policy change is reviewed and recorded.
- **Testing**: policies can be tested against example good and bad configurations before rollout.
- **Shift left**: the same policies can run in the deployment pipeline, so problems are caught before resources exist (lesson 3).

## Exceptions

Some resources genuinely need to differ from the baseline (a public website bucket). Handle exceptions explicitly:

- requested with a business reason;
- approved by the risk owner;
- **scoped** to specific resources, not whole accounts;
- **time-limited** and reviewed.

An exception process that is easy to use stops teams from working around the controls.

:::takeaway
Write a baseline based on recognised benchmarks, enforce the critical items preventively, detect deviations from the rest, automate fixes only where safe, manage policies as reviewed and tested code, and handle exceptions explicitly with owners and expiry dates.
:::
