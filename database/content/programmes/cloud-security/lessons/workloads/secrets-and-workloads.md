## Secure compute

Virtual machines in the cloud need the same care as on-premises servers, plus attention to cloud-specific features:

- Build from **hardened, patched images** and rebuild rather than patch by hand where possible.
- Give each workload its own **workload identity** with least-privilege permissions, instead of embedding access keys.
- Restrict access to the **instance metadata service**, which can hand out workload credentials; use the hardened version your provider offers (for example IMDSv2 on AWS).
- Avoid public IP addresses on workloads that do not need them.

## Secrets management

Secrets include API keys, database passwords, certificates and tokens. Rules:

1. Never store secrets in source code, container images or plain environment files committed to repositories.
2. Use a **secrets manager** or key vault, and grant workloads permission to read only the secrets they need.
3. **Rotate** secrets regularly, and immediately after any suspected exposure.
4. Scan repositories and images for accidentally committed secrets.
5. Prefer identity-based access that needs no secret at all.

## Container fundamentals

- Use minimal, trusted **base images**, and scan images for vulnerabilities in the build pipeline.
- Run containers as **non-root** with read-only filesystems where possible.
- Do not mount the container runtime socket into containers.
- In orchestrators such as Kubernetes, apply role-based access control, network policies and admission controls, and keep the control plane private.

## Patch and vulnerability management

Maintain an inventory of workloads and their software. Scan regularly, prioritise internet-facing and high-privilege workloads, and automate patching through image rebuilds. Track remediation times as a metric.
