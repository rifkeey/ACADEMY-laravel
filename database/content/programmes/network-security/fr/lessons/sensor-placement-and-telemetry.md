Un capteur ne peut détecter que ce qu’il voit. L’emplacement des capteurs, et la **télémétrie** que vous conservez en plus des alertes, déterminent ce que votre SOC peut détecter et sur quoi il peut enquêter.

## Amener le trafic jusqu’au capteur

| Méthode | Fonctionnement | Remarques |
| --- | --- | --- |
| **Port miroir (SPAN)** | Le commutateur copie le trafic de ports ou de VLAN choisis vers le port du capteur | Simple, mais le commutateur peut perdre des copies sous forte charge |
| **Tap réseau** | Un équipement matériel passif copie le trafic d’un lien | Fiable et complet ; nécessite une installation physique |
| **En coupure** | Le trafic traverse l’équipement (IPS) | Indispensable pour bloquer ; ne doit devenir ni un goulet d’étranglement ni un point de défaillance unique |
| **Mise en miroir du trafic cloud** | Des fonctions du fournisseur copient le trafic du réseau virtuel vers un capteur | Disponible chez les grands fournisseurs de cloud ; le coût augmente avec le volume |

## Où regarder

| Emplacement | Ce qu’il repère | Exemple chez Kanem |
| --- | --- | --- |
| **Périphérie Internet**, derrière le pare-feu | Les attaques entrantes que le pare-feu a laissées passer ; la commande et contrôle sortante | Entre FW-HQ-01 et les zones internes |
| **Devant la DMZ** | Les attaques contre les services publics | Devant le site web public |
| **Cœur du centre de données / entre les zones** | Les déplacements est-ouest vers les systèmes critiques | Entre les zones app, db et cards |
| **Point d’entrée de l’accès distant** | L’activité des utilisateurs du VPN | Après le concentrateur VPN |

La position est-ouest est souvent la plus négligée et la plus précieuse : un attaquant déjà entré se déplace **à l’intérieur**.

## La télémétrie au-delà des alertes

Les alertes vous disent que *quelque chose ne va peut-être pas*. Les enquêtes ont besoin de savoir *ce qui s’est réellement passé*. Conservez au minimum :

| Télémétrie | Ce qu’elle enregistre | Source habituelle |
| --- | --- | --- |
| **Enregistrements de connexions (flux)** | Qui a parlé à qui, quand, ports, octets, durée | NetFlow/IPFIX des routeurs, journaux des pare-feu, journaux de flux cloud, journaux de connexions Zeek |
| **Journaux de protocole** | Requêtes et réponses DNS, requêtes HTTP, noms de serveur et certificats TLS | Zeek, serveurs DNS, proxys |
| **Journaux d’alertes** | Les détections, avec leur contexte | IDS/IPS |
| **Capture complète des paquets** (de façon sélective) | Chaque octet | Courte durée de conservation, segments critiques uniquement, ou déclenchée par des alertes |

La capture complète des paquets coûte cher et soulève des questions de vie privée : la plupart des organisations conservent donc les **journaux de flux et de protocole pendant des semaines ou des mois**, et les **paquets pendant quelques jours ou uniquement à la demande**. Le module 8 travaille sur des captures de paquets dans Wireshark.

## Horodatage et contexte

Les preuves réseau ne sont utiles que si elles peuvent être **rapprochées** des autres preuves :

- **Une heure synchronisée** (NTP) sur chaque capteur et chaque équipement, journalisée en UTC.
- **Le contexte des actifs** : quelles adresses IP appartiennent à quel système et à quel responsable, d’après le plan d’adressage et les journaux DHCP (module 1).
- **Une collecte centralisée** dans un SIEM (module 9), pour qu’une alerte puisse être corrélée avec les journaux d’authentification et des postes.

:::takeaway
Placez les capteurs là où passe le trafic qui vous importe, en particulier entre les zones internes, en utilisant des taps ou des ports miroirs pour la détection et la coupure uniquement là où il faut bloquer. Conservez les journaux de flux et de protocole assez longtemps pour enquêter, les paquets de façon sélective, et assurez-vous que l’horodatage et le contexte des actifs permettent de rapprocher les preuves réseau de tout le reste.
:::
