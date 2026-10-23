Encryption and access control protect confidentiality. **Backups** protect availability and integrity: the ability to recover when data is deleted, corrupted or encrypted by ransomware. In the cloud, backups need the same care as the data itself, and they need to be **tested**.

## What can go wrong

| Event | Example |
| --- | --- |
| Accidental deletion | A script deletes the wrong prefix in a bucket |
| Corruption | A faulty release writes bad data to the invoice database |
| Malicious deletion or encryption | An attacker with stolen administrator credentials deletes resources or encrypts data, and tries to delete backups too |
| Provider or region outage | A region is unavailable for hours |

The third case shapes modern backup design: **backups must survive an attacker who has your administrator credentials.**

## Design principles

| Principle | Cloud implementation |
| --- | --- |
| **Separate the backups from what they protect** | Store backups in a different account, subscription or project, with different administrators |
| **Make them immutable** | Write-once storage or locked backup vaults: S3 Object Lock, immutable Azure Blob storage or backup vault immutability, Google Cloud bucket lock or backup vault enforcement |
| **Keep multiple copies, including off-region** | Cross-region copies for critical data, within approved data-location rules |
| **Protect the keys** | Backups encrypted with keys the backup service can use even if the production account is compromised |
| **Retain for the right time** | Retention set by business need and regulation, not by default |

A widely used rule of thumb is **3-2-1**: three copies of the data, on two different types of storage, with one copy off-site (or, in the cloud, in a separate account and region). It is a practice, not a standard, but a useful checklist.

## Recovery objectives

Agree two numbers with the business for each system:

- **RPO** (recovery point objective): how much data, measured in time, you can afford to lose. Hourly backups give an RPO of about an hour.
- **RTO** (recovery time objective): how long the system may be unavailable.

The invoice application might accept an RPO of 1 hour and an RTO of 4 hours; core banking would need far less. These numbers decide backup frequency, cross-region design and cost.

## Test the restore

An untested backup is a hope, not a control. Schedule restore tests:

1. Restore a recent backup into an isolated environment.
2. Check the data is complete and the application works.
3. Measure the time taken against the RTO.
4. Record the result, and fix what failed.

Restore tests also confirm that the people on call know how to do it, and that the permissions and keys needed are available in an emergency.

:::takeaway
Back up to a separate account, make backups immutable, keep off-region copies where allowed, protect the keys, set retention by need, agree RPO and RTO with the business, and test restores on a schedule. Assume an attacker will try to delete your backups.
:::
