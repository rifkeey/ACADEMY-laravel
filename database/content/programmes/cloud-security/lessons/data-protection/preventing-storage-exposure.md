## The most common cloud data breach

Object storage (AWS S3, Azure Blob Storage, Google Cloud Storage) is simple to use and simple to expose. Publicly readable buckets containing backups, customer exports or logs have caused many well-publicised breaches.

## How exposure happens

- A bucket policy or ACL grants read access to "everyone" or "all authenticated users of the provider" (which means anyone with any account).
- A shared link or signed URL with a very long lifetime is posted somewhere public.
- An application with excessive permissions is compromised and used to read data.
- Account-level public-access protections are disabled "temporarily".

## Preventive controls

1. Enable the provider's **account-level block on public access**, and require an exception process to override it.
2. Grant access through identities and policies, not public ACLs.
3. Use **private endpoints** and policies that deny access from outside your networks for sensitive data.
4. Monitor and alert on policy changes that make data public.

## Encryption

- **In transit**: enforce TLS; deny requests that do not use it.
- **At rest**: most providers encrypt storage by default with provider-managed keys. **Customer-managed keys** in a key management service (KMS) add control: you can restrict who may use the key, audit every use, and revoke access by disabling the key.

Encryption does not help if an over-privileged identity can both read the data and use the key. Key policies must also follow least privilege.

## Classification and access policies

Classify data (for example public, internal, confidential, restricted) and tag storage accordingly. Use tags in policies and monitoring: restricted data should never be in a bucket that allows public access, and any attempt should alert.

## Detecting and remediating exposure

When you find exposed data: remove public access immediately, preserve evidence of the configuration and access logs, determine from the access logs whether anyone downloaded the data, and escalate for possible breach-notification obligations.
