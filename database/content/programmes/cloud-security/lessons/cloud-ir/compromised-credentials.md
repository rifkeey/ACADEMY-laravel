## Why credentials are the main target

Cloud attackers rarely need to break infrastructure. Stolen credentials, leaked keys in code repositories, phished passwords and tokens taken from compromised laptops give them legitimate API access. MITRE ATT&CK's cloud matrix lists the techniques involved, with Valid Accounts (T1078) prominent among them.

## Signs of a compromised identity

- API calls from unfamiliar IP addresses, networks or countries.
- Activity in regions the organisation never uses.
- Reconnaissance calls: "who am I", listing users, roles, buckets and permissions.
- Bursts of access-denied errors as the attacker probes permissions.
- New access keys, new users or new role trust relationships (persistence).
- Policy changes that open data to the public or to another account.
- Logging disabled or modified (defence evasion).
- Unexpected compute launches, often for cryptocurrency mining.

## Investigation steps

1. **Scope the identity**: every action it performed in the window of interest, from every source IP.
2. **Identify persistence**: new keys, users, roles, federated trust or OAuth applications.
3. **Assess data access**: data-plane logs for reads and downloads, where they are enabled.
4. **Check for spread**: did the identity assume other roles or create new identities?
5. **Find the source of the leak**: code repository, CI system, laptop, phishing.

## Containment

Contain the identity completely and at once:

- disable or delete **all** its access keys, including any created by the attacker;
- revoke active sessions and tokens;
- remove attacker-created identities, keys and trust relationships;
- revert malicious policy changes;
- re-enable and protect logging.

Then rotate any secrets the identity could read, and continue monitoring closely. The capstone lab for this module walks through a compromised developer identity using a synthetic audit log.
