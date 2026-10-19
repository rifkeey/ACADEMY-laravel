## What a SIEM actually does

A Security Information and Event Management (SIEM) platform collects logs from many sources, makes them searchable in one place, and runs detection logic over them. Splunk, Microsoft Sentinel, Elastic Security, Google Security Operations and IBM QRadar are common examples. The concepts in this module apply to all of them.

A SIEM pipeline has five stages:

1. **Collection**: agents, syslog forwarders, API connectors and cloud-native integrations deliver data.
2. **Parsing**: raw text becomes structured fields (`src_ip`, `user`, `action`).
3. **Normalisation**: fields from different products are mapped to a common schema, so that "source IP" has one name whatever produced it.
4. **Enrichment**: lookups add context, such as asset owner, geolocation or a threat-intelligence verdict.
5. **Storage and retention**: hot storage for fast search, and cheaper archive storage for long-term evidence.

## Parsing and normalisation in practice

Consider the same failed SSH logon from two products:

```text
# Raw syslog
Mar  4 03:01:15 web-01 sshd[2213]: Failed password for root from 198.51.100.23 port 51130 ssh2

# A vendor's JSON audit format
{"ts":"2026-03-04T02:01:15Z","host":"web-01","evt":"auth_fail","account":"root","client":"198.51.100.23"}
```

After normalisation both should become something like:

| Field | Value |
| --- | --- |
| TimeGenerated | 2026-03-04T02:01:15Z |
| DeviceName | web-01 |
| EventType | LogonFailed |
| TargetUser | root |
| SourceIP | 198.51.100.23 |

Only then can one rule detect brute force across every source. Microsoft Sentinel's Advanced Security Information Model (ASIM) and the Elastic Common Schema (ECS) are examples of normalisation schemas.

## Timestamps: the silent failure

Each event can carry several times: when it happened on the device, when the device wrote it, when the collector received it and when the SIEM ingested it. Detection rules that look back over "the last 5 minutes" by ingestion time can miss events that arrived late. Analysts should know:

- which timestamp field their queries filter on;
- the typical ingestion delay for each source;
- whether any source reports local time without an offset.

## Coverage and cost

Ingesting everything is rarely affordable, and SIEM licensing is often priced by volume. Decide what to collect from detection needs, not habit. A useful question for each source is: *which detections or investigations would fail without this data?* NIST SP 800-92 provides vendor-neutral guidance on planning log management, including retention and protecting log integrity.

> **Analyst habit:** When a query returns nothing, first prove that the data source is present for that time range. "No results" and "no data" are different findings.
