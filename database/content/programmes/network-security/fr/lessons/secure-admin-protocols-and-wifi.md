Deux autres connexions du quotidien demandent une configuration sécurisée : les protocoles qu’utilisent les administrateurs pour gérer les systèmes, et le Wi-Fi qu’utilisent chaque jour le personnel et les invités.

## Les protocoles d’administration sécurisés

| Usage | À privilégier | À éviter |
| --- | --- | --- |
| Administration en ligne de commande | **SSH** (la RFC 4253 définit sa couche de transport) avec authentification par clé ou par certificat | Telnet, rlogin (identifiants en clair) |
| Administration web | **HTTPS** avec des certificats valides | Consoles en HTTP |
| Transfert de fichiers | **SFTP** ou **SCP** sur SSH ; HTTPS | FTP, TFTP pour tout ce qui est sensible |
| Supervision des équipements réseau | **SNMPv3** avec authentification et chiffrement | SNMPv1/v2c avec chaînes de communauté |
| Bureau à distance | RDP via une passerelle ou un bastion, avec authentification au niveau du réseau et MFA | RDP directement accessible depuis des réseaux non fiables |

Pratiques valables pour tous :

- **Comptes nominatifs** et authentification centralisée ; aucun identifiant d’administrateur partagé.
- **MFA** pour l’accès d’administration (leçon 5).
- **Zone d’administration uniquement** : ces services n’écoutent que sur les interfaces d’administration (module 4).
- **Gestion des clés SSH** : inventoriez les clés autorisées, supprimez celles des personnes qui sont parties, et préférez des certificats SSH de courte durée lorsque les outils le permettent.
- **Journalisation des sessions** privilégiées.

## La sécurité du Wi-Fi

La NIST SP 800-153 fournit des recommandations pour sécuriser les réseaux locaux sans fil, et la Wi-Fi Alliance définit la certification de sécurité actuelle, **WPA3**.

| Réseau | Approche recommandée |
| --- | --- |
| **Wi-Fi du personnel** | WPA3-Enterprise (ou WPA2-Enterprise si les équipements l’exigent) avec authentification 802.1X, idéalement par certificat (module 3 de Sécurité réseau), qui place les équipements dans le bon VLAN |
| **Wi-Fi invités** | Réseau distinct, isolé des zones internes, Internet uniquement, avec isolation des clients ; un portail d’enregistrement si vous devez identifier les utilisateurs |
| **Équipements qui ne gèrent que les clés prépartagées** | Un réseau distinct avec une phrase secrète robuste et unique, limité à ce dont ces équipements ont besoin |

Mesures complémentaires :

- **Surveillance sans fil** pour détecter les points d’accès non autorisés qui diffusent les noms de réseau de Kanem.
- **Trames de gestion protégées**, exigées par WPA3, pour rendre plus difficile la déconnexion des clients.
- Traitez le Wi-Fi comme toute autre zone : une politique de pare-feu entre le Wi-Fi et les réseaux internes.

:::takeaway
N’administrez les systèmes qu’avec des protocoles chiffrés et authentifiés (SSH, HTTPS, SFTP, SNMPv3), depuis la zone d’administration, avec des comptes nominatifs, la MFA et la journalisation. Faites fonctionner le Wi-Fi du personnel en WPA3- ou WPA2-Enterprise avec 802.1X, isolez complètement les invités, séparez les équipements à clé prépartagée et surveillez les points d’accès non autorisés.
:::
