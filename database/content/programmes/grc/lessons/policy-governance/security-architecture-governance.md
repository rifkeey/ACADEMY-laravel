## Governing change, not just documents

Policies are only effective if new systems and changes are built to meet them. **Security architecture governance** is the set of processes that ensure security requirements are considered when systems are designed, bought, changed and retired.

## Where security enters the lifecycle

| Stage | Governance activity |
| --- | --- |
| Idea and business case | Classify the data involved; identify legal and contractual obligations |
| Design or procurement | Define security requirements from policy and standards; supplier due diligence |
| Architecture review | Review the design against approved patterns and principles |
| Build and test | Verify requirements are met: configuration checks, security testing |
| Go-live approval | Confirm residual risks are accepted by the right owner |
| Operate and change | Change management checks security impact of modifications |
| Retire | Data return or secure deletion; access removal |

Adding security at go-live is expensive. Adding it at design time is cheap.

## Architecture review boards

Many organisations use an **architecture review board** (ARB) or design authority. A security representative reviews significant proposals against:

- **Security principles**, such as least privilege, defence in depth, secure defaults, segregation of environments and minimising the data collected.
- **Approved patterns**, such as a standard way to expose an API, connect to a cloud tenancy, or integrate single sign-on.
- **Standards**, such as the encryption or logging standard.

Deviations are either redesigned or recorded as exceptions with a risk owner's approval.

## Security requirements

Derive requirements from policy so they are traceable. A requirements checklist for a new customer-facing application might include: authentication through the corporate identity provider with MFA for staff; encryption of personal data in transit and at rest; audit logs sent to the central logging platform; vulnerability scanning before go-live; data retention aligned to the retention schedule.

## Traceability matrix

A traceability matrix links each policy statement to the standards, controls and evidence that implement it:

| Policy statement | Standard | Control | Evidence |
| --- | --- | --- | --- |
| P-4.2 Remote access must use MFA | Authentication Standard §3 | VPN and SSO conditional access | Conditional access policy export; monthly MFA coverage report |
| P-6.1 Critical systems must be recoverable | Backup Standard §2 | Daily backups; quarterly restore tests | Backup job reports; restore test records |

Gaps show up in two ways: a policy statement with no control (unimplemented intent), or a control with no policy (work nobody mandated, which may be unfunded or inconsistent).

## Change management

Normal changes should include a security impact question: does this change affect authentication, network exposure, logging, data flows or privileged access? If yes, it needs security review. Emergency changes should be reviewed after the fact. Change records are valuable audit evidence and are often the first thing investigators read after an incident.
