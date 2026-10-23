A CI/CD pipeline (continuous integration and delivery) builds, tests and deploys code automatically. Kanem's pipeline can change production, so it is one of the most **privileged identities** in the organisation. Whoever controls the pipeline controls what runs in the cloud.

## Where pipelines go wrong

The OWASP Top 10 CI/CD Security Risks project lists the common problems. In plain terms:

| Risk | Example at Kanem | Fix |
| --- | --- | --- |
| **Long-lived cloud keys stored in the pipeline** | An access key with administrator rights saved as a repository secret years ago | Use **OIDC federation**: the pipeline exchanges a short-lived token for temporary credentials scoped to one role |
| **Over-privileged pipeline tokens** | Every job can write to the repository and packages | Set minimal permissions per workflow and job |
| **Unprotected branches** | Anyone can push straight to `main`, which deploys to production | Branch protection, required reviews, required status checks |
| **No approval before production** | Merges deploy immediately | Deployment environments with required reviewers for production |
| **Untrusted code running with secrets** | Pull requests from forks run jobs that can read deployment secrets | Never expose secrets to untrusted builds; separate build and deploy workflows |
| **Unpinned third-party actions** | `uses: some-org/deploy@main` pulls whatever is on that branch today | Pin actions to a full commit SHA; review and update deliberately |
| **Shared, persistent build machines** | Self-hosted runners reused across projects keep files between jobs | Ephemeral runners, isolated per project; patch the images |
| **Poor logging** | Nobody can tell who triggered a deployment | Keep pipeline logs and cloud audit logs; alert on deployments outside the pipeline (module 7) |

## Short-lived credentials with OIDC

The most valuable single change is usually removing stored cloud keys.

```text
1. The pipeline job asks its CI platform for an identity token
   (issuer, repository, branch, environment are inside the token).
2. The job presents the token to the cloud provider.
3. The cloud trust policy accepts it only for repository kanem/loans-api,
   branch main, environment production.
4. The provider returns temporary credentials for one deploy role,
   valid for about an hour.
```

Nothing long-lived is stored, and a token from another repository or branch is refused. This uses the workload-identity ideas from module 2.

## Separation of duties

| Control | Purpose |
| --- | --- |
| Required review by someone other than the author | One person cannot ship a change alone |
| Production environment approval | A named group approves releases |
| Pipeline definitions protected like code | Changes to the pipeline itself need review |
| Break-glass access logged and reviewed | Emergency fixes are possible but visible |

:::warning Pipeline changes are production changes
Editing the workflow file can change what is deployed or what secrets a job can reach. Protect workflow files with the same reviews as application code.
:::

:::takeaway
Treat the pipeline as a privileged identity: replace stored cloud keys with OIDC and short-lived credentials scoped to one repository, branch and environment; give jobs minimal permissions; protect branches and require approval for production; never run untrusted code with secrets; pin third-party actions; and log every deployment.
:::
