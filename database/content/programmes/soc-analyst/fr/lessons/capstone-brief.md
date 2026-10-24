## Présentation du projet final

Le projet final réunit tout ce que vous avez appris dans ce programme. Vous serez l’analyste SOC de permanence pour **Kanem Logistics Ltd**, une entreprise de fret fictive installée à Lagos et à Kano. L’environnement, les utilisateurs, les adresses IP et les incidents de Kanem sont entièrement fictifs ; toute ressemblance avec une organisation réelle serait fortuite.

## Ce que vous allez faire

1. **Concevoir et tester une logique de détection** (lab 4) pour un comportement observé dans l’incident du projet final.
2. **Investiguer l’incident en plusieurs étapes** (lab 6) à partir de la télémétrie de connexion, de processus, réseau et DNS dans l’espace de travail KQL.
3. **Documenter au fur et à mesure les preuves et les actions de réponse**, selon la structure de notes de dossier du module 9.
4. **Rédiger un rapport d’incident final** selon la structure décrite dans le module 9.

## Critères d’évaluation

Le lab du projet final est corrigé automatiquement sur les constatations qui peuvent être vérifiées objectivement :

- le compte compromis et l’adresse IP source de l’attaquant ;
- le mécanisme de persistance utilisé ;
- la destination de l’exfiltration ;
- les tactiques ATT&CK présentes dans l’intrusion ;
- une sévérité et des priorités de confinement appropriées.

Votre rapport écrit n’est pas noté par la plateforme. Nous vous recommandons vivement de le rédiger malgré tout et de le vérifier à l’aide de la liste ci-dessous. Votre rapport est le document que vous pourrez montrer à un employeur (en indiquant clairement qu’il s’agit d’un scénario fictif).

## Liste de vérification du rapport

- [ ] Une synthèse d’une page pour la direction, rédigée pour un lecteur non technique
- [ ] Une chronologie en UTC avec des références aux preuves
- [ ] Un tableau de correspondance ATT&CK avec les identifiants de technique et la version d’ATT&CK utilisée
- [ ] Le périmètre : comptes, postes et données touchés, ainsi que ce qui ne l’a **pas** été
- [ ] Les actions de confinement, d’éradication et de rétablissement, horodatées
- [ ] La cause première et les lacunes de contrôle qui y ont contribué
- [ ] Des recommandations hiérarchisées, avec des responsables
- [ ] En annexe : les indicateurs de compromission et les requêtes KQL utilisées

## Règles de base

Toute l’activité du projet final se déroule dans l’environnement simulé d’AMANIQ. N’appliquez jamais les techniques ou les requêtes apprises à des systèmes qui ne vous appartiennent pas ou que vous n’êtes pas explicitement autorisé à tester. Consultez la politique d’utilisation acceptable des labs de cybersécurité.
