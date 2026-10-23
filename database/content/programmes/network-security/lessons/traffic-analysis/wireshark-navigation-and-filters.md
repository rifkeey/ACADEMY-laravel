Wireshark is the standard free tool for reading captures. Its window has three panes: the **packet list** (one line per frame), the **packet details** (each protocol layer, expandable) and the **packet bytes** (raw hexadecimal).

## Set up the view

| Setting | How | Why |
| --- | --- | --- |
| Time format | View → Time Display Format → **UTC Date and Time of Day**, or **Seconds Since Previous Displayed Packet** | UTC matches your logs; "since previous" shows delays |
| Name resolution | View → Name Resolution: keep network-name lookups **off** | Looking up names can leak queries and slows large files |
| Columns | Right-click a field in packet details → **Apply as Column** | Show what matters for the case, such as `tcp.stream` or `dns.qry.name` |

## Display filters you will use every day

```text
ip.addr == 10.40.9.21                      to or from one host
ip.src == 10.40.9.21 && ip.dst == 10.10.0.15
tcp.port == 443                            one port, either direction
tcp.stream == 3                            one TCP conversation
dns                                        DNS only
dns.flags.rcode == 3                       DNS "no such name" (NXDOMAIN)
tcp.flags.syn == 1 && tcp.flags.ack == 0   connection attempts
tcp.flags.reset == 1                       resets
tcp.analysis.retransmission                retransmitted segments
tcp.analysis.flags                         any TCP problem Wireshark noticed
tls.handshake.type == 1                    TLS Client Hello
```

Filters combine with `&&` (and), `||` (or) and `!` (not). The filter bar turns **green** when the syntax is valid and **red** when it isn't. The Wireshark Display Filter Reference lists every field.

## The tools that save the most time

| Tool | Menu | Answers |
| --- | --- | --- |
| **Conversations** | Statistics → Conversations | Who talked to whom, how many packets and bytes, how long |
| **Protocol Hierarchy** | Statistics → Protocol Hierarchy | What kinds of traffic are in the file |
| **Endpoints** | Statistics → Endpoints | Every address seen |
| **Follow Stream** | Right-click → Follow → TCP Stream | One conversation in order (encrypted content stays unreadable) |
| **Expert Information** | Analyze → Expert Information | Warnings such as retransmissions, resets and malformed packets |

## A method for any capture

1. **Orient**: Protocol Hierarchy and Conversations. What is here, and between whom?
2. **Focus**: filter to the host and time of the complaint.
3. **Follow**: pick the relevant stream; read it from first packet to last.
4. **Measure**: use "seconds since previous" to find the delays.
5. **Explain**: match what you see to a cause (next two lessons).
6. **Record**: note frame numbers, filters and times as you go (lesson 5).

:::takeaway
Set the time display to UTC or "since previous", turn off name resolution, and use display filters, Conversations, Follow Stream and Expert Information to move from the whole file to the few packets that explain the problem.
:::
