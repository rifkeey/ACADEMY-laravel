Most firewall incidents are not caused by clever attackers defeating the firewall. They are caused by the firewall doing exactly what its rules say, when the rules say something nobody intended. This lesson is about reading a rule set the way the firewall reads it.

## First match wins

Almost every network firewall evaluates rules **from top to bottom** and stops at the **first rule that matches** the connection. The action of that rule (allow or deny) is final; rules further down are never consulted for that connection.

```text
#  Action  Source      Destination  Protocol  Port   Log
1  ALLOW   internet    dmz          tcp       443    yes
2  ALLOW   users       servers      tcp       443    yes
3  ALLOW   management  servers      tcp       22     yes
4  DENY    any         any          any       any    yes
```

To predict what happens to a connection, walk down the list and stop at the first rule whose source, destination, protocol **and** port all match. Try it:

| Connection | Matching rule | Result |
| --- | --- | --- |
| internet → dmz, TCP 443 | 1 | Allowed |
| users → servers, TCP 22 | 4 (rules 1–3 don't match) | Denied |
| management → servers, TCP 22 | 3 | Allowed |

## Default deny

The last line of a sound rule set is a **default deny**: anything not explicitly allowed is blocked. Some firewalls have an *implicit* deny at the end; others need you to write it. Writing it explicitly has two advantages: everyone reading the policy sees it, and you can switch **logging** on for it, which records scanning, misconfigured applications and attempts to reach things that should not be reached.

Default deny is not just good habit. NIST SP 800-53 Rev. 5 includes it as a control enhancement of boundary protection (SC-7(5), "deny by default — allow by exception"), and NIST SP 800-41 recommends building policy the same way.

## When rule order goes wrong

Long-lived rule sets develop four well-known problems:

| Problem | What it means | Example |
| --- | --- | --- |
| **Shadowing** | A rule can never match because an earlier rule matches all of its traffic first | `ALLOW dmz → servers any` above `DENY dmz → servers tcp 22`: the deny never fires |
| **Redundancy** | A rule is fully covered by another rule with the same action, so removing it changes nothing | `ALLOW users → servers tcp 443` and later `ALLOW users → servers tcp any` |
| **Correlation (overlap)** | Two rules partly overlap with different actions, so the result depends on their order | `ALLOW users → any tcp 3389` and `DENY any → servers tcp 3389` |
| **Over-generalisation** | A broad rule permits much more than its purpose needs | `ALLOW any → dmz any any` added "temporarily" for a vendor |

Shadowed and over-general rules are dangerous because the rule set *looks* safer than it is. A reviewer who sees `DENY dmz → servers tcp 22` assumes SSH from the DMZ is blocked. The firewall disagrees.

:::tip Hit counters are evidence
Most firewalls count how many connections matched each rule. A deny rule with **zero hits** for 90 days, sitting below a broad allow, is a strong sign it is shadowed. A "temporary" rule with **thousands** of hits shows that something now depends on it, so removing it needs a plan (lesson 4).
:::

## Walkthrough: find the shadowed rule

```text
#  Action  Source      Destination  Protocol  Port   Hits (90 days)
1  ALLOW   internet    dmz          tcp       443    2,914,005
2  ALLOW   dmz         servers      tcp       any    410,778     "Legacy batch job"
3  ALLOW   management  servers      tcp       22     5,312
4  DENY    dmz         servers      tcp       22     0           "Block SSH from DMZ"
5  DENY    any         any          any       any    61,442
```

1. Rule 4 denies DMZ → servers on TCP 22.
2. Look *above* it for any rule that matches the same traffic: rule 2 allows DMZ → servers on **any** TCP port, which includes 22.
3. So every DMZ → servers SSH connection matches rule 2 first and is **allowed**. Rule 4 is shadowed, which its zero hit count confirms.
4. The fix is not to move rule 4 up and keep rule 2. Find out what the "legacy batch job" really needs (say TCP 5432 to one database), replace rule 2 with that narrow rule, and keep the explicit deny.

:::reveal Why not just move rule 4 above rule 2?
Moving it would block SSH from the DMZ, but rule 2 would still allow the DMZ to reach **every other** TCP port on every server. If the public web server in the DMZ is compromised (ATT&CK T1190, Exploit Public-Facing Application), rule 2 gives the attacker a path to the whole server zone. The real problem is the over-general rule, not only the order.
:::

## Writing rules that stay readable

- Put **specific rules before general ones**, and group rules by zone pair (all dmz → servers rules together).
- Give every rule a **description, owner and ticket reference**. "Legacy batch job" with no owner is how rule 2 survived for years.
- Prefer **named objects** (`kanem-db-01`, `web-ports`) over raw addresses, so a change is made once.
- End with an explicit, **logged** default deny.

:::takeaway
Read a rule set as the firewall does: top to bottom, first match wins. The dangerous rules are the broad ones near the top, and the misleading ones are the denies that can never fire.
:::
