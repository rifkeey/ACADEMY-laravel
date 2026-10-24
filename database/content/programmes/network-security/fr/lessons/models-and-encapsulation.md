:::scenario Votre premier ticket
C’est votre première semaine dans l’équipe sécurité de Kanem Financial Services. Un ticket arrive de l’agence d’Abuja : *« Le chargé de prêts n’arrive pas à ouvrir l’application bancaire centrale. L’imprimante fonctionne. Internet est lent. »* Votre responsable vous dit : « Avant de toucher au moindre outil de sécurité, trouve où se situe le problème dans le réseau. » Ce module vous donne le modèle mental pour y parvenir, et c’est le même modèle qui vous servira à comprendre chaque attaque et chaque mesure de sécurité de ce cours.
:::

Chaque conversation sur un réseau, du chargement d’une page web au vol de données par un attaquant, est un empilement de protocoles qui ont chacun une tâche. Les professionnels de la sécurité utilisent des **modèles en couches** pour dire précisément *où* quelque chose se passe : « le pare-feu l’a bloqué en couche 4 », « l’attaque est en couche 7 », « c’est un problème ARP, couche 2 ».

## Deux modèles, une même idée

| Couche OSI | Nom | Modèle TCP/IP | Exemples de protocoles | Mesures de sécurité habituelles |
| --- | --- | --- | --- | --- |
| 7 | Application | Application | HTTP, DNS, SMTP, SSH | Pare-feu applicatif web, filtrage de la messagerie, authentification applicative |
| 6 | Présentation | Application | TLS (le chiffrement et l’encodage se situent ici sur le plan conceptuel) | Validation des certificats, configuration des suites de chiffrement |
| 5 | Session | Application | Gestion des sessions au sein des applications | Expiration des sessions, gestion des jetons |
| 4 | Transport | Transport | TCP, UDP | Règles de pare-feu par port, suivi des connexions |
| 3 | Réseau | Internet | IPv4, IPv6, ICMP | Contrôles de routage, ACL fondées sur les adresses IP, segmentation |
| 2 | Liaison de données | Accès réseau | Ethernet, Wi-Fi (802.11), ARP | 802.1X, sécurité des ports, VLAN |
| 1 | Physique | Accès réseau | Câbles, ondes radio | Locaux techniques fermés à clé, ports inutilisés désactivés |

Le **modèle OSI** (sept couches) est un outil pédagogique et un vocabulaire. Le **modèle TCP/IP** (quatre couches) est plus proche de la façon dont Internet est réellement construit. Vous entendrez les deux : « attaque en couche 7 » utilise la numérotation OSI, même si les protocoles sont ceux de TCP/IP.

:::note Pourquoi s’embarrasser de modèles ?
Ils rendent le dépannage et l’analyse des menaces méthodiques. Si un ordinateur portable n’a pas d’adresse IP, inutile de vérifier les règles de pare-feu (couche 4) pour l’instant. Si un pare-feu autorise TCP 443, il ne peut pas arrêter une attaque par injection qui se déroule dans HTTP, en couche 7.
:::

## L’encapsulation : comment une requête est emballée

Lorsque le navigateur du chargé de prêts envoie une requête, chaque couche enveloppe les données de la couche supérieure avec son propre **en-tête** :

```text
[ en-tête Ethernet | en-tête IP | en-tête TCP | enregistrement TLS | requête HTTP ] [fin de trame Ethernet]
   couche 2           couche 3     couche 4      « 6 »                couche 7
   prochain saut      hôte de      quelle        contenu              ce que veut
   sur ce lien        destination  application   chiffré              l’utilisateur
```

Valeurs concrètes pour cette requête :

| En-tête | Champs clés | Valeur |
| --- | --- | --- |
| Ethernet | MAC source / destination | ordinateur portable `3c:52:82:1a:9e:04` → routeur de l’agence `00:1b:21:aa:10:01` |
| IPv4 | IP source / destination, TTL | `10.40.9.77` → `10.10.0.15`, TTL 64 |
| TCP | port source / destination, indicateurs | `51544` → `443`, SYN |
| TLS | nom du serveur (SNI), version | `corebanking.kanem.example`, TLS 1.3 |

Deux détails comptent énormément pour la sécurité :

- **L’en-tête Ethernet change à chaque routeur traversé**, car il ne décrit que le lien suivant. Les adresses de l’en-tête IP restent normalement les mêmes de bout en bout (sauf si la NAT les réécrit, leçon 5).
- **Chaque équipement ne lit que les couches dont il a besoin.** Un commutateur lit la couche 2, un routeur la couche 3, un pare-feu à états les couches 3 et 4, un pare-feu applicatif ou un proxy jusqu’à la couche 7. Une mesure de sécurité ne peut imposer que ce qu’elle voit.

## Penser comme un défenseur (et comme un attaquant)

| Couche | Ce qui peut mal tourner | Exemple que vous rencontrerez dans ce cours |
| --- | --- | --- |
| 1–2 | Équipements non autorisés, empoisonnement ARP, saut de VLAN | Le mini-PC de la salle de réunion (module 3) |
| 3 | Usurpation d’adresse IP, routage vers la mauvaise destination, reconnaissance par balayage ping | Le balayage réseau du module 2 |
| 4 | Balayage de ports, inondations SYN, règles de ports trop larges | Les revues de pare-feu du module 3 |
| 7 | Hameçonnage, injection, tunnel DNS, détournement de protocoles autorisés | Les investigations DNS du module 7 |

:::reveal Retour au ticket : par où commenceriez-vous ?
L’imprimante fonctionne : la connexion réseau de l’ordinateur portable en couches 1 et 2 est donc probablement correcte, et il peut joindre au moins un équipement local. Le problème consiste à joindre un serveur situé dans un *autre* réseau, ce qui oriente vers la couche 3 : la configuration IP ou le routage. « Internet est lent » n’a peut-être aucun rapport. Vous résoudrez exactement ce ticket dans le lab du module.
:::

:::takeaway
Les modèles en couches vous donnent un vocabulaire précis et un ordre de dépannage. Chaque paquet est un empilement d’en-têtes ; chaque équipement et chaque mesure de sécurité agit sur des couches précises, et ne peut imposer que ce qu’il voit.
:::
