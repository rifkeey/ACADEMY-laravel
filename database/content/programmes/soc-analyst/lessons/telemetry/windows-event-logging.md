## The Windows Security log

Windows records security-relevant activity in the **Security** event log when auditing is enabled by policy. Analysts memorise a handful of event IDs because they appear in nearly every investigation.

| Event ID | Meaning | Why it matters |
| --- | --- | --- |
| 4624 | Successful logon | Who accessed what, from where, and how |
| 4625 | Failed logon | Brute force and password spraying |
| 4634 / 4647 | Logoff | Session duration |
| 4648 | Logon with explicit credentials | `runas`, lateral movement tooling |
| 4672 | Special privileges assigned | Administrator-equivalent logon |
| 4688 | Process creation | What ran (command line requires an extra policy setting) |
| 4720 | User account created | Persistence via new accounts |
| 4728 / 4732 / 4756 | Member added to a security group | Privilege escalation |
| 1102 | Audit log cleared | Possible anti-forensics |

### Logon types

The `LogonType` field on 4624 and 4625 tells you *how* the logon happened:

- **2**: interactive (keyboard at the machine)
- **3**: network (e.g. accessing a file share)
- **4**: batch (scheduled task)
- **5**: service
- **7**: unlock
- **10**: RemoteInteractive (RDP)
- **11**: cached interactive

A burst of type 3 failures across many accounts from one source suggests spraying against a network service. Type 10 successes from an unfamiliar workstation suggest RDP-based lateral movement.

### Failure reasons

Event 4625 includes `Status` and `SubStatus` codes. Two to recognise: `0xC000006A` means the username was valid but the password was wrong, and `0xC0000064` means the username does not exist. An attacker who receives many `0xC0000064` results is guessing usernames; many `0xC000006A` results suggests they already hold a valid user list.

## Beyond the Security log

The Security log is not enough on its own. Microsoft Sysmon, a free Sysinternals tool, adds rich endpoint telemetry when deployed with a tuned configuration: process creation with full command lines and hashes (Sysmon Event ID 1), network connections by process (3), and registry changes (12–14), among others. Microsoft Defender for Endpoint and other EDR products collect similar data.

## Reading an event critically

```text
EventID: 4624  TimeCreated: 2026-03-04T02:14:09Z  Computer: FIN-WS-07
TargetUserName: f.manager  LogonType: 3  IpAddress: 203.0.113.45
AuthenticationPackageName: NTLM  WorkstationName: -
```

An NTLM network logon from an external address directly to a finance workstation should not be possible in a well-designed network. That observation leads to a second question for the network team: why was this host reachable from the internet at all? Good analysts record both the security finding and the control gap that allowed it.
