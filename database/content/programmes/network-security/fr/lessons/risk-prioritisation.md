Une revue trouve toujours plus de problèmes qu’il n’y a d’argent ou de temps pour les corriger. L’appréciation des risques fixe l’ordre, et permet au conseil d’administration de choisir en connaissance de cause ce qu’il finance et ce qu’il accepte.

## Le risque en termes simples

La NIST SP 800-30 Rev. 1, *Guide for Conducting Risk Assessments*, décrit le risque comme la combinaison de la **vraisemblance** (la probabilité qu’une menace exploite une faiblesse) et de l’**impact** (l’ampleur du préjudice). Beaucoup d’organisations utilisent une matrice simple :

| Vraisemblance ↓ / Impact → | Faible | Moyen | Élevé |
| --- | --- | --- | --- |
| **Élevée** | Moyen | Élevé | Critique |
| **Moyenne** | Faible | Moyen | Élevé |
| **Faible** | Faible | Faible | Moyen |

## Évaluer la vraisemblance et l’impact

| Augmente la vraisemblance | Augmente l’impact |
| --- | --- |
| Accessible depuis Internet ou des zones non fiables | Les systèmes qui sont les joyaux de la couronne (application bancaire centrale, données financières, identité) |
| Faiblesse connue et facile à exploiter | Données personnelles ou de paiement (conséquences réglementaires) |
| Authentification faible ou absente | De nombreux utilisateurs ou agences touchés |
| Aucune surveillance, si bien que l’abus passe inaperçu | Un long délai de rétablissement |
| Observée dans des incidents réels (y compris ceux de Kanem) | Une atteinte à la réputation |

Soyez cohérent : écrivez ce que signifie « élevé » pour Kanem, afin que des évaluateurs différents notent de la même façon.

## Le registre des risques

| Champ | Exemple |
| --- | --- |
| Identifiant et titre | NR-01 Une règle temporaire de prestataire expose la DMZ |
| Description | La règle 2 autorise toute source vers tout port de la DMZ ; journalisation désactivée |
| Vraisemblance / impact / niveau | Élevée / Élevé / Critique |
| Responsable | Responsable réseau |
| Traitement | Réduire : supprimer la règle 2 ; accès du prestataire via ZTNA (module 6) |
| Date cible | Sous 2 semaines |
| Risque résiduel | Faible |

## Construire la feuille de route

| Horizon | Contenu |
| --- | --- |
| **Maintenant (0 à 30 jours)** | Les risques critiques et les gains rapides, peu coûteux et à forte valeur : supprimer les règles dangereuses, activer la journalisation, bloquer l’accès d’administration depuis les zones utilisateurs |
| **Ensuite (1 à 6 mois)** | Les risques élevés qui exigent des projets : segmentation de la zone des serveurs, NAC, paire de pare-feu redondante, mesures sur le DNS |
| **Plus tard (6 à 18 mois)** | Les changements plus lourds : accès zero trust pour les utilisateurs à distance et les prestataires, microsegmentation des joyaux de la couronne, résilience complète |

Les dépendances comptent : la journalisation et la surveillance doivent souvent précéder les grands changements de segmentation, pour voir ce qui casse.

## Accepter un risque

Certains risques seront acceptés. L’acceptation doit être **explicite** : un responsable nommé disposant de l’autorité nécessaire, la raison, les éventuelles mesures compensatoires et une date de revue. « Nous n’avons pas eu le temps de nous en occuper » n’est pas une acceptation.

:::takeaway
Notez les risques selon leur vraisemblance et leur impact sur une matrice cohérente, consignez-les dans un registre avec des responsables, des traitements, des dates et le risque résiduel, et transformez-les en une feuille de route maintenant / ensuite / plus tard qui respecte les dépendances. N’acceptez les risques qu’explicitement, avec un responsable, une raison et une date de revue.
:::
