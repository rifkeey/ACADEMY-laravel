The question every executive asks first is: **"Was customer data taken?"** Answering it honestly needs the right logs, careful analysis and an early conversation with legal and data protection colleagues.

## The evidence for data access

| Source | Shows | Caveat |
| --- | --- | --- |
| **Data-access logs** for storage (object-level read and write events) | Which identity read which object, when, from where | Often **off by default** and charged separately; if they weren't enabled, you may not be able to prove what was read |
| Control-plane audit log | Policy changes that exposed data, snapshot shares, key usage | Shows exposure, not reads |
| Key management logs | Decrypt calls using a data key (module 4) | Useful when data-access logs are missing |
| Storage access logs / network flow logs | Request counts and volumes | Less detail, but better than nothing |
| Data classification (module 4) | What kind of data each bucket holds | Tells you how serious the exposure is |

## Working out what happened

1. **Which stores** did suspicious identities touch? List buckets, databases and snapshots by identity.
2. **How much?** Count objects read and bytes transferred per store.
3. **What kind of data?** Use the classification: public, internal, confidential, restricted.
4. **Was anything made public or shared externally**, and for how long? Public exposure counts even if you can't see who read it.
5. **What can't you see?** Record gaps honestly, for example "data-access logging was not enabled on kanem-archive, so reads cannot be ruled out".

## Notification decisions

When personal data may have been accessed, the incident lead brings in the **data protection officer** and **legal** early. Under the Nigeria Data Protection Act 2023, notifiable personal data breaches must be reported to the Nigeria Data Protection Commission within a short deadline (72 hours of becoming aware), and affected people may need to be told. The DPO and lawyers decide whether the duty applies; your job is to give them accurate facts quickly: what data, how many records, which people, what is known and what is not.

:::note This is not legal advice
The rules and deadlines above are a pointer for the incident team. Kanem's DPO and lawyers make the decision, and they should check the current law and NDPC guidance.
:::

## Containing exposure

- Restore **block public access** and remove external shares; check that organisation policies enforce it (module 4).
- Rotate any credentials or keys that were stored in the exposed data.
- Turn on data-access logging for sensitive stores if it was off, so future questions can be answered.

:::takeaway
Answer "was data taken?" with data-access, key-usage and audit logs, combined with data classification. Count what was read, note what was exposed and for how long, record the gaps honestly, and involve the DPO and legal early so notification decisions are made on accurate facts.
:::
