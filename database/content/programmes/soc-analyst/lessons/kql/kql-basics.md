## Where KQL fits

Kusto Query Language (KQL) is the query language of Microsoft Sentinel, Microsoft Defender XDR advanced hunting, Azure Monitor Log Analytics and Azure Data Explorer. It is read-only: queries filter and reshape data, they never modify it. That makes KQL a safe and expressive tool for investigation.

> **About the AMANIQ lab environment:** You do not need a Microsoft subscription for this programme. AMANIQ labs run your queries with a built-in interpreter that implements a documented subset of KQL over synthetic datasets. Syntax you learn here is real KQL and transfers directly to Sentinel. Operators not supported by the simulator (such as `join`) are explained in the lessons and flagged clearly if you try them in a lab.

## The shape of a query

A KQL query starts with a table and passes rows through a pipeline of operators, separated by `|`:

```kql
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType != "0"
| project TimeGenerated, UserPrincipalName, IPAddress, Location, ResultType
| take 50
```

Read it top to bottom: take the `SigninLogs` table, keep the last day, keep failures (in Entra ID sign-in logs a `ResultType` of `"0"` means success), choose five columns, and show 50 rows.

## Filtering with `where`

| Operator | Meaning | Case-sensitive? |
| --- | --- | --- |
| `==`, `!=` | Equals / not equals | Yes |
| `=~`, `!~` | Equals / not equals | No |
| `contains`, `!contains` | Substring match | No |
| `has`, `!has` | Whole-term match (faster on large tables) | No |
| `startswith`, `endswith` | Prefix / suffix | No |
| `in (...)`, `!in (...)` | Value in a list | Yes |
| `<`, `<=`, `>`, `>=` | Comparison (numbers, datetimes) | n/a |

Combine conditions with `and` and `or`, and use parentheses to make precedence obvious:

```kql
SecurityEvent
| where EventID == 4625 and (TargetUserName =~ "administrator" or TargetUserName =~ "admin")
```

## Time filters first

Always filter on time as early as possible. `ago(1h)`, `ago(7d)` and `between (datetime(2026-03-04T00:00:00Z) .. datetime(2026-03-05T00:00:00Z))` are the usual patterns. In production Sentinel, time filtering is also the biggest single factor in query performance.

## Choosing columns: `project` and `extend`

`project` keeps only the columns you list and can rename them. `extend` adds calculated columns without dropping existing ones:

```kql
SigninLogs
| extend Hour = bin(TimeGenerated, 1h)
| project Hour, UserPrincipalName, IPAddress
```

## Practice

In the lab workspace, try these and compare the outputs:

```kql
SigninLogs | take 10
SigninLogs | where ResultType != "0" | count
SigninLogs | where Location != "NG" | project TimeGenerated, UserPrincipalName, Location
```
