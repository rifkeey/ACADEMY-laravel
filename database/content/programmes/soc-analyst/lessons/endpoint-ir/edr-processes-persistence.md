## What EDR gives an analyst

Endpoint Detection and Response (EDR) tools record detailed activity on each endpoint (process starts, command lines, network connections per process, file writes, registry changes) and provide response actions such as isolating a host or killing a process. Examples include Microsoft Defender for Endpoint, CrowdStrike Falcon, SentinelOne and open-source options such as Wazuh combined with Sysmon.

The analyst's core skill is reading a **process tree**: who started what, with which arguments, as which user.

## Suspicious process patterns

| Pattern | Why it is suspicious | Example |
| --- | --- | --- |
| Office or PDF reader spawning a shell | Malicious document executing code | `WINWORD.EXE -> powershell.exe -enc ...` |
| Scripting engine downloading content | Living-off-the-land download | `powershell.exe -c "iwr http://... -OutFile ..."` |
| Signed system binary used to proxy execution | Defence evasion (LOLBins) | `rundll32.exe` loading a DLL from `%TEMP%`; `mshta.exe` with a URL |
| Processes running from user-writable paths | Malware rarely installs to Program Files | `C:\Users\Public\svch0st.exe` |
| Credential tooling | Credential access | Access to `lsass.exe` memory by an unusual process |
| Mass file renaming with new extensions | Ransomware encryption | Thousands of `*.docx.locked` writes per minute |

Look for **misspellings** of real system binaries (`svch0st.exe`, `explorer .exe`) and real names in the wrong place (`svchost.exe` running from a user's Downloads folder).

## Persistence: how attackers survive a reboot

Persistence mechanisms are high-value detections because legitimate software changes them relatively rarely:

- **Registry Run keys**: `HKCU\Software\Microsoft\Windows\CurrentVersion\Run` and the `HKLM` equivalent (ATT&CK T1547.001).
- **Scheduled tasks** (T1053.005): `schtasks /create` with a task that runs a script from a user folder.
- **Services** (T1543.003): a new service pointing to an unusual binary.
- **WMI event subscriptions** (T1546.003).
- **New local or domain accounts** (T1136).
- On Linux: cron jobs, systemd units, `authorized_keys` and shell profile changes.

```text
Sysmon Event 13 (RegistryValueSet)
Image: C:\Users\a.bello\AppData\Roaming\upd\updater.exe
TargetObject: HKCU\Software\Microsoft\Windows\CurrentVersion\Run\OneDriveSync
Details: C:\Users\a.bello\AppData\Roaming\upd\updater.exe
```

The value name imitates a legitimate product (`OneDriveSync`), but the binary path does not belong to OneDrive. That mismatch is the finding.

## Ransomware indicators

Modern ransomware operations usually follow a sequence: initial access, credential theft, lateral movement, disabling backups and security tools, data exfiltration and only then encryption. Early indicators therefore matter more than the encryption itself:

- `vssadmin delete shadows`, `wbadmin delete catalog` or `bcdedit` changes;
- attempts to stop security services;
- unusual remote execution tools (PsExec, remote WMI) spreading from one host;
- large outbound transfers to file-sharing or cloud-storage services before encryption.
