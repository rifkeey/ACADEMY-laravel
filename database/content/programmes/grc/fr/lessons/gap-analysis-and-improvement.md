## Des profils au plan d’action

Une analyse d’écarts compare votre **profil actuel** à votre **profil cible** et transforme les différences en un plan d’action hiérarchisé et financé. Le CSF décrit un cycle qui se répète : définir le périmètre du profil, recueillir les informations, établir le profil actuel, analyser les écarts et créer un plan d’action, le mettre en œuvre puis mettre à jour le profil.

## Étape 1 : définir le périmètre

Décidez de ce que couvre le profil : toute l’organisation, une unité, ou un seul service comme une application de banque mobile. Un premier profil ciblé qui aboutit vaut davantage qu’un profil à l’échelle de l’organisation qui s’enlise.

## Étape 2 : établir honnêtement le profil actuel

Pour chaque résultat retenu, consignez ce qui est réellement vrai aujourd’hui et la preuve correspondante. Utilisez une échelle simple, définie à l’avance, par exemple :

| Note | Signification |
| --- | --- |
| 0 Non atteint | Résultat non traité |
| 1 Partiellement | Traité de manière informelle ou sur une partie du périmètre |
| 2 Largement | Défini et en grande partie appliqué, avec des lacunes connues |
| 3 Pleinement | Appliqué sur tout le périmètre, preuves à l’appui |

Notez à partir des preuves, pas des intentions. « Nous avons une politique de sauvegarde » ne prouve pas que les sauvegardes peuvent être restaurées.

## Étape 3 : fixer le profil cible

Les cibles découlent des risques, des obligations et de l’appétence, et non de l’envie d’obtenir 3 partout. Une petite entreprise sans équipe de développement peut raisonnablement fixer une cible basse pour les résultats liés au développement logiciel sécurisé, et une cible haute pour ceux liés aux identités et aux sauvegardes.

## Étape 4 : analyser et hiérarchiser les écarts

| Résultat | Actuel | Cible | Écart | Risque associé | Priorité |
| --- | --- | --- | --- | --- | --- |
| PR.AA (accès à privilèges) | 1 | 3 | 2 | R1 compromission d’un administrateur (20) | Élevée |
| RC.RP (exécution du rétablissement) | 0 | 2 | 2 | R2 échec de restauration (16) | Élevée |
| PR.AT (sensibilisation) | 2 | 3 | 1 | R1, R6 | Moyenne |
| DE.CM (surveillance) | 1 | 2 | 1 | R3 intrusion non détectée (12) | Moyenne |

Hiérarchisez selon la réduction de risque qu’apporte la résorption de chaque écart, puis tenez compte du coût, de l’effort, des dépendances et des obligations assorties d’échéances. Les gains rapides qui réduisent réellement le risque (activer une MFA dont la licence est déjà acquise) passent en premier.

## Étape 5 : le plan d’action

Chaque action a besoin d’un responsable, d’une échéance, de ressources et d’un critère d’achèvement mesurable. Regroupez les actions dans une feuille de route, par exemple à 90 jours, six mois et douze mois. Rendez compte de l’avancement par rapport à la feuille de route, et refaites le profil à intervalle fixe pour montrer l’évolution.

## Les niveaux en contexte

Les niveaux (1 *Partial*, 2 *Risk Informed*, 3 *Repeatable*, 4 *Adaptive*) caractérisent la manière dont la gouvernance et la gestion des risques de cybersécurité sont exercées : à quel point les pratiques sont formalisées, éclairées et adaptatives. Utilisez-les pour discuter de la *rigueur des pratiques*, en complément des écarts au niveau des résultats, et non à leur place. Passer à un niveau supérieur doit être une décision délibérée, justifiée par le risque, et non un but en soi.

## Les pièges courants

- Copier le profil cible d’une autre organisation sans tenir compte de vos propres risques.
- Noter généreusement pour faire bonne impression devant le conseil, ce qui masque les écarts que le conseil doit financer.
- Traiter le profil comme un projet ponctuel plutôt que comme un outil de pilotage vivant.
