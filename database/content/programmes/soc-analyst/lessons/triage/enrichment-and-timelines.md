## Enrichment: adding meaning to entities

Enrichment answers "what do we know about this thing?" for each entity in an alert.

| Entity | Internal enrichment | External enrichment |
| --- | --- | --- |
| User | Role, department, manager, privileged groups, recent password resets, usual sign-in locations | Breach-exposure services (where policy allows) |
| Host | Owner, function, criticality, OS, patch level, EDR status | n/a |
| IP address | Is it ours? VPN range? Cloud egress? Previously seen? | Geolocation, ASN, reputation, passive DNS |
| Domain / URL | Previously visited by other hosts? Category in proxy | Registration age, reputation, sandbox results |
| File hash | Seen on other endpoints? First seen date | Reputation services, sandbox reports |

Treat external reputation as one input, not a verdict. A "clean" reputation for a newly registered domain often just means nobody has reported it yet.

> **Privacy and operational security:** Submitting an internal file to a public sandbox may disclose confidential data and alert the attacker that you have found them. Follow your organisation's policy on which enrichment services may receive which data.

## Building a timeline

A timeline is the single most useful investigation artefact. It turns scattered evidence into a story that you can test.

Rules for a good timeline:

1. **One time zone**, normally UTC, stated at the top.
2. **One event per row**: time, source, entity, what happened, and evidence reference.
3. **Facts only in the main columns**; interpretation in a separate notes column.
4. **Mark gaps.** "No proxy logs available 02:00–02:30 (collector outage)" is important.

```text
Timeline – INC-4471 – all times UTC
02:01:15  Entra ID   203.0.113.45   First failed sign-in (spray begins)       Q-01
02:14:09  Entra ID   f.manager      Successful sign-in from 203.0.113.45      Q-01
02:16:40  Exchange   f.manager      Inbox rule created: forward to external   Q-03
02:21:05  SharePoint f.manager      312 files downloaded from /Finance        Q-04
02:40:00  Entra ID   f.manager      Sign-in from usual Lagos device (user)    Q-01
```

Reading this timeline, the story is clear: password spray, account takeover, persistence via a mail rule, and data collection, all within 20 minutes. The 02:40 sign-in is probably the real user, which is useful context when you contact them.

## Testing the story

Once you have a hypothesis, look for evidence that would **disprove** it. If you think the 02:14 sign-in was the attacker, check its device and user-agent against the user's normal values. If they match the user's laptop exactly, your hypothesis needs revisiting.
