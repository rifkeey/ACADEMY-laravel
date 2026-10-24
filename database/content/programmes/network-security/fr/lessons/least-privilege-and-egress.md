Le « moindre privilège » paraît abstrait tant qu’on n’a pas à écrire la règle. Cette leçon transforme des besoins métier en règles de pare-feu qui autorisent exactement ce qui est nécessaire, dans les deux sens, et explique pourquoi le sens sortant compte autant que le sens entrant.

![Schéma du réseau du siège de Kanem : un pare-feu central FW-HQ-01 relie six zones : internet, dmz, users, guest, servers et management.](/content/network-security/kanem-hq-zones.svg)

*Siège de Kanem Financial Services (fictif). Tout le trafic entre les zones passe par FW-HQ-01, utilisé tout au long de ce module et de son lab.*

## Partir des besoins, pas du pare-feu

Une règle doit exister parce qu’un **besoin métier identifié** l’exige. Avant de toucher au pare-feu, consignez ce besoin comme une ligne d’une simple matrice des flux :

| N° | Qui (source) | Doit atteindre (destination) | Service | Pourquoi | Responsable |
| --- | --- | --- | --- | --- | --- |
| R1 | internet | serveur web de la dmz | TCP 443 | Site web public et formulaire de demande de prêt | Canaux numériques |
| R2 | users | servers (application bancaire centrale) | TCP 443 | Le personnel utilise l’application bancaire centrale | Opérations |
| R3 | serveur web de la dmz | servers (base de données) | TCP 5432 | Le site web lit les produits de prêt | Canaux numériques |
| R4 | management | servers | TCP 22 | Administration des serveurs | Infrastructure informatique |

Chaque ligne devient alors une règle, et chaque règle peut être rattachée à une ligne. Quand le besoin d’une ligne disparaît, sa règle disparaît aussi.

## Les quatre questions pour chaque règle d’autorisation

1. **Source** : est-ce le plus petit groupe de systèmes qui a besoin de l’accès ? Une zone, un sous-réseau, un groupe d’hôtes nommé, ou un groupe d’utilisateurs sur un pare-feu qui connaît les identités.
2. **Destination** : est-ce le système précis, et non « servers » ou « any » ?
3. **Service** : est-ce le protocole et le port précis, et non « any » ?
4. **Justification** : la règle a-t-elle un responsable, une raison et, pour une règle temporaire, une date d’expiration ?

Le mot `any` dans une règle d’autorisation est l’endroit où se cachent la plupart des permissions excessives. Il est parfois réellement justifié (le site web public accepte bel et bien des connexions de toute adresse Internet), mais il doit toujours être un choix délibéré.

:::warning Les règles « temporaires »
`ALLOW any → dmz any any — temp, vendor troubleshooting` (temporaire, dépannage prestataire) est l’un des constats les plus fréquents des revues de pare-feu. Elle a servi un après-midi et elle est toujours là deux ans plus tard, exposant à Internet tous les ports de tous les serveurs de la DMZ. Si vous devez ajouter une règle temporaire, donnez-lui une date d’expiration, un ticket et un responsable, et vérifiez qu’elle a bien été supprimée.
:::

## Le trafic entrant n’est que la moitié de la politique

Beaucoup d’organisations filtrent soigneusement le trafic entrant et autorisent presque tout en sortie (« les utilisateurs et les serveurs peuvent aller partout »). C’est un cadeau pour les attaquants, car les étapes qui suivent une prise de pied sont sortantes :

| Objectif de l’attaquant | Trafic habituel | ATT&CK |
| --- | --- | --- |
| Dialoguer avec un serveur de commande et contrôle | HTTPS, DNS ou ports inhabituels vers un serveur de l’attaquant | T1071 Application Layer Protocol |
| Cacher un canal dans un autre protocole | Tunnels DNS ou HTTPS | T1572 Protocol Tunneling |
| Voler des données | Envois via FTP, SMB, ou tout ce qui est autorisé | T1048 Exfiltration Over Alternative Protocol |

Le **filtrage en sortie** limite les zones autorisées à ouvrir des connexions sortantes, et les services qu’elles peuvent utiliser :

- **Les serveurs ont rarement besoin d’Internet.** Ils ont besoin du DNS (idéalement uniquement vers vos résolveurs internes, qui interrogent ensuite Internet) et d’un accès aux dépôts de correctifs, de préférence via un proxy. « Servers → internet any any » fait de chaque serveur compromis un point d’exfiltration parfait.
- **Les utilisateurs ont besoin d’un accès web** (TCP 443, éventuellement 80) via un proxy ou une passerelle web sécurisée, pas de ports arbitraires. Un ordinateur portable qui se connecte soudain en sortie sur TCP 4444 doit tomber sur un refus journalisé.
- **Les invités ont besoin d’Internet et de rien d’autre** : le Wi-Fi invités ne doit jamais atteindre les zones internes.
- **Le trafic d’administration part de la zone d’administration**, jamais l’inverse depuis les zones utilisateurs.

Les règles de sortie produisent aussi des preuves précieuses. Un refus journalisé pour `servers → internet TCP 8443` à 03:00 est souvent le premier signe d’une compromission.

## Démonstration : du besoin à la règle

Besoin : *le personnel du support informatique doit pouvoir prendre le contrôle à distance des ordinateurs portables des utilisateurs avec le Bureau à distance.*

| Projet | Problème |
| --- | --- |
| `ALLOW users → any tcp 3389` | Mauvais sens (le support se connecte *vers* les utilisateurs), et permet à chaque ordinateur portable d’ouvrir du RDP vers chaque serveur et vers Internet : un chemin idéal de déplacement latéral (ATT&CK T1021.001) |
| `ALLOW any → users tcp 3389` | Permet à n’importe quelle zone, y compris guest et la DMZ, d’atteindre le RDP des ordinateurs portables |
| `ALLOW management → users tcp 3389` | ✔ Uniquement la zone d’administration, uniquement le RDP, uniquement vers les utilisateurs |

Si le pare-feu connaît les identités, vous pourriez resserrer encore : uniquement les membres du groupe « IT Support », depuis les postes d’administration.

:::reveal Défi avancé : que demanderiez-vous avant d’écrire R3 ?
- Quel hôte *exact* est le serveur web, et lequel est la base de données ? Utilisez-les, pas des zones entières.
- Le serveur web a-t-il besoin d’écrire dans la base de données, ou seulement de la lire ? (C’est une permission de la base de données, pas une règle de pare-feu, mais la discussion doit avoir lieu.)
- La connexion doit-elle être chiffrée (TLS vers PostgreSQL) ?
- Que faut-il journaliser, et qui examine ces journaux ?
:::

:::takeaway
Chaque règle d’autorisation doit renvoyer à un besoin écrit, avec un responsable. Filtrez la sortie aussi soigneusement que l’entrée : limiter le trafic sortant est ce qui empêche un seul hôte compromis de devenir une violation de données.
:::
