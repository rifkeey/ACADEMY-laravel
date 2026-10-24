La compétence pratique de ce module consiste à décider, à partir des métadonnées DNS, DHCP et web, si le comportement d’un hôte doit être escaladé. Cette leçon réunit les sources de preuves dans un seul processus de décision.

## Rapprocher les preuves

| Question | Source |
| --- | --- |
| Quel équipement avait cette adresse IP à ce moment-là ? | Baux DHCP (ou attributions d’adresses du VPN) |
| Qui l’utilisait ? | Journaux d’authentification du proxy, journaux de connexion |
| Quels noms a-t-il résolus ? | Journaux des résolveurs DNS |
| Où s’est-il connecté, et quel volume de données a circulé ? | Journaux du pare-feu et du proxy, enregistrements de flux |
| La destination est-elle connue et légitime ? | Propriétaire et ancienneté du domaine, services de réputation, connaissance interne |
| Est-ce normal pour cet équipement ? | Les mêmes journaux pour les jours précédents |

## Une grille d’escalade

Pondérez les observations, puis décidez :

| Observation | Poids |
| --- | --- |
| Le comportement est nouveau pour cet hôte | + |
| La destination est récemment enregistrée, rare ou inconnue de l’activité | + |
| Horaires réguliers, de type machine | + |
| Schémas DNS inhabituels (longs sous-domaines aléatoires, nombreuses résolutions en échec) | + |
| Le volume de données sortantes est inhabituel pour l’hôte | ++ |
| L’hôte est de haute valeur (serveur, poste d’administrateur, données restreintes) | ++ |
| Alerte corrélée provenant d’une autre source (poste, identité) | ++ |
| Explication bénigne claire et confirmée (responsable, ticket, produit connu) | −− |

| Résultat | Action |
| --- | --- |
| Explication bénigne confirmée | Clôturer avec la raison ; envisager un réglage |
| Un ou deux indicateurs faibles | Surveiller, revérifier plus tard, documenter |
| Plusieurs indicateurs, ou un indicateur fort sur un hôte de haute valeur | **Escalader** vers la gestion des incidents avec les preuves (module 9) |
| Incertain mais inquiétant | Escalader ; ne jamais clôturer comme « rien » sans raison |

## Rédiger l’escalade

Une bonne escalade permet à l’équipe de gestion des incidents d’agir immédiatement :

- **Quoi** : une phrase qui résume le comportement.
- **Qui** : hôte, utilisateur, zone, responsable.
- **Quand** : première et dernière observation (UTC).
- **Preuves** : les lignes de journal ou les décomptes précis, et leur provenance.
- **Pourquoi c’est important** : quels indicateurs, et pourquoi l’hôte est important.
- **Ce que vous avez vérifié et ce que vous n’avez pas vérifié**.
- **L’étape suivante recommandée**, par exemple isoler l’hôte après accord du responsable de l’incident.

:::takeaway
Rapprochez les preuves DHCP, DNS, proxy, pare-feu et identité pour savoir qui, quoi, où, et si c’est normal. Pondérez les indicateurs, donnez un poids important aux données qui quittent des hôtes de haute valeur et aux alertes corrélées, et escaladez avec un résumé clair et fondé sur les preuves dès que plusieurs indicateurs concordent.
:::
