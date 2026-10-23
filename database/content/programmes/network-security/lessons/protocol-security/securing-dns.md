:::scenario The services everyone forgets
Kanem's network has firewalls, segmentation and detection. But every connection still starts with DNS, every device still gets its address from DHCP, and most traffic is web traffic. This module secures those everyday services, and shows how their logs help you decide when something needs escalating.
:::

DNS turns names into addresses (module 1, lesson 5). Because almost everything depends on it, DNS is both a critical service to protect and one of the richest sources of security evidence. NIST SP 800-81 Rev. 3, *Secure Domain Name System (DNS) Deployment Guide* (March 2026), covers secure deployment.

## Protect the DNS service itself

| Area | Practice |
| --- | --- |
| **Internal resolvers** | Staff devices and servers use Kanem's own resolvers, not arbitrary public ones, so queries are logged and filtering applies |
| **Separation** | Keep internal (private) zones separate from public zones; don't expose internal names on public servers |
| **Authoritative servers** | Restrict zone transfers to authorised secondaries; patch; monitor changes |
| **Resolver hardening** | Resolvers answer only internal clients; rate limiting where supported |
| **Registrar and hosting accounts** | Strong authentication and registrar locks on Kanem's public domains, since losing control of a domain redirects everything |

## DNSSEC

**DNSSEC** (introduced in RFC 4033) adds digital signatures to DNS data so resolvers can check that answers are authentic and unmodified. It protects **integrity**, not confidentiality.

- **Signing** your public zones protects people resolving your names.
- **Validating** on your resolvers protects your users from forged answers for signed domains.

It needs careful key management; a mistake can make a domain unreachable for validating resolvers.

## Encrypted DNS

**DNS over TLS (DoT, RFC 7858)** and **DNS over HTTPS (DoH, RFC 8484)** encrypt queries between client and resolver, protecting privacy on untrusted networks. For an organisation, the design question is **which resolver** devices use:

- Encrypted DNS to **Kanem's** resolvers keeps privacy and visibility.
- Browsers or applications sending encrypted DNS to **outside** resolvers bypass internal logging and filtering. Many organisations configure managed devices to use only the corporate resolver, and block or monitor direct connections to public resolvers.

## Protective DNS and filtering

**Protective DNS** resolvers refuse or redirect lookups for known malicious or unwanted domains. CISA operates a protective DNS service for US federal agencies, and commercial services offer the same idea to organisations. Filtering at the resolver is cheap, blocks many threats before any connection is made, and produces useful logs.

:::takeaway
Make devices use internal resolvers, harden and separate internal and public DNS, protect registrar accounts, sign and validate with DNSSEC where you can, use encrypted DNS to your own resolvers rather than around them, and add protective DNS filtering.
:::
