Segmentation still assumes, to some degree, that being on the right network means you are allowed in. **Zero trust** removes that assumption: no user, device or workload is trusted because of *where* it is; every access is decided on *who* and *what* it is, right now.

## What NIST SP 800-207 says

NIST SP 800-207, *Zero Trust Architecture*, describes zero trust as a set of principles rather than a product. Its tenets include, in summary:

- All data sources and computing services are treated as **resources**.
- All communication is secured **regardless of network location**.
- Access is granted **per session**, to individual resources.
- Access is decided by **dynamic policy**: identity, device state, behaviour and other attributes.
- The organisation **monitors the integrity and security posture** of all its assets.
- Authentication and authorisation are **dynamic and strictly enforced** before access.
- The organisation **collects information** about assets, network traffic and access requests and uses it to improve security.

## The logical architecture

```text
   user + device ──request──► PEP (Policy Enforcement Point) ───► resource (application, data)
                                   ▲  allow / deny
                                   │
                         PA (Policy Administrator)
                                   ▲
                         PE (Policy Engine)  ◄── identity provider, device management,
                                                 threat intelligence, logs, data classification
```

- The **policy engine** makes the decision, using many signals.
- The **policy administrator** tells the enforcement point to open or close the session.
- The **policy enforcement point** sits in front of the resource: a gateway, proxy, agent or the application itself.

## In practice: what changes

| Traditional | Zero trust |
| --- | --- |
| VPN puts the user "on the network"; they can then reach many systems | **Zero trust network access (ZTNA)** gives the user access to specific applications only, after checking identity and device |
| Trust once at login | Re-evaluate continuously (device falls out of compliance → access reduced) |
| Firewall rules by IP range | Policies by identity, device and application |
| Internal traffic often unencrypted | Encrypt and authenticate everywhere, including inside |

## A maturity journey, not a switch

CISA's **Zero Trust Maturity Model** (version 2.0) organises the work into five pillars (**Identity, Devices, Networks, Applications and Workloads, Data**) plus cross-cutting capabilities (visibility and analytics, automation and orchestration, governance), and four stages: **Traditional, Initial, Advanced, Optimal**. It is a useful way to plan: few organisations are "optimal" anywhere, and progress is pillar by pillar.

A realistic sequence for Kanem:

1. **Identity first**: single sign-on and phishing-resistant MFA for all staff, strongest for administrators.
2. **Device health**: only managed, compliant devices can reach sensitive applications.
3. **Application access**: replace "full VPN" for contractors with application-level access (ZTNA) to the systems they need.
4. **Segmentation and microsegmentation** (lessons 2–3) to limit what any identity or device can reach if policy fails.
5. **Visibility**: every access decision logged and fed back into detection.

:::note Zero trust and segmentation work together
Zero trust does not make network segmentation obsolete. NIST's own guidance treats network controls as one of the enforcement layers. If an identity is compromised, segmentation still limits what can be reached; if a network control fails, identity checks still apply.
:::

:::reveal Practice: the contractor request
A software contractor needs to support the loans application for three months. The current practice is to give contractors a full VPN account. What would a zero-trust approach look like?

*Answer:* a named account in Kanem's identity provider with MFA, access granted only to the loans application (through a ZTNA gateway or the application's own access control), only from a compliant device or a managed virtual desktop, time-limited to the contract, logged, and reviewed at the end. No network-level access to anything else.
:::

:::takeaway
Zero trust means no implicit trust from network location: each access is decided per session using identity, device and context, by a policy engine and enforced in front of the resource. Treat it as a journey across identity, devices, networks, applications and data, and keep segmentation as a second line of defence.
:::
