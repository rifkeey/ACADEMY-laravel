## Ce qui n’est pas écrit n’a pas eu lieu

Les notes de dossier sont le compte rendu d’une investigation. Elles permettent à un autre analyste de prendre le relais au changement d’équipe, servent d’appui aux procédures juridiques ou disciplinaires et alimentent le retour d’expérience. Rédigez-les **au fil du travail**, pas de mémoire à la fin.

## Le contenu de bonnes notes de dossier

- **En-tête** : identifiant du dossier, sévérité, statut, responsable, entités concernées, fuseau horaire utilisé.
- **Journal chronologique des actions de l’analyste** : ce que vous avez interrogé ou fait, quand, et ce que vous avez trouvé.
- **Registre des preuves** : chaque élément (journaux exportés, captures d’écran, échantillons de fichiers) avec son lieu de stockage, son empreinte le cas échéant et la personne qui l’a collecté.
- **Évaluation en cours** : un court paragraphe lisible en 30 secondes.
- **Questions ouvertes et prochaines étapes**.

```text
DOSSIER INC-4471 | Sévérité : Élevée | Responsable : A. Musa | Heures : UTC
Évaluation (mise à jour à 03:10) : une pulvérisation de mots de passe depuis 203.0.113.45 a compromis f.manager.
  L’attaquant a créé une règle de transfert vers l’extérieur et téléchargé 312 fichiers du service Finance.
  Aucune autre connexion réussie depuis la source. Sessions révoquées à 02:58.
En suspens : l’attaquant a-t-il enregistré une méthode MFA ? Des e-mails ont-ils été transférés ? Faut-il informer le DPO ?
```

## La préservation des preuves

- Exportez les événements bruts qui étayent chaque conclusion, pas seulement des captures d’écran. Les captures d’écran sont difficiles à rechercher et faciles à contester.
- Consignez les **empreintes** (SHA-256) des fichiers exportés pour pouvoir démontrer leur intégrité plus tard.
- Conservez les preuves dans un emplacement dont l’accès est restreint et journalisé.
- Tenez un registre de **chaîne de possession** lorsque les preuves peuvent servir dans une procédure judiciaire : qui les a manipulées, quand et pourquoi.

## La passation

C’est à la passation entre équipes que les investigations s’enlisent. Une note de passation doit permettre à l’analyste suivant de reprendre en moins de cinq minutes :

1. L’évaluation en cours, en deux phrases.
2. Ce qui est contenu et ce qui ne l’est pas.
3. Ce qui est en cours (requêtes lancées, personnes contactées, autorisations attendues).
4. Les trois prochaines actions, dans l’ordre.
5. Qui appeler si la situation s’aggrave.

## Le style d’écriture

- Employez un langage simple et précis : « le compte s’est connecté depuis 203.0.113.45 », et non « le compte a été piraté ».
- Distinguez les faits **observés** des conclusions **évaluées** : « Nous estimons avec un degré de confiance modéré que… ».
- Évitez les spéculations sur l’attribution, sauf si vous disposez de preuves solides et que cela compte pour la réponse.
- N’incluez jamais de mots de passe, de numéros complets de carte bancaire ni de données personnelles inutiles dans les notes de dossier.
