## Why models matter

Network models give you a vocabulary for *where* a problem or control sits. The **OSI model** has seven layers (physical, data link, network, transport, session, presentation, application). The **TCP/IP model** used on the internet collapses these into four: link, internet, transport and application. Security controls map to layers: switch port security at layer 2, routing ACLs at layer 3, firewalls filtering ports at layer 4, web application firewalls at layer 7.

## IPv4 addressing and subnetting

An IPv4 address is 32 bits, written as four octets (`10.20.4.17`). A prefix length such as `/24` says how many leading bits identify the network.

| Prefix | Mask | Addresses | Usable hosts |
| --- | --- | --- | --- |
| /24 | 255.255.255.0 | 256 | 254 |
| /26 | 255.255.255.192 | 64 | 62 |
| /28 | 255.255.255.240 | 16 | 14 |
| /30 | 255.255.255.252 | 4 | 2 |

Example: `10.20.4.64/26` covers `10.20.4.64`–`10.20.4.127`. The first address is the network address and the last is broadcast.

Private ranges (RFC 1918) are `10.0.0.0/8`, `172.16.0.0/12` and `192.168.0.0/16`. Training material uses the documentation ranges `192.0.2.0/24`, `198.51.100.0/24` and `203.0.113.0/24`.

## IPv6 essentials

IPv6 addresses are 128 bits, written in hexadecimal groups (`2001:db8::1`; `2001:db8::/32` is reserved for documentation). Subnets are normally `/64`. Many networks run IPv6 alongside IPv4, sometimes without anyone monitoring it: an IPv6 path that bypasses IPv4 firewall rules is a classic blind spot.

## Routing

Routers forward packets between networks using a routing table: the most specific matching prefix wins, and a **default route** (`0.0.0.0/0`) catches everything else. From a security view, routing defines which networks *can* talk; firewalls define which traffic *may*.

## Core protocols at a glance

- **DNS** (UDP/TCP 53): name resolution, a rich source of security evidence.
- **DHCP** (UDP 67/68): automatic address assignment; DHCP logs map IPs to devices over time.
- **HTTP/HTTPS** (TCP 80/443): web traffic; HTTPS wraps HTTP in TLS.
- **TLS**: encryption and server authentication using certificates; TLS 1.3 is current.
