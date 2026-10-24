## Qui décide, qui fait, qui vérifie

La sécurité échoue sans bruit lorsque personne ne sait qui porte une décision. Les structures de gouvernance existent pour répondre à trois questions sur chaque sujet de sécurité important : **qui décide**, **qui fait le travail**, et **qui vérifie qu’il a été bien fait**.

## Les instances de gouvernance habituelles

| Instance | Rôle dans la gouvernance de la sécurité |
| --- | --- |
| Conseil d’administration | Fixe l’appétence au risque, approuve la politique de plus haut niveau, demande des comptes à la direction. Répond devant la loi et les régulateurs. |
| Comité des risques ou comité d’audit du conseil | Reçoit les rapports détaillés sur les risques et l’assurance ; interpelle la direction au nom du conseil. |
| Direction générale | Porte les risques métier, finance les mesures de sécurité, approuve les dérogations dans la limite de ses délégations. |
| Comité de pilotage de la sécurité | Instance transverse (informatique, opérations, juridique, RH, métiers) qui hiérarchise les travaux de sécurité et arbitre les conflits. |
| RSSI ou responsable de la sécurité | Conseille, fixe les normes internes, pilote le programme de sécurité et rend compte des risques. N’est généralement pas le propriétaire des risques métier. |
| Audit interne | Apporte au conseil une assurance indépendante sur l’efficacité des mesures de sécurité. |

Les petites organisations cumulent ces rôles. Une société de microfinance de 40 personnes n’a peut-être pas de comité de pilotage, mais quelqu’un doit tout de même approuver la politique, quelqu’un doit porter chaque risque, et une personne indépendante doit vérifier de temps en temps.

## Rendre compte ou réaliser

- **Responsable de la décision** (*Accountable*) : la seule personne qui répond du résultat et a autorité pour décider. Il ne doit y en avoir qu’une.
- **Réalisateur** (*Responsible*) : les personnes qui font le travail. Il peut y en avoir plusieurs.

Une matrice **RACI** (*Responsible, Accountable, Consulted, Informed* : réalise, rend compte, est consulté, est informé) le rend explicite :

| Activité | Directeur général | RSSI | Responsable informatique | RH |
| --- | --- | --- | --- | --- |
| Approuver la politique de contrôle d’accès | A | R | C | C |
| Gérer le processus arrivées-mutations-départs | I | C | R | A |
| Accepter un risque résiduel élevé | A | C | I | I |

Deux erreurs fréquentes : rendre le RSSI responsable de risques que les métiers créent et maîtrisent, et attribuer plusieurs « A » à une même activité, si bien que personne ne la porte vraiment.

## La séparation des tâches

Aucune personne ne doit pouvoir réaliser seule une action sensible de bout en bout, sans contrôle. Celui qui demande un paiement ne doit pas l’approuver ; l’administrateur qui accorde un accès à privilèges ne doit pas aussi le revoir. Lorsque l’effectif rend une séparation complète impossible, utilisez des mesures compensatoires, comme une revue indépendante des journaux.

## Les délégations de pouvoir

Les documents de gouvernance doivent préciser qui peut approuver quoi. Par exemple : les risques notés 15 ou plus ne peuvent être acceptés que par le directeur général et sont signalés au conseil ; les risques notés de 8 à 14 peuvent être acceptés par un directeur. Sans délégation de pouvoir, soit tout remonte au conseil, soit des collaborateurs subalternes acceptent discrètement des risques graves.
