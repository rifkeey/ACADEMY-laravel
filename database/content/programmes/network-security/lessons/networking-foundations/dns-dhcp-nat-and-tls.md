Four services make almost every connection possible: **DHCP** gives a device its address, **DNS** turns names into addresses, **NAT** lets private addresses reach the internet, and **TLS** protects the conversation. Each is also a favourite target, and each leaves evidence you will use in investigations.

## DHCP: how a device gets its configuration

When the loans officer's laptop joins the network it broadcasts for configuration (RFC 2131). The exchange is often remembered as **DORA**:

```text
laptop  ── Discover (broadcast: "any DHCP server?") ──────────►
        ◄── Offer   ("use 10.40.9.77/22, gateway 10.40.8.1, DNS 10.10.0.53")
        ── Request ("I'll take 10.40.9.77") ──────────────────►
        ◄── Ack     ("it's yours for 8 hours")
```

DHCP hands out the IP address, mask, **default gateway** and **DNS servers**. That makes it powerful: a **rogue DHCP server** (a misconfigured home router plugged in, or an attacker) can hand out a malicious gateway or DNS server and quietly redirect traffic. Switch features such as **DHCP snooping** only allow DHCP replies from trusted ports. DHCP server logs (which MAC got which IP, when) are also key evidence for tying an IP address in a log to a physical device.

## DNS: names to addresses

DNS (RFC 1034) is a distributed database. A lookup for `corebanking.kanem.example` usually goes:

```text
laptop ──► internal resolver 10.10.0.53 (cache?) ──► root ──► .example ──► kanem.example authoritative
       ◄── 10.10.0.15 ◄──────────────────────────────────────────────────
```

| Record type | Purpose | Example |
| --- | --- | --- |
| A / AAAA | Name → IPv4 / IPv6 address | `corebanking.kanem.example A 10.10.0.15` |
| CNAME | Alias to another name | `portal → corebanking` |
| MX | Mail servers for a domain | `kanem.example MX mail.kanem.example` |
| TXT | Free text (also used for email security records) | SPF, DKIM, DMARC |
| PTR | Address → name (reverse lookup) | `15.0.10.10.in-addr.arpa` |

Security relevance: almost every malicious connection starts with a DNS lookup, so **DNS logs are one of the richest data sources** for detecting malware and phishing. DNS is also abused directly (tunnelling data in queries, fast-changing domains) and attacked (spoofed answers, hijacked domains). Module 7 goes deep.

## NAT and PAT: private to public

Kanem's laptops use private RFC 1918 addresses, which the internet will not route. The edge firewall performs **NAT** (network address translation, RFC 3022). Almost always it is **PAT** (port address translation, also called NAT overload): many internal hosts share one public address, distinguished by port.

```text
Edge firewall translation table (extract, synthetic)
Inside (private)        Outside (public)          Destination
10.40.9.77:51602    →   198.51.100.10:40211   →   203.0.113.80:443
10.40.9.81:52700    →   198.51.100.10:40212   →   203.0.113.80:443
10.20.4.21:50111    →   198.51.100.10:40213   →   192.0.2.44:443
```

Two consequences for security work:

- **An external log only shows the public address.** If a website reports abuse from `198.51.100.10` at 14:02, you need the firewall's **NAT logs** to find which internal host it was. No NAT logs, no answer.
- **NAT is not a security control.** It hides internal addressing and blocks unsolicited inbound connections as a side effect of how translation works, but the firewall policy is what actually decides what is allowed.

## HTTPS and TLS basics

HTTPS is HTTP inside **TLS** (TLS 1.3 is RFC 8446). TLS gives:

- **Confidentiality**: traffic is encrypted, so the network sees *that* you talked, not *what* you said.
- **Integrity**: tampering is detected.
- **Server authentication**: the server proves its identity with a **certificate** issued for its name; the browser checks the certificate chain, the name and the validity dates.

What a network sensor can still see in TLS 1.3 without decryption: IP addresses, ports, timing and volume, and usually the **server name indication (SNI)** the client asks for (`corebanking.kanem.example`). Encrypted Client Hello can hide even that. This is why encrypted traffic shifts detection towards metadata, DNS and endpoint telemetry (module 5).

:::warning Certificate warnings are security events
Teaching users to click through certificate warnings undoes TLS. A sudden warning for an internal site can mean an expired certificate, or an interception attempt. Both deserve a ticket.
:::

## Putting it together: one connection, all four services

1. The laptop got `10.40.9.77/22`, gateway `10.40.8.1` and DNS `10.10.0.53` from **DHCP**.
2. It asked **DNS** for `corebanking.kanem.example` and got `10.10.0.15`.
3. `10.10.0.15` is not on its /22, so it sent the packet to the **gateway** (ARP for `10.40.8.1`), and the router sent it over the VPN to Lagos.
4. It completed a **TCP** handshake to port 443 and negotiated **TLS**, checking the server's certificate.
5. Had the destination been on the internet, the edge firewall would have applied **PAT**.

:::takeaway
DHCP configures the host, DNS finds the server, NAT/PAT carries private traffic to the internet, and TLS protects the conversation. Each one can be abused, and each one's logs (DHCP leases, DNS queries, NAT translations, TLS metadata) is evidence you will rely on.
:::
