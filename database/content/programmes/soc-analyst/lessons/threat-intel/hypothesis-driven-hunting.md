## Hunting assumes detections are incomplete

Threat hunting is the proactive search for malicious activity that existing detections did not flag. It starts from the assumption that a capable adversary may already be present and has avoided your alerts.

Hunting is not "browsing logs until something looks odd". Effective hunts are structured.

## The hunt loop

1. **Hypothesis**: a testable statement. *"If an attacker is using DNS tunnelling for command and control, some internal host will make an unusually high number of queries to unique subdomains of a single parent domain."*
2. **Data**: which telemetry would prove or disprove it? (DNS query logs with client IP and query name.)
3. **Analysis**: queries, statistics and visualisation to find candidates.
4. **Investigation**: triage each candidate as you would an alert.
5. **Outcome**: findings, and, just as important, **new or improved detections**, data-collection gaps and documented baselines.

## Sources of hypotheses

- Threat intelligence about techniques used against your sector.
- ATT&CK techniques with **no** detection coverage in your environment.
- Recent incidents elsewhere in your organisation.
- Changes in your environment (a new VPN, a cloud migration, a merger).

## Example hunts

**Beaconing.** Command-and-control implants often call home at regular intervals. Group outbound connections by host and destination, and look for low variance in the time between connections:

```kql
DnsEvents
| summarize Queries = count(), Subdomains = dcount(Name) by ClientIP, ParentDomain
| where Subdomains > 50
| order by Subdomains desc
```

**Rare parent-child processes.** Count how often each parent/child process pair appears across all endpoints over 30 days. Pairs seen on only one or two hosts deserve a look.

**First-time behaviours.** A user who has never used PowerShell before suddenly runs encoded commands; a service account signs in interactively for the first time.

## Analysing IOCs during a hunt

When a hunt surfaces a suspicious domain, IP or hash:

- **Domain**: registration age, other hosts that resolved it, what IPs it resolved to, whether those IPs host other suspicious domains.
- **IP**: ownership (ASN), whether it is a hosting provider, VPN exit or residential range; historic passive DNS.
- **File hash**: prevalence in your estate (one host or many?), first-seen time, signer, path.

## Measuring a hunt

A hunt that finds no intrusion is still valuable when it produces a baseline, a detection, or proof that a data source is missing. Record these outcomes so that hunting's value is visible to management.
