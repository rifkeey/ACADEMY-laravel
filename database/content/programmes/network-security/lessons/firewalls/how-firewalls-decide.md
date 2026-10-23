:::scenario Monday morning at Kanem Financial Services
The overnight report from the head-office firewall shows 88,000 connections into the DMZ on ports nobody recognises. The network team says "the firewall is stateful, so it's fine". Before you can judge that claim, you need to know exactly what a firewall checks, and what it cannot see.
:::

A firewall enforces a **policy about which network conversations are allowed between two places**. Every product implements that idea differently, but they all fall into a few families. Knowing which family you are dealing with tells you what the device can and cannot protect against.

## Stateless packet filters

A stateless filter looks at each packet on its own and compares header fields with a list of rules:

| Field | Example |
| --- | --- |
| Source and destination IP address | `10.20.4.21` → `10.10.1.5` |
| Protocol | TCP, UDP, ICMP |
| Source and destination port | `52114` → `443` |
| Interface or direction | inbound on the WAN interface |

Router access-control lists (ACLs) and many cloud network ACLs work this way. They are fast and simple, but they have no memory. Because a reply packet is judged separately from the request that caused it, you must write rules for **both directions**, usually by allowing a wide range of high "ephemeral" ports for returning traffic. That makes stateless rule sets broader than they look.

## Stateful inspection

A stateful firewall keeps a **connection table**. When it allows the first packet of a conversation (for TCP, the SYN), it records the conversation and automatically allows the replies that belong to it.

```text
Connection table on FW-HQ-01 (simplified)
proto  inside              outside             state         idle
tcp    10.20.4.21:52114    10.10.1.5:443       ESTABLISHED   00:00:03
udp    10.10.1.9:41003     198.51.100.53:53    REPLIED       00:00:01
tcp    10.20.7.44:50211    203.0.113.80:443    SYN_SENT      00:00:00
```

Two practical consequences:

- **You write rules for the direction that starts the conversation.** "Users may browse the internet on TCP 443" is one rule; the replies are handled by the connection table.
- **Unsolicited packets are dropped.** A packet that claims to be a reply but matches no entry in the table is not let through, which defeats a whole class of crafted-packet tricks that stateless filters miss.

TCP connection states are defined in RFC 9293, the current TCP specification. UDP has no handshake, so firewalls track it with a timeout instead of real states.

:::note What "stateful" does not mean
Stateful inspection tracks *conversations*, not *content*. A stateful firewall that allows TCP 443 to the DMZ will happily pass an attack against the web application on that port. That is why the Monday-morning claim above is not reassuring: 88,000 unexpected connections are a policy problem, whatever the firewall type.
:::

## Application-aware ("next-generation") firewalls

Next-generation firewalls (NGFWs) add decisions above the port number:

- **Application identification**: recognising the protocol actually in use, so "TCP 443" can be split into "the banking web app" and "an unknown tunnelling tool on port 443".
- **User identity**: rules written for groups ("Loans team") instead of IP ranges, using a directory or the VPN.
- **Threat prevention**: built-in intrusion prevention, URL filtering and malware scanning.
- **TLS inspection**: decrypting traffic to look inside it. This is powerful but sensitive: it needs a trusted certificate on every device, breaks some applications, and raises privacy and legal questions (for example for banking or health sites visited by staff). Many organisations exempt categories of traffic.

"Next-generation" is a marketing label, not a standard. Ask which of these features a product has and which are switched on.

## Other places firewalls live

| Type | Where it runs | Typical use |
| --- | --- | --- |
| Network (perimeter / internal) firewall | Dedicated appliance or virtual appliance between zones | Internet edge, DMZ, data-centre segmentation |
| Host-based firewall | On each server or laptop (Windows Defender Firewall, `nftables`) | Last line of defence; blocks lateral movement between hosts in the same zone |
| Web application firewall (WAF) | In front of web applications | Inspects HTTP requests for attacks such as injection |
| Cloud security groups and network ACLs | Provider-managed, attached to cloud resources | Stateful (security groups) and stateless (network ACLs) filtering in cloud networks, covered in the Cloud Security programme |

NIST SP 800-41 Rev. 1 describes these technologies and how to build firewall policy. It was published in 2009, so it predates cloud firewalls and today's NGFW features, but its core advice (decide policy first, deny by default, manage the rule set over its life) is still the standard approach.

## Walkthrough: what does each firewall see?

A laptop in the head office opens `https://portal.kanem.example`. Trace the decision at each layer:

1. **Stateless ACL on the core router**: sees `10.20.4.21:52114 → 10.10.1.5:443 TCP`. It needs one rule out and one rule for returning traffic to high ports.
2. **Stateful firewall**: sees the SYN, matches "users → servers TCP 443", creates a table entry. Replies pass automatically.
3. **NGFW with app-ID**: after a few packets, also labels the flow as `ssl` / `kanem-portal` and can apply a rule specific to that application.
4. **WAF**: sees `GET /loans/apply` and the request body, and could block an injection attempt that every firewall above allowed.

:::takeaway
Know your firewall's family. Stateless filters judge single packets, stateful firewalls judge conversations, application-aware firewalls also judge applications and users, and none of them replaces securing the application itself.
:::
