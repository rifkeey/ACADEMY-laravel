## Why a prioritised control set helps

Frameworks like CSF 2.0 describe outcomes; ISO/IEC 27001 describes a management system. Neither tells a small IT team which ten things to do first on Monday. The CIS Critical Security Controls fill that gap with a **prioritised, prescriptive** set of safeguards drawn from what attackers actually exploit.

## The 18 Controls in v8.1

| # | Control | # | Control |
| --- | --- | --- | --- |
| 1 | Inventory and Control of Enterprise Assets | 10 | Malware Defenses |
| 2 | Inventory and Control of Software Assets | 11 | Data Recovery |
| 3 | Data Protection | 12 | Network Infrastructure Management |
| 4 | Secure Configuration of Enterprise Assets and Software | 13 | Network Monitoring and Defense |
| 5 | Account Management | 14 | Security Awareness and Skills Training |
| 6 | Access Control Management | 15 | Service Provider Management |
| 7 | Continuous Vulnerability Management | 16 | Application Software Security |
| 8 | Audit Log Management | 17 | Incident Response Management |
| 9 | Email and Web Browser Protections | 18 | Penetration Testing |

Each Control contains numbered **Safeguards** (for example, 5.1 is about keeping an inventory of accounts). Each Safeguard is tagged with an asset type and a security function. Version 8.1, released in 2024, updated these tags and definitions to align with NIST CSF 2.0, including the Govern function.

The order is deliberate: you cannot protect assets or software you have not inventoried, so Controls 1 and 2 come first.

## Implementation Groups

| Group | Typical organisation | Focus |
| --- | --- | --- |
| IG1 | Small to medium, limited IT expertise, mostly protecting business data | Essential cyber hygiene: about 56 Safeguards that defend against common, non-targeted attacks |
| IG2 | Multiple departments, dedicated IT staff, some regulatory obligations | IG1 plus Safeguards for more complex environments and sensitive data |
| IG3 | Security specialists on staff, sensitive data, targeted-attack exposure | All Safeguards, including those requiring specialist skills |

Each group includes the one before it. An organisation should achieve IG1 before claiming IG2.

## Choosing an Implementation Group

Consider data sensitivity, regulatory obligations, the size and skills of the IT team, and the attackers you realistically face. A 40-person microfinance company with an outsourced core banking platform would sensibly target IG1 now and selected IG2 Safeguards (for example around service provider management and audit logs) driven by its risk register.

## Measuring progress

Track each Safeguard as *not implemented*, *partially implemented*, *implemented on some assets* or *implemented on all assets*, with evidence. Coverage matters: MFA "implemented" for 60% of accounts is not the same as for 100%. CIS also publishes free tools and companion guides, such as a risk assessment method and mappings to other frameworks, on its website.

## Strengths and limits

The Controls are concrete and actionable, which makes them excellent for technical roadmaps. They are less concerned with governance, risk appetite or management review, so most organisations pair them with a governance framework such as CSF 2.0 or an ISMS.
