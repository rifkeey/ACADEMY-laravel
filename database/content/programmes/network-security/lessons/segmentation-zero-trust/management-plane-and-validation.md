Two topics finish this module. First, the most valuable segment of all: the **management plane**, the interfaces used to configure the network and servers. Second, **validation**: proving that the segmentation you designed is the segmentation you have.

## Protecting the management plane

Every switch, router, firewall, hypervisor and server has an administrative interface: SSH, a web console, an API, a remote management card. Whoever controls those controls everything. Treat them as their own, most protected zone.

| Control | What it means at Kanem |
| --- | --- |
| **Dedicated management network** | Management interfaces are only reachable from VLAN 199, never from user, guest or DMZ zones |
| **Jump hosts / privileged access workstations** | Administrators connect first to a hardened, monitored jump host (or use a dedicated admin workstation), and only from there to infrastructure |
| **Strong authentication** | Named accounts (no shared "admin"), MFA, central authentication (RADIUS/TACACS+ or the directory) |
| **Secure protocols only** | SSH and HTTPS; Telnet, HTTP and old SNMP versions disabled |
| **Out-of-band access** | A separate path (console server, dedicated link) to recover devices if the main network fails, protected just as strongly |
| **Logging and change control** | Every login and configuration change logged centrally and tied to a change ticket (module 3) |
| **Separate admin identities** | Administrators use a separate privileged account, not their email-and-browsing account |

Remember the firewall review in module 3: rule 11 exposed the firewall admin console to the users zone. That single rule undermines every other control the firewall enforces.

## Validating segmentation

A diagram is a claim. Validation is the evidence. NIST SP 800-115 describes security testing methods; for segmentation the essentials are:

1. **Test flows from each zone.** For every zone, attempt the connections that must succeed and a sample of those that must fail, with authorisation and a defined scope. Record the result, as the lab graders in this course do.
2. **Review the enforcement configuration**: firewall policies, switch ACLs, host firewall baselines. Look for `any` rules, shadowed denies and undocumented exceptions.
3. **Compare with observed traffic**: flow logs should show no traffic on paths that are supposed to be closed. Any match is either a misconfiguration or an unknown dependency.
4. **Repeat after changes and on a schedule.** Segmentation decays as rules are added. Many regulatory regimes for payment-card environments, for example, require regular segmentation testing.

```text
Segmentation test record (extract, synthetic)
From          To            Port   Expected  Result   Evidence
users         database      1433   deny      deny ✔   FW-DC-01 rule 40 hit
dmz           database      1433   deny      ALLOW ✘  rule 17 "temp migration" still active → ticket SEC-418
management    cards         22     allow     allow ✔
guest         application   443    deny      deny ✔
```

One failed line is a finding with a ticket, an owner and a retest date.

:::tip Build test cases from the requirements
If every allowed flow came from a written requirement (module 3, lesson 3), the test cases write themselves: each requirement is an "expect allow" test, and every other zone pair is an "expect deny" test. The lab for this module grades your design exactly this way.
:::

:::takeaway
Put management interfaces in their own, most restricted zone, reached only through hardened jump hosts with strong authentication and full logging. Then validate segmentation with recorded tests from every zone, configuration review and flow-log comparison, after every significant change.
:::
