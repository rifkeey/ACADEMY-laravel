"Shifting left" means finding security problems early, when they are cheap to fix, by building checks into the developer workflow and the pipeline. The aim is fast, useful feedback, not a wall of alerts nobody reads.

## The checks and where they run

| Stage | Check | Finds |
| --- | --- | --- |
| Developer's editor | Linters, secret detection hooks | Obvious mistakes before commit |
| Pull request | **SAST** (static application security testing) | Risky code patterns such as missing input validation or unsafe queries |
| Pull request | **SCA** and **secret scanning** (lesson 3) | Vulnerable libraries, committed keys |
| Pull request | **IaC scanning** (module 7) | Public buckets, open security groups, missing encryption in templates |
| Build | Image scanning, SBOM generation, signing (lesson 5) | Vulnerable images; records what was built |
| Test environment | **DAST** (dynamic testing) against the running application | Misconfigured headers, authentication and session problems |
| Test environment | Authorisation tests for APIs (lesson 1) | One user reaching another user's data |
| Before production | Policy check (for example, "image must be signed and have no critical findings") | Releases that break the rules |

## Gates: block, warn or record

Not every finding should stop a release. Decide in advance:

| Finding | Action |
| --- | --- |
| Secret committed | **Block**; rotate the secret immediately (removing it from code is not enough) |
| Critical or known-exploited vulnerability in an internet-facing service | **Block** |
| IaC change making storage public or opening admin ports to the internet | **Block** |
| High-severity finding | Warn, ticket with a deadline |
| Medium and low | Record on the backlog |

## Handling false positives and exceptions

- Triage findings **with** developers; mark false positives with a reason so they don't return.
- **Exceptions** need an owner, a reason, compensating controls and an **expiry date**, after which the gate applies again.
- Track the numbers: findings blocked, time to fix, exceptions open. Rising exceptions mean the rules or the tooling need attention.

:::tip Start small
Turning on every scanner in blocking mode on day one usually leads to the checks being switched off. Start with secrets and critical findings blocking, everything else reporting, then tighten.
:::

:::takeaway
Build SAST, SCA, secret scanning, IaC scanning, image scanning, DAST and authorisation tests into the workflow; agree in advance what blocks a release; and manage false positives and time-limited exceptions so the checks stay trusted.
:::
