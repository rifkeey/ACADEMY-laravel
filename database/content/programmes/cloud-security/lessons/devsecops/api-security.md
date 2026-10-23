:::scenario From code to cloud
Kanem's loans team now ships a **loans API** to the cloud several times a week. Code goes from a developer's laptop through a Git repository and an automated pipeline into production. Every step in that path can let a weakness or an attacker through. This module secures the application, the API and the pipeline that delivers them.
:::

An API (application programming interface) lets other programs use a service: Kanem's mobile app, partner banks and internal tools all call the loans API. APIs are attractive targets because they expose data and actions directly, without a user interface to slow anyone down.

## The OWASP API Security Top 10

The OWASP API Security Top 10 (2023 edition) lists the most common API weaknesses. The first few cover most real problems:

| Risk | What goes wrong | Typical fix |
| --- | --- | --- |
| **Broken object level authorisation** | The API checks that a caller is signed in, but not that loan 4471 belongs to them | Check ownership of **every** object on **every** request, on the server |
| **Broken authentication** | Weak token handling, no rate limits on sign-in, tokens that never expire | Use a proven identity provider and standard tokens; short lifetimes; rate limits |
| **Broken object property level authorisation** | The API returns or accepts fields the caller shouldn't see or set (for example `approved: true`) | Return only the fields needed; accept only an allow-listed set of fields |
| **Unrestricted resource consumption** | No limits on request rate, size or cost | Rate limits, quotas, size limits, timeouts |
| **Broken function level authorisation** | Ordinary users can call administrator functions | Deny by default; check the caller's role for each function |

The rest of the list covers sensitive business flows, server-side request forgery, security misconfiguration, poor inventory of API versions, and unsafe use of third-party APIs. The OWASP Top 10:2025 covers web applications more broadly.

:::note Authentication is not authorisation
Most serious API weaknesses are **authorisation** failures: the caller is correctly identified, but the API never checks whether that caller may do this to this object. Authorisation checks belong on the server, close to the data, never only in the mobile app or web page.
:::

## Layers of API protection

| Layer | Controls |
| --- | --- |
| **Identity** | OAuth 2.0 / OpenID Connect through a managed identity provider; short-lived access tokens; scopes per client |
| **API gateway** | Authentication at the edge, rate limiting, request size limits, schema validation, TLS, logging |
| **Application** | Object- and function-level authorisation, input validation, output filtering |
| **Network** | Private back ends reachable only from the gateway (module 3) |
| **Inventory** | A list of every API, version and owner; old versions retired |
| **Monitoring** | Logs of who called what; alerts on unusual volumes, many authorisation failures or access to many different objects |

A gateway helps, but it cannot know whether loan 4471 belongs to the caller. **Object-level checks must live in the application.**

## Testing APIs safely

- Include authorisation tests in the automated test suite: "user A cannot read user B's loan".
- Test only Kanem's own APIs, in test environments, with written permission.
- Use the OWASP Application Security Verification Standard (ASVS) as a checklist of requirements.

:::takeaway
Most API breaches are authorisation failures. Check ownership of every object and permission for every function on the server, limit what fields go in and out, rate-limit everything, keep an inventory of API versions, and log enough to spot misuse.
:::
