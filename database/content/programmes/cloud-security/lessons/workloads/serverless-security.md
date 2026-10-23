Serverless functions (AWS Lambda, Azure Functions, Google Cloud Run functions) remove servers from your responsibilities entirely: no OS to patch, no instances to harden. What remains is concentrated in a few places, and mistakes there matter more.

## What you still own

AWS's Lambda security documentation, like the other providers', makes the split clear: the provider secures the infrastructure and runtime environment; you secure your **code**, its **dependencies**, its **configuration** and its **permissions**.

| Area | Your controls |
| --- | --- |
| **Permissions** | One execution role or identity per function, with only the permissions that function needs. A single shared, broad role for every function is the most common serverless finding |
| **Triggers and exposure** | Know what can invoke each function (an API gateway, a queue, a storage event). Public HTTP endpoints need authentication and input validation |
| **Code and dependencies** | Secure coding, dependency scanning, keeping libraries current |
| **Secrets** | From a secrets manager, not hard-coded or in plain configuration (module 4) |
| **Runtime versions** | Move off runtimes the provider has deprecated |
| **Input validation** | Every event is untrusted input, including events from storage or queues |
| **Logging** | Function logs and invocation metrics to the central platform |
| **Limits** | Timeouts, concurrency limits and budget alerts, so runaway or abused functions can't create unbounded cost |

## Why least privilege matters even more here

A function is small and does one thing, so it is usually **easy** to give it exactly the permissions it needs. Because functions are so numerous, though, teams are tempted to reuse one broad role. If any one function is then compromised (for example through a vulnerable dependency), the attacker inherits permissions intended for all of them.

## Event-driven means many entry points

In a serverless application, "the perimeter" is the set of triggers: API routes, queues, storage events, schedules. Review them as you would review firewall rules:

| Trigger | Question |
| --- | --- |
| Public API route | Is authentication required? Is input validated? Is there rate limiting? |
| Storage upload event | Can untrusted users upload files that trigger processing? How are files checked? |
| Queue message | Who can send messages to the queue? |
| Schedule | Does it need permissions beyond its task? |

:::takeaway
Serverless removes infrastructure work but concentrates responsibility in code, dependencies, configuration and permissions. Give each function its own least-privilege identity, review every trigger as an entry point, keep secrets and runtimes managed, validate all input and set limits on cost and concurrency.
:::
