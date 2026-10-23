Firewall rules, routing tables, segmentation designs and log investigations are all written in IP addresses and prefixes. If you can't read `10.40.8.0/22` instantly, every later module will be harder. This lesson gets you there.

## IPv4 addresses and prefixes

An IPv4 address is 32 bits, written as four decimal numbers (octets) from 0 to 255: `10.40.9.77`. The **prefix length** (CIDR notation, RFC 4632) says how many of those bits identify the **network**; the rest identify the **host** within it.

| Notation | Mask | Network bits | Host bits | Addresses | Usable hosts |
| --- | --- | --- | --- | --- | --- |
| /24 | 255.255.255.0 | 24 | 8 | 256 | 254 |
| /23 | 255.255.254.0 | 23 | 9 | 512 | 510 |
| /22 | 255.255.252.0 | 22 | 10 | 1,024 | 1,022 |
| /26 | 255.255.255.192 | 26 | 6 | 64 | 62 |
| /30 | 255.255.255.252 | 30 | 2 | 4 | 2 |

**Usable hosts = 2^(host bits) − 2**: the first address is the **network address** and the last is the **broadcast address**. (A /31, used on point-to-point links, and a /32, a single host, are special cases.)

## Working out a subnet by hand

Find the network that contains `10.40.9.77/22`:

1. /22 means the first 22 bits are the network. 22 = 8 + 8 + 6, so the first two octets are fully network, and **6 bits of the third octet** are network.
2. Block size in the third octet = 2^(8−6) = **4**. Networks start at multiples of 4: 0, 4, 8, 12 …
3. 9 falls in the block starting at **8**. So the network is `10.40.8.0/22`.
4. The block covers third-octet values 8, 9, 10, 11. Range: `10.40.8.0` – `10.40.11.255`.
5. Network address `10.40.8.0`, broadcast `10.40.11.255`, usable hosts `10.40.8.1` – `10.40.11.254` (1,022 hosts).

:::reveal Practice: is 10.40.12.5 in the same /22?
No. The block for third-octet 12 starts a new /22 (`10.40.12.0/22`, covering 12–15). Two hosts are only on the same subnet if their network addresses match.
:::

:::tip Subnetting refresher
If binary is rusty, remember the "magic numbers" for the interesting octet: /17–/24 → 128, 64, 32, 16, 8, 4, 2, 1 (third octet); /25–/32 → the same in the fourth octet. The block size tells you where every subnet starts.
:::

## Private and special addresses

RFC 1918 reserves three ranges for private networks. They are not routed on the internet, so traffic from them must be translated (NAT, lesson 5) to reach it.

| Range | CIDR | Typical use |
| --- | --- | --- |
| 10.0.0.0 – 10.255.255.255 | 10.0.0.0/8 | Large enterprises (Kanem uses this) |
| 172.16.0.0 – 172.31.255.255 | 172.16.0.0/12 | Medium networks, often guest or lab networks |
| 192.168.0.0 – 192.168.255.255 | 192.168.0.0/16 | Homes and small offices |

Other addresses you will see in logs: `127.0.0.1` (loopback, the host itself), `169.254.x.x` (link-local: the host tried DHCP and got no answer, a classic troubleshooting clue), and documentation ranges such as `192.0.2.0/24`, `198.51.100.0/24` and `203.0.113.0/24`, used for examples (including in this course).

## IPv6 in brief

IPv6 (RFC 8200, addressing in RFC 4291) uses **128-bit** addresses written in hexadecimal groups: `2001:db8:40:9::4d`. `::` replaces one run of zero groups. Things a security professional must know:

- LANs are normally **/64**, which is far too many addresses to scan exhaustively the way IPv4 subnets are scanned.
- Every interface has a **link-local** address (`fe80::/10`) even if nobody configured IPv6, so IPv6 may be active on your network whether or not you planned it. Firewalls and monitoring must cover it too.
- IPv6 is designed for end-to-end addressing; NAT is not normally used. Don't assume "private addresses mean unreachable": filtering does that job.
- `2001:db8::/32` is the documentation range.

## Why this matters for security

| Situation | What subnetting tells you |
| --- | --- |
| A firewall rule allows `10.40.0.0/16` | It covers 65,536 addresses: every branch, not just Abuja |
| A host in the logs is `10.40.9.77` | It is in the Abuja staff subnet `10.40.8.0/22` (from the address plan) |
| A laptop shows `169.254.20.11` | It never received an address from DHCP |
| A designer proposes `/16` for the guest network | Far bigger than needed, and easy to overlap with internal ranges |

:::takeaway
Read every address with its prefix. The prefix defines the network, the network defines what is "local", and every rule, route and investigation depends on getting that right.
:::
