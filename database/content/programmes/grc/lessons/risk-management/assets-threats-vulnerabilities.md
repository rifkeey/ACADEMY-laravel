## Start with what matters

You cannot assess risk to things you do not know you have. A risk assessment begins by identifying the **assets** in scope and deciding how much each matters to the business.

## Identifying and classifying assets

Assets include more than hardware:

- **Information**: customer records, financial data, intellectual property, credentials.
- **Systems and services**: core banking, email, payroll, the mobile app, cloud tenancies.
- **People and knowledge**: the one engineer who understands the legacy system.
- **Facilities and suppliers**: offices, data centres, outsourced IT.
- **Intangibles**: reputation, licences to operate.

Group assets sensibly; a register of 4,000 individual laptops is less useful for risk assessment than "staff laptops (≈ 400, managed, encrypted)". Then **classify** information by the harm its disclosure, alteration or loss would cause. A four-level scheme is common: *Public*, *Internal*, *Confidential*, *Restricted*. Each asset gets an owner who decides its classification and approves access.

## Threats

A threat is anything that could cause harm. Consider several sources:

| Source | Examples |
| --- | --- |
| Adversarial | Criminal groups (ransomware, fraud), insiders acting maliciously, hacktivists |
| Accidental | Staff mistakes, misconfiguration, data sent to the wrong recipient |
| Structural | Hardware failure, software defects, supplier outages |
| Environmental | Flooding, fire, prolonged power loss |

Use threat intelligence relevant to your sector and region. A Nigerian fintech faces persistent social engineering and SIM-swap fraud; a hospital faces ransomware that targets availability.

## Vulnerabilities and predisposing conditions

Vulnerabilities are weaknesses a threat can exploit: missing patches, absent MFA, untrained staff, single points of failure, unclear processes. NIST SP 800-30 also describes **predisposing conditions**, features of the environment that make exploitation more likely, such as relying on a single internet link or operating in a region with frequent power interruptions.

## Writing risk scenarios

Combine the elements into a **scenario** that can be rated:

> *A ransomware group (threat) gains access through a phishing email to a staff member without MFA (vulnerability), encrypts the file server and its attached backups (asset), and halts loan processing for a week (impact).*

Good scenarios are specific enough to rate and to choose controls for. "Cyber attack" is not a risk scenario; it is a category. Aim for ten to thirty well-written scenarios rather than hundreds of vague ones.

## Gathering information

Combine document review (network diagrams, previous audits, incident history), interviews with asset owners, technical data (vulnerability scans, configuration reports) and workshops where business and technical staff rate scenarios together. Record the source for each judgement so the assessment can be reviewed later.
