## Ce que fait réellement un SIEM

Une plateforme de gestion des informations et des événements de sécurité (SIEM, *Security Information and Event Management*) collecte les journaux de nombreuses sources, les rend consultables en un seul endroit et y applique une logique de détection. Splunk, Microsoft Sentinel, Elastic Security, Google Security Operations et IBM QRadar en sont des exemples courants. Les notions de ce module s’appliquent à tous.

Le traitement d’un SIEM comporte cinq étapes :

1. **Collecte** : des agents, des relais syslog, des connecteurs d’API et des intégrations natives du cloud acheminent les données.
2. **Analyse syntaxique** (*parsing*) : le texte brut devient des champs structurés (`src_ip`, `user`, `action`).
3. **Normalisation** : les champs des différents produits sont mis en correspondance avec un schéma commun, de sorte que « IP source » porte un seul nom, quel que soit le produit d’origine.
4. **Enrichissement** : des recherches ajoutent du contexte, comme le propriétaire de l’actif, la géolocalisation ou un verdict du renseignement sur les menaces.
5. **Stockage et conservation** : un stockage « chaud » pour la recherche rapide, et un archivage moins coûteux pour les preuves à long terme.

## Analyse syntaxique et normalisation en pratique

Voici le même échec de connexion SSH vu par deux produits :

```text
# Raw syslog
Mar  4 03:01:15 web-01 sshd[2213]: Failed password for root from 198.51.100.23 port 51130 ssh2

# A vendor's JSON audit format
{"ts":"2026-03-04T02:01:15Z","host":"web-01","evt":"auth_fail","account":"root","client":"198.51.100.23"}
```

Le premier est une ligne syslog brute, le second le format d’audit JSON d’un éditeur. Après normalisation, tous deux devraient donner quelque chose comme :

| Champ | Valeur |
| --- | --- |
| TimeGenerated | 2026-03-04T02:01:15Z |
| DeviceName | web-01 |
| EventType | LogonFailed |
| TargetUser | root |
| SourceIP | 198.51.100.23 |

C’est seulement alors qu’une même règle peut détecter la force brute sur toutes les sources. L’Advanced Security Information Model (ASIM) de Microsoft Sentinel et l’Elastic Common Schema (ECS) sont des exemples de schémas de normalisation.

## L’horodatage : la défaillance silencieuse

Chaque événement peut porter plusieurs heures : quand il s’est produit sur l’équipement, quand l’équipement l’a écrit, quand le collecteur l’a reçu et quand le SIEM l’a ingéré. Les règles de détection qui examinent « les 5 dernières minutes » selon l’heure d’ingestion peuvent manquer les événements arrivés en retard. Les analystes doivent savoir :

- sur quel champ d’horodatage leurs requêtes filtrent ;
- le délai d’ingestion habituel de chaque source ;
- si une source indique l’heure locale sans décalage.

## Couverture et coût

Tout ingérer est rarement abordable, et les licences SIEM sont souvent facturées au volume. Choisissez ce que vous collectez en fonction des besoins de détection, pas de l’habitude. Une question utile pour chaque source : *quelles détections ou investigations échoueraient sans ces données ?* La publication NIST SP 800-92 propose des recommandations indépendantes des éditeurs pour planifier la gestion des journaux, y compris leur conservation et la protection de leur intégrité.

> **Réflexe d’analyste :** lorsqu’une requête ne renvoie rien, commencez par prouver que la source de données est présente sur cette période. « Aucun résultat » et « aucune donnée » sont deux constatations différentes.
