A sensor can only detect what it sees. Where you place sensors, and which **telemetry** you keep besides alerts, decides what your SOC can detect and investigate.

## Getting traffic to a sensor

| Method | How it works | Notes |
| --- | --- | --- |
| **Mirror (SPAN) port** | The switch copies traffic from chosen ports or VLANs to the sensor port | Easy, but the switch may drop copies under load |
| **Network tap** | A passive hardware device copies traffic from a link | Reliable and complete; needs physical installation |
| **Inline** | Traffic passes through the device (IPS) | Required for blocking; must not become a bottleneck or single point of failure |
| **Cloud traffic mirroring** | Provider features copy virtual network traffic to a sensor | Available in major clouds; costs scale with volume |

## Where to look

| Location | What it catches | Kanem example |
| --- | --- | --- |
| **Internet edge**, inside the firewall | Inbound attacks that the firewall allowed; outbound command-and-control | Between FW-HQ-01 and the internal zones |
| **In front of the DMZ** | Attacks on public services | Before the public website |
| **Data-centre core / between zones** | East–west movement towards critical systems | Between app, db and cards zones |
| **Remote-access entry** | Activity from VPN users | After the VPN concentrator |

The east–west position is often the most neglected and the most valuable: an attacker who has already got in is moving **inside**.

## Telemetry beyond alerts

Alerts tell you *something might be wrong*. Investigations need *what actually happened*. Keep at least:

| Telemetry | What it records | Typical source |
| --- | --- | --- |
| **Connection (flow) records** | Who talked to whom, when, ports, bytes, duration | NetFlow/IPFIX from routers, firewall logs, cloud flow logs, Zeek connection logs |
| **Protocol logs** | DNS queries and answers, HTTP requests, TLS server names and certificates | Zeek, DNS servers, proxies |
| **Alert logs** | Detections with context | IDS/IPS |
| **Full packet capture** (selectively) | Every byte | Short retention, critical segments only, or triggered by alerts |

Full packet capture is expensive and raises privacy questions, so most organisations keep **flow and protocol logs for weeks or months** and **packets for days or only on demand**. Module 8 works with packet captures in Wireshark.

## Time and context

Network evidence is only useful if it can be **joined** with other evidence:

- **Synchronised time** (NTP) on every sensor and device, logged in UTC.
- **Asset context**: which IP addresses belong to which system and owner, from the address plan and DHCP logs (module 1).
- **Central collection** in a SIEM (module 9), so an alert can be correlated with authentication and endpoint logs.

:::takeaway
Place sensors where traffic you care about passes, especially between internal zones, using taps or mirror ports for detection and inline only where you need blocking. Keep flow and protocol logs long enough to investigate, packets selectively, and make sure time and asset context let you join network evidence with everything else.
:::
