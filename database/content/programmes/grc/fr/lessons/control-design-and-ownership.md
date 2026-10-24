## Ce qu’est une mesure de sécurité

Une **mesure de sécurité** (*control*) est toute mesure qui modifie un risque : une politique, un processus, un paramètre technique, une barrière physique, un programme de formation. Une bonne mesure est décrite assez précisément pour qu’une autre personne puisse vérifier si elle fonctionne.

## Décrire une mesure

Une description complète répond aux questions suivantes :

| Élément | Exemple : revue des accès à privilèges |
| --- | --- |
| Objectif | Garantir que seul le personnel autorisé conserve des droits d’administrateur sur le système bancaire central |
| Risque(s) traité(s) | R1 accès non autorisé par des comptes à privilèges |
| Activité | Le responsable informatique exporte la liste des membres du groupe d’administrateurs ; chaque responsable hiérarchique confirme ou révoque les accès |
| Fréquence | Trimestrielle, et à chaque changement de poste |
| Propriétaire | Responsable des opérations informatiques (rend compte) |
| Opérateur | Administrateur informatique (réalise l’export et les suppressions) |
| Preuves | Compte rendu de revue signé, fichier d’export daté, tickets des suppressions |

## Classer les mesures

Selon leur **finalité** :

- **Préventives** : elles empêchent un événement (MFA, règles de pare-feu, circuits d’approbation).
- **Détectives** : elles repèrent un événement (surveillance des journaux, rapprochements, revues des accès).
- **Correctives** : elles limitent les dégâts et rétablissent la situation (sauvegardes et procédures de restauration, réponse aux incidents).

Selon leur **nature** : administratives (politiques, formation), techniques (configurations, logiciels), physiques (serrures, gardiennage). Certains référentiels distinguent aussi les mesures **dissuasives** et **compensatoires**.

Une conception équilibrée combine plusieurs types. Les mesures préventives finissent par échouer ; les mesures détectives vous préviennent quand cela arrive.

## Manuelles ou automatisées

Les mesures automatisées fonctionnent de façon constante et produisent des preuves automatiquement, mais elles peuvent être mal configurées sans que personne ne s’en aperçoive. Les mesures manuelles sont souples, mais dépendent de personnes qui doivent penser à les appliquer et les appliquer correctement. Lorsqu’une mesure manuelle est réalisée en retard ou sautée, la piste de preuves le montre généralement.

## Propriétaires et opérateurs

Le **propriétaire** répond de l’existence de la mesure, de sa bonne conception et de son efficacité. L’**opérateur** la réalise. Les séparer favorise la séparation des tâches : l’administrateur qui accorde les accès ne doit pas être la seule personne qui les revoit.

## Les mesures compensatoires

Lorsqu’une mesure requise ne peut pas être mise en œuvre, une mesure compensatoire réduit le même risque d’une autre manière. Exemple : une ancienne application ne peut pas imposer la MFA ; l’accès est donc limité à un serveur de rebond qui l’impose, et toutes les sessions sont enregistrées et revues chaque semaine. Une mesure compensatoire doit traiter le même risque dans une mesure comparable, et être documentée, approuvée et revue, généralement via le processus de dérogation.

## Définir les preuves avant d’en avoir besoin

Décidez, dès la conception d’une mesure, quelles preuves elle produira et où elles seront conservées. Reconstituer des preuves la semaine précédant un audit coûte cher et s’avère souvent impossible. Préférez des preuves générées par le système, horodatées et couvrant toute la période, à des captures d’écran prises le jour même.
