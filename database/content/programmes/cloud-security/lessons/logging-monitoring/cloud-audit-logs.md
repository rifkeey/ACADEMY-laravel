:::scenario "Can we tell who changed it?"
After the storage exposure in module 4, Kanem's head of risk asks a simple question: *"If someone changes a security setting in our cloud accounts, can we see who did it, when, and from where, and would anyone be told?"* This module is about making the answer "yes".
:::

In the cloud, every change is an API call, so every change **can** be recorded. Whether it **is** recorded, kept and reviewed depends on configuration that is the customer's responsibility (module 1).

## Two kinds of activity

| | Management (control-plane) events | Data (data-plane) events |
| --- | --- | --- |
| What they record | Creating, changing and deleting resources and settings: new users, policy changes, network rules, logging configuration | Using resources: reading or writing objects, querying data, using keys |
| Volume | Moderate | Can be very high |
| Default | Usually recorded by default for a limited period | Usually **off** by default; enable for sensitive resources |

## Where each provider records them

| Provider | Management activity | Data access | Notes |
| --- | --- | --- | --- |
| AWS | **CloudTrail** management events (90-day event history by default; create a trail to keep them) | CloudTrail data events (for example S3 object access), enabled per resource | An organisation trail covers every account |
| Azure | **Activity log** (subscription-level operations) | Resource logs via diagnostic settings (for example storage reads) | Microsoft Entra ID has its own sign-in and audit logs (lesson 2) |
| Google Cloud | **Admin Activity** audit logs (always on) | **Data Access** audit logs (mostly off by default; enable per service) | Configured at organisation, folder or project level |

## What an audit record tells you

Every provider's format differs, but a useful audit record answers the same questions:

| Question | Typical field |
| --- | --- |
| Who? | The identity (user, role, service account) and how they authenticated |
| What? | The API operation and the resource |
| When? | Timestamp (UTC) |
| From where? | Source IP address, client or user agent |
| Did it work? | Success or error code |

## A sensible baseline

1. **Record management events for every account, subscription and project**, using organisation-wide trails or policies so new accounts are covered automatically.
2. **Enable data-access logging for confidential and restricted data** (the classification from module 4), and for key usage.
3. **Keep logs long enough** for investigations and regulatory needs, which is usually much longer than the default.
4. **Send them to a central, protected location** (lesson 3).
5. **Alert on the changes that matter most** (lesson 4).

:::takeaway
Cloud audit logs record management changes (usually on by default for a limited time) and data access (usually off unless you enable it). Turn on management logging everywhere, data logging where the data is sensitive, keep the logs long enough and centralise them.
:::
