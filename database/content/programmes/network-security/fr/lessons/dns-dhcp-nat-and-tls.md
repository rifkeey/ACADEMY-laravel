Quatre services rendent possible presque toute connexion : **DHCP** donne son adresse à un équipement, **DNS** transforme les noms en adresses, la **NAT** permet aux adresses privées d’atteindre Internet, et **TLS** protège la conversation. Chacun est aussi une cible de choix, et chacun laisse des traces que vous utiliserez lors des investigations.

## DHCP : comment un équipement obtient sa configuration

Lorsque l’ordinateur portable du chargé de prêts rejoint le réseau, il diffuse une demande de configuration (RFC 2131). On retient souvent l’échange sous l’acronyme **DORA** :

```text
ordinateur ── Discover (diffusion : « un serveur DHCP ? ») ──────────►
           ◄── Offer   (« utilise 10.40.9.77/22, passerelle 10.40.8.1, DNS 10.10.0.53 »)
           ── Request (« je prends 10.40.9.77 ») ─────────────────►
           ◄── Ack     (« elle est à toi pour 8 heures »)
```

DHCP fournit l’adresse IP, le masque, la **passerelle par défaut** et les **serveurs DNS**. Cela le rend puissant : un **serveur DHCP non autorisé** (un routeur domestique branché par erreur, ou un attaquant) peut distribuer une passerelle ou un serveur DNS malveillant et détourner discrètement le trafic. Des fonctions des commutateurs comme le **DHCP snooping** n’autorisent les réponses DHCP que depuis des ports de confiance. Les journaux du serveur DHCP (quelle adresse MAC a reçu quelle IP, et quand) sont aussi une preuve clé pour relier une adresse IP vue dans un journal à un équipement physique.

## DNS : des noms aux adresses

Le DNS (RFC 1034) est une base de données distribuée. Une résolution de `corebanking.kanem.example` se déroule généralement ainsi :

```text
ordinateur ──► résolveur interne 10.10.0.53 (en cache ?) ──► racine ──► .example ──► serveur faisant autorité pour kanem.example
           ◄── 10.10.0.15 ◄──────────────────────────────────────────────────
```

| Type d’enregistrement | Rôle | Exemple |
| --- | --- | --- |
| A / AAAA | Nom → adresse IPv4 / IPv6 | `corebanking.kanem.example A 10.10.0.15` |
| CNAME | Alias vers un autre nom | `portal → corebanking` |
| MX | Serveurs de messagerie d’un domaine | `kanem.example MX mail.kanem.example` |
| TXT | Texte libre (sert aussi aux enregistrements de sécurité de la messagerie) | SPF, DKIM, DMARC |
| PTR | Adresse → nom (résolution inverse) | `15.0.10.10.in-addr.arpa` |

Intérêt pour la sécurité : presque toute connexion malveillante commence par une requête DNS ; les **journaux DNS comptent donc parmi les sources de données les plus riches** pour détecter les logiciels malveillants et l’hameçonnage. Le DNS est aussi détourné directement (données transportées dans des requêtes, domaines qui changent rapidement) et attaqué (réponses falsifiées, domaines détournés). Le module 7 approfondit le sujet.

## NAT et PAT : du privé au public

Les ordinateurs portables de Kanem utilisent des adresses privées RFC 1918, qu’Internet ne route pas. Le pare-feu de périphérie réalise une **NAT** (traduction d’adresses réseau, RFC 3022). Il s’agit presque toujours de **PAT** (traduction d’adresses et de ports, aussi appelée *NAT overload*) : de nombreux hôtes internes partagent une même adresse publique et sont distingués par le port.

```text
Edge firewall translation table (extract, synthetic)
Inside (private)        Outside (public)          Destination
10.40.9.77:51602    →   198.51.100.10:40211   →   203.0.113.80:443
10.40.9.81:52700    →   198.51.100.10:40212   →   203.0.113.80:443
10.20.4.21:50111    →   198.51.100.10:40213   →   192.0.2.44:443
```

Deux conséquences pour le travail de sécurité :

- **Un journal externe ne montre que l’adresse publique.** Si un site web signale un abus depuis `198.51.100.10` à 14:02, il vous faut les **journaux NAT** du pare-feu pour trouver de quel hôte interne il s’agissait. Pas de journaux NAT, pas de réponse.
- **La NAT n’est pas une mesure de sécurité.** Elle masque l’adressage interne et bloque les connexions entrantes non sollicitées par effet de bord de son fonctionnement, mais c’est la politique du pare-feu qui décide réellement de ce qui est autorisé.

## Les bases de HTTPS et TLS

HTTPS, c’est HTTP dans **TLS** (TLS 1.3 est la RFC 8446). TLS apporte :

- **La confidentialité** : le trafic est chiffré ; le réseau voit *que* vous avez communiqué, pas *ce que* vous avez dit.
- **L’intégrité** : toute altération est détectée.
- **L’authentification du serveur** : le serveur prouve son identité avec un **certificat** émis pour son nom ; le navigateur vérifie la chaîne de certificats, le nom et les dates de validité.

Ce qu’une sonde réseau peut encore voir dans TLS 1.3 sans déchiffrement : les adresses IP, les ports, le rythme et le volume, et généralement l’**indication du nom du serveur (SNI)** demandé par le client (`corebanking.kanem.example`). Encrypted Client Hello peut masquer jusqu’à cette information. C’est pourquoi le trafic chiffré déplace la détection vers les métadonnées, le DNS et la télémétrie des postes (module 5).

:::warning Les alertes de certificat sont des événements de sécurité
Apprendre aux utilisateurs à ignorer les alertes de certificat annule l’intérêt de TLS. Une alerte soudaine pour un site interne peut signifier un certificat expiré, ou une tentative d’interception. Les deux méritent un ticket.
:::

## Assembler le tout : une connexion, quatre services

1. L’ordinateur portable a obtenu `10.40.9.77/22`, la passerelle `10.40.8.1` et le DNS `10.10.0.53` par **DHCP**.
2. Il a demandé `corebanking.kanem.example` au **DNS** et obtenu `10.10.0.15`.
3. `10.10.0.15` n’est pas dans son /22 : il a donc envoyé le paquet à la **passerelle** (requête ARP pour `10.40.8.1`), et le routeur l’a acheminé par le VPN jusqu’à Lagos.
4. Il a réalisé une négociation **TCP** vers le port 443 et négocié **TLS**, en vérifiant le certificat du serveur.
5. Si la destination avait été sur Internet, le pare-feu de périphérie aurait appliqué la **PAT**.

:::takeaway
DHCP configure l’hôte, le DNS trouve le serveur, la NAT/PAT emmène le trafic privé vers Internet, et TLS protège la conversation. Chacun peut être détourné, et les journaux de chacun (baux DHCP, requêtes DNS, traductions NAT, métadonnées TLS) sont des preuves sur lesquelles vous vous appuierez.
:::
