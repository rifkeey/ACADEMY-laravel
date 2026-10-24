## La chasse part du principe que les détections sont incomplètes

La chasse aux menaces (*threat hunting*) est la recherche proactive d’activités malveillantes que les détections existantes n’ont pas signalées. Elle part de l’hypothèse qu’un adversaire compétent est peut-être déjà présent et qu’il a échappé à vos alertes.

Chasser, ce n’est pas « parcourir les journaux jusqu’à ce que quelque chose paraisse bizarre ». Une chasse efficace est structurée.

## La boucle de chasse

1. **Hypothèse** : un énoncé vérifiable. *« Si un attaquant utilise un tunnel DNS pour la commande et le contrôle, un poste interne émettra un nombre anormalement élevé de requêtes vers des sous-domaines uniques d’un même domaine parent. »*
2. **Données** : quelle télémétrie permettrait de la confirmer ou de l’infirmer ? (Les journaux de requêtes DNS avec l’IP du client et le nom demandé.)
3. **Analyse** : requêtes, statistiques et visualisations pour trouver des candidats.
4. **Investigation** : triez chaque candidat comme vous le feriez pour une alerte.
5. **Résultat** : les constatations et, tout aussi important, des **détections nouvelles ou améliorées**, les lacunes de collecte de données et des références documentées.

## D’où viennent les hypothèses

- Du renseignement sur les techniques employées contre votre secteur.
- Des techniques ATT&CK **sans** couverture de détection dans votre environnement.
- Des incidents récents survenus ailleurs dans votre organisation.
- Des changements dans votre environnement (un nouveau VPN, une migration vers le cloud, une fusion).

## Exemples de chasses

**Le balisage.** Les implants de commande et contrôle contactent souvent leur serveur à intervalles réguliers. Regroupez les connexions sortantes par poste et par destination, puis recherchez une faible variation du temps écoulé entre deux connexions :

```kql
DnsEvents
| summarize Queries = count(), Subdomains = dcount(Name) by ClientIP, ParentDomain
| where Subdomains > 50
| order by Subdomains desc
```

**Les couples parent-enfant rares.** Comptez combien de fois chaque couple processus parent/processus enfant apparaît sur l’ensemble des postes sur 30 jours. Les couples observés sur un ou deux postes seulement méritent un examen.

**Les comportements inédits.** Un utilisateur qui n’a jamais utilisé PowerShell exécute soudain des commandes encodées ; un compte de service ouvre pour la première fois une session interactive.

## Analyser les IOC pendant une chasse

Lorsqu’une chasse fait apparaître un domaine, une IP ou une empreinte suspects :

- **Domaine** : ancienneté de l’enregistrement, autres postes qui l’ont résolu, adresses IP vers lesquelles il s’est résolu, présence d’autres domaines suspects sur ces IP.
- **IP** : propriétaire (ASN), hébergeur, sortie de VPN ou plage résidentielle ; historique de DNS passif.
- **Empreinte de fichier** : prévalence dans votre parc (un poste ou plusieurs ?), date de première apparition, signataire, chemin.

## Mesurer une chasse

Une chasse qui ne trouve aucune intrusion reste précieuse lorsqu’elle produit une référence, une détection ou la preuve qu’une source de données manque. Consignez ces résultats pour que la valeur de la chasse soit visible de la direction.
