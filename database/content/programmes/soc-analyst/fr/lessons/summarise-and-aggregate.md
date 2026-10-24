## Des lignes aux réponses

La plupart des questions d’investigation sont des agrégations : *combien*, *combien de valeurs distinctes*, *première et dernière apparition*, *les N premiers*. En KQL, on y répond avec `summarize`.

```kql
SigninLogs
| where ResultType != "0"
| summarize Failures = count() by UserPrincipalName
| order by Failures desc
```

`summarize` regroupe les lignes selon les colonnes indiquées après `by` et calcule des fonctions d’agrégation pour chaque groupe.

## Les fonctions d’agrégation du quotidien

| Fonction | Renvoie |
| --- | --- |
| `count()` | Le nombre de lignes du groupe |
| `dcount(col)` | Le nombre approximatif de valeurs distinctes (exact dans le simulateur AMANIQ) |
| `min(col)`, `max(col)` | La plus petite / la plus grande valeur ; avec des horodatages, la première et la dernière apparition |
| `sum(col)`, `avg(col)` | Totaux et moyennes |
| `make_set(col)` | Les valeurs distinctes sous forme de tableau |

## Pulvérisation de mots de passe ou force brute

Ces deux attaques se distinguent une fois agrégées, d’où l’utilité de `summarize` :

- **Force brute** : de nombreux mots de passe contre **un seul** compte. En regroupant par utilisateur, le décompte est élevé.
- **Pulvérisation de mots de passe** : un ou deux mots de passe courants contre **de nombreux** comptes, pour rester sous les seuils de verrouillage. En regroupant par IP source, `dcount(UserPrincipalName)` est élevé.

```kql
SigninLogs
| where ResultType != "0"
| summarize Attempts = count(), TargetedUsers = dcount(UserPrincipalName),
            FirstSeen = min(TimeGenerated), LastSeen = max(TimeGenerated) by IPAddress
| where TargetedUsers >= 5
| order by TargetedUsers desc
```

## Les séries temporelles avec `bin()`

`bin()` arrondit les valeurs par défaut à des intervalles fixes. Regrouper par `bin(TimeGenerated, 1h)` crée un histogramme horaire, c’est ainsi que l’on repère les pics et le balisage :

```kql
DnsEvents
| where ClientIP == "10.20.4.17"
| summarize Queries = count() by bin(TimeGenerated, 5m)
| order by TimeGenerated asc
```

## Trier et limiter

- `order by Col desc` (ou `sort by`) trie. **Par défaut, KQL trie par ordre décroissant**, ce qui surprend les habitués de SQL. Écrivez explicitement `asc` ou `desc`.
- `take 10` renvoie 10 lignes quelconques (sans garantie d’ordre).
- `top 10 by Col` trie et limite en une seule étape.
- `distinct Col1, Col2` renvoie les combinaisons uniques.

## Vérifiez votre compréhension

Si `summarize count() by IPAddress` renvoie une ligne avec 300 échecs, s’agit-il de pulvérisation ou de force brute ? Impossible de le dire à ce stade. Ajoutez `dcount(UserPrincipalName)` : un seul utilisateur signifie force brute, 300 utilisateurs signifient pulvérisation.
