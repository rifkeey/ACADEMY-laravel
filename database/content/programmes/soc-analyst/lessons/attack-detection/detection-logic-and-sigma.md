## Detection engineering as engineering

Detection engineering applies software-engineering discipline to detections: requirements, version control, peer review, testing and maintenance. A detection is code, and it can have bugs.

A simple detection life cycle:

```text
Threat or behaviour of interest
  -> Hypothesis ("an attacker who does X will produce telemetry Y")
  -> Data check (do we collect Y?)
  -> Logic (query) and documentation
  -> Test against known-bad and known-good data
  -> Peer review and deployment
  -> Monitor precision and volume; tune or retire
```

## Sigma: vendor-neutral detection rules

Sigma is an open, YAML-based format for describing log detections independently of any SIEM. A Sigma rule can be converted into KQL, Splunk SPL, Elastic queries and other formats with the Sigma toolchain. The SigmaHQ repository contains thousands of community rules you can study and adapt.

A simplified Sigma rule:

```yaml
title: Office Application Spawning a Command Shell
id: 00000000-0000-4000-8000-000000000000   # placeholder; real rules use a unique UUID
status: experimental
description: Detects Microsoft Office applications starting cmd.exe or powershell.exe, a common pattern after a malicious document is opened.
logsource:
  category: process_creation
  product: windows
detection:
  parent:
    ParentImage|endswith:
      - '\WINWORD.EXE'
      - '\EXCEL.EXE'
      - '\POWERPNT.EXE'
  child:
    Image|endswith:
      - '\cmd.exe'
      - '\powershell.exe'
  condition: parent and child
falsepositives:
  - Legitimate macros used by finance teams (document them and add narrow exclusions)
level: high
tags:
  - attack.execution
  - attack.t1204.002
```

The same logic expressed in KQL over a process-events table:

```kql
DeviceProcessEvents
| where InitiatingProcessFileName in~ ("winword.exe", "excel.exe", "powerpnt.exe")
| where FileName in~ ("cmd.exe", "powershell.exe")
| project Timestamp, DeviceName, AccountName, InitiatingProcessFileName, FileName, ProcessCommandLine
```

## Precision through specificity

A detection becomes higher fidelity when it requires several independent conditions that legitimate activity rarely satisfies together. Compare:

- `FileName == "powershell.exe"`: fires constantly.
- `... and ProcessCommandLine has "-enc"`: encoded commands, still common in some administration tools.
- `... and InitiatingProcessFileName in~ ("winword.exe","excel.exe")`: PowerShell with an encoded command launched by Office is rare and suspicious.

## Test data

Never deploy a detection that you have only seen *not* fire. Build a small test set: a handful of events representing the attack and a larger set of realistic normal events. Your detection should return all of the former and none of the latter. Lab 4 asks you to do exactly that.
