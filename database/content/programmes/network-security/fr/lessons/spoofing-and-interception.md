De nombreux protocoles réseau ont été conçus pour des réseaux de confiance et croient ce qu’on leur dit. L’**usurpation** (*spoofing*) consiste à se faire passer pour une autre adresse, un autre équipement ou un autre service. Lorsqu’elle réussit, un trafic destiné à quelqu’un d’autre passe par l’usurpateur, ce que MITRE ATT&CK appelle *Adversary-in-the-Middle* (T1557, l’adversaire au milieu). Cette leçon explique où ce risque existe, comment le repérer et comment le prévenir.

## Où l’usurpation est possible

| Couche | Ce qui peut être usurpé | Qui pourrait le faire |
| --- | --- | --- |
| **Réseau local** | L’adresse matérielle de la passerelle par défaut (la résolution d’adresses sur un réseau local croit toute réponse) | Tout équipement du même VLAN |
| **Attribution d’adresses** | Le serveur DHCP, qui distribue une fausse passerelle ou un faux serveur DNS (module 7) | Tout équipement du même VLAN |
| **Résolution de noms** | Les réponses DNS, ou les mécanismes de résolution locale de repli utilisés par certains systèmes d’exploitation | Un équipement du réseau local, ou un résolveur compromis |
| **Sans-fil** | Un réseau Wi-Fi entier portant un nom familier | Toute personne à portée radio |
| **Internet** | L’adresse source des paquets, pour masquer leur provenance ou diriger les réponses vers une victime | N’importe qui, là où les réseaux ne filtrent pas les sources falsifiées |

Le point commun : le protocole accepte une affirmation (« je suis la passerelle », « voici l’adresse de la banque ») sans preuve.

## Quel peut être l’impact

- Le trafic entre les utilisateurs et les serveurs est lu ou modifié.
- Les utilisateurs sont envoyés vers le mauvais serveur.
- Des identifiants non chiffrés sont capturés.
- Des connexions sont perturbées.

Un chiffrement fort avec une vérification correcte des certificats limite l’essentiel de ces effets ; c’est pourquoi c’est la mesure la plus importante de cette leçon.

## Les signes à rechercher

| Signal | Où |
| --- | --- |
| L’adresse matérielle de la passerelle change soudainement, ou deux équipements revendiquent la même adresse IP | Journaux des commutateurs, alertes du système d’exploitation des hôtes, supervision réseau |
| Des réponses DHCP depuis des ports inattendus | Journaux des violations du DHCP snooping (module 7) |
| Des réponses de résolution d’adresses rejetées par l’inspection | Journaux de l’inspection ARP dynamique sur les commutateurs |
| Des alertes de certificat pour des sites internes ou bien connus | Signalements des utilisateurs, télémétrie des postes et des navigateurs |
| Un nom de Wi-Fi familier diffusé par un point d’accès inconnu | Détection des points d’accès non autorisés du contrôleur sans-fil |
| Des réponses DNS qui diffèrent des données faisant autorité, ou des échecs de validation | Journaux du résolveur, validation DNSSEC (module 7) |
| Du trafic arrivant d’Internet avec des adresses source internes ou réservées | Journaux du pare-feu périmétrique et des routeurs |

## La prévenir

| Mesure | Ce qu’elle empêche |
| --- | --- |
| **TLS partout, avec validation des certificats** (et HSTS pour les sites web) | La lecture ou la modification du trafic, même s’il est détourné ; il ne faut jamais apprendre aux utilisateurs à ignorer les alertes de certificat |
| **DHCP snooping** et **inspection ARP dynamique** sur les commutateurs d’accès | Les faux serveurs DHCP et les réponses de résolution d’adresses falsifiées |
| **802.1X / NAC** (module 3) | L’arrivée d’équipements inconnus sur le réseau local |
| **Sécurité des ports et VLAN de petite taille** | Limite le nombre d’équipements partageant un segment |
| **Désactiver les mécanismes de résolution de noms de repli hérités** sur les postes gérés | Les réponses de n’importe quel équipement du réseau local |
| **Validation DNSSEC et résolveurs internes** (module 7) | Les réponses DNS falsifiées pour les domaines signés |
| **WPA3- ou WPA2-Enterprise**, avec des clients qui valident le certificat du serveur (module 6) | Les clients qui rejoignent silencieusement un réseau imposteur |
| **Filtrage anti-usurpation** en périphérie : rejeter les paquets entrants aux sources internes ou réservées, et les paquets sortants aux sources qui ne vous appartiennent pas (BCP 38, RFC 2827) | L’entrée ou la sortie d’adresses source falsifiées dans votre réseau |

:::note Le chiffrement est le dernier rempart
Les fonctions des commutateurs empêchent l’usurpation sur le réseau local, mais le trafic traverse aussi des réseaux que vous ne maîtrisez pas. Le chiffrement avec vérification des certificats le protège partout : considérez-le comme indispensable, pas comme facultatif.
:::

:::takeaway
L’usurpation fonctionne parce que certains protocoles font confiance à des affirmations non vérifiées. Surveillez les changements d’adresse de la passerelle, les adresses en double, les rejets du snooping et de l’inspection, les alertes de certificat, les points d’accès non autorisés et les sources falsifiées. Prévenez-la avec TLS et la validation des certificats, le DHCP snooping et l’inspection ARP, la NAC, le Wi-Fi Enterprise, la validation DNSSEC, la suppression des protocoles hérités et les filtres anti-usurpation.
:::
