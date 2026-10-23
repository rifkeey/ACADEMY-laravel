Many network protocols were designed for trusted networks and believe what they are told. **Spoofing** means pretending to be another address, device or service. When it succeeds, traffic meant for someone else passes through the impersonator, which MITRE ATT&CK calls *Adversary-in-the-Middle* (T1557). This lesson covers where that risk exists, how to spot it and how to prevent it.

## Where impersonation is possible

| Layer | What can be impersonated | Who could do it |
| --- | --- | --- |
| **Local network** | The default gateway's hardware address (address resolution on a LAN trusts any reply) | Any device on the same VLAN |
| **Address assignment** | The DHCP server, handing out a false gateway or DNS server (module 7) | Any device on the same VLAN |
| **Name resolution** | DNS answers, or local name-resolution fallbacks some operating systems use | A device on the LAN, or a compromised resolver |
| **Wireless** | A whole Wi-Fi network with a familiar name | Anyone within radio range |
| **Internet** | The source address of packets, to hide where they come from or to direct replies at a victim | Anyone, where networks do not filter forged sources |

The common thread: the protocol accepts a claim ("I am the gateway", "this is the bank's address") without proof.

## What the impact can be

- Traffic between users and servers is read or altered.
- Users are sent to the wrong server.
- Unencrypted credentials are captured.
- Connections are disrupted.

Strong encryption with proper certificate checking limits most of this, which is why it is the most important control in this lesson.

## Signs to look for

| Signal | Where |
| --- | --- |
| The gateway's hardware address suddenly changes, or two devices claim the same IP address | Switch logs, host operating system warnings, network monitoring |
| DHCP replies from unexpected ports | DHCP snooping violation logs (module 7) |
| Address-resolution replies dropped by inspection | Dynamic ARP inspection logs on switches |
| Certificate warnings for internal or well-known sites | User reports, endpoint and browser telemetry |
| A familiar Wi-Fi name broadcast by an unknown access point | Wireless controller's rogue-access-point detection |
| DNS answers that differ from authoritative data, or validation failures | Resolver logs, DNSSEC validation (module 7) |
| Traffic arriving from the internet with internal or reserved source addresses | Perimeter firewall and router logs |

## Preventing it

| Control | What it stops |
| --- | --- |
| **TLS everywhere, with certificate validation** (and HSTS for websites) | Reading or altering traffic even if it is redirected; users must never be trained to click through certificate warnings |
| **DHCP snooping** plus **dynamic ARP inspection** on access switches | False DHCP servers and forged address-resolution replies |
| **802.1X / NAC** (module 3) | Unknown devices getting onto the LAN at all |
| **Port security and small VLANs** | Limits how many devices share a segment |
| **Disable legacy name-resolution fallbacks** on managed devices | Answers from any device on the LAN |
| **DNSSEC validation and internal resolvers** (module 7) | Forged DNS answers for signed domains |
| **WPA3- or WPA2-Enterprise** with clients validating the server certificate (module 6) | Clients joining an impostor network silently |
| **Anti-spoofing filtering** at the edge: drop inbound packets with internal or reserved sources, and outbound packets with sources that aren't yours (BCP 38, RFC 2827) | Forged source addresses entering or leaving your network |

:::note Encryption is the backstop
Switch features stop impersonation on the local network, but traffic also crosses networks you don't control. Encryption with certificate checking protects it everywhere, so treat it as essential, not optional.
:::

:::takeaway
Spoofing works because some protocols trust unverified claims. Watch for gateway address changes, duplicate addresses, snooping and inspection drops, certificate warnings, rogue access points and forged sources. Prevent it with TLS and certificate validation, DHCP snooping and ARP inspection, NAC, enterprise Wi-Fi, DNSSEC validation, legacy-protocol removal and anti-spoofing filters.
:::
