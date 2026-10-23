TLS protects web traffic, many VPNs, APIs and email between servers. Its security depends on **configuration** and on **certificates** being issued, validated and renewed correctly. NIST SP 800-52 Rev. 2 gives guidance on selecting and configuring TLS.

## Configuring TLS well

| Setting | Guidance |
| --- | --- |
| Protocol versions | TLS 1.2 and 1.3; older versions (SSL, TLS 1.0, 1.1) disabled |
| Cipher suites | Strong, current suites with forward secrecy; follow NIST SP 800-52 Rev. 2 and vendor guidance |
| Certificates | Issued for the right names, from a trusted authority, with appropriate key lengths |
| HTTP Strict Transport Security (HSTS) | For websites, so browsers always use HTTPS |
| Internal traffic | Encrypt it too, especially across zone boundaries and in the cloud |

## How certificate validation works

When a client connects, it checks the server's certificate (the X.509 format is defined in RFC 5280):

1. **Chain**: the certificate is signed by an issuer the client trusts, directly or through intermediate certificates.
2. **Name**: the certificate is valid for the name the client asked for (for example `corebanking.kanem.example`).
3. **Dates**: the current time is within the validity period.
4. **Revocation and policy**: the certificate has not been revoked, and meets the client's policy.

If any check fails, the client should refuse the connection. **Applications that skip validation** (common in quick scripts and some internal tools) lose the server authentication that TLS is supposed to provide.

## Certificate management

Most certificate incidents are operational, not cryptographic:

| Problem | Prevention |
| --- | --- |
| Expired certificate breaks a service | An inventory of every certificate with its expiry and owner; automated renewal where possible; alerts well before expiry |
| Certificate issued for the wrong names | Review requests; use naming standards |
| Private key exposed | Store keys in protected key stores; restrict access; revoke and replace on suspicion |
| Unknown certificates appear | Monitor certificate transparency logs for certificates issued for your domains |

Organisations increasingly automate issuance and renewal because certificate lifetimes are getting shorter across the industry.

## Mutual TLS

In **mutual TLS (mTLS)**, the client also presents a certificate, so both ends authenticate. It is used between services (for example in service meshes, Cloud Security module 6), for partner APIs and for device authentication.

:::takeaway
Configure TLS 1.2/1.3 with strong suites, validate certificates (chain, name, dates, revocation) in every client, keep an inventory with owners and automated renewal, protect private keys, and use mutual TLS where both ends must be authenticated.
:::
