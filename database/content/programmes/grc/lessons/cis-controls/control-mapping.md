## The CIS Critical Security Controls

The CIS Controls are a prioritised set of safeguards maintained by the Center for Internet Security. Version 8.1 organises them into 18 Controls containing specific Safeguards, grouped into three **Implementation Groups** (IG1, IG2, IG3). IG1 is described as essential cyber hygiene, a realistic starting point for small organisations.

## Ownership and evidence

Every control needs:

- a **control objective** (what risk it reduces);
- an **owner** accountable for its operation;
- a description of **how it is implemented**;
- **evidence** that it operates, such as reports, configurations, logs or tickets.

A control without an owner tends to decay. A control without evidence cannot be demonstrated to an auditor or to yourself.

## Mapping across frameworks

Organisations rarely follow only one framework. A Nigerian bank might need to show alignment with a regulator's requirements, ISO/IEC 27001 for customers, and use CSF 2.0 to report to the board. Mapping lets one implemented control provide evidence for several requirements.

Rules for responsible mapping:

1. **Map purpose, not words.** Two requirements that both mention "logging" may demand very different things.
2. **State the relationship**: equivalent, subset, superset or related. Most mappings are partial.
3. **Avoid false equivalence.** "We meet CIS Control 6, therefore we meet the ISO access-control requirements" is rarely fully true.
4. **Use published mappings as a starting point.** NIST and CIS publish informative references and mappings; review them for your context.
5. **Record who made the mapping and when**, because frameworks change.

## Example

| Implemented control | CIS Controls v8.1 | CSF 2.0 | Relationship note |
| --- | --- | --- | --- |
| MFA for all remote access | Control 6 (Access Control Management) | PR.AA | Partial: CSF outcome also covers identity proofing |
| Asset inventory | Control 1 (Inventory of Enterprise Assets) | ID.AM | Closely related |
