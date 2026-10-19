## Defining the ISMS scope

Before choosing controls, an organisation must understand its **context**: its purpose, the internal and external issues that affect information security, and the **interested parties** (customers, regulators, staff, suppliers, shareholders) and their requirements. The scope follows from this.

A good scope statement identifies:

- the **business activities and services** covered (for example, "payroll processing services for corporate clients");
- the **locations** (offices, data centres, remote working);
- the **information, systems and teams** involved;
- the **interfaces and dependencies** with things outside scope, such as a parent company's IT department or a cloud host.

### Scope pitfalls

| Pitfall | Why it matters |
| --- | --- |
| Excluding the part that customers care about | A certificate covering head-office administration says nothing about the hosted payroll platform. |
| Ignoring interfaces | If an out-of-scope team administers in-scope servers, the risk still exists and must be managed. |
| Scope that does not match marketing claims | Telling customers "we are ISO certified" when only one site is in scope is misleading. |

## Risk assessment and treatment

Within scope, the organisation runs a defined information security risk assessment process and produces a **risk treatment plan**. Controls are selected to treat the risks. The organisation then compares its selected controls against the Annex A reference set to make sure nothing necessary has been overlooked. Annex A is a cross-check, not a shopping list.

## The Statement of Applicability

The SoA is the bridge between risk treatment and the controls an auditor will test. In our own words, for each control in the reference set it records:

- whether the control is **included** (applicable) or **excluded**;
- the **justification** for inclusion (usually the risks it treats, or a legal or contractual requirement);
- the **justification for exclusion**, which must be credible given the scope;
- the **implementation status** (implemented, partially implemented, planned).

Many organisations also add the control owner and a pointer to evidence, which makes audits faster.

### Reviewing an SoA

When you review an SoA, ask:

1. **Do exclusions make sense for this scope?** Excluding controls for secure development is reasonable for a firm that builds no software; it is not reasonable if the scope includes an in-house development team.
2. **Do justifications trace to risks?** "Best practice" is weak; "Treats R4 (supplier breach) and Contract C-12" is strong.
3. **Does status match evidence?** A control marked "implemented" with no evidence, or contradicted by interview notes, is a likely finding.
4. **Is it current?** Version, owner and approval date should be recent and consistent with the risk treatment plan.

Exclusions are not wrong in themselves. The problem is an exclusion that leaves a real risk untreated or contradicts the scope.
