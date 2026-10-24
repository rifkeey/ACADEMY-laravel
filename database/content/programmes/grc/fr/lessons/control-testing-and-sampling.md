## Les types d’audit

| Type | Réalisé par | Exemple |
| --- | --- | --- |
| Première partie (interne) | Les auditeurs de l’organisation elle-même | Audit interne du SMSI ; revue du contrôle d’accès par l’audit interne |
| Seconde partie | Un client ou un partenaire qui audite un fournisseur | Une banque qui audite son prestataire informatique |
| Tierce partie | Un organisme indépendant | Audit de certification ; rapport d’assurance indépendant ; inspection réglementaire |

Quel que soit le type, les auditeurs suivent la même logique : comprendre la mesure, décider comment la tester, recueillir les preuves et conclure.

## Les tests de cheminement

Un **test de cheminement** (*walkthrough*) suit une transaction du début à la fin à travers la mesure : par exemple un nouvel arrivant, de la demande RH à la création de son compte. Il confirme que l’auditeur comprend comment la mesure fonctionne réellement, ce qui diffère souvent de ce que dit la procédure écrite.

## Les techniques de test

| Technique | Ce que fait l’auditeur | Force probante |
| --- | --- | --- |
| Entretien (*inquiry*) | Demande aux personnes comment fonctionne la mesure | La plus faible à elle seule ; toujours corroborer |
| Observation | Regarde la mesure être réalisée | Ne la montre qu’à un instant donné |
| Inspection | Examine des enregistrements, configurations, tickets, journaux | Solide lorsque les enregistrements sont générés par le système |
| Réexécution | Réalise lui-même la mesure et compare les résultats | La plus solide ; par exemple, recalculer quels partants ont encore un compte |

Un bon test combine les techniques : l’entretien pour comprendre, l’inspection pour confirmer, la réexécution pour les mesures clés.

## Efficacité de la conception et du fonctionnement

- **Conception** : si elle était réalisée comme décrit, la mesure réduirait-elle le risque ? Une revue trimestrielle des accès qui ne porte que sur les comptes de messagerie est mal conçue si le risque concerne l’accès au système bancaire central.
- **Fonctionnement** : a-t-elle réellement été réalisée, correctement, pendant toute la période ? On le teste par échantillonnage.

Une mesure peut être bien conçue et jamais appliquée, ou appliquée fidèlement mais mal conçue. Les deux donnent lieu à des constats, de natures différentes.

## L’échantillonnage

Les auditeurs testent rarement chaque élément. Ils testent un **échantillon** tiré d’une **population** et en tirent des conclusions sur l’ensemble.

1. **Vérifiez que la population est complète.** Obtenez la liste de tous les nouveaux arrivants dans le système RH, et non dans la file de tickets de l’informatique ; sinon, les arrivants qui n’ont jamais eu de ticket sont invisibles.
2. **Choisissez une taille d’échantillon** adaptée à la fréquence et au risque de la mesure. Une mesure trimestrielle peut être testée sur deux occurrences sur quatre ; une mesure quotidienne sur 20 à 40 jours ; une mesure appliquée à des centaines de transactions sur un échantillon de 25 ou plus.
3. **Sélectionnez sans biais** : une sélection aléatoire ou systématique sur toute la période, et non les dix premiers.
4. **Évaluez les exceptions.** Demandez-vous si chaque exception est une erreur isolée ou le signe d’une faiblesse systémique.

## Conclure

Conclusions habituelles : *efficace* ; *efficace avec exceptions* (écarts mineurs et isolés) ; *défaut de conception* ; *défaut de fonctionnement*. Indiquez le fondement : « Sur 25 nouveaux arrivants échantillonnés, 3 ont reçu des accès avant approbation ; nous concluons que la mesure n’a pas fonctionné efficacement sur la période. »

## Se préparer en tant qu’audité

Connaissez vos populations, conservez les preuves là où vous pouvez les retrouver rapidement, réalisez vos propres tests d’échantillons avant l’auditeur, et répondez aux questions avec précision. Fournir spontanément des documents sans rapport ralentit les audits ; retenir des documents pertinents abîme la confiance.
