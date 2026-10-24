Les zones sont une conception. Les **VLAN, sous-réseaux, pare-feu et mesures sur les hôtes** sont la façon de les construire. Cette leçon explique ce que chaque mécanisme applique réellement, car l’erreur de segmentation la plus fréquente consiste à croire qu’un VLAN est à lui seul une frontière de sécurité.

## Les VLAN séparent ; ils ne filtrent pas

Un VLAN (IEEE 802.1Q) divise un réseau commuté en domaines de diffusion de couche 2 distincts. Les équipements du VLAN 20 ne peuvent pas envoyer directement de trames aux équipements du VLAN 30.

Mais le trafic **entre** VLAN doit passer par un équipement de couche 3, et c’est cet équipement qui décide :

| Routage inter-VLAN sur… | Ce qui se passe |
| --- | --- |
| Un commutateur de cœur avec routage activé et sans ACL | Chaque VLAN peut atteindre tous les autres : le réseau est segmenté sur le papier et à plat en pratique |
| Un commutateur de cœur avec ACL | Un filtrage sans état élémentaire entre VLAN (module 3, leçon 1) |
| Un pare-feu | Une politique avec état, journalisée et vérifiable entre les zones : le choix habituel pour les frontières importantes |

Une conception de VLAN a donc besoin d’une **conception de l’application des règles** correspondante : quel équipement route entre ces VLAN, et quelle politique applique-t-il ?

## Faire correspondre VLAN, sous-réseaux et zones

Gardez une correspondance un pour un quand c’est possible. Cela rend les règles, les journaux et les enquêtes lisibles :

| Zone | VLAN | Sous-réseau | Passerelle / application |
| --- | --- | --- | --- |
| DMZ | 110 | 10.30.0.0/24 | Pare-feu FW-DC-01 |
| Application | 120 | 10.31.0.0/24 | Pare-feu FW-DC-01 |
| Base de données | 130 | 10.32.0.0/24 | Pare-feu FW-DC-01 |
| Cartes (restreinte) | 140 | 10.33.0.0/26 | Pare-feu FW-DC-01, section de règles distincte |
| Administration | 199 | 10.99.0.0/24 | Pare-feu FW-DC-01 |

Un analyste qui voit `10.32.0.14` dans un journal sait alors immédiatement qu’il s’agit d’une base de données.

## L’hygiène des commutateurs compte

Des erreurs de configuration de couche 2 peuvent laisser le trafic franchir des frontières de VLAN que la conception déclare séparées. Le durcissement standard, que couvrent aussi les CIS Benchmarks pour les équipements réseau :

- Désactivez la **négociation automatique des trunks** sur les ports d’accès ; configurez les trunks explicitement et uniquement là où ils sont nécessaires.
- N’autorisez sur chaque trunk **que les VLAN qu’il doit transporter**.
- Utilisez un **VLAN inutilisé comme VLAN natif** sur les trunks, et ne placez pas d’utilisateurs dans le VLAN 1.
- **Désactivez les ports inutilisés** et affectez-les à un VLAN isolé.
- Utilisez le **802.1X** (module 3) pour que les équipements arrivent automatiquement dans le bon VLAN.

## À l’intérieur d’une zone : la faille est-ouest

Les pare-feu entre les zones ne font rien pour le trafic **à l’intérieur** d’une zone. Si dix serveurs de bases de données partagent le VLAN 130, une base de données compromise peut communiquer librement avec les neuf autres. Les options pour combler cette faille :

| Mesure | Fonctionnement | Contrepartie |
| --- | --- | --- |
| **Pare-feu d’hôte** | Chaque serveur n’accepte que les connexions dont il a besoin (pare-feu Windows Defender, `nftables`) | Doit être géré de façon centralisée, sinon il dérive |
| **VLAN privés / isolation de ports** | Les hôtes d’un même VLAN atteignent la passerelle mais pas les autres hôtes | Adapté aux groupes d’hôtes similaires (par exemple des bornes) ; moins souple |
| **Segments plus petits** | Diviser la zone (par exemple un sous-réseau par application) | Davantage de règles à gérer |
| **Microsegmentation** (leçon suivante) | Une politique par charge de travail, indépendante de la topologie réseau | Exige un bon inventaire et de bons outils |

## Comment la segmentation est appliquée, de bout en bout

```text
laptop (VLAN 20)  ──►  core switch: routes to firewall only  ──►  FW-DC-01: policy users→application tcp 443
                                                                       │ logged allow
                                                                       ▼
                                                     app server (VLAN 120): host firewall accepts 443 only
```

*L’ordinateur portable passe par le commutateur de cœur, qui ne route que vers le pare-feu ; FW-DC-01 applique la règle users → application TCP 443 et journalise l’autorisation ; le pare-feu d’hôte du serveur d’applications n’accepte que le 443.*

Deux couches indépendantes (pare-feu réseau et pare-feu d’hôte) font qu’une seule erreur de configuration n’ouvre pas le chemin.

:::takeaway
Les VLAN créent des réseaux de couche 2 distincts ; c’est l’équipement qui route entre eux qui applique la sécurité. Faites passer les frontières importantes par un pare-feu, gardez VLAN, sous-réseaux et zones alignés, durcissez les commutateurs contre les fuites entre VLAN, et ajoutez des mesures sur les hôtes ou de la microsegmentation pour le trafic est-ouest à l’intérieur d’une zone.
:::
