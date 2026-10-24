## La précision du vocabulaire compte

Les analystes emploient trois termes souvent confondus. Les utiliser avec précision rend les rapports plus clairs et les indicateurs plus parlants.

- **Événement** : tout fait observable dans un système ou un réseau. Un utilisateur qui se connecte, un fichier créé, une requête DNS. La plupart des événements sont normaux.
- **Alerte** : une notification indiquant qu’un ou plusieurs événements correspondent à une condition de détection et méritent l’attention d’une personne. Une alerte est une *hypothèse* : quelque chose ne va peut-être pas.
- **Incident** : un fait qui compromet, ou menace de façon imminente, la confidentialité, l’intégrité ou la disponibilité des informations ou des systèmes, ou qui enfreint la politique de sécurité. Un incident est un problème *confirmé* qui exige une réponse.

Un même incident peut impliquer des milliers d’événements et plusieurs alertes. Beaucoup d’alertes ne deviennent jamais des incidents.

## L’issue d’une alerte

Une fois le tri d’une alerte terminé, classez son issue :

| Issue | Signification | Exemple |
| --- | --- | --- |
| Vrai positif | La règle a détecté ce pour quoi elle a été conçue, et c’est malveillant | Pulvérisation de mots de passe (*password spraying*) depuis une IP externe |
| Vrai positif bénin | La règle a correctement détecté le comportement, mais celui-ci est autorisé | Un administrateur informatique qui utilise PsExec pendant une fenêtre de maintenance |
| Faux positif | La règle s’est déclenchée, mais le comportement ciblé ne s’est pas produit | Une alerte « téléchargement de logiciel malveillant » causée par un fichier de mise à jour d’un éditeur de sécurité |
| Faux négatif | Une activité malveillante s’est produite sans qu’aucune alerte ne se déclenche | Découverte plus tard lors d’une chasse aux menaces |

Les faux négatifs sont la catégorie la plus dangereuse, car personne ne les voit. La chasse aux menaces et les tests de détection existent pour les réduire.

## La télémétrie : la matière première

Une détection ne vaut que par les données collectées. Les grandes familles de télémétrie sont :

- **Identité** : journaux de connexion, événements MFA, modifications de l’annuaire.
- **Postes de travail et serveurs** : création de processus, connexions réseau par processus, modifications de fichiers et du registre (issues d’un EDR ou d’outils comme Sysmon).
- **Réseau** : journaux de pare-feu, journaux DNS, journaux de proxy, enregistrements de flux, alertes IDS.
- **Cloud et SaaS** : journaux d’audit du plan de contrôle, journaux d’accès au stockage, pistes d’audit des applications.
- **Applications** : journaux d’accès des serveurs web, journaux d’erreurs des applications, journaux d’audit des bases de données.

Une question revient sans cesse chez les analystes : « est-ce que nous journalisons seulement cela ? » Connaissez votre couverture avant de vous fier à l’absence de preuve.

## Le contexte transforme les événements en preuves

Un même événement peut être anodin ou alarmant selon le contexte. Une connexion depuis Lagos est normale pour un employé basé à Lagos, et suspecte pour un compte qui s’est connecté depuis Londres dix minutes plus tôt. Le contexte provient de l’inventaire des actifs, des données RH (arrivées et départs), des calendriers de changements et du renseignement sur les menaces. Recueillir ce contexte fait partie du travail de l’analyste ; ce n’est pas un supplément facultatif.
