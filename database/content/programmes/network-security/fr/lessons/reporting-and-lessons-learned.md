Un incident n’est pas terminé quand l’intrus est dehors. Le rapport consigne ce qui s’est passé pour la direction, les auditeurs et les autorités de contrôle, et la revue transforme l’incident en améliorations.

## Le rapport d’incident

| Rubrique | Contenu |
| --- | --- |
| **Synthèse pour la direction** | Une demi-page en langage clair : ce qui s’est passé, l’impact, la situation actuelle, les actions clés |
| **Chronologie** | Les heures UTC, de la première preuve à la clôture, chacune avec sa source |
| **Périmètre et impact** | Systèmes, comptes et données concernés ; volume de données ; impact sur l’activité |
| **Cause profonde** | Comment l’intrus est entré et pourquoi les mesures ne l’ont pas arrêté |
| **Réponse** | Les actions de détection, de confinement, d’éradication et de rétablissement, avec leurs heures |
| **Preuves** | Ce qui a été préservé, où, avec les empreintes |
| **Lacunes et inconnues** | Ce que les preuves n’ont pas pu montrer, et pourquoi |
| **Recommandations** | Chacune avec un responsable, une priorité et une date |

Rédigez les faits avec précision et séparez-les de l’interprétation, comme au module 8. Utilisez les mêmes heures et les mêmes noms d’un bout à l’autre.

## Les indicateurs à suivre

| Indicateur | Signification |
| --- | --- |
| **Délai de détection** | De la première activité malveillante à la première alerte ou au premier signalement |
| **Délai de confinement** | De la détection à l’arrêt du préjudice |
| **Délai de rétablissement** | De la détection au retour à un service normal |
| **Temps de présence** (*dwell time*) | Combien de temps l’intrus était présent avant la détection |

Observez la tendance sur de nombreux incidents plutôt que de juger un incident isolé.

## La revue des enseignements

Organisez-la dans les deux semaines, **sans recherche de coupable** : le but est d’améliorer les systèmes, pas de punir des personnes.

1. Que s’est-il passé, et quand l’avons-nous su ?
2. Qu’est-ce qui a bien fonctionné ?
3. Qu’est-ce qui nous a ralentis ou a failli mal tourner ?
4. Quelles mesures ont échoué ou manquaient ?
5. Que allons-nous changer ? (Responsable, date.)

Les résultats habituels renvoient aux modules précédents : retirer les règles de pare-feu temporaires (module 3), journaliser chaque règle d’autorisation qui franchit une frontière, ajouter des détections DMZ vers zone des serveurs (module 5), protéger le DNS (module 7) et s’entraîner par des exercices.

:::takeaway
Rédigez un rapport avec une synthèse claire, une chronologie UTC sourcée, le périmètre et l’impact, la cause profonde, la réponse, les preuves, des lacunes exposées honnêtement et des recommandations attribuées. Suivez les délais de détection, de confinement et de rétablissement, et menez des revues sans recherche de coupable qui débouchent sur des changements précis et attribués.
:::
