Most well-run cloud environments are built from **infrastructure as code (IaC)**: templates (for example Terraform, AWS CloudFormation, Azure Bicep or ARM templates) that describe resources, applied by a pipeline. That changes where security happens: you can fix misconfigurations **in the code, before they exist**.

## Why IaC helps security

| Benefit | Why |
| --- | --- |
| **Review before deployment** | A change to a network rule or storage policy appears in a pull request, where reviewers and automated checks can see it |
| **Repeatability** | Every environment is built the same way; no hand-made differences |
| **History** | Version control shows who changed what and when |
| **Fast, consistent fixes** | Fix the module once, redeploy everywhere |
| **Disaster recovery** | Environments can be rebuilt from code |

## Scanning IaC

**IaC scanners** check templates against security rules (often the same baseline as your CSPM) and report problems such as storage without public-access blocking, databases with public endpoints, or logging disabled. Run them:

1. **In the developer's editor or pre-commit**, for quick feedback;
2. **In the pull request**, as a required check;
3. **In the deployment pipeline**, blocking deployment for high-severity findings.

The same exception process from lesson 2 applies: a finding can be suppressed only with a recorded, scoped reason.

## Secure IaC practices

- **Use reviewed, reusable modules** for common patterns (a "secure bucket" module with public access blocked, encryption and logging already set), so teams start from a secure default.
- **Keep secrets out of templates and state files**; IaC state can contain sensitive values, so store it encrypted with restricted access.
- **Protect the pipeline**: the identity that applies IaC is powerful. Limit who can change pipelines and approve deployments (Cloud Security module 8 covers pipeline security).
- **Make the pipeline the only way to change production** where possible, so console changes are exceptions that are noticed (lesson 4).

## The limits

IaC scanning only sees what is in the code. Resources created by hand, changes made in the console and settings inherited from elsewhere are invisible to it, which is why CSPM (runtime checking) and drift detection are still needed.

:::takeaway
Infrastructure as code moves security checks before deployment: review changes in pull requests, scan templates in the editor, pull request and pipeline, build from secure reusable modules, protect secrets in templates and state, and keep runtime posture checks for everything the code doesn't cover.
:::
