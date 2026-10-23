:::scenario What is in the bucket?
Kanem's loans team keeps scanned loan applications in a cloud storage bucket called `kanem-loan-docs`. Nobody is sure how many files it holds, which contain national ID numbers and bank statements, or how long they should be kept. Before you can protect data, you need to know what it is and where it lives.
:::

Every data-protection decision (who may access it, whether it must be encrypted with customer-managed keys, how long to keep it, where it may be stored) depends on **how sensitive the data is**. Classification makes that explicit; discovery checks that reality matches.

## A simple classification scheme

Most organisations use three to five levels. Kanem's scheme:

| Level | Examples | Minimum controls |
| --- | --- | --- |
| **Public** | Published loan rates, marketing pages | Integrity (no unauthorised changes) |
| **Internal** | Policies, internal reports | Staff-only access |
| **Confidential** | Customer names and contact details, invoices | Named groups only, encryption, access logging |
| **Restricted** | National ID numbers, bank statements, card data, credentials | Smallest possible access, customer-managed keys where justified, private access paths only, strict retention, alerting on access anomalies |

Classification should be **simple enough that staff use it**. A scheme nobody can remember is applied inconsistently.

## Label where it matters

Put the classification where tools and people can see it:

- **Tags or labels on storage accounts, buckets and databases** (`data-classification = restricted`), so guardrails and reviews can act on them.
- **Separate stores for different levels.** Don't mix public website images and customer ID scans in one bucket: the access rules for the most sensitive item end up applying to everything, or the least restrictive rules leak the sensitive items.
- **Document owners**: each store has a business owner who decides who may access it and how long data is kept.

## Discovery: finding sensitive data you didn't know about

Sensitive data spreads: exports, test copies, spreadsheets, logs. Cloud providers offer services that scan storage for patterns such as identity numbers, financial account numbers and credentials:

| Provider | Service |
| --- | --- |
| AWS | Amazon Macie (for S3) |
| Microsoft | Microsoft Purview data discovery and classification |
| Google Cloud | Sensitive Data Protection |

Use them to **confirm** your classification and to **find surprises** (customer data in a "test" bucket). Pattern matching produces false positives and misses data in unusual formats, so treat findings as leads to review, not as final truth.

## Data minimisation and retention

The safest data is data you don't hold. Data-protection law, including the Nigeria Data Protection Act 2023, expects organisations to collect only what they need and keep it no longer than necessary. Practical steps:

- Define a **retention period** per data type with legal and compliance (for example, loan files for the period regulators require after the loan closes).
- Apply it automatically with **lifecycle rules** that delete or archive data at the end of the period.
- Remove **copies**: exports and test datasets are often the forgotten exposures.

:::takeaway
Classify data with a simple scheme, label stores and keep different levels apart, use discovery tools to check what is really there, and keep only what you need for as long as you need it. Every other control in this module is sized by the classification.
:::
