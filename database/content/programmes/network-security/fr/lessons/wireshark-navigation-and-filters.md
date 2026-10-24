Wireshark est l’outil gratuit de référence pour lire les captures. Sa fenêtre comporte trois volets : la **liste des paquets** (une ligne par trame), le **détail des paquets** (chaque couche de protocole, dépliable) et les **octets du paquet** (en hexadécimal brut).

## Préparer l’affichage

Les noms de menus ci-dessous sont ceux de l’interface anglaise de Wireshark.

| Réglage | Comment | Pourquoi |
| --- | --- | --- |
| Format de l’heure | View → Time Display Format → **UTC Date and Time of Day**, ou **Seconds Since Previous Displayed Packet** | L’UTC correspond à vos journaux ; « depuis le précédent » montre les délais |
| Résolution de noms | View → Name Resolution : laissez la résolution des noms réseau **désactivée** | Résoudre les noms peut faire fuiter des requêtes et ralentit les gros fichiers |
| Colonnes | Clic droit sur un champ du détail des paquets → **Apply as Column** | Afficher ce qui compte pour le dossier, comme `tcp.stream` ou `dns.qry.name` |

## Les filtres d’affichage du quotidien

```text
ip.addr == 10.40.9.21                      vers ou depuis un hôte
ip.src == 10.40.9.21 && ip.dst == 10.10.0.15
tcp.port == 443                            un port, dans les deux sens
tcp.stream == 3                            une conversation TCP
dns                                        uniquement le DNS
dns.flags.rcode == 3                       DNS « nom inexistant » (NXDOMAIN)
tcp.flags.syn == 1 && tcp.flags.ack == 0   tentatives de connexion
tcp.flags.reset == 1                       réinitialisations
tcp.analysis.retransmission                segments retransmis
tcp.analysis.flags                         tout problème TCP repéré par Wireshark
tls.handshake.type == 1                    Client Hello TLS
```

Les filtres se combinent avec `&&` (et), `||` (ou) et `!` (non). La barre de filtre devient **verte** quand la syntaxe est valide et **rouge** quand elle ne l’est pas. La Wireshark Display Filter Reference liste tous les champs.

## Les outils qui font gagner le plus de temps

| Outil | Menu | Répond à |
| --- | --- | --- |
| **Conversations** | Statistics → Conversations | Qui a parlé à qui, combien de paquets et d’octets, pendant combien de temps |
| **Hiérarchie des protocoles** | Statistics → Protocol Hierarchy | Quels types de trafic contient le fichier |
| **Points de terminaison** | Statistics → Endpoints | Toutes les adresses vues |
| **Suivre le flux** | Clic droit → Follow → TCP Stream | Une conversation dans l’ordre (le contenu chiffré reste illisible) |
| **Informations expertes** | Analyze → Expert Information | Des avertissements comme les retransmissions, les réinitialisations et les paquets malformés |

## Une méthode pour n’importe quelle capture

1. **S’orienter** : Protocol Hierarchy et Conversations. Que contient le fichier, et entre qui ?
2. **Cibler** : filtrez sur l’hôte et l’heure de la plainte.
3. **Suivre** : choisissez le flux pertinent ; lisez-le du premier au dernier paquet.
4. **Mesurer** : utilisez « secondes depuis le précédent » pour trouver les délais.
5. **Expliquer** : rattachez ce que vous voyez à une cause (les deux leçons suivantes).
6. **Consigner** : notez au fil de l’eau les numéros de trame, les filtres et les heures (leçon 5).

:::takeaway
Réglez l’affichage de l’heure sur l’UTC ou sur « depuis le précédent », désactivez la résolution de noms, et utilisez les filtres d’affichage, Conversations, Follow Stream et Expert Information pour passer du fichier entier aux quelques paquets qui expliquent le problème.
:::
