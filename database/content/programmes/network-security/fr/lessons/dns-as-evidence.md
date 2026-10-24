Les journaux DNS enregistrent quels noms chaque équipement a résolus. Comme la plupart des connexions commencent par une résolution, ils sont l’une des sources les plus utiles pour repérer une compromission, et ils restent utiles quand le trafic lui-même est chiffré.

## Ce que contiennent les journaux DNS

| Champ | Exemple |
| --- | --- |
| Heure | 2026-03-09T03:31:12Z |
| Client | 10.20.7.44 |
| Nom demandé | portal.kanem.example |
| Type de requête | A, AAAA, TXT, MX… |
| Réponse | 10.10.0.15, ou une erreur comme NXDOMAIN (le nom n’existe pas) |

## Les indicateurs qui méritent une enquête

Ces schémas sont des **raisons d’y regarder de plus près**, pas des preuves. Chacun a des explications bénignes.

| Indicateur | Pourquoi il peut compter | Causes bénignes fréquentes |
| --- | --- | --- |
| Résolutions de domaines **récemment enregistrés** ou **rares** | Les attaquants utilisent souvent une infrastructure toute neuve | Nouveaux partenaires, nouveaux outils SaaS |
| Résolutions **régulières, de type machine**, vers le même domaine | Contacts automatiques d’un logiciel indésirable | Vérificateurs de mises à jour, agents de supervision |
| Sous-domaines **inhabituellement longs ou d’apparence aléatoire**, ou volumes inhabituels de requêtes TXT vers un domaine | Des données peuvent être encodées dans le trafic DNS (DNS utilisé comme tunnel ; ATT&CK T1071.004 et T1572 décrivent ces comportements) | Certains services de sécurité et de diffusion de contenu utilisent des noms longs |
| Nombreuses réponses **NXDOMAIN** pour un même hôte | Un logiciel qui essaie de nombreux noms générés pour trouver son serveur (ATT&CK T1568, Dynamic Resolution) | Applications mal configurées, fautes de frappe dans des scripts |
| Hôtes qui interrogent **directement des résolveurs externes** | Contournement de la journalisation et du filtrage internes | Équipements mal configurés, applications personnelles |

## Transformer les indicateurs en décision

Posez les mêmes questions que pour toute alerte (module 5) :

1. **Quel hôte et quel utilisateur ?** Responsable de l’actif, zone, criticité.
2. **Que fait d’autre cet hôte ?** Journaux du pare-feu et du proxy sur la même période ; alertes des postes.
3. **Le domaine est-il connu ?** Propriétaire, ancienneté, réputation, et si des collègues le reconnaissent.
4. **Est-ce un comportement nouveau ?** Cet hôte l’a-t-il déjà fait ?
5. **Décidez** : bénin (documenter), suspect (escalader avec les preuves) ou incertain (escalader).

Un indicateur isolé justifie rarement une escalade. **Plusieurs indicateurs sur un même hôte** (de longs sous-domaines aléatoires, plus une régularité, plus un domaine récent, plus un trafic sortant inhabituel) la justifient généralement.

## Rendre la journalisation DNS utile

- Journalisez sur les **résolveurs internes**, y compris l’adresse du client et la réponse.
- Conservez les journaux assez longtemps pour remonter dans le temps lorsqu’un incident est découvert.
- Envoyez-les au SIEM et corrélez-les avec les baux DHCP (pour identifier l’équipement) et les journaux du pare-feu.
- Assurez-vous que les équipements **utilisent** réellement les résolveurs internes (leçon précédente), sinon les journaux seront incomplets.

:::takeaway
Les journaux DNS montrent ce que chaque équipement a résolu et restent utiles quand le trafic est chiffré. Considérez les domaines récents ou rares, la régularité de type machine, les longs sous-domaines aléatoires, les rafales de résolutions en échec et l’usage direct de résolveurs extérieurs comme des raisons d’enquêter ; escaladez quand plusieurs indicateurs concordent sur un même hôte.
:::
