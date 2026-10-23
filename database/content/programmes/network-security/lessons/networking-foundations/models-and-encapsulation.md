:::scenario Your first ticket
It is your first week in Kanem Financial Services' security team. A ticket arrives from the Abuja branch: *"Loans officer cannot open the core banking app. Printer works fine. Internet is slow."* Your manager says: "Before you touch any security tool, work out where in the network the problem is." This module gives you the mental model to do that, and it is the same model you will use to understand every attack and every control in this course.
:::

Every network conversation, from loading a web page to an attacker stealing data, is a stack of protocols each doing one job. Security professionals use **layer models** to say precisely *where* something is happening: "the firewall blocked it at layer 4", "the attack is at layer 7", "it's an ARP problem, layer 2".

## Two models, one idea

| OSI layer | Name | TCP/IP model | Example protocols | Typical security controls |
| --- | --- | --- | --- | --- |
| 7 | Application | Application | HTTP, DNS, SMTP, SSH | Web application firewall, email filtering, application authentication |
| 6 | Presentation | Application | TLS (encryption and encoding sit here conceptually) | Certificate validation, cipher configuration |
| 5 | Session | Application | Session management inside applications | Session timeouts, token handling |
| 4 | Transport | Transport | TCP, UDP | Firewall port rules, connection tracking |
| 3 | Network | Internet | IPv4, IPv6, ICMP | Routing controls, IP-based ACLs, segmentation |
| 2 | Data link | Link | Ethernet, Wi-Fi (802.11), ARP | 802.1X, port security, VLANs |
| 1 | Physical | Link | Cables, radio | Locked comms rooms, disabled unused ports |

The **OSI model** (seven layers) is a teaching and vocabulary tool. The **TCP/IP model** (four layers) is closer to how the internet is actually built. You will hear both: "layer 7 attack" uses OSI numbering even though the protocols are TCP/IP.

:::note Why bother with models?
They make troubleshooting and threat analysis systematic. If a laptop has no IP address, there is no point checking firewall rules (layer 4) yet. If a firewall allows TCP 443, it cannot stop an injection attack that happens inside HTTP at layer 7.
:::

## Encapsulation: how a request is wrapped

When the loans officer's browser sends a request, each layer wraps the data from the layer above with its own **header**:

```text
[ Ethernet header | IP header | TCP header | TLS record | HTTP request ] [Ethernet trailer]
   layer 2           layer 3     layer 4      "6"           layer 7
   who next on       who is the  which        encrypted     what the user
   this link         final host  application  contents      actually wants
```

Concrete values for that request:

| Header | Key fields | Value |
| --- | --- | --- |
| Ethernet | source / destination MAC | laptop `3c:52:82:1a:9e:04` → branch router `00:1b:21:aa:10:01` |
| IPv4 | source / destination IP, TTL | `10.40.9.77` → `10.10.0.15`, TTL 64 |
| TCP | source / destination port, flags | `51544` → `443`, SYN |
| TLS | server name (SNI), version | `corebanking.kanem.example`, TLS 1.3 |

Two details matter enormously for security:

- **The Ethernet header changes at every router hop**, because it only describes the next link. The IP header's addresses normally stay the same end to end (unless NAT rewrites them, lesson 5).
- **Each device only reads the layers it needs.** A switch reads layer 2, a router layer 3, a stateful firewall layers 3–4, an application-aware firewall or proxy up to 7. A control can only enforce what it can see.

## Thinking like a defender (and an attacker)

| Layer | What can go wrong | Example you will meet in this course |
| --- | --- | --- |
| 1–2 | Rogue devices, ARP poisoning, VLAN hopping | The mini-PC in the meeting room (module 3) |
| 3 | IP spoofing, routing to the wrong place, reconnaissance with ping sweeps | Network scanning in module 2 |
| 4 | Port scanning, SYN floods, overly broad port rules | Firewall reviews in module 3 |
| 7 | Phishing, injection, DNS tunnelling, abuse of allowed protocols | DNS investigations in module 7 |

:::reveal Back to the ticket: where would you start?
The printer works, so the laptop's network connection at layers 1–2 is probably fine and it can reach at least one local device. The problem is reaching a server in *another* network, which points to layer 3: the IP configuration or routing. "Internet is slow" may be unrelated. You will solve this exact ticket in the module lab.
:::

:::takeaway
Layer models give you a precise vocabulary and an order for troubleshooting. Every packet is a stack of headers; each device and each security control acts on specific layers, and can only enforce what it can see.
:::
