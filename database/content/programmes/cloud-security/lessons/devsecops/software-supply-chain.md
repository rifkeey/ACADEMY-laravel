The software supply chain is everything between source code and running workload: repositories, dependencies, build systems, registries and deployment. Several well-publicised incidents in recent years showed that compromising one supplier's build can affect thousands of customers. The defence is to be able to **prove** what was built, from what, and by whom, and to refuse to run anything you can't verify.

## Frameworks

| Framework | What it gives you |
| --- | --- |
| **NIST SP 800-218, Secure Software Development Framework (SSDF) v1.1** | Practices grouped as Prepare the organisation, Protect the software, Produce well-secured software, Respond to vulnerabilities. A Rev. 1 draft (v1.2) was published for comment in December 2025 |
| **NIST SP 800-204D** | How to build supply-chain security into cloud-native CI/CD pipelines |
| **SLSA** (Supply-chain Levels for Software Artifacts) | Increasing levels of build integrity, from documented builds to hardened, isolated build platforms that produce signed **provenance** |

## Provenance and signing

| Concept | Meaning |
| --- | --- |
| **Provenance** | A signed record of how an artefact was built: source repository and commit, build platform, workflow, time |
| **Signing** | A cryptographic signature on the image or package, so anyone can check it came from Kanem's pipeline and wasn't changed. Projects such as Sigstore make signing with short-lived certificates practical |
| **Verification at deploy** | The platform (for example a Kubernetes admission policy, module 6) refuses images that are unsigned, signed by an unknown identity, or missing provenance |

```text
commit → pipeline builds image → generates SBOM and provenance → signs
      → pushes to registry → deploy policy verifies signature and provenance
      → only then runs in production
```

## A practical supply-chain checklist for Kanem

| Area | Control |
| --- | --- |
| Source | Branch protection, required reviews, signed commits for sensitive repositories, MFA for all developers |
| Dependencies | Lock files, trusted registries or a mirror, SCA, SBOM (lesson 3) |
| Build | Builds run only in the pipeline on ephemeral runners; no manual builds for production |
| Artefacts | Images signed, with provenance; registry write access only for the pipeline |
| Deploy | Admission policy verifies signatures; production changes only via the pipeline (module 7 drift alerts) |
| Suppliers | Ask key software suppliers for SBOMs and their secure development practices |
| Respond | A process to answer "are we affected?" within hours, using SBOMs and the registry |

:::note Proportionate effort
Not every internal tool needs the highest SLSA level. Start with the services that handle restricted data or face the internet, and raise the bar over time.
:::

:::takeaway
Secure the whole path from source to production: protect source, control dependencies, build only in hardened pipelines, sign artefacts with provenance, verify signatures before anything runs, and be ready to answer "are we affected?" quickly. Use SSDF, SP 800-204D and SLSA to guide the work.
:::
