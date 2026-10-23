Two more everyday connections need secure configuration: the protocols administrators use to manage systems, and the Wi-Fi that staff and guests use every day.

## Secure administrative protocols

| Use | Prefer | Avoid |
| --- | --- | --- |
| Command-line administration | **SSH** (RFC 4253 defines its transport layer) with key-based or certificate-based authentication | Telnet, rlogin (credentials in clear text) |
| Web administration | **HTTPS** with valid certificates | HTTP consoles |
| File transfer | **SFTP** or **SCP** over SSH; HTTPS | FTP, TFTP for anything sensitive |
| Network device monitoring | **SNMPv3** with authentication and encryption | SNMPv1/v2c with community strings |
| Remote desktop | RDP through a gateway or bastion, with network-level authentication and MFA | RDP reachable directly from untrusted networks |

Practices that apply to all of them:

- **Named accounts** and central authentication; no shared administrator logins.
- **MFA** for administrative access (lesson 5).
- **Management-zone only**: these services listen only on management interfaces (module 4).
- **Key management for SSH**: inventory authorised keys, remove those of leavers, and prefer short-lived SSH certificates where the tooling supports it.
- **Session logging** for privileged sessions.

## Wi-Fi security

NIST SP 800-153 provides guidelines for securing wireless LANs, and the Wi-Fi Alliance defines the current security certification, **WPA3**.

| Network | Recommended approach |
| --- | --- |
| **Staff Wi-Fi** | WPA3-Enterprise (or WPA2-Enterprise where devices require it) with 802.1X authentication, ideally certificate-based (Network Security module 3), placing devices in the right VLAN |
| **Guest Wi-Fi** | Separate network, isolated from internal zones, internet only, with client isolation; a registration portal if you need to identify users |
| **Devices that only support pre-shared keys** | A separate network with a strong, unique passphrase, restricted to what those devices need |

Additional controls:

- **Wireless monitoring** to detect unauthorised access points broadcasting Kanem's network names.
- **Protected management frames**, required by WPA3, to make it harder to disconnect clients.
- Treat Wi-Fi like any other zone: firewall policy between Wi-Fi and internal networks.

:::takeaway
Administer systems only over encrypted, authenticated protocols (SSH, HTTPS, SFTP, SNMPv3), from the management zone, with named accounts, MFA and logging. Run staff Wi-Fi on WPA3- or WPA2-Enterprise with 802.1X, keep guests fully isolated, separate pre-shared-key devices, and monitor for unauthorised access points.
:::
