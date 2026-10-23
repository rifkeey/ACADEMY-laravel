Two pieces of architecture are almost impossible to add properly later: where the logs go, and who controls the encryption keys. Both should be designed with the landing zone.

## Logging architecture

| Layer | Design choice for Kanem Pay |
| --- | --- |
| **Collection** | Organisation trail for all accounts and regions; data-access logs on buckets and databases holding payment and personal data; sign-in logs; flow logs on production networks; workload and application logs |
| **Destination** | The **log archive account**, written to by the services, readable by the security team, unchangeable by workload administrators |
| **Integrity** | Object lock or immutable storage, log file validation, deny policies on deleting or stopping trails (module 9 showed why) |
| **Retention** | Set by regulation and investigation needs, for example 90 days searchable in the SIEM and 7 years archived for audit trails |
| **Analysis** | Logs streamed to the SIEM; managed threat detection enabled in every account (module 5) |
| **Monitoring the monitoring** | Alerts if a log source stops sending, a trail is changed, or a detection service is disabled |

## Key architecture

Module 4 explained encryption and key management. At design level the decisions are:

| Decision | Recommended for Kanem Pay |
| --- | --- |
| Who manages the keys? | **Customer-managed keys** in the cloud key management service for payment and personal data; provider-managed keys acceptable for low-sensitivity data |
| How many keys? | Separate keys per application and data class, so one key policy can't expose everything |
| Who can use and who can administer? | Applications can encrypt and decrypt; a small key-administration group can manage policies but **cannot** decrypt data |
| Where do key logs go? | Key usage logs to the log archive, with alerts on unusual decrypt volume or policy changes |
| Rotation | Automatic rotation enabled; documented procedure for compromise |
| Hardware or external keys? | Only where a regulation or contract requires it; they add cost and operational risk |
| Secrets | Database passwords and API keys in a secrets manager, rotated automatically, never in code or pipelines (module 8) |

## How the two connect

- Encrypt the log archive with its own customer-managed key, administered by security, not by workload teams.
- Key usage events are evidence in incidents (module 9, lesson 4): they can show data being read when object-level logs are missing.
- Deleting or disabling a key can make data permanently unreadable, so key deletion needs a waiting period, approval and an alert.

:::takeaway
Design logging and keys with the landing zone: collect broadly, deliver to a protected log archive with integrity controls and sensible retention, and monitor the monitoring. Use customer-managed keys per application and data class, separate key administration from data use, log key usage, rotate automatically and keep secrets in a secrets manager.
:::
