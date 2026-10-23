Containment stops the harm from spreading. Done carelessly, it destroys the evidence you need to understand what happened, or warns the intruder while other footholds remain. Good containment is fast, deliberate and coordinated.

## Network containment options

| Option | Effect | Trade-off |
| --- | --- | --- |
| **Block an external address or domain** (firewall, DNS sinkhole or protective DNS) | Cuts known external connections | Easily worked around if the intruder has other addresses |
| **Disable or tighten the rule that allowed access** | Closes the way in | May affect legitimate users of the rule |
| **Isolate the host** (NAC quarantine VLAN, switch port shutdown, host firewall, cloud quarantine group) | Stops everything to and from the host | The service is down; volatile evidence may be lost if the host is powered off |
| **Restrict a zone** (temporary rules between zones) | Limits spread from a compromised zone | Broad business impact |
| **Disable accounts and reset credentials** | Removes stolen access | Must cover every account in scope at once |

Choose the narrowest action that stops the harm, and agree it with the incident lead and system owner. If data is actively leaving, containment comes first.

## Preserve evidence before and during containment

| Evidence | Action |
| --- | --- |
| Firewall, flow, IDS, DNS and proxy logs | Export the incident window from the SIEM and record hashes; confirm retention will not delete them |
| Host state | Ask the system owner to capture memory and disk images **before** rebuilding; **isolate rather than power off** |
| Network traffic | Start a targeted packet capture if activity continues (module 8) |
| Configuration | Save the firewall rule set and change history as they were |
| Your actions | Record every containment change, with time and who made it |

NIST SP 800-86 and SP 800-61r3 describe evidence handling. Keep a chain of custody for anything that may be used in disciplinary or legal action.

## Eradication and recovery

After containment:

1. **Remove the cause**: close or correct the rule, patch the exposed service, remove unauthorised accounts.
2. **Rebuild** compromised hosts from known-good images rather than cleaning them.
3. **Reset credentials** that were on or used by compromised systems.
4. **Restore** services and **monitor closely** for recurrence.
5. **Verify**: re-run the queries that found the incident; nothing new should appear.

:::warning Don't destroy the crime scene
Wiping and rebuilding a server in the first hour feels decisive, but it can make it impossible to tell what was taken or how. Isolate first, preserve, then rebuild.
:::

:::takeaway
Contain with the narrowest effective action (block, close the rule, isolate, reset), agreed with the incident lead and fast if data is leaving. Preserve logs, host images, configuration and a record of your own changes first, then eradicate, rebuild, reset, restore and verify.
:::
