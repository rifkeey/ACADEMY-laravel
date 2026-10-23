Most of the code in Kanem's loans API was not written by Kanem. Open-source libraries, base container images and build tools make up the majority. Each one can carry known vulnerabilities, so you need to know what you use and keep it current.

## Know what you ship

| Practice | Why |
| --- | --- |
| **Lock files** (for example `package-lock.json`, `composer.lock`) | Builds use exactly the versions that were tested |
| **Pinned versions and digests** | `node:22.11-slim@sha256:…` rather than `node:latest`, so the build is repeatable |
| **Trusted registries** | Packages and images come from approved sources or an internal mirror |
| **Software bill of materials (SBOM)** | A machine-readable list of every component and version in a release (CISA's SBOM resources explain formats such as SPDX and CycloneDX) |

An SBOM answers the question every security team gets when a major library vulnerability is announced: **"Do we use it, and where?"** Without one, the answer takes days.

## Scanning

| Scan | Finds | When |
| --- | --- | --- |
| **Software composition analysis (SCA)** | Known vulnerabilities and licence issues in dependencies | Every pull request and nightly |
| **Container image scanning** | Vulnerable OS packages and libraries in images | At build, and again regularly in the registry, because new vulnerabilities are published every day |
| **Secret scanning** | Keys and passwords committed to code | Every push, and on the whole history once |

## Prioritising what scanners find

Scanners report hundreds of findings. Use the same thinking as module 7:

1. **Is it exploitable in this context?** A vulnerable function that the application never calls is lower priority.
2. **Is it known to be exploited?** Findings on CISA's Known Exploited Vulnerabilities catalogue jump the queue.
3. **Is the workload exposed?** Internet-facing services and those handling restricted data come first.
4. **Is a fix available?** Often the fix is simply updating the base image or library.

Set clear rules, for example: *critical and known-exploited findings in internet-facing services block the release; high findings must be fixed within 14 days; others go on the backlog with an owner.*

## Keeping current

- Automated dependency update pull requests, tested by the pipeline.
- Small, regular base-image updates rather than rare, painful ones.
- Remove dependencies you no longer use: less code, fewer findings.

:::takeaway
Lock and pin what you build with, use trusted sources, produce an SBOM for each release, scan dependencies, images and secrets continuously, prioritise by exploitability and exposure, and keep dependencies current with small, regular updates.
:::
