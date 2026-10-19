## Packet captures

A packet capture (PCAP or PCAPNG file) records frames as they crossed a network interface. Wireshark is the standard free tool for analysing them; `tcpdump` and `tshark` are command-line equivalents. Captures can contain sensitive data such as credentials or personal information, so handle them as confidential evidence and capture only on networks you are authorised to monitor.

## Display filters

Wireshark display filters narrow a large capture quickly:

```text
ip.addr == 10.20.4.21              all traffic to or from a host
tcp.port == 443                    HTTPS (and anything else on 443)
dns                                DNS traffic only
tcp.flags.syn == 1 && tcp.flags.ack == 0   connection attempts
tcp.analysis.retransmission        retransmitted segments
```

Use **Statistics → Conversations** to see who talked to whom and how much, and **Follow → TCP Stream** to read a single session in order.

## The TCP life cycle

1. **SYN**: the client asks to open a connection.
2. **SYN, ACK**: the server agrees.
3. **ACK**: the client confirms. The connection is established.
4. Data flows, each side acknowledging what it receives.
5. **FIN** (or **RST** for an abrupt reset) closes it.

The time between SYN and SYN-ACK approximates the network round-trip time. **Retransmissions** mean a segment was not acknowledged in time, which usually indicates packet loss or congestion. Many SYNs with no SYN-ACK suggest a filtered port, an unreachable host or scanning.

## Normal versus suspicious

Judge traffic against what is normal for that host:

- A workstation talking HTTPS to well-known services is normal. A workstation sending regular, similarly sized HTTPS requests every 60 seconds to a new domain may be beaconing.
- DNS from clients to the internal resolver is normal. DNS from clients directly to external resolvers may bypass your monitoring.
- SMB between workstations is unusual in most networks and may indicate lateral movement.

The lab for this module provides a pre-extracted packet list, so you can practise without handling raw capture files.
