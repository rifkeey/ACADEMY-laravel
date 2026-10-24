## Un langage commun pour décrire le comportement des attaquants

MITRE ATT&CK est une base de connaissances publique des tactiques et techniques des attaquants, fondée sur des observations réelles. Elle donne aux défenseurs un vocabulaire commun : au lieu d’écrire « l’attaquant a fait quelque chose avec des tâches planifiées », vous pouvez écrire *T1053.005 Scheduled Task/Job: Scheduled Task*, et chaque lecteur sait exactement de quoi il s’agit.

ATT&CK est mis à jour régulièrement et versionné. Lorsque vous rattachez des détections ou rapportez des constatations, indiquez la version d’ATT&CK utilisée, car les identifiants et les noms des techniques changent parfois.

## Tactiques, techniques et procédures

- **Tactique** : l’*objectif* de l’attaquant à une étape donnée, comme Initial Access, Persistence, Privilege Escalation, Credential Access, Lateral Movement ou Exfiltration.
- **Technique** : *la manière* dont il atteint cet objectif, par exemple T1110 Brute Force. De nombreuses techniques comportent des **sous-techniques** (T1110.003 Password Spraying).
- **Procédure** : la mise en œuvre précise observée, par exemple « a utilisé une liste de 3 mots de passe saisonniers contre tous les comptes de l’annuaire depuis un serveur privé virtuel ».

Ce sont les procédures que vous voyez dans les journaux. Les techniques permettent de généraliser à partir d’elles pour évaluer la couverture de détection.

## Rattacher les preuves à ATT&CK

Reprenez la chronologie du module précédent et rattachez chaque étape :

| Comportement observé | Tactique | Technique |
| --- | --- | --- |
| De nombreux comptes essayés avec peu de mots de passe | Credential Access | T1110.003 Password Spraying |
| Connexion réussie avec le mot de passe deviné | Initial Access / Persistence | T1078 Valid Accounts |
| Règle de transfert de la boîte de réception vers une adresse externe | Collection | T1114.003 Email Collection: Email Forwarding Rule |
| Téléchargement massif depuis SharePoint | Collection | T1213 Data from Information Repositories |

Le rattachement relève du jugement. Vérifiez la description de la technique et ses recommandations de détection sur le site d’ATT&CK plutôt que de vous fier à son seul nom.

## Couverture et lacunes

ATT&CK est aussi un outil de planification. Pour chaque technique pertinente, demandez-vous :

1. Collectons-nous les données qui la révéleraient ?
2. Disposons-nous d’une détection pour elle ?
3. Cette détection a-t-elle été **testée** ?

Une carte de couverture qui se contente d’indiquer « nous avons une règle » surestime la protection. Distinguez *données disponibles*, *détection existante* et *détection validée*. Donnez la priorité aux techniques employées par les groupes d’attaquants qui visent votre secteur et votre région plutôt que de chercher à couvrir toute la matrice de façon uniforme.

## Précautions

- ATT&CK décrit des comportements ; ce n’est pas un score de maturité. « Nous couvrons 70 % d’ATT&CK » n’a pas de sens en soi.
- Certaines techniques recoupent largement l’administration légitime (par exemple PowerShell). Leur détection repose alors sur le contexte, et non sur la technique seule.
