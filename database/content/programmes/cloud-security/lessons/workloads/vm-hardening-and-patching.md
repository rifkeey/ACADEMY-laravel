:::scenario Three ways to run the same app
Kanem's engineering team wants to move the reporting service off its IaaS virtual machine. One proposal containerises it on managed Kubernetes; another rewrites parts as serverless functions. Each option moves different security work to the provider and leaves different work with Kanem. This module walks through all three so you can review any of them.
:::

Virtual machines are still the most common cloud workload, and in IaaS the customer owns everything from the operating system up (module 1). Most VM compromises come from the same few causes: unpatched software, weak configuration, too much network exposure and over-powerful identities.

## Start from a hardened image

Don't build each VM by hand. Use a **golden image**: a standard operating-system image with security settings applied, unnecessary software removed and monitoring agents installed.

- Base it on a recognised baseline such as the **CIS Benchmarks** for the operating system (CIS also publishes pre-hardened images in cloud marketplaces).
- Build it **automatically** (an image pipeline), so every rebuild is identical and changes are reviewed.
- **Rebuild regularly** with the latest patches, and retire old images.

## Patch management

NIST SP 800-40 Rev. 4 frames patching as **preventive maintenance**: planned, routine and measured, not an emergency activity.

| Practice | In the cloud |
| --- | --- |
| Know what you run | Use the provider's inventory and patch services to list every VM, OS version and installed package |
| Prioritise | Internet-facing systems and known-exploited vulnerabilities first; agree target times by severity |
| Patch or replace | Apply patches in place, or (better for stateless systems) replace instances with ones built from the updated image |
| Verify | Confirm patch status after each cycle; report exceptions with owners and dates |

Treat a VM that cannot be patched (unsupported OS, vendor restriction) as a risk to be recorded, isolated and monitored more closely.

## Configuration essentials

| Area | Baseline |
| --- | --- |
| Remote access | No SSH or RDP open to the internet; use provider session services or bastions with identity-based access (module 3) |
| Accounts | No shared local admin accounts; central identity where possible |
| Instance identity | One workload identity per role, with least privilege (module 2); hardened metadata-service settings |
| Disk encryption | On, with keys chosen by data classification (module 4) |
| Logging and agents | OS logs and security agents sending to the central platform (module 5) |
| Software | Only what the workload needs |

## Immutable infrastructure

For many workloads, the strongest pattern is **immutable infrastructure**: servers are never changed after deployment; to update, you deploy new ones from a new image and remove the old ones. Configuration drift disappears, rollback is simple, and an attacker's changes to a running server are wiped at the next deployment.

:::takeaway
Build VMs from hardened, automatically produced images; patch as routine preventive maintenance with priorities and verification; follow a configuration baseline for access, identity, encryption and logging; and prefer replacing instances over changing them.
:::
