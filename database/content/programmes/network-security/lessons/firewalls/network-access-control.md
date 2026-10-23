:::scenario A device nobody owns
During a branch visit, the Abuja IT officer finds a small mini-PC plugged into a spare network socket in the meeting room. It has an IP address in the staff range and has been talking to the internet for three weeks. The firewall policy is excellent, but the device was already *inside* the users zone.
:::

Firewalls control traffic **between** zones. **Network access control (NAC)** decides whether a device is allowed **into** a zone at all, and which one. Without it, anyone who can plug in a cable or guess a Wi-Fi password inherits all the trust given to that network segment.

## IEEE 802.1X: port-based access control

IEEE 802.1X (current edition 802.1X-2020) is the standard for authenticating devices before a switch port or Wi-Fi connection carries their traffic. It has three roles:

| Role | What it is | At Kanem |
| --- | --- | --- |
| **Supplicant** | Software on the device that proves its identity | The 802.1X client built into Windows and macOS |
| **Authenticator** | The switch or wireless access point that holds the port closed until authentication succeeds | Branch access switches and Wi-Fi access points |
| **Authentication server** | Decides whether the identity is valid and what access it gets, usually over RADIUS | A RADIUS server connected to the directory |

The flow, simplified:

```text
laptop (supplicant)        switch (authenticator)        RADIUS server
      | -- link up --------------> |                              |
      | <- "who are you?" (EAP) -- |                              |
      | -- credentials/cert (EAP) -> | -- forwards (RADIUS) -------> |
      |                            | <-- accept + VLAN 20 ------- |
      | <======= port opens, placed in VLAN 20 (users) ========> |
```

Until authentication succeeds, the port only carries authentication messages. If it fails, the port stays closed or the device lands in a restricted VLAN.

### Which credential?

- **Certificates (EAP-TLS)** are the strongest common choice: each managed device has its own certificate, nothing to phish or guess, and devices can be revoked individually. It needs a working certificate infrastructure.
- **Username and password inside a TLS tunnel (for example PEAP)** is easier to deploy but depends on password strength and on clients validating the server certificate. If clients don't validate it, attackers can set up a fake access point and harvest credentials.

## Devices that can't do 802.1X

Printers, cameras, card readers and older equipment often have no supplicant. The usual fallback is **MAC authentication bypass (MAB)**: the switch sends the device's MAC address to the RADIUS server as its identity. MAC addresses are easy to copy, so MAB devices should be placed in tightly restricted segments (printers only reach the print server) and monitored for unusual behaviour.

## Posture, quarantine and dynamic placement

NAC products often go beyond "is this identity valid?":

- **Posture checks**: is the antivirus running, is the disk encrypted, is the OS patched? Non-compliant devices go to a **quarantine VLAN** that can only reach remediation services.
- **Dynamic placement**: the RADIUS reply assigns the VLAN or access list, so a finance laptop and a printer plugged into the same socket end up in different zones.
- **Guest onboarding**: visitors register through a portal and are placed in the guest zone, which reaches only the internet.

## Where NAC fits

NAC is one layer, not the whole answer:

| Control | Question it answers |
| --- | --- |
| NAC (802.1X / MAB) | Is this device allowed on the network, and in which segment? |
| Firewall between zones | What may this segment talk to? |
| Host firewall and endpoint protection | What may this device accept and run? |
| Zero trust access (module 4) | Should this user, on this device, right now, reach this application? |

Zero Trust Architecture (NIST SP 800-207) moves further away from trusting network location at all. NAC is still useful in that world: it keeps unknown devices out and gives the access policy better information about each device.

:::reveal Back to the scenario: what would have stopped the mini-PC?
With 802.1X on the meeting-room socket, the mini-PC would have had no supplicant and no certificate, so the port would have stayed closed (or put it in a quarantine VLAN with no internet). Unused sockets should also be disabled on the switch. The firewall's egress rules would have been the second line of defence: three weeks of connections to an unknown internet host on an unusual port should have produced logged denies and an alert.
:::

:::takeaway
Firewalls decide what zones may say to each other; NAC decides who gets into a zone in the first place. Use 802.1X with certificates for managed devices, isolate devices that need MAB, and treat NAC as one layer in a defence-in-depth design.
:::
