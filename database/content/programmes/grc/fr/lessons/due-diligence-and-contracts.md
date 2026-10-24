## Classer les fournisseurs par niveau

Tous les fournisseurs ne méritent pas le même examen. Un modèle de classement note chaque fournisseur sur quelques critères et fixe en conséquence la profondeur de l’évaluation.

| Critère | Questions |
| --- | --- |
| Données | Le fournisseur stocke-t-il ou traite-t-il des données personnelles ou confidentielles ? En quelle quantité ? |
| Accès | Se connecte-t-il à notre réseau ou détient-il des identifiants à privilèges ? |
| Criticité | Sa défaillance arrêterait-elle un service critique ? En combien de temps pourrions-nous le remplacer ? |
| Enjeu réglementaire | Le service est-il soumis aux règles d’un régulateur sur l’externalisation ? |

| Niveau | Exemple | Évaluation |
| --- | --- | --- |
| Critique | Hébergeur du système bancaire central qui traite toutes les données clients | Questionnaire complet, examen des preuves, rapports d’assurance, éventuellement audit sur site ou à distance ; réévaluation annuelle |
| Élevé | Prestataire de paie qui détient les données personnelles du personnel | Questionnaire avec preuves ; certifications et leur domaine d’application ; réévaluation tous les un à deux ans |
| Moyen | Agence de marketing disposant de quelques coordonnées | Questionnaire court ; clauses contractuelles |
| Faible | Fournisseur de papeterie | Conditions standard uniquement |

## Les techniques de diligence raisonnable

- **Questionnaires de sécurité** : utiles pour la couverture, mais déclaratifs. Demandez des preuves pour les réponses les plus importantes.
- **Certifications** : vérifiez que le certificat est en cours de validité, délivré par un organisme accrédité, et que son **domaine d’application** inclut le service et les sites que vous utiliserez.
- **Rapports d’assurance indépendants** : par exemple, les rapports de contrôle des organisations de services établis par des auditeurs indépendants. Un rapport qui teste l’efficacité du fonctionnement sur une période apporte plus d’assurance qu’un rapport décrivant la conception à un instant donné. Lisez les **exceptions** et les **mesures complémentaires attendues des utilisateurs** (*complementary user entity controls*) : les mesures que le fournisseur attend que *vous* appliquiez.
- **Synthèses de tests d’intrusion** : vérifiez la date, le périmètre, et si les problèmes de gravité élevée ont été corrigés.
- **Solidité financière et opérationnelle** : la faillite d’un fournisseur est aussi un risque pour vos données.

## Les quatrièmes parties et la concentration

Demandez à quels **sous-traitants ultérieurs** le fournisseur fait appel et où les données seront stockées. Vos données peuvent aboutir chez un prestataire que vous n’avez jamais évalué, peut-être dans un autre pays. Tenez compte aussi du **risque de concentration** : si plusieurs fournisseurs critiques dépendent de la même région cloud, une seule panne les touche tous.

## Les clauses contractuelles qui comptent

| Clause | Objet |
| --- | --- |
| Exigences de sécurité | Mesures minimales, ou conformité à une norme désignée, pour le service concerné |
| Protection des données | Traitement uniquement sur instructions documentées ; confidentialité ; assistance pour les demandes des personnes concernées |
| Notification des violations | Vous avertir dans les meilleurs délais et dans un délai fixé (par exemple 24 heures), avec un contenu défini |
| Droit d’audit | Vous, ou un auditeur indépendant, pouvez évaluer la conformité ; ou le fournisseur remet chaque année des rapports d’assurance |
| Sous-traitants ultérieurs | Information ou accord préalable ; les mêmes obligations s’imposent en cascade |
| Localisation et transfert des données | Où les données peuvent être stockées ; conditions des transferts transfrontaliers |
| Résiliation et sortie | Restitution des données dans un format exploitable ; suppression certifiée ; assistance à la transition |
| Responsabilité et assurance | Répartition de la responsabilité financière ; niveaux de couverture de l’assurance cyber |

Un délai de notification plus long que votre propre délai légal est un signal d’alarme : si la loi vous impose de notifier le régulateur sous 72 heures, un fournisseur qui peut vous prévenir au bout de 30 jours rend la conformité impossible.

## Décider

L’issue de la diligence raisonnable est une décision du propriétaire métier : approuver, approuver sous conditions (remédiation dans un délai fixé et modifications du contrat) ou refuser. Consignez les risques résiduels dans le registre des risques comme tout autre risque.
