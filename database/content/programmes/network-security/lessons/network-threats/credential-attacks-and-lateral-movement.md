Most serious intrusions involve two steps after the first foothold: **getting valid credentials**, and **moving from the first system to more valuable ones** (lateral movement). Both use legitimate protocols and accounts, so they can look like normal work. The network is often where they become visible.

## Credential attacks at a glance

| Technique (ATT&CK) | What it means | Typical evidence |
| --- | --- | --- |
| **Brute Force** (T1110): guessing | Many passwords tried against one account | Bursts of failed logins for one account from one source |
| **Password spraying** (T1110.003) | A few common passwords tried against many accounts, slowly, to avoid lockouts | Failed logins spread across many accounts from one or a few sources |
| **Credential stuffing** (T1110.004) | Username and password pairs leaked from other websites are tried | Logins for many accounts, some succeeding, often from many addresses |
| **Valid Accounts** (T1078) | Stolen or reused credentials used successfully | A successful login that is unusual for that user: time, place, device or system |

## Lateral movement at a glance

After a foothold, an intruder uses normal administration protocols to reach more systems. MITRE ATT&CK groups these under *Remote Services* (T1021), for example remote desktop (T1021.001), SMB/Windows admin shares (T1021.002) and SSH (T1021.004).

What makes it detectable is **who connects to what**:

| Signal | Why it matters |
| --- | --- |
| A workstation connecting to many other workstations | Users rarely need to; administrators work from the management zone |
| A user account logging on to many servers in a short time | Unusual for most roles |
| Administration protocols (RDP, SMB, SSH, remote management) from zones that shouldn't use them | Breaks the design from module 4 |
| A service account logging on interactively, or from a new host | Service accounts should follow fixed patterns |
| DMZ or VPN hosts reaching restricted zones | The module 9 incident pattern |

## Detecting it

1. **Authentication logs**: failed-login bursts, spraying patterns, successful logins after failures, impossible travel, new devices (identity provider and domain controller logs).
2. **Network evidence**: flow records and IDS alerts for administration protocols between zones (modules 5 and 9).
3. **Baselines**: which accounts normally use which systems, so a new combination stands out.
4. **Correlation**: a successful login followed by connections to several servers from the same host is far stronger evidence than either alone.

## Reducing the risk

| Control | Effect |
| --- | --- |
| **Phishing-resistant MFA** for remote access, administrators and cloud consoles (module 6) | Stolen passwords alone are not enough |
| **Lockout and throttling**, plus checking new passwords against known-breached lists | Slows guessing and spraying |
| **Segmentation and least-privilege rules** (modules 3 and 4) | Administration protocols allowed only from the management zone |
| **Tiered administration and a jump host** | Administrator credentials never used on ordinary workstations |
| **Unique local administrator passwords** managed by a tool | One captured password doesn't open every machine |
| **Remove standing privilege** | Just-in-time administration |
| **Monitoring and alerting** on the signals above | Earlier detection |

:::takeaway
Credential attacks show up in authentication logs as guessing, spraying or unusual successes; lateral movement shows up in network and login data as accounts and hosts reaching systems they normally don't. Detect both with baselines and correlation, and reduce them with phishing-resistant MFA, throttling, segmentation, tiered administration, unique local passwords and less standing privilege.
:::
