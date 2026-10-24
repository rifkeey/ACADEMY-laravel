## La place de KQL

Kusto Query Language (KQL) est le langage de requête de Microsoft Sentinel, de la chasse avancée de Microsoft Defender XDR, d’Azure Monitor Log Analytics et d’Azure Data Explorer. Il est en lecture seule : les requêtes filtrent et remodèlent les données, elles ne les modifient jamais. KQL est donc un outil sûr et expressif pour l’investigation.

> **À propos de l’environnement de labs AMANIQ :** aucun abonnement Microsoft n’est nécessaire pour ce programme. Les labs AMANIQ exécutent vos requêtes avec un interpréteur intégré qui met en œuvre un sous-ensemble documenté de KQL sur des jeux de données fictifs. La syntaxe apprise ici est du vrai KQL, directement transposable à Sentinel. Les opérateurs non pris en charge par le simulateur (comme `join`) sont expliqués dans les leçons et clairement signalés si vous les essayez dans un lab.

## La forme d’une requête

Une requête KQL commence par une table et fait passer les lignes dans une chaîne d’opérateurs séparés par `|` :

```kql
SigninLogs
| where TimeGenerated > ago(1d)
| where ResultType != "0"
| project TimeGenerated, UserPrincipalName, IPAddress, Location, ResultType
| take 50
```

Lisez-la de haut en bas : prendre la table `SigninLogs`, garder le dernier jour, garder les échecs (dans les journaux de connexion Entra ID, un `ResultType` égal à `"0"` signifie une réussite), choisir cinq colonnes et afficher 50 lignes.

## Filtrer avec `where`

| Opérateur | Signification | Sensible à la casse ? |
| --- | --- | --- |
| `==`, `!=` | Égal / différent | Oui |
| `=~`, `!~` | Égal / différent | Non |
| `contains`, `!contains` | Correspondance de sous-chaîne | Non |
| `has`, `!has` | Correspondance de terme entier (plus rapide sur les grandes tables) | Non |
| `startswith`, `endswith` | Préfixe / suffixe | Non |
| `in (...)`, `!in (...)` | Valeur dans une liste | Oui |
| `<`, `<=`, `>`, `>=` | Comparaison (nombres, dates et heures) | sans objet |

Combinez les conditions avec `and` et `or`, et utilisez des parenthèses pour rendre la priorité évidente :

```kql
SecurityEvent
| where EventID == 4625 and (TargetUserName =~ "administrator" or TargetUserName =~ "admin")
```

## Filtrer d’abord sur le temps

Filtrez toujours sur le temps le plus tôt possible. `ago(1h)`, `ago(7d)` et `between (datetime(2026-03-04T00:00:00Z) .. datetime(2026-03-05T00:00:00Z))` sont les formes habituelles. En production sous Sentinel, le filtre temporel est aussi le premier facteur de performance d’une requête.

## Choisir les colonnes : `project` et `extend`

`project` ne garde que les colonnes listées et peut les renommer. `extend` ajoute des colonnes calculées sans supprimer les colonnes existantes :

```kql
SigninLogs
| extend Hour = bin(TimeGenerated, 1h)
| project Hour, UserPrincipalName, IPAddress
```

## Mise en pratique

Dans l’espace de travail du lab, essayez ces requêtes et comparez les résultats :

```kql
SigninLogs | take 10
SigninLogs | where ResultType != "0" | count
SigninLogs | where Location != "NG" | project TimeGenerated, UserPrincipalName, Location
```
