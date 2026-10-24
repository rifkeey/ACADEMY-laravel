IP achemine les paquets jusqu’au bon hôte. **TCP et UDP** acheminent les données jusqu’à la bonne *application* sur cet hôte, et c’est leur comportement que les pare-feu, les sondes IDS et les enquêteurs lisent le plus souvent.

## Les ports

Un **port** est un nombre de 16 bits (0 à 65535) qui identifie un point de terminaison applicatif. Une connexion est identifiée par cinq valeurs, le **quintuplet** (*5-tuple*) : protocole, IP source, port source, IP de destination, port de destination.

| Plage | Nom | Signification |
| --- | --- | --- |
| 0–1023 | Ports système (bien connus) | Services standard : 22 SSH, 53 DNS, 80 HTTP, 443 HTTPS |
| 1024–49151 | Ports utilisateur (enregistrés) | Applications enregistrées : 3389 RDP, 5432 PostgreSQL |
| 49152–65535 | Ports dynamiques / éphémères | Ports temporaires côté client choisis par le système d’exploitation |

Ces plages sont définies dans la RFC 6335, et l’IANA tient le registre officiel des ports attribués. Un numéro de port est une **convention, pas une preuve** : rien n’empêche un logiciel malveillant d’utiliser TCP 443 pour autre chose que du HTTPS, d’où l’existence de l’inspection applicative.

| Port | Service | Remarque de sécurité |
| --- | --- | --- |
| 22/tcp | SSH | Accès d’administration : à restreindre aux réseaux d’administration |
| 25/tcp | SMTP | Seuls les serveurs de messagerie doivent émettre sur ce port ; du 25 sortant depuis des ordinateurs portables évoque un logiciel malveillant d’envoi de spam |
| 53/udp, 53/tcp | DNS | Indispensable, et détourné pour des tunnels (module 7) |
| 80/tcp, 443/tcp | HTTP, HTTPS | L’essentiel du trafic, et c’est là que se cache la plupart des canaux de commande et contrôle |
| 445/tcp | SMB (partage de fichiers) | Ne doit jamais être exposé à Internet ; courant dans les déplacements latéraux |
| 3389/tcp | RDP | Souvent attaqué par force brute lorsqu’il est exposé |

## TCP : fiable et orienté connexion

TCP (RFC 9293) établit une connexion par une **négociation en trois temps** (*three-way handshake*), numérote chaque octet pour que les données perdues soient retransmises, et ferme explicitement la connexion.

```text
ordinateur 10.40.9.77:51544                     serveur 10.10.0.15:443
        │ ── SYN, seq=1000 ───────────────────────────► │   « je veux parler »
        │ ◄─────────────── SYN-ACK, seq=5000, ack=1001 ─ │   « d’accord, moi aussi »
        │ ── ACK, ack=5001 ────────────────────────────► │   connexion ESTABLISHED
        │ ══ données (TLS, HTTP) ═════════════════════ ═ │
        │ ── FIN ──► … ◄── FIN … ACK                     │   fermeture ordonnée
```

Les indicateurs (*flags*) à reconnaître dans les journaux et les captures :

| Indicateur | Signification | Ce que suggèrent des schémas inhabituels |
| --- | --- | --- |
| SYN | Ouvrir une connexion | De nombreux SYN vers de nombreux ports : **balayage de ports** ; des rafales de SYN : **inondation SYN** |
| SYN-ACK | Accepter | Un SYN-ACK prouve que le port est ouvert et en écoute |
| RST | Interrompre / refuser | Un SYN suivi d’un RST : port **fermé** ; les scanners en génèrent beaucoup |
| FIN | Fermeture ordonnée | |
| Aucune réponse au SYN | | Paquet **filtré** par un pare-feu (ou hôte éteint) |

Ces trois issues (SYN-ACK, RST, silence) sont exactement la façon dont les scanners classent les ports en ouverts, fermés ou filtrés, et la façon dont vous pouvez reconnaître un balayage dans une capture.

## UDP : rapide et sans connexion

UDP (RFC 768) se contente d’envoyer des datagrammes : pas de négociation, pas de garantie de livraison, pas d’ordre. Les requêtes DNS, DHCP, NTP, la voix sur IP et la vidéo l’utilisent. Conséquences pour la sécurité :

- Les pare-feu à états suivent UDP avec des **délais d’expiration**, puisqu’il n’y a pas de négociation à observer.
- Les adresses source UDP sont **faciles à usurper**, ce qui explique l’usage des services UDP dans les attaques par déni de service par **réflexion et amplification**.
- L’« absence de réponse » d’un port UDP ne prouve pas qu’il est fermé.

## Lire les connexions actives sur un hôte

```text
$ ss -tn            (Linux; on Windows: netstat -ano)
State   Local Address:Port     Peer Address:Port
ESTAB   10.40.9.77:51544       10.10.0.15:443
ESTAB   10.40.9.77:51602       203.0.113.80:443
SYN-SENT 10.40.9.77:51610      198.51.100.200:4444
```

*(Sortie fictive.)* La dernière ligne mérite un second regard : un ordinateur portable qui tente de joindre un hôte sur Internet en **TCP 4444**, un port sans aucun usage métier chez Kanem. Si un filtrage en sortie est en place (module 3), cela apparaît comme un refus journalisé ; sinon, la connexion peut réussir.

:::takeaway
Les ports identifient les applications par convention ; le quintuplet identifie une connexion. La négociation et les indicateurs de TCP vous disent si les ports sont ouverts, fermés ou filtrés et révèlent les balayages ; UDP fonctionne sans connexion et s’usurpe facilement. Les ports et les schémas inhabituels comptent parmi les tout premiers signes de problème.
:::
