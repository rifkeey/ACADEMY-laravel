IPsec is the standard for protecting IP traffic between gateways and hosts, and the technology behind most site-to-site VPNs. NIST SP 800-77 Rev. 1, *Guide to IPsec VPNs*, is the reference for planning and configuring it. This lesson covers the concepts you need to read a VPN configuration and troubleshoot a tunnel.

## What IPsec provides

- **Confidentiality**: traffic is encrypted.
- **Integrity**: tampering is detected.
- **Peer authentication**: each end proves its identity, with certificates or a pre-shared key.
- **Replay protection**: captured packets can't be re-sent later.

## Two phases

IPsec tunnels are set up with **IKE** (Internet Key Exchange; IKEv2 is the current version):

| Phase | What happens | What must match on both ends |
| --- | --- | --- |
| **IKE SA** (phase 1) | The two gateways authenticate each other and set up a secure channel for negotiation | Authentication method and credentials, encryption and integrity algorithms, key exchange group |
| **IPsec / Child SA** (phase 2) | They agree how to protect the actual traffic and **which traffic** goes through the tunnel | Algorithms, and the traffic selectors (which subnets on each side) |

Most "tunnel won't come up" problems are a **mismatch** in one of these: different algorithms, wrong credentials, or traffic selectors that don't mirror each other (head office says Abuja is `10.40.8.0/22`, Abuja says it is `10.40.0.0/16`).

## Configuration choices

| Choice | Guidance |
| --- | --- |
| IKE version | IKEv2 |
| Authentication | Certificates are stronger and easier to manage at scale than shared keys; if pre-shared keys are used, make them long, random and unique per tunnel |
| Algorithms | Current, strong algorithms as recommended by NIST SP 800-77 Rev. 1 and your vendor; disable legacy options |
| Perfect forward secrecy | On, so a compromised long-term key doesn't expose past traffic |
| Lifetimes | Reasonable re-keying intervals, matched on both sides |
| Resilience | Two tunnels to different gateways, monitored |

## Tunnel mode and transport mode

- **Tunnel mode** encrypts the whole original packet and adds a new header between the gateways. Site-to-site VPNs use it.
- **Transport mode** protects the payload between two hosts and keeps the original header. It is less common in enterprise VPNs.

## What a VPN does not do

A tunnel only protects traffic **in transit between the gateways**. It does not decide what traffic is allowed once it arrives. Traffic from a branch tunnel should still pass through firewall policy (modules 3 and 4): a compromised branch PC must not have open access to the data centre just because the link is encrypted.

:::takeaway
IPsec gives encryption, integrity, authentication and replay protection, negotiated in two phases by IKEv2. Most tunnel failures are mismatches in authentication, algorithms or traffic selectors. Use certificates or strong unique keys, current algorithms and forward secrecy, and keep firewall policy on traffic that leaves the tunnel.
:::
