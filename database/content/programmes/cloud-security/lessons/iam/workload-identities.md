:::scenario A key in the repository
A developer at Kanem finds this in the invoice application's configuration file, committed to the company Git repository eight months ago:

```text
AWS_ACCESS_KEY_ID=AKIA****************
AWS_SECRET_ACCESS_KEY=(40 characters)
```

(The values here are fake.) Anyone who has ever cloned the repository has a copy. How should the application have authenticated instead?
:::

Applications, virtual machines, containers, functions and pipelines need cloud permissions too. How they **prove who they are** matters as much as what they are allowed to do.

## The problem with stored secrets

A long-lived access key or client secret is a password for a machine:

- It works from **anywhere** on the internet, not just from the workload.
- It **doesn't expire** unless someone rotates it.
- It gets **copied**: into code, container images, CI variables, laptops and chat messages.
- It is **hard to attribute**: if three systems share one key, the logs cannot tell you which one acted.

Exposed keys are one of the most common starting points for cloud compromise, which is why provider guidance favours temporary credentials.

## Temporary credentials: the better default

Instead of storing a secret, the workload is given an **identity by the platform**, and the platform hands it **short-lived credentials** that rotate automatically:

| Provider | Mechanism | How the workload gets credentials |
| --- | --- | --- |
| AWS | **IAM role** attached to an EC2 instance profile, Lambda function, ECS task, etc. | The service obtains temporary credentials from AWS STS (Security Token Service) and refreshes them automatically |
| Azure | **Managed identity** (system-assigned, tied to one resource, or user-assigned, shareable) | The resource requests tokens from a local endpoint; no secret is stored |
| Google Cloud | **Service account** attached to the VM, function or workload | The metadata server supplies short-lived access tokens; Google recommends avoiding service-account keys where possible |

The code just calls the provider SDK, which finds the credentials automatically. There is nothing to commit, rotate or leak, and every action in the audit log carries the workload's own identity.

## Workloads outside the cloud: federation

Pipelines (GitHub Actions, GitLab CI), on-premises servers and workloads in another cloud can also avoid stored keys with **workload identity federation**: the external system presents a signed token from its own identity provider (for example an OpenID Connect token from the CI platform), and the cloud exchanges it for short-lived credentials, **if a trust policy says that exact repository, branch or workload is allowed**.

```text
CI job (repo kanem/invoice-app, branch main)
   └─ OIDC token signed by the CI platform
        └─ cloud checks the trust rule: issuer + repo + branch match? ✔
             └─ short-lived credentials for role "invoice-deployer" (1 hour)
```

The trust rule is the security control. "Any repository on the CI platform may assume this role" is as bad as a public key.

## Least privilege still applies

A role or managed identity with administrator rights is just a better-hidden problem. Give each workload **its own identity**, scoped to what that workload needs (lesson 3), so a compromise of one application does not hand over the others.

:::warning Instance metadata
Cloud VMs fetch their temporary credentials from a local metadata service. If an application on the VM can be tricked into making requests on an attacker's behalf (server-side request forgery), the attacker may be able to read those credentials. Providers have hardened this (for example, AWS IMDSv2 requires a session token). Use the hardened mode, and remember that a workload's permissions are what an attacker inherits if the workload is compromised.
:::

:::reveal Back to the scenario: what should Kanem do now?
1. **Treat the key as compromised**: deactivate it, then delete it once the application uses a role.
2. **Investigate** its use in the audit logs (CloudTrail) for the last eight months: what was called, from which IP addresses.
3. **Replace it** with an IAM role attached to the application's compute, scoped to the invoice needs.
4. **Remove it from Git history** is not enough on its own: clones and forks keep copies, which is why step 1 comes first.
5. **Prevent recurrence** with secret scanning in the repository and a policy that pipelines use federation.
:::

:::takeaway
Workloads should get identities from the platform, not stored secrets. Roles, managed identities and attached service accounts give short-lived, automatically rotated credentials; federation extends that to pipelines and external systems. Then apply least privilege to each workload identity.
:::
