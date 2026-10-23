You cannot defend a network you cannot see. **Flow logs** record the connections in your cloud networks: who talked to whom, on which port, how much, and whether it was accepted or rejected. They are the cloud equivalent of the firewall connection logs from the Network Security course.

## What each provider offers

| Provider | Feature | Notes |
| --- | --- | --- |
| AWS | **VPC Flow Logs** | At VPC, subnet or network-interface level; to CloudWatch Logs, S3 or Firehose |
| Azure | **Virtual network flow logs** (Network Watcher) | Microsoft is moving customers from the older NSG flow logs to virtual network flow logs; check Microsoft Learn for dates |
| Google Cloud | **VPC Flow Logs** | Per subnet; records a sample of flows (sampling rate is configurable) |

## Reading a flow record

An AWS flow log record in the default format:

```text
version account-id    interface-id  srcaddr       dstaddr     srcport dstport protocol packets bytes start      end        action log-status
2       111122223333  eni-0a1b2c3d  198.51.100.77 10.50.30.12 51544   5432    6        5       312   1773025200 1773025260 ACCEPT OK
```

*(Synthetic.)* Read it as: an internet address (`198.51.100.77`) connected to a private address in the database subnet (`10.50.30.12`) on PostgreSQL (5432), over TCP (protocol 6), and the network **accepted** it. For the invoice database, that one line is evidence of the exposure in this module's scenario, and the start of an investigation into what that address did.

## What flow logs are good for

| Use | Question answered |
| --- | --- |
| **Investigation** | Which hosts did this compromised instance talk to? Did data leave, and to where? |
| **Detection** | Is anything on the internet connecting to tiers that should be private? Is a server suddenly talking to an unusual destination? |
| **Validating segmentation** | Is there any traffic on paths the design says are closed? (Network Security module 4) |
| **Troubleshooting** | Why is this connection being rejected? |

## What they don't show

- **Content**: no payloads, URLs or queries. Combine with application, load balancer, DNS and audit logs.
- **Everything**: some platform traffic is not recorded (for example, AWS documents that traffic to its own DNS resolver and the instance metadata service is excluded). Read each provider's limitations.
- **Identity**: a flow record shows IP addresses, not which user or role. Correlate with cloud audit logs (module 5).

## Making them useful

- **Turn them on deliberately**: at least for production networks, internet-facing subnets and anything holding sensitive data.
- **Centralise** them in a logging account or workspace that workload administrators cannot alter.
- **Set retention** to meet investigation and regulatory needs, and control cost with sampling, filtering or aggregation where acceptable.
- **Alert on the few patterns that matter**: accepted inbound connections from the internet to private tiers, and new outbound destinations from servers that shouldn't have them.

:::takeaway
Flow logs record who talked to whom and whether it was allowed. Enable them for important networks, centralise them where they can't be tampered with, know their blind spots, and combine them with audit and application logs to answer what really happened.
:::
