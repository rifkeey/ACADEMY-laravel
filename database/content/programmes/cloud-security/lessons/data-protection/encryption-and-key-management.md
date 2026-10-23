Encryption is easy to switch on in the cloud and easy to misunderstand. The real security question is rarely "is it encrypted?" but **"who can use the key?"**

## In transit and at rest

| | Protects against | Cloud reality |
| --- | --- | --- |
| **In transit** (TLS) | Eavesdropping or tampering on the network | Provider APIs require TLS; you must also use TLS between your own components and enforce it (for example, storage policies that reject unencrypted requests) |
| **At rest** | Someone obtaining the physical media or underlying storage | Most major services now encrypt stored data by default |

As module 1 showed, encryption at rest does **not** protect data from a request the service authorises: if an identity or a public policy allows reading, the service decrypts the data for that request. Access control decides who receives plaintext.

## Who manages the key?

| Option | Who controls the key | Typical use |
| --- | --- | --- |
| **Provider-managed keys** | The provider, automatically | Default; good baseline for most data |
| **Customer-managed keys** in the provider's key service (AWS KMS, Azure Key Vault, Google Cloud KMS) | You control key policy, rotation and who may use it; the provider operates the hardware | Confidential and restricted data; separation of duties; ability to make data unusable by disabling the key |
| **Customer-supplied or external keys** | You hold the key outside the provider | Strict regulatory or sovereignty requirements; more operational burden and risk of losing access |

Customer-managed keys add a **second, independent permission check**: to read a restricted file, an identity needs permission on the storage **and** permission to use the key. That lets you separate duties: storage administrators can manage buckets without being able to decrypt the data.

## Envelope encryption

Cloud key services rarely encrypt your data directly. They use **envelope encryption**: each object or volume is encrypted with its own **data key**, and the data key is encrypted with a **key-encryption key** held in the key service. Benefits: large data never leaves the service to be encrypted, the master key never leaves the key service's protected hardware, and every use of the master key is **logged**.

## Key management practices

NIST SP 800-57 Part 1 is the long-standing reference for key management. The practical essentials:

- **Least privilege on keys**: separate who can *administer* a key from who can *use* it to encrypt or decrypt.
- **Rotation**: enable automatic rotation where supported; rotation limits how much data one key version protects.
- **Logging**: key-usage events (every decrypt) are some of the most valuable audit records you have; alert on unusual patterns.
- **Deletion is irreversible**: deleting a key makes everything encrypted with it unreadable. Providers build in waiting periods; protect key deletion with strong permissions and alerts.
- **Separate keys by purpose and sensitivity**: one key for loan documents, another for invoices, so access and incidents can be scoped.

:::reveal Back to the scenario: do the loan documents need customer-managed keys?
They are *restricted* (national ID numbers, bank statements), so yes is a defensible answer: a customer-managed key with a key policy that allows only the loans application role to decrypt gives an extra control and a precise audit trail. But it does not replace fixing access to the bucket itself, and it adds operational work (key policies, rotation, monitoring) that must have an owner.
:::

:::takeaway
Encrypt in transit and at rest, but remember that the service decrypts for anyone it authorises. Choose who manages keys based on data sensitivity, separate key administration from key use, rotate, log every use, and protect keys from deletion.
:::
