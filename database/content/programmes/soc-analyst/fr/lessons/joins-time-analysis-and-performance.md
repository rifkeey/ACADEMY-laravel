## Combiner des tables avec `join`

Les investigations exigent souvent deux tables à la fois : les connexions et les événements d’audit, ou les événements de processus et les événements réseau. L’opérateur `join` de KQL les combine sur une clé commune :

```kql
let SuspiciousIPs =
    SigninLogs
    | where ResultType != "0"
    | summarize TargetedUsers = dcount(UserPrincipalName) by IPAddress
    | where TargetedUsers >= 5;
SigninLogs
| where ResultType == "0"
| join kind=inner SuspiciousIPs on IPAddress
| project TimeGenerated, UserPrincipalName, IPAddress, TargetedUsers
```

Cette requête trouve les connexions **réussies** depuis des adresses IP qui ont pulvérisé au moins cinq comptes : la corrélation « échecs suivis d’une réussite » du module 3.

Les types de jointure à connaître :

- `inner` : les lignes qui correspondent des deux côtés.
- `leftouter` : toutes les lignes de gauche, avec les colonnes de droite correspondantes lorsqu’elles existent.
- `leftanti` : les lignes de gauche **sans** correspondance à droite. C’est idéal pour « les actifs qui n’ont pas envoyé de journaux aujourd’hui » ou « les utilisateurs qui se sont connectés sans jamais terminer la MFA ».
- `innerunique` est le type **par défaut** en KQL et dédoublonne d’abord le côté gauche. Précisez `kind=` explicitement pour éviter les surprises.

> **Note sur les labs :** le simulateur KQL d’AMANIQ ne met pas en œuvre `let` ni `join`. Les tâches de lab qui exigent une corrélation sont conçues pour que vous puissiez y répondre avec deux requêtes distinctes. Utilisez les modèles de cette leçon lorsque vous travaillez dans un véritable espace de travail Sentinel.

## L’analyse temporelle

- **Première et dernière apparition** : `summarize min(TimeGenerated), max(TimeGenerated) by Entity`.
- **Creux et rafales** : `summarize count() by bin(TimeGenerated, 1m)`, puis recherchez les pics.
- **Ordonner les événements pour une chronologie** : `order by TimeGenerated asc`, en ne projetant que les champs qui racontent l’histoire.
- **Fenêtres de temps autour d’un point pivot** : une fois établi qu’une compromission a eu lieu à 02:14, recherchez `between (datetime(2026-03-04T01:44:00Z) .. datetime(2026-03-04T02:44:00Z))` dans les autres tables.

## Écrire des requêtes dignes d’une règle de détection

Une requête qui répond à une question ponctuelle peut être brouillonne. Une requête qui s’exécutera toutes les cinq minutes pendant des années doit être :

- **Spécifique** : filtrez d’abord sur les champs fiables les plus étroits (`EventID`, `ResultType`), puis sur les chaînes de caractères.
- **Bornée dans le temps** : incluez toujours un filtre temporel.
- **Lisible** : un opérateur par ligne, des commentaires avec `//`.
- **Testée** : exécutez-la sur des données saines et malveillantes connues avant de l’activer.

## Règles empiriques de performance (Sentinel en production)

1. Filtrez d’abord par le temps, puis par les colonnes indexées, puis par les correspondances de chaînes.
2. Préférez `has` à `contains` lorsque vous recherchez des mots entiers ; il utilise l’index des termes.
3. Utilisez `project` tôt pour réduire les colonnes transportées dans la chaîne.
4. Évitez les recherches `*` sur toutes les tables dans les règles planifiées.
5. Agrégez avant de joindre lorsque c’est possible, pour que la jointure traite moins de lignes.

La documentation officielle de KQL publiée par Microsoft comporte une page de bonnes pratiques qui les détaille ; elle figure dans les références de cette leçon.
