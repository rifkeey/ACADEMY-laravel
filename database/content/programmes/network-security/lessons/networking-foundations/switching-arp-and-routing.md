How does a packet actually get from a laptop in Abuja to a server in the Lagos data centre? Two mechanisms do the work: **switching** delivers frames inside a local network, **routing** moves packets between networks. Both rely on trust that attackers love to abuse.

## Inside the local network: switches and MAC addresses

A switch learns which **MAC address** (the 48-bit hardware address, e.g. `3c:52:82:1a:9e:04`) is reachable on which port, and keeps a **MAC address table**:

```text
Abuja branch switch SW-ABJ-01: MAC address table (extract)
VLAN  MAC address          Port     Type
20    3c52.821a.9e04       Gi1/0/14 dynamic   ← loans officer laptop
20    001b.21aa.1001       Gi1/0/48 dynamic   ← branch router (gateway)
30    a4bb.6d22.0f19       Gi1/0/22 dynamic   ← printer
```

Frames for a known MAC go only to that port; unknown or broadcast frames are flooded to every port in the VLAN. **VLANs** split one physical switch into separate layer-2 networks; traffic between VLANs must go through a router or firewall, which is where segmentation (module 4) is enforced.

## ARP: finding the MAC for an IP address

A host knows the destination *IP* address but needs a *MAC* address to build the Ethernet frame. **ARP** (RFC 826) asks the whole local network:

```text
Who has 10.40.8.1? Tell 10.40.9.77          (broadcast)
10.40.8.1 is at 00:1b:21:aa:10:01            (reply)
```

The answer is cached in the host's **ARP table** for a few minutes. ARP has **no authentication**: any device can reply, or send unsolicited replies, claiming to be the gateway. That is **ARP spoofing / poisoning**, a classic way to put an attacker in the middle of local traffic (module 2). Switch features such as dynamic ARP inspection exist to counter it.

IPv6 replaces ARP with **Neighbor Discovery** (RFC 4861), which has the same trust problem and its own protections.

## Leaving the local network: the default gateway

Every host makes one key decision for each packet:

> Is the destination **on my subnet**? Then deliver it directly (ARP for the destination). **Otherwise**, send it to my **default gateway** (ARP for the gateway).

The host decides "on my subnet" using **its own IP address and mask**. If the mask is wrong, that decision is wrong, which is exactly what breaks connectivity in this module's lab.

## Routers and routing tables

Routers forward packets between networks using a **routing table**. For each packet they pick the matching route with the **longest prefix** (the most specific one):

```text
Branch router R-ABJ-01: routing table (extract)
Destination        Next hop         Interface    Notes
10.40.8.0/22       directly conn.   Gi0/1        Abuja staff
10.40.12.0/24      directly conn.   Gi0/2        Abuja printers
10.0.0.0/8         172.31.0.1       Tunnel1      VPN to Lagos data centre
0.0.0.0/0          198.51.100.1     Gi0/0        Internet (default route)
```

A packet for `10.10.0.15` matches both `10.0.0.0/8` and the default route `0.0.0.0/0`; the /8 is more specific, so it goes over the VPN tunnel to Lagos. A packet for `203.0.113.80` matches only the default route and goes to the internet.

At each hop the router **decrements the TTL** (time to live) and rewrites the Ethernet header for the next link. When TTL reaches zero, the packet is dropped and an ICMP "time exceeded" message is sent back, which is how `traceroute` (`tracert` on Windows) discovers the path:

```text
$ traceroute 10.10.0.15
 1  10.40.8.1      1.2 ms    (Abuja branch router)
 2  172.31.0.1    18.7 ms    (Lagos VPN concentrator)
 3  10.10.0.1     19.1 ms    (data-centre core)
 4  10.10.0.15    19.4 ms    (core banking server)
```

*(Illustrative output: values are synthetic.)*

## Security takeaways from layers 2 and 3

| Mechanism | Built-in trust | Risk | Typical control |
| --- | --- | --- | --- |
| Switch MAC learning | Any frame's source MAC is believed | MAC flooding, spoofing | Port security, 802.1X |
| ARP / Neighbor Discovery | Any reply is believed | Man-in-the-middle on the LAN | Dynamic ARP inspection, RA guard, segmentation |
| Routing | Routes from neighbours are believed | Traffic sent the wrong way | Routing authentication, change control |
| Default gateway | The host trusts its configuration | Wrong gateway or mask breaks or diverts traffic | DHCP, monitoring, configuration standards |

:::takeaway
Switches deliver inside a VLAN using MAC addresses; ARP maps IPs to MACs with no authentication; hosts use their own mask to decide whether to use the gateway; routers pick the longest matching prefix. Most layer-2 and layer-3 attacks abuse one of these trust assumptions.
:::
