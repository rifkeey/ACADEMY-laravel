Most traffic today is encrypted, which is good for privacy and security, and a real limit for network detection. This lesson is about what network sensors can and cannot do in that world, and how detection programmes adapt.

## What encryption hides, and what it doesn't

| Without decryption, a sensor **cannot** see | It **can** still see |
| --- | --- |
| URLs, request contents and file transfers inside HTTPS | IP addresses, ports, timing, connection duration and volume |
| Most of the content of encrypted protocols | DNS queries (unless DNS itself is encrypted) |
| | TLS metadata: protocol version, the requested server name (SNI) in most cases, certificate details visible to it |

Newer protocol features (encrypted DNS, Encrypted Client Hello) reduce even the metadata. Detection therefore shifts towards:

- **Behaviour in metadata**: regular, machine-like connection timing; connections to new or rare destinations; unusual volumes leaving a server.
- **DNS and proxy logs**, where the organisation controls the resolver or proxy.
- **Endpoint detection**, which sees processes and decrypted activity on the device.
- **Selective TLS inspection** at a proxy for categories where it is justified and lawful.

## TLS inspection: a decision, not a default

Decrypting traffic lets sensors see content again, but it carries costs:

| Benefit | Cost or risk |
| --- | --- |
| Content-based detection and data-loss prevention | Requires installing a trusted root certificate on every managed device |
| Visibility into threats delivered over HTTPS | Breaks some applications (certificate pinning), adds latency |
| | Privacy and legal considerations for categories such as banking, health and personal email |
| | The inspection device holds decrypted data and becomes a high-value target |

Most organisations inspect selectively and document exemptions.

## The limits of prevention

An inline IPS that blocks legitimate traffic causes an outage. Practical safeguards:

- Start new signatures in **detect-only** mode; enable blocking once they are proven quiet.
- Block only on **high-confidence** signatures; alert on the rest.
- Decide **fail-open or fail-closed** behaviour deliberately for each inline device, based on what matters more for that link: availability or security.
- Keep a fast, documented way to **bypass** a rule that is causing an outage, with change control.

## Detection is a programme, not a device

No single sensor sees everything. A mature detection programme:

- maps its detections to the attacker behaviours that matter (ATT&CK), and knows its gaps;
- combines network, endpoint, identity and cloud telemetry;
- reviews and tunes continuously with the metrics from lesson 2;
- feeds lessons from every incident back into new or better detections.

:::takeaway
Encryption hides content but not metadata. Detect through behaviour, DNS, endpoints and selective, justified TLS inspection. Use IPS blocking carefully (detect first, block only high-confidence matches, plan failure modes), and treat detection as a continuously improved programme across all telemetry.
:::
