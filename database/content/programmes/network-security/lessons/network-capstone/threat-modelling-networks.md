:::scenario After the incident
After the overnight incident in module 9, Kanem's board has approved a network security programme. They want a **target architecture**, a risk register and a plan they can fund, not a list of firewall tweaks. In this capstone you produce them, using everything from modules 1–9.
:::

Threat modelling a network uses the same four questions as any system (the Threat Modeling Manifesto): what are we working on, what can go wrong, what will we do about it, and did we do a good job? For networks the "drawing" is the **zones, trust boundaries and flows**.

## Step 1: describe the network

| Element | For Kanem head office |
| --- | --- |
| **Zones** | Internet, DMZ, users, servers, management, guest Wi-Fi; branches (Abuja, Kano); remote users; cloud (the invoice VPC) |
| **Crown jewels** | Core banking servers and database, the finance file server, the management zone, identity services |
| **Trust boundaries** | Every place traffic crosses between zones, especially internet-to-DMZ, DMZ-to-servers, users-to-servers, anything-to-management, branch and remote links |
| **Flows** | Which zones must talk, on which ports, for which business purpose (module 4) |

## Step 2: what can go wrong?

Walk each boundary and ask, at a high level:

| Prompt | Kanem example |
| --- | --- |
| **Exposure**: what is reachable from a less trusted zone? | A temporary vendor rule exposing the DMZ (module 9) |
| **Paths to crown jewels**: if one host in a zone is compromised, what can it reach? | DMZ to every server port through rule 4 |
| **Identity at the boundary**: is access tied to a strong identity? | Remote access without device checks (module 6) |
| **Visibility**: would we see misuse at this boundary? | Allow rules without logging; DNS bypass (modules 3, 7) |
| **Availability**: is there a single point of failure? | One perimeter firewall, one internet link |
| **Management plane**: can device administration be reached from user zones? | Rule 11, users to firewall console (module 3) |

STRIDE (spoofing, tampering, repudiation, information disclosure, denial of service, elevation of privilege) is a useful checklist for each boundary as well.

## Step 3: decide what to do

For each threat, choose to **reduce** it (a control), **accept** it (with a named owner), **transfer** it (for example insurance or a managed service) or **avoid** it (stop doing the risky thing). Most network threats are reduced with controls from earlier modules: segmentation, least-privilege rules, strong remote access, monitoring.

## Step 4: check the model

Review the model with the people who run the network and the systems. Revisit it whenever the network changes significantly: a new branch, a cloud migration, a new remote-access method.

:::takeaway
Threat-model a network by mapping zones, crown jewels, trust boundaries and flows; ask about exposure, paths to crown jewels, identity, visibility, availability and the management plane at each boundary; choose to reduce, accept, transfer or avoid each threat; and review the model when the network changes.
:::
