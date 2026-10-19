## Where Linux records authentication

On Debian and Ubuntu systems, authentication activity is written by `rsyslog` to `/var/log/auth.log`. On RHEL-family systems the equivalent is `/var/log/secure`. On systems that use only the systemd journal, query it with `journalctl`.

```bash
# Recent SSH activity from the journal
journalctl -u ssh --since "2026-03-04 00:00" --until "2026-03-04 06:00"

# Failed password attempts in a classic log file
grep "Failed password" /var/log/auth.log | tail -n 20

# Count failures per source address
grep "Failed password" /var/log/auth.log | grep -oE "from [0-9.]+" | sort | uniq -c | sort -rn | head
```

## Lines you must recognise

```text
Mar  4 03:01:12 web-01 sshd[2211]: Failed password for invalid user admin from 198.51.100.23 port 51122 ssh2
Mar  4 03:01:15 web-01 sshd[2213]: Failed password for root from 198.51.100.23 port 51130 ssh2
Mar  4 03:07:40 web-01 sshd[2290]: Accepted publickey for deploy from 10.20.1.5 port 40022 ssh2: ED25519 SHA256:...
Mar  4 03:09:02 web-01 sudo:   deploy : TTY=pts/0 ; PWD=/srv/app ; USER=root ; COMMAND=/usr/bin/systemctl restart app
```

- `invalid user` means the username does not exist on the host. Many of these from one address is opportunistic brute forcing.
- `Accepted publickey` versus `Accepted password` tells you which authentication method succeeded. If your policy is key-only, a password success is itself a finding.
- `sudo` lines record the invoking user, the working directory, the target user and the exact command. They are essential for reconstructing what an intruder did after logging in.

## Timestamps and time zones

Classic syslog timestamps (`Mar  4 03:01:12`) contain **no year and no time zone**. They are written in the host's local time. Before correlating with cloud logs (normally UTC) you must establish the host's time zone and convert. Many investigation errors come from a one-hour offset that nobody noticed. Nigeria uses West Africa Time (UTC+1) all year, so a Lagos server's local `03:01` is `02:01` UTC.

Modern `rsyslog` and journald configurations can emit RFC 3339 timestamps with offsets, for example `2026-03-04T03:01:12+01:00`. Prefer that format wherever you control the configuration.

## Brute force versus compromise

A brute-force *attempt* is common and usually low severity on an internet-facing SSH server. The question that matters is whether any attempt **succeeded**. Always search for `Accepted` lines from the same source address, and for successful logons followed by unusual `sudo` usage, new SSH keys in `~/.ssh/authorized_keys`, or new cron entries.
