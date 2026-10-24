:::scenario Lundi matin chez Kanem Financial Services
Le rapport de la nuit du pare-feu du siège montre 88 000 connexions vers la DMZ sur des ports que personne ne reconnaît. L’équipe réseau affirme : « le pare-feu est à états, donc tout va bien ». Avant de pouvoir juger cette affirmation, vous devez savoir précisément ce qu’un pare-feu vérifie, et ce qu’il ne peut pas voir.
:::

Un pare-feu applique une **politique qui détermine quelles conversations réseau sont autorisées entre deux endroits**. Chaque produit met en œuvre cette idée à sa façon, mais tous appartiennent à quelques familles. Savoir à quelle famille vous avez affaire vous dit contre quoi l’équipement peut et ne peut pas protéger.

## Les filtres de paquets sans état

Un filtre sans état examine chaque paquet isolément et compare les champs de l’en-tête à une liste de règles :

| Champ | Exemple |
| --- | --- |
| Adresses IP source et de destination | `10.20.4.21` → `10.10.1.5` |
| Protocole | TCP, UDP, ICMP |
| Ports source et de destination | `52114` → `443` |
| Interface ou sens | entrant sur l’interface WAN |

Les listes de contrôle d’accès (ACL) des routeurs et de nombreuses ACL réseau dans le cloud fonctionnent ainsi. Elles sont rapides et simples, mais elles n’ont aucune mémoire. Comme un paquet de réponse est jugé indépendamment de la requête qui l’a provoqué, vous devez écrire des règles pour **les deux sens**, généralement en autorisant une large plage de ports « éphémères » élevés pour le trafic de retour. Les jeux de règles sans état sont donc plus larges qu’ils n’en ont l’air.

## L’inspection avec état

Un pare-feu à états tient une **table des connexions**. Lorsqu’il autorise le premier paquet d’une conversation (pour TCP, le SYN), il enregistre la conversation et autorise automatiquement les réponses qui en font partie.

```text
Connection table on FW-HQ-01 (simplified)
proto  inside              outside             state         idle
tcp    10.20.4.21:52114    10.10.1.5:443       ESTABLISHED   00:00:03
udp    10.10.1.9:41003     198.51.100.53:53    REPLIED       00:00:01
tcp    10.20.7.44:50211    203.0.113.80:443    SYN_SENT      00:00:00
```

Deux conséquences pratiques :

- **Vous écrivez les règles dans le sens qui ouvre la conversation.** « Les utilisateurs peuvent naviguer sur Internet en TCP 443 » tient en une règle ; les réponses sont gérées par la table des connexions.
- **Les paquets non sollicités sont rejetés.** Un paquet qui prétend être une réponse mais ne correspond à aucune entrée de la table n’est pas laissé passer, ce qui déjoue toute une catégorie de paquets forgés que les filtres sans état laissent passer.

Les états de connexion TCP sont définis dans la RFC 9293, la spécification actuelle de TCP. UDP n’a pas de négociation : les pare-feu le suivent donc avec un délai d’expiration plutôt qu’avec de vrais états.

:::note Ce que « à états » ne veut pas dire
L’inspection avec état suit des *conversations*, pas du *contenu*. Un pare-feu à états qui autorise TCP 443 vers la DMZ laissera passer sans broncher une attaque contre l’application web sur ce port. C’est pourquoi l’affirmation du lundi matin n’a rien de rassurant : 88 000 connexions inattendues relèvent d’un problème de politique, quel que soit le type de pare-feu.
:::

## Les pare-feu applicatifs (« de nouvelle génération »)

Les pare-feu de nouvelle génération (NGFW) ajoutent des décisions au-delà du numéro de port :

- **Identification des applications** : reconnaître le protocole réellement utilisé, pour distinguer dans « TCP 443 » « l’application web bancaire » d’« un outil de tunnel inconnu sur le port 443 ».
- **Identité de l’utilisateur** : des règles écrites pour des groupes (« équipe des prêts ») plutôt que pour des plages d’IP, à l’aide d’un annuaire ou du VPN.
- **Prévention des menaces** : prévention des intrusions, filtrage d’URL et analyse antimalware intégrés.
- **Inspection TLS** : déchiffrer le trafic pour regarder à l’intérieur. C’est puissant mais sensible : il faut un certificat de confiance sur chaque équipement, cela casse certaines applications et soulève des questions de vie privée et juridiques (par exemple pour les sites bancaires ou de santé consultés par le personnel). Beaucoup d’organisations exemptent certaines catégories de trafic.

« Nouvelle génération » est une étiquette commerciale, pas une norme. Demandez quelles fonctions un produit possède, et lesquelles sont activées.

## Les autres emplacements des pare-feu

| Type | Où il s’exécute | Usage habituel |
| --- | --- | --- |
| Pare-feu réseau (périmétrique / interne) | Boîtier dédié ou appliance virtuelle entre les zones | Périphérie Internet, DMZ, segmentation du centre de données |
| Pare-feu d’hôte | Sur chaque serveur ou ordinateur portable (pare-feu Windows Defender, `nftables`) | Dernière ligne de défense ; bloque le déplacement latéral entre hôtes d’une même zone |
| Pare-feu applicatif web (WAF) | Devant les applications web | Inspecte les requêtes HTTP à la recherche d’attaques comme l’injection |
| Groupes de sécurité et ACL réseau cloud | Gérés par le fournisseur, rattachés aux ressources cloud | Filtrage avec état (groupes de sécurité) et sans état (ACL réseau) dans les réseaux cloud, traité dans le programme Sécurité du cloud |

La publication NIST SP 800-41 Rev. 1 décrit ces technologies et la façon de construire une politique de pare-feu. Publiée en 2009, elle est antérieure aux pare-feu cloud et aux fonctions actuelles des NGFW, mais ses conseils essentiels (décider d’abord de la politique, refuser par défaut, gérer le jeu de règles tout au long de sa vie) restent l’approche de référence.

## Démonstration : que voit chaque pare-feu ?

Un ordinateur portable du siège ouvre `https://portal.kanem.example`. Suivez la décision à chaque couche :

1. **ACL sans état sur le routeur de cœur** : voit `10.20.4.21:52114 → 10.10.1.5:443 TCP`. Il faut une règle à l’aller et une règle pour le trafic de retour vers les ports élevés.
2. **Pare-feu à états** : voit le SYN, applique la règle « utilisateurs → serveurs TCP 443 », crée une entrée dans la table. Les réponses passent automatiquement.
3. **NGFW avec identification des applications** : après quelques paquets, étiquette aussi le flux comme `ssl` / `kanem-portal` et peut appliquer une règle propre à cette application.
4. **WAF** : voit `GET /loans/apply` et le corps de la requête, et pourrait bloquer une tentative d’injection que tous les pare-feu précédents ont autorisée.

:::takeaway
Connaissez la famille de votre pare-feu. Les filtres sans état jugent des paquets isolés, les pare-feu à états jugent des conversations, les pare-feu applicatifs jugent aussi les applications et les utilisateurs, et aucun d’eux ne remplace la sécurisation de l’application elle-même.
:::
