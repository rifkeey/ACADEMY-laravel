## Combining tables with `join`

Investigations often need two tables at once: sign-ins and audit events, or process events and network events. KQL's `join` combines them on a shared key:

```kql
let SuspiciousIPs =
    SigninLogs
    | where ResultType != "0"
    | summarize TargetedUsers = dcount(UserPrincipalName) by IPAddress
    | where TargetedUsers >= 5;
SigninLogs
| where ResultType == "0"
| join kind=inner SuspiciousIPs on IPAddress
| project TimeGenerated, UserPrincipalName, IPAddress, TargetedUsers
```

This finds **successful** sign-ins from IP addresses that sprayed at least five accounts: the "failures followed by success" correlation from Module 3.

Join kinds to know:

- `inner`: rows that match on both sides.
- `leftouter`: every row from the left, with matching right-side columns where available.
- `leftanti`: left rows that have **no** match on the right. This is ideal for "assets that did not send logs today" or "users who signed in but never completed MFA".
- `innerunique` is the **default** in KQL and de-duplicates the left side first. Specify `kind=` explicitly to avoid surprises.

> **Lab note:** The AMANIQ KQL simulator does not implement `let` or `join`. Lab tasks that need correlation are designed so you can answer them with two separate queries. Use this lesson's patterns when you work in a real Sentinel workspace.

## Time-based analysis

- **First and last seen**: `summarize min(TimeGenerated), max(TimeGenerated) by Entity`.
- **Gaps and bursts**: `summarize count() by bin(TimeGenerated, 1m)` and look for spikes.
- **Ordering events for a timeline**: `order by TimeGenerated asc` and project only the fields that tell the story.
- **Time windows around a pivot**: once you know a compromise happened at 02:14, search `between (datetime(2026-03-04T01:44:00Z) .. datetime(2026-03-04T02:44:00Z))` across other tables.

## Writing detection-quality queries

A query that answers a one-off question can be messy. A query that will run every five minutes for years must be:

- **Specific**: filter on the narrowest reliable fields first (`EventID`, `ResultType`), then on strings.
- **Time-bounded**: always include a time filter.
- **Readable**: one operator per line, comments with `//`.
- **Tested**: run it against known-good and known-bad data before enabling it.

## Performance rules of thumb (production Sentinel)

1. Filter by time first, then by indexed columns, then by string matches.
2. Prefer `has` to `contains` when you are matching whole words; it uses the term index.
3. `project` early to reduce the columns carried through the pipeline.
4. Avoid `*` searches across all tables in scheduled rules.
5. Summarise before joining where possible, so that the join handles fewer rows.

Microsoft's official KQL documentation includes a best-practices page that explains these in depth; it is listed in this lesson's references.
