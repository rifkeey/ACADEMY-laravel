## From rows to answers

Most investigation questions are aggregations: *how many*, *how many distinct*, *first and last seen*, *top N*. In KQL these are answered with `summarize`.

```kql
SigninLogs
| where ResultType != "0"
| summarize Failures = count() by UserPrincipalName
| order by Failures desc
```

`summarize` groups rows by the columns after `by` and computes aggregation functions for each group.

## Aggregation functions you will use daily

| Function | Returns |
| --- | --- |
| `count()` | Number of rows in the group |
| `dcount(col)` | Approximate number of distinct values (exact in the AMANIQ simulator) |
| `min(col)`, `max(col)` | Smallest / largest value; with timestamps, first and last seen |
| `sum(col)`, `avg(col)` | Totals and means |
| `make_set(col)` | The distinct values as an array |

## Password spraying versus brute force

These two attacks look different once aggregated, which is why `summarize` is so useful:

- **Brute force**: many passwords against **one** account. Group by user and the count is high.
- **Password spraying**: one or two common passwords against **many** accounts, to stay under lockout thresholds. Group by source IP and `dcount(UserPrincipalName)` is high.

```kql
SigninLogs
| where ResultType != "0"
| summarize Attempts = count(), TargetedUsers = dcount(UserPrincipalName),
            FirstSeen = min(TimeGenerated), LastSeen = max(TimeGenerated) by IPAddress
| where TargetedUsers >= 5
| order by TargetedUsers desc
```

## Time series with `bin()`

`bin()` rounds values down to fixed buckets. Grouping by `bin(TimeGenerated, 1h)` creates an hourly histogram, which is how you spot spikes and beaconing:

```kql
DnsEvents
| where ClientIP == "10.20.4.17"
| summarize Queries = count() by bin(TimeGenerated, 5m)
| order by TimeGenerated asc
```

## Sorting and limiting

- `order by Col desc` (or `sort by`) sorts. **KQL's default sort direction is descending**, which surprises people coming from SQL. Write `asc` or `desc` explicitly.
- `take 10` returns any 10 rows (no ordering guarantee).
- `top 10 by Col` sorts and limits in one step.
- `distinct Col1, Col2` returns unique combinations.

## Check your understanding

If `summarize count() by IPAddress` returns one row with 300 failures, is that spraying or brute force? You cannot tell yet. Add `dcount(UserPrincipalName)`: one user means brute force, 300 users means spraying.
