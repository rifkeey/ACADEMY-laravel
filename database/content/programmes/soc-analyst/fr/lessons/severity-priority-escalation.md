## Sévérité n’est pas priorité

La **sévérité** décrit à quel point un incident est, ou pourrait être, préjudiciable. La **priorité** décrit l’ordre dans lequel vous traitez les dossiers. Les deux coïncident en général, mais pas toujours : un problème de sévérité moyenne sur un système qui traite la paie ce soir peut passer avant un problème de sévérité élevée sur un serveur de test isolé.

## Un modèle de sévérité pratique

La plupart des organisations définissent quatre ou cinq niveaux. Voici un point de départ raisonnable :

| Sévérité | Critères habituels | Délai de prise en charge visé |
| --- | --- | --- |
| Critique | Compromission active de systèmes critiques ou de données sensibles ; rançongiciel en cours de propagation ; exfiltration de données confirmée | Immédiat, 24 h/24 et 7 j/7, avec un responsable de la gestion de l’incident désigné |
| Élevée | Compromission confirmée d’un seul poste ou d’un seul compte ; propagation probable | Dans l’heure |
| Moyenne | Activité suspecte dont l’explication malveillante est plausible ; violation de politique à l’impact limité | Le jour ouvré même |
| Faible | Information ; possibilité de durcissement ; attaque échouée sans impact | Dans le délai convenu pour le traitement en attente |

Les délais ci-dessus sont donnés à titre d’exemple. Le plan de réponse aux incidents de votre organisation et ses éventuelles obligations contractuelles fixent les valeurs réelles.

## Facteurs qui augmentent ou diminuent la sévérité

Posez ces questions dans l’ordre :

1. **Est-ce confirmé ?** Une alerte non confirmée justifie rarement le niveau de sévérité le plus élevé.
2. **Quel actif est concerné ?** Les contrôleurs de domaine, les fournisseurs d’identité, les systèmes de paiement et les sauvegardes pèsent lourd.
3. **Quelles données sont menacées ?** Les données personnelles, les données financières et les identifiants augmentent la sévérité et peuvent déclencher des obligations légales de notification.
4. **Y a-t-il propagation ou persistance ?** Un déplacement latéral ou une persistance établie signale un intrus actif.
5. **Quel est l’impact actuel sur l’activité ?** Une interruption de service ou une fraude en cours exige une action immédiate.

## L’escalade

Escalader n’est pas un échec. C’est le mécanisme qui apporte au problème l’autorité et l’expertise nécessaires. Escaladez lorsque :

- la sévérité dépasse ce que vous êtes habilité à traiter ;
- le confinement exige une action que vous n’êtes pas autorisé à effectuer (par exemple désactiver un compte à privilèges) ;
- des conséquences juridiques, réglementaires ou d’image sont possibles ;
- vous êtes bloqué. Limiter la durée d’une investigation évite qu’un analyste garde seul, sans le dire, un dossier critique.

Un bon message d’escalade est court et complet :

```text
SÉVÉRITÉ : Élevée (proposée)
QUOI : Connexion réussie au compte f.manager@example.org après 212 échecs depuis 203.0.113.45
QUAND : 2026-03-04 02:14 UTC (premier échec à 01:52 UTC)
PÉRIMÈTRE À CE STADE : 1 compte confirmé ; 14 autres comptes ciblés, aucune autre réussite constatée
ACTIONS MENÉES : sessions révoquées, réinitialisation du mot de passe demandée au centre de services (ticket INC-4471)
BESOIN : autorisation de bloquer 203.0.113.0/24 en périphérie ; contact du responsable financier
```

Remarquez la structure : sévérité, faits, périmètre, actions, et la décision précise dont vous avez besoin. Un décideur ne devrait jamais avoir à lire un mur de lignes de journal pour comprendre ce que vous lui demandez.
