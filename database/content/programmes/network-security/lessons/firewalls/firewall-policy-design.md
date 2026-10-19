## Stateless and stateful filtering

A **stateless** filter (such as a basic router ACL) judges each packet in isolation, so return traffic needs its own rules. A **stateful** firewall tracks connections: once an outbound connection is allowed, its replies are permitted automatically. Most enterprise firewalls are stateful and add application awareness. NIST SP 800-41 Rev. 1 describes firewall technologies and policy guidance.

## Rule order and first match

Most firewalls evaluate rules top to bottom and apply the **first** matching rule. Consequences:

- A broad allow rule near the top silently overrides narrower deny rules below it.
- Specific rules should come before general ones.
- A final **default deny** (explicit or implicit) blocks anything not permitted.

```text
1  ALLOW  internet  → dmz       tcp/443
2  ALLOW  dmz       → servers   tcp/5432
3  ALLOW  users     → internet  tcp/443
4  DENY   any       → any       any        (default deny)
```

## Writing least-privilege rules

Each allow rule should name a specific source, destination, protocol and port, plus a business owner and justification in its description. Avoid `any` in allow rules unless the requirement truly is "any".

## Egress matters too

Outbound filtering is often neglected. Restricting which zones can reach the internet, and on which ports, makes command-and-control and data exfiltration harder and raises alerts when malware tries unusual ports.

## Common misconfigurations

- `ANY → ANY` allow rules left over from troubleshooting.
- Management interfaces (SSH, web admin) reachable from user or internet zones.
- Shadowed rules that can never match because an earlier rule catches their traffic.
- Rules for decommissioned systems.
- Logging disabled on deny rules, which hides scanning and misconfiguration.

## Rule review

Review rule sets on a schedule: confirm owners, remove unused rules (hit counters help), and check new rules against the design. Treat firewall changes as changes: ticketed, approved and recorded.
