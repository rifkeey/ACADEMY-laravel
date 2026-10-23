A container image packages an application with its dependencies. That makes deployments consistent, and it also means every vulnerability and secret inside the image is deployed everywhere the image runs. NIST SP 800-190, *Application Container Security Guide*, describes the main risks; this lesson covers the practical controls.

## Where image risks come from

| Source | Example |
| --- | --- |
| **Base image** | An old OS layer with known vulnerabilities |
| **Dependencies** | Outdated libraries pulled in by the application |
| **Secrets** | API keys or passwords copied into a layer during the build |
| **Untrusted origin** | An image pulled from a public registry without checking who published it |
| **Excess software** | Shells, compilers and tools the application never uses |

## Controls through the life of an image

| Stage | Control |
| --- | --- |
| **Build** | Use minimal, maintained base images; pin versions; never put secrets in images; build in a controlled pipeline |
| **Scan** | Scan images for known vulnerabilities and embedded secrets in the pipeline and in the registry, and fail builds above an agreed severity |
| **Store** | Use a private registry with access control; keep images immutable once published; sign images so their origin can be verified |
| **Deploy** | Allow only images from approved registries, ideally only signed and scanned ones (admission controls) |
| **Run** | Run as a non-root user, with a read-only file system where possible and only the capabilities needed |
| **Maintain** | Rescan stored images regularly (new vulnerabilities are published every day) and rebuild when base images are updated |

## Containers are not virtual machines

Containers on the same host share the host's operating-system kernel. Isolation is good but not as strong as between virtual machines, so:

- don't run containers with more privileges than they need (privileged mode removes most isolation);
- keep the host OS patched and minimal;
- separate workloads of different sensitivity onto different hosts or node pools where risk justifies it.

## Software bill of materials

An **SBOM** lists the components in an image. When a new vulnerability is announced in a widely used library, an SBOM lets you answer "which of our images contain it?" in minutes instead of days. Many build tools can generate one automatically.

:::takeaway
Build images from minimal, maintained bases without secrets; scan them in the pipeline and in the registry; store them privately, immutably and signed; deploy only approved images; run them with least privilege; and rebuild and rescan continuously.
:::
