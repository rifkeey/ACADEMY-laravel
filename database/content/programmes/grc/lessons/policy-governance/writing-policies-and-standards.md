## Policies people can follow

A policy that is ignored is worse than no policy: it creates a false sense of control and, after an incident, evidence that the organisation knew what it should have done. Write for the people who must follow it.

## Structure of a security policy

| Section | Content |
| --- | --- |
| Purpose | Why the policy exists, in one or two sentences |
| Scope | Who and what it applies to: staff, contractors, systems, locations |
| Policy statements | The requirements, short and numbered |
| Roles and responsibilities | Who owns, approves, implements and monitors it |
| Compliance and exceptions | How compliance is measured; how to request an exception |
| Related documents | Standards and procedures that implement it |
| Document control | Version, owner, approver, approval date, next review |

## Clear requirement language

- Use **must** for mandatory requirements, **should** for recommendations, and **may** for permissions. Define these words once and use them consistently.
- One requirement per statement, so each can be tested.
- Say what must be achieved, not which product to use: "Remote access must require multi-factor authentication" stays true when you change VPN vendor.
- Avoid vague words that cannot be audited: "appropriate", "adequate", "where possible", unless a standard defines them.

| Weak | Strong |
| --- | --- |
| "Passwords should be strong where possible." | "User passwords must meet the Authentication Standard. Administrator accounts must use MFA." |
| "Backups are taken regularly." | "Critical systems must be backed up daily. Restores must be tested at least quarterly." |

## Policies, standards and procedures together

Keep the **policy** short and stable; it states intent and is approved by senior management. Move specific, technical, changeable requirements into **standards** approved at a lower level. Write **procedures** for the people doing the work. A typical small organisation might have one information security policy of four pages, ten topic standards (access control, cryptography, logging, backup, supplier security, and so on) and many procedures.

## Common policy set

Information security policy; acceptable use; access control; data classification and handling; cryptography; backup and recovery; logging and monitoring; incident response; supplier security; secure development (if you build software); business continuity; data protection and privacy.

## Making policies stick

- Involve the teams who must comply while drafting; they will find unworkable requirements early.
- Communicate changes: what changed, why, and what staff must do differently.
- Require acknowledgement for key policies, and test understanding through awareness training.
- Measure compliance with real data, not assumptions.
