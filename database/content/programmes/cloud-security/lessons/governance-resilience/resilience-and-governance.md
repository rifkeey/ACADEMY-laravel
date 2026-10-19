## Governing cloud use

Without governance, cloud adoption creates sprawl: accounts nobody owns, resources in unexpected regions and inconsistent controls. A **landing zone** provides a governed starting structure: an account or subscription hierarchy, central identity, central logging, network patterns and preventive guardrails applied to every new environment.

Key governance decisions:

- Which providers, regions and services are approved?
- Who may create accounts or subscriptions, and how are they tagged with owners and data classification?
- Which guardrails are mandatory everywhere?

## Cloud risk assessment

Assess cloud services with the same discipline as any risk (see the GRC programme), with extra questions: Which data will this hold? Which shared-responsibility items fall to us? What does the provider's independent assurance cover? What is our exit plan?

## Backup and recovery

Cloud services are highly available, but **availability is not backup**. Replication faithfully copies deletions, corruption and ransomware encryption. Protect against those with:

- backups stored in a **separate account** with separate credentials;
- **immutability** or retention locks so backups cannot be deleted early;
- regular, documented **restore tests**;
- defined **recovery time** and **recovery point** objectives per system.

## Resilience and continuity

Design critical systems across multiple availability zones, and decide deliberately whether they also need multi-region capability. Document and rehearse what happens if a region, an identity provider or the cloud account itself becomes unavailable.

## Compliance evidence

Cloud makes evidence easier to collect automatically: configuration snapshots, posture reports, audit logs and policy-as-code results. Map them to your control framework (for example NIST CSF 2.0 outcomes) so audits draw on continuous evidence rather than screenshots taken the week before.
