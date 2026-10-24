## Le minimum réseau indispensable à tout analyste

Il n’est pas nécessaire d’être ingénieur réseau pour travailler dans un SOC, mais vous devez lire les preuves réseau avec aisance. Trois notions portent l’essentiel des investigations : l’adressage, les ports et protocoles de transport, et la résolution de noms.

### Les adresses

- **Plages IPv4 privées** (RFC 1918) : `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`. Le trafic entre ces adresses est interne.
- **Plages de documentation** que vous verrez dans les données de formation : `192.0.2.0/24`, `198.51.100.0/24`, `203.0.113.0/24`. Les labs AMANIQ les utilisent pour qu’aucune adresse d’une organisation réelle n’apparaisse dans un exercice.
- Un `/24` contient 256 adresses ; un `/16` en contient 65 536. Lorsqu’une alerte indique « balayage depuis 198.51.100.0/24 », elle décrit une plage, pas un seul hôte.

### Ports et protocoles de transport

TCP assure une livraison fiable et ordonnée et commence par une négociation en trois temps (`SYN`, `SYN-ACK`, `ACK`). UDP envoie des datagrammes sans négociation. Voici les ports courants à reconnaître sans les chercher :

| Port | Service | Intérêt pour l’analyste |
| --- | --- | --- |
| 22/tcp | SSH | Force brute, administration à distance |
| 53/udp, 53/tcp | DNS | Tunnel, résolutions de commande et contrôle |
| 80, 443/tcp | HTTP, HTTPS | Trafic web, la plupart des canaux C2 |
| 445/tcp | SMB | Déplacement latéral, partages de fichiers |
| 3389/tcp | RDP | Accès à distance, point d’entrée des rançongiciels |
| 88/tcp | Kerberos | Authentification sur le domaine |

Une tentative de connexion qui ne termine jamais la négociation (de nombreux paquets `SYN` sans `ACK`) signale souvent un balayage ou un rejet par un pare-feu.

### Le DNS

Le DNS traduit les noms en adresses. Chaque journal de requête enregistre **qui a demandé** (IP du client), **ce qui a été demandé** (nom et type de la requête, par exemple `A`, `AAAA`, `TXT`) et **la réponse**. Les journaux DNS sont précieux, car presque tout logiciel malveillant doit résoudre un nom avant de pouvoir communiquer avec son infrastructure.

Les schémas qui méritent une investigation :

- De nombreuses requêtes vers des sous-domaines longs et d’apparence aléatoire d’un même domaine (possible tunnel DNS).
- Un poste qui résout un domaine enregistré très récemment et qu’aucun autre poste ne contacte.
- Des requêtes régulières, à intervalles égaux (balisage, ou *beaconing*).
- Des requêtes d’enregistrements `TXT` émises par des postes de travail, inhabituelles pour une navigation ordinaire.

```text
2026-03-04T09:12:01Z client=10.20.4.17 query=a7f3k2.cdn-update-check.example type=TXT rcode=NOERROR
2026-03-04T09:13:01Z client=10.20.4.17 query=b91qz8.cdn-update-check.example type=TXT rcode=NOERROR
2026-03-04T09:14:01Z client=10.20.4.17 query=c02mm4.cdn-update-check.example type=TXT rcode=NOERROR
```

Trois faits ressortent : un seul hôte interne, un intervalle d’une minute, et des sous-domaines d’apparence aléatoire demandés en `TXT`. Pris isolément, chacun pourrait être anodin ; ensemble, ils justifient une investigation.
