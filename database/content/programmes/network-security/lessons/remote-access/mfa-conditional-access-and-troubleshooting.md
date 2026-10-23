Remote access is only as strong as its authentication. This lesson covers MFA and conditional access, then a structured way to troubleshoot the remote-access failures every network team meets.

## Multi-factor authentication

MFA combines factors from different categories: something you know (password), something you have (a device or security key), something you are (biometric). NIST SP 800-63B, part of the Digital Identity Guidelines, describes authenticator types and assurance levels.

Not all MFA is equal:

| Method | Resistance to phishing |
| --- | --- |
| SMS or voice codes | Low: codes can be intercepted or phished |
| Authenticator app codes (time-based) | Moderate: codes can still be phished in real time |
| Push approvals | Moderate: vulnerable to users approving prompts they didn't start; number matching helps |
| **FIDO2 / WebAuthn security keys and platform authenticators**, smart cards | **High**: bound to the genuine site, so they resist phishing |

CISA's guidance recommends **phishing-resistant MFA**, starting with administrators and remote access.

## Conditional access

Conditional access policies decide **whether and how** a sign-in is allowed, using signals such as:

| Signal | Example policy |
| --- | --- |
| User and group | Administrators always need phishing-resistant MFA |
| Device | Only managed, compliant devices may reach the loans application |
| Location and network | Block sign-ins from countries where Kanem has no staff |
| Application sensitivity | Stronger requirements for core banking than for the staff handbook |
| Risk | Require re-authentication or block when the identity provider flags a risky sign-in |

Microsoft Entra Conditional Access is one example; other identity providers offer equivalents. This is the policy-engine idea from zero trust (module 4) applied to sign-in.

## Troubleshooting remote access

When a connection fails, work through the layers in order, and collect evidence at each step:

| Step | Question | Evidence |
| --- | --- | --- |
| 1. Reachability | Can the client reach the VPN or access gateway at all? | Client network status; gateway reachable on its port |
| 2. Authentication | Did the user or device authenticate? Which factor failed? | Identity provider sign-in logs; gateway authentication logs |
| 3. Policy | Did conditional access or device compliance block it? | Sign-in log result and policy name |
| 4. Tunnel / session | Did the tunnel or session establish? | Gateway logs: negotiation errors, mismatched settings |
| 5. Routing and access | Once connected, can traffic reach the resource? | Assigned address, routes, firewall logs for the destination |
| 6. Name resolution | Does the internal name resolve correctly? | DNS settings and lookups on the client |

Common root causes: an expired certificate (lesson 3), a changed password or MFA device, a device falling out of compliance, mismatched tunnel settings (lesson 2), overlapping address ranges between home and corporate networks, and missing routes or firewall rules for the VPN address pool.

:::tip Secure fixes only
Under pressure, it is tempting to "fix" remote access by relaxing security: disabling certificate checks, exempting a user from MFA, or widening a firewall rule for the whole VPN pool. Each of these trades a support ticket for a lasting weakness. Fix the root cause, and if a temporary exception is unavoidable, give it an owner and an expiry.
:::

:::takeaway
Use phishing-resistant MFA for administrators and remote access, and conditional access to decide sign-ins by user, device, location, application and risk. Troubleshoot remote access layer by layer with evidence, and fix root causes instead of weakening controls.
:::
