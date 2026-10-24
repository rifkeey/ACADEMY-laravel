Le DHCP donne à chaque équipement son adresse, sa passerelle et ses serveurs DNS (module 1). Celui qui répond aux requêtes DHCP décide où les équipements envoient leur trafic : le DHCP doit donc être protégé, et ses journaux relient les adresses IP à des équipements réels.

## Les risques à maîtriser

| Risque | Effet |
| --- | --- |
| **Serveur DHCP non autorisé** (souvent un routeur domestique mal configuré branché sur le réseau) | Les équipements reçoivent une mauvaise passerelle ou un mauvais serveur DNS, ce qui provoque des pannes ou un trafic mal acheminé |
| **Épuisement des adresses** | Les équipements légitimes ne peuvent pas obtenir d’adresse |
| **Équipements inconnus** | Tout ce qui est branché reçoit automatiquement une configuration réseau |

## Les mesures

| Mesure | Comment elle aide |
| --- | --- |
| **DHCP snooping** sur les commutateurs | Seuls les ports reliés aux vrais serveurs DHCP peuvent envoyer des réponses DHCP ; les réponses venant des autres ports sont rejetées |
| **Contrôle d’accès au réseau** (module 3) | Les équipements doivent s’authentifier avant même d’accéder à un VLAN |
| **Conception des étendues** | Des étendues distinctes par VLAN, dimensionnées selon le besoin, avec des baux courts sur les réseaux invités |
| **Réservations** pour les serveurs et l’infrastructure | Des adresses prévisibles pour les systèmes importants |
| **Supervision** | Des alertes en cas d’épuisement d’une étendue et de réponses DHCP venant de sources inattendues |
| **Redondance** | Deux serveurs DHCP ou un basculement, pour que le service ne devienne pas un point de défaillance unique |

Le DHCP snooping sert aussi d’appui à d’autres protections des commutateurs (par exemple l’inspection ARP), car il construit une table indiquant quelle adresse a été attribuée à quel équipement sur quel port.

## Les journaux DHCP comme preuves

| Question | Ce que répondent les journaux DHCP |
| --- | --- |
| Quel équipement avait l’IP 10.20.7.44 à 03:30 le 9 mars ? | Le bail : adresse MAC, nom d’hôte, heure de début et de fin |
| Quand cet équipement inconnu est-il apparu pour la première fois ? | Son premier bail |
| Est-ce le même équipement qu’hier ? | La correspondance de l’adresse MAC et du nom d’hôte d’un bail à l’autre |

Comme les adresses changent de main, **une adresse IP dans un journal ne signifie rien sans l’heure** et l’historique des baux. Conservez les journaux DHCP aussi longtemps que les journaux qu’ils aident à expliquer (pare-feu, DNS, proxy).

:::takeaway
Protégez le DHCP par le snooping, le contrôle d’accès au réseau, des étendues bien conçues, des réservations, la supervision et la redondance, et conservez les journaux des baux : ce sont eux qui transforment une adresse IP à un instant donné en un équipement précis.
:::
