La plupart des incidents liés aux pare-feu ne viennent pas d’attaquants habiles qui ont déjoué le pare-feu. Ils viennent d’un pare-feu qui fait exactement ce que disent ses règles, alors que les règles disent quelque chose que personne n’a voulu. Cette leçon vous apprend à lire un jeu de règles comme le pare-feu le lit.

## La première correspondance l’emporte

Presque tous les pare-feu réseau évaluent les règles **de haut en bas** et s’arrêtent à la **première règle qui correspond** à la connexion. L’action de cette règle (autoriser ou refuser) est définitive ; les règles suivantes ne sont jamais consultées pour cette connexion.

```text
#  Action  Source      Destination  Protocol  Port   Log
1  ALLOW   internet    dmz          tcp       443    yes
2  ALLOW   users       servers      tcp       443    yes
3  ALLOW   management  servers      tcp       22     yes
4  DENY    any         any          any       any    yes
```

Pour prévoir ce qu’il advient d’une connexion, parcourez la liste et arrêtez-vous à la première règle dont la source, la destination, le protocole **et** le port correspondent tous. Essayez :

| Connexion | Règle correspondante | Résultat |
| --- | --- | --- |
| internet → dmz, TCP 443 | 1 | Autorisée |
| users → servers, TCP 22 | 4 (les règles 1 à 3 ne correspondent pas) | Refusée |
| management → servers, TCP 22 | 3 | Autorisée |

## Le refus par défaut

La dernière ligne d’un jeu de règles solide est un **refus par défaut** : tout ce qui n’est pas explicitement autorisé est bloqué. Certains pare-feu ont un refus *implicite* à la fin ; d’autres exigent que vous l’écriviez. L’écrire explicitement présente deux avantages : quiconque lit la politique le voit, et vous pouvez activer sa **journalisation**, qui enregistre les balayages, les applications mal configurées et les tentatives d’atteindre ce qui ne devrait pas l’être.

Le refus par défaut n’est pas qu’une bonne habitude. La publication NIST SP 800-53 Rev. 5 l’inclut comme renforcement de la mesure de protection des frontières (SC-7(5), « refuser par défaut, autoriser par exception »), et la NIST SP 800-41 recommande de construire la politique de la même façon.

## Quand l’ordre des règles déraille

Les jeux de règles anciens développent quatre problèmes bien connus :

| Problème | Ce que cela signifie | Exemple |
| --- | --- | --- |
| **Masquage** (*shadowing*) | Une règle ne peut jamais correspondre, car une règle précédente correspond d’abord à tout son trafic | `ALLOW dmz → servers any` au-dessus de `DENY dmz → servers tcp 22` : le refus ne se déclenche jamais |
| **Redondance** | Une règle est entièrement couverte par une autre règle de même action : la supprimer ne change rien | `ALLOW users → servers tcp 443` puis, plus bas, `ALLOW users → servers tcp any` |
| **Corrélation (chevauchement)** | Deux règles se chevauchent en partie avec des actions différentes : le résultat dépend de leur ordre | `ALLOW users → any tcp 3389` et `DENY any → servers tcp 3389` |
| **Généralisation excessive** | Une règle large autorise bien plus que ce qu’exige son objet | `ALLOW any → dmz any any` ajoutée « temporairement » pour un prestataire |

Les règles masquées et trop générales sont dangereuses parce que le jeu de règles *paraît* plus sûr qu’il ne l’est. Un relecteur qui voit `DENY dmz → servers tcp 22` suppose que le SSH depuis la DMZ est bloqué. Le pare-feu n’est pas de cet avis.

:::tip Les compteurs de correspondances sont des preuves
La plupart des pare-feu comptent combien de connexions ont correspondu à chaque règle. Une règle de refus avec **zéro correspondance** sur 90 jours, placée sous une autorisation large, est un signe fort de masquage. Une règle « temporaire » avec **des milliers** de correspondances montre que quelque chose en dépend désormais : la supprimer demande un plan (leçon 4).
:::

## Démonstration : trouver la règle masquée

```text
#  Action  Source      Destination  Protocol  Port   Hits (90 days)
1  ALLOW   internet    dmz          tcp       443    2,914,005
2  ALLOW   dmz         servers      tcp       any    410,778     "Legacy batch job"
3  ALLOW   management  servers      tcp       22     5,312
4  DENY    dmz         servers      tcp       22     0           "Block SSH from DMZ"
5  DENY    any         any          any       any    61,442
```

1. La règle 4 refuse DMZ → serveurs en TCP 22.
2. Cherchez *au-dessus* une règle qui correspond au même trafic : la règle 2 autorise DMZ → serveurs sur **n’importe quel** port TCP, 22 compris.
3. Toute connexion SSH DMZ → serveurs correspond donc d’abord à la règle 2 et est **autorisée**. La règle 4 est masquée, ce que confirme son compteur à zéro.
4. La correction ne consiste pas à remonter la règle 4 en gardant la règle 2. Il faut découvrir ce dont la « Legacy batch job » (ancienne tâche par lots) a réellement besoin (par exemple TCP 5432 vers une seule base de données), remplacer la règle 2 par cette règle étroite et conserver le refus explicite.

:::reveal Pourquoi ne pas simplement remonter la règle 4 au-dessus de la règle 2 ?
La remonter bloquerait le SSH depuis la DMZ, mais la règle 2 permettrait toujours à la DMZ d’atteindre **tous les autres** ports TCP de tous les serveurs. Si le serveur web public de la DMZ est compromis (ATT&CK T1190, Exploit Public-Facing Application), la règle 2 offre à l’attaquant un chemin vers toute la zone des serveurs. Le vrai problème est la règle trop générale, pas seulement l’ordre.
:::

## Écrire des règles qui restent lisibles

- Placez les **règles précises avant les règles générales**, et regroupez les règles par couple de zones (toutes les règles dmz → servers ensemble).
- Donnez à chaque règle une **description, un responsable et une référence de ticket**. « Legacy batch job », sans responsable, est la raison pour laquelle la règle 2 a survécu des années.
- Préférez les **objets nommés** (`kanem-db-01`, `web-ports`) aux adresses brutes, pour qu’une modification se fasse en un seul endroit.
- Terminez par un refus par défaut explicite et **journalisé**.

:::takeaway
Lisez un jeu de règles comme le fait le pare-feu : de haut en bas, la première correspondance l’emporte. Les règles dangereuses sont les règles larges situées en haut, et les règles trompeuses sont les refus qui ne peuvent jamais se déclencher.
:::
