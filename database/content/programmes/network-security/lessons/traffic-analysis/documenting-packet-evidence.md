An analysis that nobody else can follow or repeat is of little use. A good findings report lets a colleague open the same file, apply the same filters, see the same frames and reach the same conclusion.

## Separate facts from interpretation

| Facts (what the capture shows) | Interpretation (what you conclude) |
| --- | --- |
| "Frames 12, 14 and 17 are SYNs from 10.40.9.21 to 10.10.0.16:443 at 0, 1 and 3 seconds; no reply was captured." | "Something between the client and 10.10.0.16 is dropping connections to port 443, most likely a firewall rule." |

Write facts precisely, then interpret them, and say how confident you are. Recommend what to check next to confirm.

## A findings report structure

| Section | Contents |
| --- | --- |
| **Summary** | Two or three sentences: what was wrong, the cause, what to do |
| **Scope** | File name, SHA-256 hash, where and when it was captured (UTC), by whom, any capture filter |
| **Method** | Filters and tools used |
| **Findings** | Each finding with frame numbers, times and measured values (for example RTT, delays, counts) |
| **Interpretation** | The likely cause, and confidence |
| **Limitations** | What the capture can't show (encrypted content, one capture point, packets dropped by the mirror port) |
| **Recommendations** | Fixes and the checks that would confirm them |

## Good habits

- Note **frame numbers** and **filters** as you go; it is much harder to find them again later.
- Use **UTC** times so they match logs.
- Quote **measured** values: "RTT 38 ms", "server response after 2.9 s", "6 retransmissions".
- Don't paste personal data from the capture into the report; describe it instead.
- If others need packets, export only the relevant ones (File → Export Specified Packets) and hash the export.

:::reveal Example finding
**F1 — Reporting service refuses connections.** The client 10.40.9.30 sent a SYN to 10.10.0.40:8443 (frame 21, 09:14:02.118 UTC) and received an RST, ACK from 10.10.0.40 within 2 ms (frame 22). The same happened on two retries (frames 25–26, 31–32). *Interpretation:* the host is reachable, but nothing is listening on port 8443, so the reporting service is probably stopped or listening on another port. *Confidence:* medium to high; the reset arrives almost immediately from the server's address, though a firewall configured to reject can look the same. *Recommendation:* check the service status and its configured port on 10.10.0.40.
:::

:::takeaway
Write findings that someone else can repeat: record the file, hash, capture point and filters; give frame numbers, UTC times and measured values; separate facts from interpretation; state limitations; and recommend how to confirm the cause.
:::
