A target architecture is only as good as its parts and only as trustworthy as its testing. Hardening makes each device secure; assessment proves that the whole design does what it claims.

## Hardening network devices

| Area | Practice |
| --- | --- |
| **Baseline** | Apply a recognised configuration benchmark (for example CIS Benchmarks for the device type) and document deviations |
| **Management plane** | Administration only from the management zone or jump host; SSH and HTTPS only; no Telnet or HTTP (module 6) |
| **Authentication** | Central authentication (RADIUS or TACACS+) with MFA for administrators; local accounts only for break-glass, with strong unique passwords stored securely |
| **Services** | Disable unused services, ports and protocols; use SNMPv3 rather than v1/v2c |
| **Logging and time** | Send logs to the central collector; synchronise time with NTP (module 9) |
| **Firmware** | Supported versions only; patch according to severity and exposure; track end-of-life dates |
| **Physical** | Locked rooms and cabinets; unused switch ports disabled or placed in an isolated VLAN |

## Security assessment

NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment*, describes three groups of techniques:

| Group | Examples | Purpose |
| --- | --- | --- |
| **Review** | Documentation, rule-set and configuration review, log review | Find weaknesses without touching live systems (modules 3 and 7 labs) |
| **Target identification and analysis** | Network discovery, port and service identification, vulnerability scanning | Confirm what is actually exposed |
| **Target vulnerability validation** | Penetration testing by authorised testers | Show whether weaknesses can really be used, and how far |

## Rules of engagement

Testing live networks needs **written authorisation** before anything starts:

| Item | Content |
| --- | --- |
| Scope | Exactly which networks, systems and addresses are in and out of scope |
| Timing | Dates, times and blackout periods |
| Methods | Which techniques are allowed; anything that could disrupt services needs explicit approval |
| Contacts | Who to call if something breaks or a real incident is found |
| Data handling | How findings and any data seen are protected and destroyed |
| Sign-off | The system owner and someone with authority over the systems |

Kanem's own staff can review configurations and run approved scanners on networks they manage. Penetration tests are normally done by an independent, qualified provider under contract.

## Validating the target architecture

Each design claim should have a test, for example:

- From a host in the guest Wi-Fi, no internal address answers (approved test from a guest laptop).
- From the DMZ, only the documented ports on the documented servers are reachable.
- From the users zone, the management zone is unreachable except through the jump host.
- Every allow rule crossing a boundary generates a log entry in the SIEM.
- Failover of the perimeter firewall pair completes within the agreed time.

:::takeaway
Harden every device against a benchmark: management plane, central MFA authentication, minimal services, logging, NTP, supported firmware and physical security. Assess with review, identification and validation techniques (SP 800-115), always under written rules of engagement, and turn each design claim into a test.
:::
