:::scenario La refonte du centre de données
Kanem Financial Services reconstruit le réseau de son centre de données de Lagos. Aujourd’hui, le site web public, l’application bancaire centrale, ses bases de données, le commutateur de paiement par carte et les postes d’administration informatique se trouvent tous dans un seul grand VLAN de serveurs. Le DSI a une exigence : *« Si un système est compromis, il ne doit pas entraîner les autres avec lui. »* Ce module vous donne les outils de conception pour y répondre.
:::

Dans un **réseau à plat**, chaque équipement peut atteindre tous les autres. Un seul ordinateur portable ou serveur web compromis peut alors atteindre directement les bases de données, les systèmes de paiement et les interfaces d’administration. La **segmentation** divise le réseau en zones aux frontières contrôlées, ce qui limite le **rayon d’impact** (*blast radius*) d’une compromission.

## Zones et frontières de confiance

Une **zone** regroupe des systèmes de niveaux de confiance et d’exigences de sécurité similaires. Une **frontière de confiance** est tout point où le trafic passe d’une zone à une autre, et chaque frontière a besoin d’un point d’application (pare-feu, ACL, proxy) avec journalisation.

| Zone | Contenu | Politique habituelle |
| --- | --- | --- |
| Internet | Tout le monde | Non fiable ; en entrée, uniquement vers les services publiés |
| DMZ | Systèmes exposés à Internet (site web, relais de messagerie, passerelle d’API) | Accessible depuis Internet sur des ports précis ; accès très étroit vers l’intérieur |
| Utilisateurs | Ordinateurs portables et postes fixes du personnel | Accès web en sortie ; uniquement certaines applications internes |
| Application | Serveurs d’applications internes | Accessible depuis les utilisateurs et la DMZ uniquement sur les ports applicatifs |
| Base de données | Bases de données | Accessible **uniquement** depuis les serveurs d’applications qui les utilisent |
| Restreinte / haute valeur | Systèmes de paiement par carte, HSM, grand livre central | Le plus petit ensemble de flux possible, la supervision la plus forte |
| Administration | Postes d’administration, hôtes de rebond, supervision | La seule zone autorisée à administrer l’infrastructure |
| Invités / IoT | Visiteurs, caméras, systèmes du bâtiment | Internet uniquement, ou uniquement leurs propres contrôleurs |

## Nord-sud et est-ouest

- Le trafic **nord-sud** traverse le périmètre : internet ↔ DMZ, utilisateurs ↔ internet. Les pare-feu périmétriques traditionnels se concentrent ici.
- Le trafic **est-ouest** circule *à l’intérieur* de l’organisation : utilisateurs ↔ serveurs, serveur ↔ serveur. Dans les centres de données modernes, c’est l’essentiel du trafic, et c’est le chemin qu’empruntent les attaquants pour se déplacer latéralement après leur première prise de pied.

Une conception qui ne filtre que le nord-sud laisse l’intérieur à plat. Une bonne segmentation contrôle les deux.

## Les architectures de DMZ

Une **DMZ** héberge les systèmes qui doivent accepter des connexions venant de réseaux non fiables, afin qu’une compromission à cet endroit ne donne pas un accès direct à l’intérieur.

```text
Single firewall, three legs                 Two firewalls ("back-to-back")
internet ─┐                                  internet ── FW-A ── DMZ ── FW-B ── internal
          FW ── DMZ
internal ─┘                                  Different administrators or even vendors
                                             can run FW-A and FW-B
```

*À gauche : un seul pare-feu à trois pattes. À droite : deux pare-feu en série (« dos à dos »), que des administrateurs, voire des fabricants, différents peuvent gérer.*

Quelle que soit la topologie, les règles de la DMZ comptent davantage que sa forme :

- Internet → DMZ : uniquement les services publiés (par exemple TCP 443 vers le serveur web).
- DMZ → interne : uniquement les appels précis vers le back-end dont chaque système de la DMZ a besoin (le front-end web appelle l’API bancaire centrale sur un port, **jamais** directement la base de données).
- DMZ → internet : généralement aucune connexion initiée, ou uniquement des sources de mises à jour nommées via un proxy.
- Interne → DMZ : administration uniquement depuis la zone d’administration.

## La confiance n’est pas binaire

Les zones expriment une confiance **relative**, pas « l’intérieur est fiable, l’extérieur ne l’est pas ». Un ordinateur portable du personnel est moins fiable qu’un serveur de base de données, alors que tous deux sont « internes » : les ordinateurs portables naviguent sur le web, ouvrent des pièces jointes et voyagent. Concevez les frontières autour de **ce que fait chaque système et de ce qu’il détient**, pas autour de son emplacement physique.

:::tip Partir des données et des flux
Avant de dessiner les zones, listez les systèmes qui détiennent ou traitent les données les plus sensibles (dossiers clients, données de cartes, grand livre) et les **flux** dont chacun a besoin. Les zones en découlent naturellement : regroupez les systèmes qui ont la même exposition et les mêmes interlocuteurs, et placez les systèmes de haute valeur derrière le plus grand nombre de frontières.
:::

:::takeaway
La segmentation limite le rayon d’impact. Regroupez les systèmes en zones selon leur niveau de confiance et leur fonction, placez un point d’application avec journalisation à chaque frontière, contrôlez le trafic est-ouest autant que le nord-sud, et gardez les chemins de la DMZ vers l’intérieur aussi étroits que l’activité le permet.
:::
