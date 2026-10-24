## L’évaluation que vous allez réaliser

Le projet final vous demande d’évaluer une organisation fictive de bout en bout. Les missions réelles suivent une méthode comparable, qu’elles soient menées par une équipe GRC interne ou par un consultant externe.

## 1. Cadrer la mission

Rédigez une **lettre de mission** d’une page : objectif (par exemple « apprécier les risques liés à la sécurité de l’information pesant sur le service de prêt mobile et recommander un plan à 12 mois »), périmètre, parties prenantes, sources d’information, calendrier et livrables. Convenez dès le départ des critères de risque et de l’énoncé d’appétence du conseil ; sans eux, les cotations ne peuvent être ni contestées ni défendues.

## 2. Recueillir le contexte

- **Documents** : politiques, schémas du réseau et des flux de données, rapports d’audit précédents, historique des incidents, liste des fournisseurs, obligations.
- **Entretiens** : les propriétaires métier (ce qui compte et ce qui ferait mal), l’informatique (comment les choses fonctionnent réellement), la conformité (ce qui doit être respecté).
- **Données techniques** : inventaire des actifs, synthèses des scans de vulnérabilités, couverture MFA, rapports de sauvegarde.

Consignez chaque fait avec sa source. Des notes d’entretien comme « Responsable informatique, 4 mars : aucune restauration de sauvegarde jamais testée » sont des preuves que vous citerez plus tard.

## 3. Identifier et coter les risques

Rédigez dix à quinze scénarios précis. Cotez le risque inhérent et le risque résiduel selon les critères convenus, en documentant votre raisonnement pour chaque cotation. Lorsque vous avez un doute, dites-le et donnez une fourchette. Faites coter deux ou trois scénarios par un collègue, de façon indépendante ; de grands écarts révèlent où vos critères manquent de clarté.

## 4. Évaluer les mesures de sécurité

Pour les mesures qui comptent face à vos principaux risques, évaluez la conception et, lorsque les preuves le permettent, le fonctionnement. Mettez-les en correspondance avec les catégories du CSF 2.0 et les CIS Controls, pour que l’organisation puisse rendre compte de façon cohérente. Signalez les correspondances partielles.

## 5. Construire le plan de remédiation

| Priorité | Action | Risques traités | Responsable | Effort et coût | Horizon |
| --- | --- | --- | --- | --- | --- |
| 1 | MFA pour tous les administrateurs et les accès à distance | R1, R6 | Responsable informatique | Faible ; licences déjà acquises | 30 jours |
| 2 | Tests de restauration trimestriels pour les systèmes centraux | R2 | Responsable informatique | Moyen | 90 jours |
| 3 | Renégociation des contrats fournisseurs (notification des violations, droit d’audit) | R4 | Responsable des opérations | Moyen ; temps du juridique | 6 mois |

Ordonnez les actions selon la réduction de risque par unité d’effort, respectez les dépendances (on ne peut pas surveiller des journaux qu’on ne collecte pas) et indiquez les cotations résiduelles cibles pour pouvoir mesurer les progrès.

## 6. Rendre compte

Utilisez la structure de rapport pour la direction de la leçon précédente : message principal, position de risque, décisions attendues, plan, annexe. Séparez les faits, les appréciations et les recommandations pour que le lecteur sache ce qui relève de chacun.

## Vérifications de qualité avant la remise

- Chaque cotation élevée renvoie-t-elle à des preuves ?
- Chaque recommandation renvoie-t-elle à un risque ?
- Chaque risque est-il porté par une personne du métier, et non par « l’informatique » ?
- Un administrateur sceptique comprendrait-il le message principal en trente secondes ?
- Avez-vous évité de promettre une certitude que vous n’avez pas ?
