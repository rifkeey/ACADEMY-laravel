## L’ingénierie de détection est une ingénierie

L’ingénierie de détection applique aux détections la discipline du génie logiciel : exigences, gestion de versions, relecture par les pairs, tests et maintenance. Une détection est du code, et elle peut comporter des bogues.

Un cycle de vie simple d’une détection :

```text
Menace ou comportement à surveiller
  -> Hypothèse (« un attaquant qui fait X produira la télémétrie Y »)
  -> Vérification des données (collectons-nous Y ?)
  -> Logique (requête) et documentation
  -> Tests sur des données malveillantes et saines connues
  -> Relecture par un pair et déploiement
  -> Suivi de la précision et du volume ; ajustement ou retrait
```

## Sigma : des règles de détection indépendantes des éditeurs

Sigma est un format ouvert, fondé sur YAML, qui décrit des détections sur des journaux indépendamment de tout SIEM. Une règle Sigma peut être convertie en KQL, en Splunk SPL, en requêtes Elastic et dans d’autres formats grâce aux outils Sigma. Le dépôt SigmaHQ contient des milliers de règles communautaires que vous pouvez étudier et adapter.

Une règle Sigma simplifiée :

```yaml
title: Office Application Spawning a Command Shell
id: 00000000-0000-4000-8000-000000000000   # placeholder; real rules use a unique UUID
status: experimental
description: Detects Microsoft Office applications starting cmd.exe or powershell.exe, a common pattern after a malicious document is opened.
logsource:
  category: process_creation
  product: windows
detection:
  parent:
    ParentImage|endswith:
      - '\WINWORD.EXE'
      - '\EXCEL.EXE'
      - '\POWERPNT.EXE'
  child:
    Image|endswith:
      - '\cmd.exe'
      - '\powershell.exe'
  condition: parent and child
falsepositives:
  - Legitimate macros used by finance teams (document them and add narrow exclusions)
level: high
tags:
  - attack.execution
  - attack.t1204.002
```

Cette règle détecte une application Microsoft Office qui lance `cmd.exe` ou `powershell.exe`, un schéma fréquent après l’ouverture d’un document malveillant. Les faux positifs prévus sont les macros légitimes des équipes financières, à documenter et à traiter par des exclusions étroites. Les règles Sigma restent en anglais, comme dans le dépôt SigmaHQ.

La même logique exprimée en KQL sur une table d’événements de processus :

```kql
DeviceProcessEvents
| where InitiatingProcessFileName in~ ("winword.exe", "excel.exe", "powerpnt.exe")
| where FileName in~ ("cmd.exe", "powershell.exe")
| project Timestamp, DeviceName, AccountName, InitiatingProcessFileName, FileName, ProcessCommandLine
```

## La précision par la spécificité

Une détection devient plus fiable lorsqu’elle exige plusieurs conditions indépendantes que l’activité légitime remplit rarement ensemble. Comparez :

- `FileName == "powershell.exe"` : se déclenche en permanence.
- `... and ProcessCommandLine has "-enc"` : des commandes encodées, encore courantes dans certains outils d’administration.
- `... and InitiatingProcessFileName in~ ("winword.exe","excel.exe")` : PowerShell avec une commande encodée lancé par Office est rare et suspect.

## Les données de test

Ne déployez jamais une détection que vous avez seulement vue *ne pas* se déclencher. Constituez un petit jeu de test : quelques événements qui représentent l’attaque et un ensemble plus large d’événements normaux réalistes. Votre détection doit renvoyer tous les premiers et aucun des seconds. Le lab 4 vous demande de faire exactement cela.
