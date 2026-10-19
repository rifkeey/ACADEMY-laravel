## What a VPN does

A virtual private network (VPN) creates an encrypted tunnel across an untrusted network. Two common uses:

- **Site-to-site**: connects office networks, usually with IPsec between gateways.
- **Remote access**: connects individual users to the corporate network, using IPsec or TLS-based VPN clients.

NIST SP 800-77 Rev. 1 is a detailed guide to IPsec VPNs.

## Tunnelling concepts

A tunnel encapsulates packets inside other packets. IPsec uses IKE (Internet Key Exchange) to authenticate the peers and negotiate keys, and ESP (Encapsulating Security Payload) to encrypt and authenticate traffic. TLS VPNs use the same TLS protocol as HTTPS.

**Split tunnelling** sends only corporate traffic through the VPN and the rest directly to the internet. It reduces load but means user traffic to the internet bypasses corporate controls. Decide deliberately and record the risk.

## Authentication

VPN gateways face the internet and are frequent attack targets. Protect them with:

- **Multi-factor authentication** for every user;
- **certificates** to authenticate devices as well as users, where possible;
- prompt **patching**, because VPN appliance vulnerabilities are regularly exploited at scale;
- restricting management interfaces to the management zone.

## Certificate basics

A certificate binds a public key to an identity and is signed by a certificate authority (CA). The client checks that the certificate is signed by a trusted CA, has not expired, matches the expected name and has not been revoked. An organisation may run its own internal CA for device certificates.

## Remote access is not full access

A VPN connection should not place the user on a flat internal network. Apply the zone model: VPN users get a zone of their own with rules allowing only the applications they need. Zero trust access products take this further by granting access per application.

## Monitoring

Log VPN authentications and sessions, and alert on logins from unusual countries, concurrent sessions for one user from different places, and logins outside normal hours for privileged users.
