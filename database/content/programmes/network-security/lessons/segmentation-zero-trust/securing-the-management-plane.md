## Three planes

Network devices are often described as having a **data plane** (forwarding user traffic), a **control plane** (routing protocols and device logic) and a **management plane** (how administrators configure and monitor the device). An attacker who controls the management plane controls the network, so it deserves the strongest protection.

## Secure administrative access

- Administer devices only from the **management zone** or a hardened jump host.
- Use encrypted protocols: **SSH** (not Telnet), **HTTPS** (not HTTP), **SNMPv3** (not v1/v2c with default community strings).
- Use **individual named accounts** with central authentication (RADIUS or TACACS+) and MFA, not a shared `admin` password.
- Apply **least privilege**: read-only roles for monitoring staff.
- Log all administrative sessions and configuration changes to a central log server.

## Configuration management

- Maintain a **baseline configuration** per device type. CIS Benchmarks provide hardening guidance for many network products.
- Store configurations in version control so changes can be compared and reverted.
- Disable unused services and interfaces.
- Set accurate time with authenticated NTP. Without correct time, logs cannot be correlated.

## Patch management

Track the firmware versions in use and vendor security advisories. Prioritise internet-facing devices such as firewalls and VPN gateways, which are frequently targeted soon after vulnerabilities are disclosed.

## Change control and backups

- Route configuration changes through change control: request, review, approval, implementation, verification.
- Back up configurations automatically after each change, and store backups securely. They contain sensitive information.
- Test restoration. A backup you cannot restore is not a backup.

## A quick audit checklist

- [ ] Management interfaces unreachable from user and internet zones
- [ ] No default or shared credentials
- [ ] SSH/HTTPS/SNMPv3 only
- [ ] Central logging and accurate time
- [ ] Firmware within vendor support and patched
- [ ] Configuration backed up and version-controlled
