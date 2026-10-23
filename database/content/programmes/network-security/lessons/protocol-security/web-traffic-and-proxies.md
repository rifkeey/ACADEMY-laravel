Web traffic (HTTP and HTTPS) is most of what crosses Kanem's perimeter. Controlling and observing it, usually through a **proxy** or **secure web gateway**, is one of the most effective ways to reduce risk from users and unwanted software.

## Reading HTTP at a glance

HTTP semantics are defined in RFC 9110. A request names a **method**, a **target** and **headers**; a response returns a **status code**:

| Element | Examples | Security relevance |
| --- | --- | --- |
| Method | GET, POST, PUT | Unexpected uploads (POST/PUT) to unknown sites can indicate data leaving |
| Host and path | `portal.kanem.example/loans/apply` | Where the user or software is going |
| User-Agent | Browser or application identifier | Unusual or missing values can identify unwanted software |
| Status code | 200 OK, 301/302 redirect, 403 forbidden, 404 not found | Patterns of errors can reveal scanning or misconfiguration |
| Bytes in and out | Request and response sizes | Large uploads to unusual destinations deserve review |

With HTTPS, a sensor without decryption sees only the metadata from module 5 (addresses, timing, volume, usually the server name). A proxy that terminates TLS (for permitted categories) can see the full request.

## Proxies and secure web gateways

| Capability | Benefit |
| --- | --- |
| **Central egress point** | Only the proxy talks to the internet; direct outbound web from servers and laptops can then be blocked (module 3) |
| **URL and category filtering** | Block known-malicious and unwanted categories; allow business categories |
| **Authentication** | Logs show which **user**, not just which IP address |
| **Malware scanning and sandboxing** | Files are checked before they reach devices |
| **Selective TLS inspection** | Content inspection where justified and lawful (module 5, lesson 5) |
| **Logging** | A complete record of web activity for investigations |

For remote users, cloud-delivered gateways apply the same policy wherever the user is.

## Secure protocol choices

| Use | Prefer | Avoid |
| --- | --- | --- |
| Web | HTTPS everywhere, including internal applications | Plain HTTP for anything with credentials or personal data |
| Email transport between servers | TLS between mail servers where supported | Unencrypted relays for sensitive mail |
| File transfer | SFTP, HTTPS | FTP |

## Email authentication in brief

Web and email security meet in phishing. Three DNS-published mechanisms help receiving mail servers decide whether a message really comes from Kanem's domain (NIST SP 800-177 Rev. 1, *Trustworthy Email*, covers them):

| Mechanism | What it does |
| --- | --- |
| **SPF** (RFC 7208) | Lists which servers may send mail for the domain |
| **DKIM** (RFC 6376) | Adds a signature proving the message was authorised by the domain and not altered |
| **DMARC** (RFC 9989, which replaced RFC 7489) | Tells receivers what to do when SPF/DKIM checks fail, and sends reports back to the domain owner |

Publishing all three, and moving DMARC towards an enforcing policy, makes it much harder to send email that convincingly pretends to come from Kanem.

:::takeaway
Route web traffic through a proxy or secure web gateway for filtering, user attribution, malware scanning and logging; prefer secure protocols everywhere; and protect Kanem's email domain with SPF, DKIM and DMARC.
:::
