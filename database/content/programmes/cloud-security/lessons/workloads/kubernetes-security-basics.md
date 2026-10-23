Kubernetes runs containers at scale. It is powerful and complex, and its security spans the cluster itself, the workloads running in it and the identities that manage it. This lesson gives you the vocabulary and the main controls; the Kubernetes documentation and the CIS Kubernetes Benchmark go deeper.

## Who is responsible for what

On a **managed** Kubernetes service (Amazon EKS, Azure AKS, Google GKE), the provider runs and patches the **control plane** (the API server and its components). The customer is responsible for:

- the **worker nodes** (unless using a fully managed node option), including their OS and upgrades;
- **cluster configuration**: access control, network policies, admission controls;
- the **workloads**: images, configuration, secrets and permissions.

## The main controls

| Area | Control |
| --- | --- |
| **Access to the cluster API** | Integrate with the cloud identity provider; use Kubernetes **RBAC** with least privilege; avoid cluster-admin for people and pipelines; keep the API endpoint private where possible |
| **Workload restrictions** | Apply the **Pod Security Standards** (Privileged, Baseline, Restricted profiles) through built-in admission, so containers can't run privileged, as root or with host access unless explicitly allowed |
| **Network** | Use **network policies** to limit which pods can talk to which (by default, pods can usually reach each other), the cluster version of microsegmentation |
| **Secrets** | Store secrets in Kubernetes secrets with encryption enabled, or integrate an external secrets manager (module 4); limit who can read them |
| **Workload identity** | Give pods their own cloud identities (the provider's workload identity feature) instead of node-wide credentials |
| **Images** | Only from approved registries, scanned and signed (previous lesson) |
| **Upgrades** | Keep cluster and node versions within the supported window |
| **Logging** | Enable control-plane audit logs and send them to the central platform |

## Namespaces are organisation, not isolation

**Namespaces** group resources and are the unit for RBAC and many policies, but they are not a strong security boundary on their own. Workloads of very different sensitivity (for example payment processing and a public marketing site) are better placed in separate clusters or at least separate node pools with strict policies.

## A review checklist

When reviewing a Kubernetes deployment, ask:

1. Who can use the cluster API, with which roles?
2. Which Pod Security Standard level applies to each namespace?
3. Are network policies in place, and is the default "deny" between namespaces?
4. Where are secrets stored and who can read them?
5. Do workloads use their own identities?
6. Which registries are allowed, and are images scanned and signed?
7. Are audit logs enabled and collected?

:::takeaway
On managed Kubernetes the provider runs the control plane; you secure access, workload restrictions (Pod Security Standards), network policies, secrets, workload identities, images, upgrades and audit logging. Treat namespaces as organisation, and separate very different trust levels more strongly.
:::
