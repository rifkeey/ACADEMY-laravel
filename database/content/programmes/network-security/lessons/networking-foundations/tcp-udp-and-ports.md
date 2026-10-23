IP gets packets to the right host. **TCP and UDP** get data to the right *application* on that host, and their behaviour is what firewalls, IDS sensors and investigators read most often.

## Ports

A **port** is a 16-bit number (0–65535) that identifies an application endpoint. A connection is identified by five values, the **5-tuple**: protocol, source IP, source port, destination IP, destination port.

| Range | Name | Meaning |
| --- | --- | --- |
| 0–1023 | System (well-known) ports | Standard services: 22 SSH, 53 DNS, 80 HTTP, 443 HTTPS |
| 1024–49151 | User (registered) ports | Registered applications: 3389 RDP, 5432 PostgreSQL |
| 49152–65535 | Dynamic / ephemeral ports | Temporary client-side ports chosen by the operating system |

These ranges are defined in RFC 6335, and IANA keeps the official registry of assigned ports. A port number is a **convention, not proof**: nothing stops malware from using TCP 443 for something that is not HTTPS, which is why application-aware inspection exists.

| Port | Service | Security note |
| --- | --- | --- |
| 22/tcp | SSH | Administrative access: restrict to management networks |
| 25/tcp | SMTP | Only mail servers should send on it; outbound 25 from laptops suggests spam malware |
| 53/udp, 53/tcp | DNS | Essential, and abused for tunnelling (module 7) |
| 80/tcp, 443/tcp | HTTP, HTTPS | Most traffic, and most command-and-control hides here |
| 445/tcp | SMB (file sharing) | Should never be exposed to the internet; common in lateral movement |
| 3389/tcp | RDP | Frequently brute-forced when exposed |

## TCP: reliable, connection-oriented

TCP (RFC 9293) sets up a connection with a **three-way handshake**, numbers every byte so lost data is retransmitted, and closes the connection explicitly.

```text
laptop 10.40.9.77:51544                         server 10.10.0.15:443
        │ ── SYN, seq=1000 ───────────────────────────► │   "I want to talk"
        │ ◄─────────────── SYN-ACK, seq=5000, ack=1001 ─ │   "OK, me too"
        │ ── ACK, ack=5001 ────────────────────────────► │   connection ESTABLISHED
        │ ══ data (TLS, HTTP) ════════════════════════ ═ │
        │ ── FIN ──► … ◄── FIN … ACK                     │   orderly close
```

Flags to recognise in logs and captures:

| Flag | Meaning | What unusual patterns suggest |
| --- | --- | --- |
| SYN | Start a connection | Many SYNs to many ports: **port scan**; floods of SYNs: **SYN flood** |
| SYN-ACK | Accept | A SYN-ACK proves the port is open and listening |
| RST | Abort / refuse | SYN answered by RST: port **closed**; scanners generate lots of these |
| FIN | Orderly close | |
| No reply to SYN | | Packet **filtered** by a firewall (or the host is down) |

These three outcomes (SYN-ACK, RST, silence) are exactly how scanners classify ports as open, closed or filtered, and how you can recognise scanning in a capture.

## UDP: fast and connectionless

UDP (RFC 768) just sends datagrams: no handshake, no delivery guarantee, no ordering. DNS queries, DHCP, NTP, VoIP and video use it. Security consequences:

- Stateful firewalls track UDP with **timeouts**, since there is no handshake to watch.
- UDP source addresses are **easy to spoof**, which is why UDP services are used in **reflection and amplification** denial-of-service attacks.
- "No response" from a UDP port does not prove it is closed.

## Reading live connections on a host

```text
$ ss -tn            (Linux; on Windows: netstat -ano)
State   Local Address:Port     Peer Address:Port
ESTAB   10.40.9.77:51544       10.10.0.15:443
ESTAB   10.40.9.77:51602       203.0.113.80:443
SYN-SENT 10.40.9.77:51610      198.51.100.200:4444
```

*(Synthetic output.)* The last line is worth a second look: a laptop trying to reach an internet host on **TCP 4444**, a port with no business purpose at Kanem. If egress filtering is in place (module 3), this shows up as a logged deny; if not, it may succeed.

:::takeaway
Ports identify applications by convention; the 5-tuple identifies a connection. TCP's handshake and flags tell you whether ports are open, closed or filtered and expose scanning; UDP is connectionless and easy to spoof. Unusual ports and patterns are some of the earliest signs of trouble.
:::
