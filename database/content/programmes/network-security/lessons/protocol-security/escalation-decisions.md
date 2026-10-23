This module's practical skill is deciding, from DNS, DHCP and web metadata, whether a host's behaviour needs escalating. This lesson brings the evidence sources together into one decision process.

## Joining the evidence

| Question | Source |
| --- | --- |
| Which device had this IP address at that time? | DHCP leases (or VPN address assignments) |
| Who was using it? | Proxy authentication logs, sign-in logs |
| What names did it look up? | DNS resolver logs |
| Where did it connect, and how much data moved? | Firewall and proxy logs, flow records |
| Is the destination known and legitimate? | Domain ownership and age, reputation services, internal knowledge |
| Is this normal for this device? | The same logs for previous days |

## An escalation rubric

Score the observations, then decide:

| Observation | Weight |
| --- | --- |
| Behaviour is new for this host | + |
| Destination is newly registered, rare or unknown to the business | + |
| Regular, machine-like timing | + |
| Unusual DNS patterns (long random subdomains, many failed lookups) | + |
| Data volume leaving is unusual for the host | ++ |
| Host is high-value (server, administrator workstation, restricted data) | ++ |
| Correlated alert from another source (endpoint, identity) | ++ |
| Clear benign explanation confirmed (owner, ticket, known product) | −− |

| Result | Action |
| --- | --- |
| Benign explanation confirmed | Close with the reason; consider tuning |
| One or two weak indicators | Monitor, check again later, document |
| Several indicators, or any strong one on a high-value host | **Escalate** to incident handling with the evidence (module 9) |
| Unclear but worrying | Escalate; never close as "nothing" without a reason |

## Writing the escalation

A good escalation lets the incident team act immediately:

- **What**: one sentence summarising the behaviour.
- **Who**: host, user, zone, owner.
- **When**: first and latest observation (UTC).
- **Evidence**: the specific log lines or counts, and where they came from.
- **Why it matters**: which indicators and why the host is important.
- **What you have and haven't checked**.
- **Recommended next step**, such as isolating the host after the incident lead agrees.

:::takeaway
Join DHCP, DNS, proxy, firewall and identity evidence to answer who, what, where and whether it is normal. Weigh the indicators, give heavy weight to data leaving high-value hosts and to correlated alerts, and escalate with a clear, evidence-based summary whenever several indicators line up.
:::
