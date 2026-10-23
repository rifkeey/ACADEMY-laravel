Most application traffic at Kanem runs over TCP (RFC 9293). Knowing what healthy TCP looks like lets you tell a slow network from a slow server, and a blocked connection from a refused one.

## A healthy connection

| Frame | Direction | Flags | Meaning |
| --- | --- | --- | --- |
| 1 | client → server | SYN | "Can we talk?" |
| 2 | server → client | SYN, ACK | "Yes" |
| 3 | client → server | ACK | Connection established |
| 4… | both | PSH, ACK / ACK | Data and acknowledgements |
| last | both | FIN, ACK | Orderly close |

**Round-trip time (RTT):** the time between the client's SYN and the server's SYN-ACK, measured at the client, is a good estimate of the network delay. Within Kanem's network this should be a few milliseconds; Abuja to Lagos over the WAN perhaps tens of milliseconds.

## Sequence numbers and acknowledgements

Each side numbers the bytes it sends. The receiver's **ACK number** says "I have everything up to here; send the next byte". Wireshark shows **relative** numbers starting at 0 to make this readable. The **window** value says how much more data the receiver can accept.

## Patterns and what they usually mean

| What you see | Usual meaning | Next check |
| --- | --- | --- |
| SYN, then SYN retransmitted after about 1 s, 2 s, 4 s…, no reply | Nothing answered: a firewall silently dropping, wrong address or the host is down | Firewall logs and routes for that destination (module 3) |
| SYN answered by **RST** | The host is reachable but nothing is listening on that port, or a firewall is rejecting | Service status; firewall reject rules |
| **Retransmission** of a data segment | The segment or its ACK was lost or delayed | Count them; see where loss happens (capture at both ends) |
| **Duplicate ACKs** followed by a fast retransmission | The receiver got later data but missed one segment | Packet loss on the path |
| Large gap after the client's request, before the server's first data | The **server or application** is slow, not the network | Application and database performance |
| **Zero window** from one side | That side cannot keep up and asked the sender to pause | The receiving host's load |
| RST in the middle of a session | One side, or a device in between, aborted it | Which side sent it; idle timeouts on firewalls or load balancers |

:::tip Network or application?
If the handshake is quick but the server takes seconds to send its first response, the network is usually fine and the delay is in the application. If the handshake itself is slow, or there are many retransmissions, look at the network.
:::

## Retransmission timing

When a segment is not acknowledged, TCP waits for its retransmission timeout before sending it again, and doubles the wait each time (RFC 6298 describes the calculation). That is why a single lost packet can add a noticeable pause, and why unanswered connection attempts make a web page hang for several seconds before giving up.

:::takeaway
Measure RTT from the handshake, read sequence and acknowledgement numbers to follow data, and recognise the patterns: unanswered SYNs mean something is dropping traffic, RST means refused, retransmissions and duplicate ACKs mean loss, and a gap after the request means a slow application.
:::
