Good security architecture survives staff changes, audits and incidents because it is written down. Documentation turns your design into something others can build, operate, test and defend.

## The architecture pack

| Document | Contents |
| --- | --- |
| **Current state** | Zones, addressing, devices, key flows and known weaknesses, with a diagram |
| **Target architecture** | Target zones and trust boundaries, the flows each zone needs, the controls at each boundary, with a diagram |
| **Segmentation rationale** | Why each zone exists and why each allowed flow is needed (owner, business purpose) |
| **Network security policy** | The rules everyone follows: default deny, least privilege, logging on boundary rules, change control, rule review intervals, remote and vendor access, device hardening standards |
| **Monitoring plan** | Which logs from which devices go to the SIEM, retention, the detections that matter for this design, who responds (modules 5 and 9) |
| **Risk register and roadmap** | Lesson 4 |
| **Validation checklist** | Tests that prove the design works (lesson 3) |
| **Decision records** | Major choices, options and reasons |

## Diagrams that help

- Show **zones as boxes** and **trust boundaries** clearly; label address ranges.
- Show **flows as arrows** with port and purpose for the important ones.
- Mark where **controls** sit: firewalls, NAC, IDS sensors, proxies.
- Keep a version number and date on every diagram; old diagrams cause mistakes.

## Mapping to a framework

Mapping the design to a recognised framework helps auditors and the board see coverage. NIST CSF 2.0 has six functions:

| Function | Network examples |
| --- | --- |
| **Govern** | Network security policy, roles, risk acceptance, supplier (vendor) access rules |
| **Identify** | Asset and flow inventory, risk register |
| **Protect** | Segmentation, least-privilege rules, NAC, hardening, secure remote access |
| **Detect** | Logging, IDS, SIEM correlation |
| **Respond** | Incident playbooks, containment options (module 9) |
| **Recover** | Configuration backups, redundancy, tested restoration |

CIS Controls v8.1 (especially Controls 12 and 13 on network infrastructure management and network monitoring and defence) offer a more detailed checklist.

## Keeping it alive

- Assign an **owner** for the architecture pack.
- Update it as part of change control: no significant network change is complete until the documents are updated.
- Review it fully once a year, and after every major incident.

:::takeaway
Produce an architecture pack: current and target state with diagrams, segmentation rationale, network security policy, monitoring plan, risk register and roadmap, validation checklist and decision records. Map it to CSF 2.0 (and CIS Controls for detail), give it an owner, and update it with every significant change.
:::
