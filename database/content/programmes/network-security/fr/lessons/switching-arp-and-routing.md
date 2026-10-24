Comment un paquet passe-t-il réellement d’un ordinateur portable à Abuja à un serveur du centre de données de Lagos ? Deux mécanismes font le travail : la **commutation** livre les trames au sein d’un réseau local, le **routage** déplace les paquets entre réseaux. Tous deux reposent sur une confiance que les attaquants adorent exploiter.

## Dans le réseau local : commutateurs et adresses MAC

Un commutateur apprend quelle **adresse MAC** (l’adresse matérielle de 48 bits, par exemple `3c:52:82:1a:9e:04`) est joignable sur quel port, et tient une **table d’adresses MAC** :

```text
Abuja branch switch SW-ABJ-01: MAC address table (extract)
VLAN  MAC address          Port     Type
20    3c52.821a.9e04       Gi1/0/14 dynamic   ← ordinateur portable du chargé de prêts
20    001b.21aa.1001       Gi1/0/48 dynamic   ← routeur de l’agence (passerelle)
30    a4bb.6d22.0f19       Gi1/0/22 dynamic   ← imprimante
```

Les trames destinées à une adresse MAC connue ne vont que vers le port correspondant ; les trames destinées à une adresse inconnue ou en diffusion sont envoyées sur tous les ports du VLAN. Les **VLAN** découpent un commutateur physique en réseaux de couche 2 distincts ; le trafic entre VLAN doit passer par un routeur ou un pare-feu, et c’est là que la segmentation (module 4) est appliquée.

## ARP : trouver l’adresse MAC d’une adresse IP

Un hôte connaît l’adresse *IP* de destination, mais il a besoin d’une adresse *MAC* pour construire la trame Ethernet. **ARP** (RFC 826) pose la question à tout le réseau local :

```text
Who has 10.40.8.1? Tell 10.40.9.77          (broadcast)
10.40.8.1 is at 00:1b:21:aa:10:01            (reply)
```

La réponse est conservée quelques minutes dans la **table ARP** de l’hôte. ARP n’a **aucune authentification** : n’importe quel équipement peut répondre, ou envoyer des réponses non sollicitées, en prétendant être la passerelle. C’est l’**usurpation ARP** ou **empoisonnement ARP** (*ARP spoofing / poisoning*), une méthode classique pour placer un attaquant au milieu du trafic local (module 2). Des fonctions des commutateurs comme l’inspection ARP dynamique existent pour la contrer.

IPv6 remplace ARP par la **découverte de voisins** (*Neighbor Discovery*, RFC 4861), qui a le même problème de confiance et ses propres protections.

## Sortir du réseau local : la passerelle par défaut

Pour chaque paquet, un hôte prend une décision clé :

> La destination est-elle **sur mon sous-réseau** ? Alors je la livre directement (requête ARP pour la destination). **Sinon**, je l’envoie à ma **passerelle par défaut** (requête ARP pour la passerelle).

L’hôte décide de « sur mon sous-réseau » à partir de **sa propre adresse IP et de son masque**. Si le masque est faux, la décision est fausse, et c’est exactement ce qui casse la connectivité dans le lab de ce module.

## Les routeurs et les tables de routage

Les routeurs acheminent les paquets entre réseaux à l’aide d’une **table de routage**. Pour chaque paquet, ils choisissent la route correspondante dont le **préfixe est le plus long** (la plus précise) :

```text
Branch router R-ABJ-01: routing table (extract)
Destination        Next hop         Interface    Notes
10.40.8.0/22       directly conn.   Gi0/1        Abuja staff
10.40.12.0/24      directly conn.   Gi0/2        Abuja printers
10.0.0.0/8         172.31.0.1       Tunnel1      VPN to Lagos data centre
0.0.0.0/0          198.51.100.1     Gi0/0        Internet (default route)
```

Un paquet pour `10.10.0.15` correspond à la fois à `10.0.0.0/8` et à la route par défaut `0.0.0.0/0` ; le /8 est plus précis, il passe donc par le tunnel VPN vers Lagos. Un paquet pour `203.0.113.80` ne correspond qu’à la route par défaut et part vers Internet.

À chaque saut, le routeur **décrémente le TTL** (*time to live*, durée de vie) et réécrit l’en-tête Ethernet pour le lien suivant. Lorsque le TTL atteint zéro, le paquet est rejeté et un message ICMP « time exceeded » est renvoyé : c’est ainsi que `traceroute` (`tracert` sous Windows) découvre le chemin :

```text
$ traceroute 10.10.0.15
 1  10.40.8.1      1.2 ms    (Abuja branch router)
 2  172.31.0.1    18.7 ms    (Lagos VPN concentrator)
 3  10.10.0.1     19.1 ms    (data-centre core)
 4  10.10.0.15    19.4 ms    (core banking server)
```

*(Sortie donnée à titre d’illustration : les valeurs sont fictives.)*

## Ce qu’il faut retenir des couches 2 et 3 pour la sécurité

| Mécanisme | Confiance intégrée | Risque | Mesure habituelle |
| --- | --- | --- | --- |
| Apprentissage des MAC par le commutateur | L’adresse MAC source de toute trame est crue | Inondation MAC, usurpation | Sécurité des ports, 802.1X |
| ARP / découverte de voisins | Toute réponse est crue | Homme du milieu sur le réseau local | Inspection ARP dynamique, RA guard, segmentation |
| Routage | Les routes des voisins sont crues | Trafic envoyé dans la mauvaise direction | Authentification du routage, gestion des changements |
| Passerelle par défaut | L’hôte fait confiance à sa configuration | Une mauvaise passerelle ou un mauvais masque coupe ou détourne le trafic | DHCP, surveillance, normes de configuration |

:::takeaway
Les commutateurs livrent au sein d’un VLAN grâce aux adresses MAC ; ARP associe les IP aux MAC sans aucune authentification ; les hôtes utilisent leur propre masque pour décider de passer ou non par la passerelle ; les routeurs choisissent le préfixe correspondant le plus long. La plupart des attaques en couches 2 et 3 exploitent l’une de ces hypothèses de confiance.
:::
