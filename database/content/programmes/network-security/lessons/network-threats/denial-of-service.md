A **denial-of-service** (DoS) attack tries to make a service unavailable to the people who need it. When the traffic comes from many sources at once, usually compromised devices, it is a **distributed** denial of service (DDoS). For Kanem, an unavailable payments portal on salary day is a business incident even if no data is touched. MITRE ATT&CK lists these as *Network Denial of Service* (T1498) and *Endpoint Denial of Service* (T1499).

## Three broad kinds

| Kind | What is exhausted | Example effect |
| --- | --- | --- |
| **Volumetric** | Bandwidth of the internet link | The link is full, so nothing gets through, including legitimate users |
| **Protocol / state exhaustion** | Connection tables in firewalls, load balancers or servers | Devices can't accept new connections even though the link is not full |
| **Application layer** | Server resources: CPU, database, expensive pages | The website is slow or failing, though traffic volume looks modest |

Some attacks **reflect and amplify** traffic: they send small requests with a forged source address (lesson 2) to open services on the internet, which send much larger replies to the victim. That is one reason every network should block forged source addresses and avoid running open, internet-facing services that answer anyone (for example misconfigured DNS resolvers).

## Signs to look for

| Signal | Where |
| --- | --- |
| Link utilisation near 100% with no matching business activity | Router and provider monitoring |
| Sudden growth in connections or half-open connections | Firewall and load balancer state tables |
| Many requests for the same expensive URL from many addresses | Web and CDN logs |
| Error rates and response times rising | Application monitoring |
| Traffic from unusual countries or networks, or with odd patterns (same user agent, no cookies) | Web application firewall and CDN analytics |

The hard part is telling an attack from a legitimate surge, such as a marketing campaign or salary day. Baselines and a calendar of business events help.

## Preparing and responding

| Control | Purpose |
| --- | --- |
| **Upstream protection** | Volumetric attacks must be absorbed before they reach your link: an ISP scrubbing service or a cloud DDoS protection provider |
| **CDN and web application firewall** in front of public websites | Absorb load, cache content and filter application-layer floods |
| **Rate limiting** and connection limits | On load balancers, APIs and login pages |
| **Capacity and autoscaling** | Room to absorb surges; cloud services can scale out |
| **Hardened devices** | Protections against state exhaustion (for example SYN cookies) enabled on firewalls and servers |
| **Remove amplifiers** | No open resolvers or other services answering the whole internet; anti-spoofing filters (BCP 38) |
| **Runbook** | Who calls the provider, how to switch on scrubbing, how to communicate with customers |

:::tip Rehearse the phone call
The first minutes are often lost finding the provider's emergency contact and contract number. Keep them in the runbook and test the procedure with the provider.
:::

:::takeaway
DoS attacks exhaust bandwidth, connection state or application resources. Watch link use, state tables, error rates and request patterns against a baseline. Prepare upstream scrubbing, a CDN and WAF, rate limits, capacity, hardened devices and a rehearsed runbook, and make sure your own network can't be used as an amplifier.
:::
