## The minimum networking every analyst needs

You do not need to be a network engineer to work in a SOC, but you must read network evidence fluently. Three ideas carry most investigations: addressing, ports and transports, and name resolution.

### Addresses

- **Private IPv4 ranges** (RFC 1918): `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`. Traffic between these is internal.
- **Documentation ranges** you will see in training data: `192.0.2.0/24`, `198.51.100.0/24`, `203.0.113.0/24`. AMANIQ labs use these so that no real organisation's address appears in an exercise.
- A `/24` contains 256 addresses; a `/16` contains 65,536. When an alert says "scanning from 198.51.100.0/24", it describes a range, not a single host.

### Ports and transports

TCP provides reliable, ordered delivery and begins with a three-way handshake (`SYN`, `SYN-ACK`, `ACK`). UDP sends datagrams with no handshake. Common ports you should recognise without looking them up:

| Port | Service | Analyst relevance |
| --- | --- | --- |
| 22/tcp | SSH | Brute force, remote administration |
| 53/udp, 53/tcp | DNS | Tunnelling, command-and-control lookups |
| 80, 443/tcp | HTTP, HTTPS | Web traffic, most C2 |
| 445/tcp | SMB | Lateral movement, file shares |
| 3389/tcp | RDP | Remote access, ransomware entry |
| 88/tcp | Kerberos | Domain authentication |

A connection attempt that never completes the handshake (many `SYN` packets with no `ACK`) often indicates scanning or a firewall drop.

### DNS

DNS translates names into addresses. Each query log records **who asked** (client IP), **what they asked for** (query name and type, e.g. `A`, `AAAA`, `TXT`) and **the answer**. DNS logs are valuable because almost all malware must resolve a name before it can talk to its infrastructure.

Patterns worth investigating:

- Many queries to long, random-looking subdomains of a single domain (possible DNS tunnelling).
- A host resolving a domain registered very recently that no other host contacts.
- Regular, evenly spaced queries (beaconing).
- `TXT` record queries from workstations, which are unusual for ordinary browsing.

```text
2026-03-04T09:12:01Z client=10.20.4.17 query=a7f3k2.cdn-update-check.example type=TXT rcode=NOERROR
2026-03-04T09:13:01Z client=10.20.4.17 query=b91qz8.cdn-update-check.example type=TXT rcode=NOERROR
2026-03-04T09:14:01Z client=10.20.4.17 query=c02mm4.cdn-update-check.example type=TXT rcode=NOERROR
```

Three facts stand out: a single internal host, a one-minute interval, and random-looking subdomains requested as `TXT`. Each fact alone might be harmless; together they justify investigation.
