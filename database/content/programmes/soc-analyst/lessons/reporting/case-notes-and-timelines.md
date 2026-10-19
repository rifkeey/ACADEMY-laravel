## If it is not written down, it did not happen

Case notes are the record of an investigation. They allow another analyst to take over at shift change, support legal or disciplinary processes, and feed lessons learned. Write them **as you work**, not from memory at the end.

## What good case notes contain

- **Header**: case ID, severity, status, owner, affected entities, time zone used.
- **Chronological log of analyst actions**: what you queried or did, when, and what you found.
- **Evidence register**: each artefact (exported logs, screenshots, file samples) with where it is stored, its hash where appropriate, and who collected it.
- **Current assessment**: a short paragraph that someone could read in 30 seconds.
- **Open questions and next steps**.

```text
CASE INC-4471 | Severity: High | Owner: A. Musa | Times: UTC
Assessment (updated 03:10): Password spray from 203.0.113.45 compromised f.manager.
  Attacker created an external forwarding rule and downloaded 312 Finance files.
  No other successful sign-ins from the source. Sessions revoked 02:58.
Open: Did the attacker register an MFA method? Were forwarded emails sent? Notify DPO?
```

## Evidence preservation

- Export the raw events that support each conclusion, not only screenshots. Screenshots are hard to search and easy to dispute.
- Record **hashes** (SHA-256) of exported files so that integrity can be demonstrated later.
- Store evidence where access is restricted and logged.
- Keep a **chain of custody** record when evidence may be used in legal proceedings: who handled it, when and why.

## Handover

Shift handover is where investigations stall. A handover note should let the next analyst continue within five minutes:

1. Current assessment in two sentences.
2. What is contained and what is not.
3. What is in progress (queries running, people contacted, approvals awaited).
4. The next three actions, in order.
5. Who to call if it gets worse.

## Writing style

- Use plain, precise language: "the account signed in from 203.0.113.45", not "the account was hacked".
- Distinguish **observed** facts from **assessed** conclusions: "We assess with moderate confidence that…".
- Avoid speculation about attribution unless you have strong evidence and it matters for the response.
- Never include passwords, full card numbers or unnecessary personal data in case notes.
