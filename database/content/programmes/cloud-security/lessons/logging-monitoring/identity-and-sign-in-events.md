Module 2 showed that identity is the cloud's main perimeter. Identity logs are therefore some of the most important records you have: they show **who signed in, how, and from where**, and **who changed who can do what**.

## Sign-in logs

| Provider | Where to find sign-ins |
| --- | --- |
| Microsoft Entra ID | **Sign-in logs** (interactive, non-interactive, service principal and managed identity sign-ins) and **audit logs** (directory changes) |
| AWS | **CloudTrail** console sign-in events and IAM Identity Center events; role assumptions appear as STS events |
| Google Cloud | **Cloud Audit Logs** and Google Workspace / Cloud Identity login audit |

A sign-in record typically shows the user, time, result, source IP address and location, the application, the device and whether MFA was used.

## Identity events worth attention

These are signals to review, not proof of wrongdoing; each needs context.

| Signal | Why it matters |
| --- | --- |
| Sign-ins without MFA where MFA is expected | A policy gap or a bypass |
| Many failed sign-ins, across many accounts or for one account | Possible password guessing; check whether any then succeeded |
| Sign-ins from unusual locations, networks or devices for that user | Possible use of stolen credentials |
| Use of the root, Global Administrator or organisation administrator account | Should be rare and always explained |
| New credentials created (access keys, client secrets, service-account keys) | Persistence risk if unauthorised |
| Changes to permissions: new role assignments, policy attachments, changed trust relationships | Privilege changes are high-value events (module 2, lesson 5) |
| Changes to MFA or conditional-access policies | Weakening the controls themselves |

## Make the logs meaningful

- **One identity per person.** Shared accounts make sign-in logs useless for accountability.
- **Federate** human access (module 2) so sign-ins appear in one identity provider's logs.
- **Name workload identities clearly** (`invoice-app-prod`, not `svc1`) so an analyst knows what is normal for each.
- **Record the expected pattern**: which identities administer which accounts, from where, and when. Detections are only as good as the definition of normal.

:::takeaway
Sign-in and identity-change logs answer who got in, how and from where, and who changed permissions. Watch the signals that indicate misuse or weakened controls, give every person and workload a clear, individual identity, and record what normal looks like so deviations stand out.
:::
