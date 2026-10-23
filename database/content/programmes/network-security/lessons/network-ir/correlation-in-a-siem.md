A SIEM (security information and event management system) collects logs from many sources, normalises them and lets analysts search and correlate them. Correlation is where single weak signals become a clear story.

## Normalisation

Different devices name the same thing differently: `src`, `SrcIP`, `source_address`. A SIEM maps them to common fields so one query can search all sources. When you write queries, check the field names for each table.

## Correlating by address and time

Most network correlation joins on **IP address** and **time window**:

| Step | Question | Source |
| --- | --- | --- |
| 1 | What alerted, and on which host? | IDS alert |
| 2 | What else did that host do before and after? | Flows, firewall, DNS |
| 3 | Who or what is the host? | Asset inventory, DHCP |
| 4 | Is this new for the host? | The same sources for previous weeks |
| 5 | Did any other host do the same? | Search all hosts for the same destination or domain |

## Query patterns in KQL

These patterns work in the lab's KQL console (and, with small changes, in most SIEMs):

```text
// Everything one host did, in time order
FlowRecords
| where SrcIP == "10.30.0.12" or DstIP == "10.30.0.12"
| sort by TimeGenerated asc

// Which destinations did it contact, and how much did it send?
FlowRecords
| where SrcIP == "10.30.0.12"
| summarize Flows = count(), Sent = sum(BytesToDst) by DstIP, DstPort

// Biggest senders to the internet overnight
FlowRecords
| where not(DstIP startswith "10.")
| summarize Sent = sum(BytesToDst) by SrcIP
| top 5 by Sent

// Who else looked up the suspicious domain?
DnsLogs
| where Query == "suspicious-domain.example"
| distinct ClientIP
```

## Correlation rules and baselines

| Rule type | Example |
| --- | --- |
| **Sequence** | An IDS alert on a host followed within an hour by a new internal connection from that host |
| **Threshold** | More than 1 GB sent to a single external address in an hour from a server |
| **New behaviour** | A host contacting a port or zone it has not used in the past 30 days |
| **Policy** | Any connection from the DMZ to the server zone on a port not on the approved list |

Rules need **baselines** (what is normal for each host or zone) and **tuning** (module 5) so they find real problems without burying analysts.

:::tip Watch the direction of bytes
Flow records count bytes in each direction. For an inbound session, data that leaves your network appears as bytes sent **back to the source**. Large "bytes to source" on an inbound connection can mean data is being pulled out through it.
:::

:::takeaway
Normalise, then correlate by address and time: alert, surrounding activity, asset, history, and other hosts. Use simple query patterns to list activity, total bytes and find who else is affected, and build correlation rules on sequences, thresholds, new behaviour and policy, with baselines and tuning.
:::
