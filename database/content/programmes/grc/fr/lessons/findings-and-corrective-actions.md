## Anatomie d’un constat bien rédigé

Les auditeurs et les équipes GRC formulent les constats selon une structure standard, pour que les lecteurs puissent les évaluer rapidement et agir.

| Élément | Question à laquelle il répond | Exemple |
| --- | --- | --- |
| Situation constatée | Qu’avons-nous trouvé ? | 3 nouveaux arrivants sur 25 échantillonnés ont reçu un accès au système bancaire central avant l’approbation de leur responsable hiérarchique. |
| Critère | Qu’est-ce qui était exigé ? | La norme de contrôle d’accès, §2.3, exige une approbation documentée avant l’octroi de l’accès. |
| Cause | Pourquoi est-ce arrivé ? | Les demandes urgentes sont traitées par téléphone ; le ticket est ouvert après coup. |
| Effet | Quel est le risque ? | Des collaborateurs peuvent détenir des accès qui n’ont jamais été autorisés, ce qui augmente le risque de fraude et d’exposition des données. |
| Recommandation | Que faut-il changer ? | Imposer l’approbation dans le circuit de tickets avant l’attribution des accès ; définir une procédure d’urgence avec approbation a posteriori sous 24 heures. |

Chaque constat reçoit une **cotation** (par exemple élevée, moyenne, faible) fondée sur l’effet et sur son étendue. Un constat sans cause conduit généralement à une correction superficielle.

## L’analyse des causes premières

Demandez « pourquoi ? » jusqu’à atteindre quelque chose que l’organisation maîtrise et peut changer. Pour l’exemple ci-dessus : *Pourquoi l’accès a-t-il été accordé trop tôt ?* À cause des demandes par téléphone. *Pourquoi par téléphone ?* Les nouveaux collaborateurs arrivent avant que les responsables n’ouvrent les tickets. *Pourquoi ?* Les RH n’avertissent l’informatique que le jour de l’arrivée. La cause première est la transmission entre les RH et l’informatique ; corriger uniquement le processus informatique n’empêchera pas la récidive.

## Correction et action corrective

- **Correction** : régler le cas présent. Revoir les trois comptes et confirmer ou retirer les accès.
- **Action corrective** : supprimer la cause. Modifier le circuit d’intégration RH pour que la demande d’accès soit déclenchée deux semaines avant la date d’arrivée.

Les deux sont généralement nécessaires.

## Le plan d’actions correctives

| Constat | Action | Responsable | Échéance | Statut | Vérifié par |
| --- | --- | --- | --- | --- | --- |
| F-03 accès anticipé | Le circuit impose l’approbation avant l’attribution des accès | Responsable informatique | 31 janvier | En cours | Audit interne |

Bonnes pratiques :

- **Réponse de la direction** : le responsable accepte le constat (ou le conteste, raisons à l’appui) et s’engage sur un plan.
- **Des échéances réalistes** : manquer les échéances à répétition est en soi un signal de gouvernance.
- **La vérification** : la clôture exige la preuve que l’action a été menée *et* qu’elle est efficace, généralement en testant à nouveau un nouvel échantillon.
- **L’escalade** : les actions à cotation élevée en retard remontent à la direction générale et au comité du conseil.

## Gérer les preuves entre deux audits

Tenez un **référentiel de preuves** organisé par mesure, en indiquant la période couverte par chaque élément, qui l’a produit et quand. Automatisez lorsque c’est possible : exports planifiés de la couverture MFA, comptes rendus de revue des accès générés par le système de gestion des identités, rapports de sauvegarde envoyés vers une boîte aux lettres surveillée. Certaines organisations pratiquent la surveillance continue des mesures pour tester automatiquement les mesures clés chaque jour, ce qui transforme la préparation des audits en rapport de routine plutôt qu’en projet.

## Les constats récurrents

Un constat qui réapparaît année après année indique au conseil que les actions correctives ne fonctionnent pas. Suivez explicitement les constats récurrents et rendez-en compte séparément ; c’est souvent l’indicateur de gouvernance le plus utile qu’un audit produise.
