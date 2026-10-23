Applications need **secrets**: database passwords, API keys for partners, signing keys, certificates. Module 2 showed how to remove many of them with workload identities. For those that remain, a **secrets manager** keeps them out of code and under control.

## Where secrets should not live

| Location | Problem |
| --- | --- |
| Source code and configuration files in Git | Copied to every clone and fork; history keeps them even after deletion |
| Container images | Anyone who can pull the image can read them |
| Plain environment variables in deployment templates | Visible in consoles, logs and templates; rarely rotated |
| Spreadsheets, chat messages, tickets | No access control, no audit trail |

## Secrets managers

| Provider | Service |
| --- | --- |
| AWS | AWS Secrets Manager (and Systems Manager Parameter Store for simpler cases) |
| Azure | Azure Key Vault (secrets, keys and certificates) |
| Google Cloud | Secret Manager |

They give you:

- **Central storage**, encrypted with keys you can control.
- **Access control by identity**: the invoice application's role may read *its* database password and nothing else.
- **Audit logs** of every read.
- **Versioning and rotation**, in some services automatic for supported databases.

The application fetches the secret at run time using its workload identity, so the only "secret" it needs is its identity, which the platform provides.

## Rotation

A secret that never changes is valid forever once leaked. Good practice:

- Rotate on a **schedule** proportionate to sensitivity.
- Rotate **immediately** when a person who knew it leaves, or when exposure is suspected.
- Design applications to **re-read** secrets, so rotation doesn't need a redeployment.
- Prefer **short-lived credentials** (database authentication using cloud identities, where supported) over passwords at all.

## Detect leaked secrets early

- Enable **secret scanning** in code repositories and in the CI pipeline, so commits containing credentials are blocked or flagged.
- Monitor secrets-manager logs for reads by unexpected identities.
- Have a **response runbook**: revoke or rotate, find where the secret was used, check audit logs, and remove it from wherever it leaked.

:::takeaway
Remove secrets where workload identity can replace them; keep the rest in a secrets manager with identity-based access, logging and rotation; and scan code and pipelines so leaks are caught before they matter.
:::
