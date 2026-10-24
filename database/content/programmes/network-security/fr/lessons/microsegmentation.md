La segmentation traditionnelle trace des frontières autour des **réseaux**. La **microsegmentation** les trace autour des **charges de travail** : chaque serveur, machine virtuelle ou conteneur reçoit sa propre politique, où qu’il s’exécute et quelle que soit son adresse IP.

## Pourquoi aller plus finement ?

| Zones larges | Microsegmentation |
| --- | --- |
| « La zone application peut atteindre la zone base de données sur le 1433 » | « Les serveurs de l’**API des prêts** peuvent atteindre la **base de données des prêts** sur le 1433 ; rien d’autre ne le peut » |
| Un serveur d’applications compromis peut atteindre toutes les bases de données de la zone | Un serveur d’applications compromis n’atteint que sa propre base de données |
| Les règles suivent des plages d’IP | Les règles suivent des **étiquettes** (application, environnement, rôle) |
| Modifier le réseau modifie la politique | Les charges de travail conservent leur politique quand elles sont déplacées |

Elle est particulièrement utile dans les centres de données virtualisés et dans le cloud, où les charges de travail sont créées et déplacées en permanence et où l’essentiel du trafic est est-ouest.

## Comment la mettre en œuvre

| Approche | Où la politique est appliquée | Exemples |
| --- | --- | --- |
| **Agents sur les hôtes** | Sur le pare-feu propre à chaque charge de travail, géré de façon centralisée | Plateformes de protection des postes et des charges de travail, pare-feu d’hôte gérés de façon centralisée |
| **Hyperviseur / réseau défini par logiciel** | Dans le commutateur virtuel de chaque hôte | Pare-feu distribués des plateformes de virtualisation |
| **Mesures natives du cloud** | Dans la couche réseau du fournisseur de cloud | Groupes de sécurité et groupes de sécurité réseau rattachés à chaque charge de travail (programme Sécurité du cloud, module 3) |
| **Maillage de services / fondé sur l’identité** | Entre les services, avec des identités de charges de travail et du TLS mutuel | Maillages de services Kubernetes |

Toutes expriment les règles en fonction de **ce qu’est une charge de travail** (étiquettes ou identité) plutôt que de **l’endroit où elle se trouve** (adresse IP), ce qui constitue un pas vers le zero trust.

## Un déploiement pragmatique

La microsegmentation échoue quand on l’active avant de savoir quels flux sont légitimes. Une séquence plus sûre :

1. **Inventoriez et étiquetez** les charges de travail : application, environnement (prod/test), niveau (web/app/db), sensibilité des données.
2. **Observez** le trafic pendant quelques semaines (journaux de flux, visibilité des agents) et construisez une carte de qui parle à qui.
3. **Écrivez la politique à partir de la carte**, revoyez-la avec les responsables des applications et supprimez les flux que personne ne peut justifier.
4. **Appliquez d’abord en mode surveillance** : journalisez ce qui serait bloqué.
5. **Appliquez réellement**, en commençant par les applications de plus haute valeur (paiements par carte, grand livre central).
6. **Tenez-la à jour** : les nouvelles applications ont besoin d’étiquettes et d’une politique avant leur mise en service.

:::warning Ne cassez pas l’activité
Les applications ont souvent des dépendances que personne n’a documentées : une tâche par lots nocturne, un serveur de licences, une sonde de supervision. Observer avant d’appliquer, et appliquer par étapes, c’est ce qui distingue un projet de microsegmentation réussi d’une panne.
:::

## Démonstration : l’application de paiement par carte

| Flux | Autorisé ? | Pourquoi |
| --- | --- | --- |
| Application bancaire centrale → API du commutateur de cartes, TCP 9443 | ✔ | Flux de paiement documenté |
| Commutateur de cartes → processeur de cartes (Internet), TCP 443 | ✔ | Nécessaire pour autoriser les paiements |
| Commutateur de cartes ↔ autres nœuds du commutateur, port du cluster | ✔ | Réplication du cluster |
| Tout ordinateur portable utilisateur → commutateur de cartes | ✘ | Aucun besoin métier |
| Autres serveurs d’applications → commutateur de cartes | ✘ | Seule l’application bancaire centrale l’appelle |
| Commutateur de cartes → Internet, toute autre destination | ✘ | Limite l’exfiltration |
| Hôte de rebond d’administration → commutateur de cartes, SSH | ✔ | Administration, journalisée |

Sept lignes, chacune avec un responsable et une raison. Ce tableau est la politique, quel que soit le produit qui l’applique.

:::takeaway
La microsegmentation applique une politique par charge de travail, avec des étiquettes ou des identités au lieu d’adresses IP, et comble la faille est-ouest à l’intérieur des zones. Inventoriez et observez avant d’appliquer, commencez par les systèmes de plus grande valeur, et considérez le tableau des flux comme la politique.
:::
