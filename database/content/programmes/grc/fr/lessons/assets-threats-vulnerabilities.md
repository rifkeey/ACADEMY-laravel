## Commencer par ce qui compte

On ne peut pas apprécier le risque pesant sur des éléments dont on ignore l’existence. Une appréciation des risques commence par l’identification des **actifs** du périmètre et par la détermination de leur importance pour l’activité.

## Identifier et classer les actifs

Les actifs ne se limitent pas au matériel :

- **Informations** : dossiers clients, données financières, propriété intellectuelle, identifiants.
- **Systèmes et services** : système bancaire central, messagerie, paie, application mobile, environnements cloud.
- **Personnes et savoir-faire** : l’unique ingénieur qui comprend l’ancien système.
- **Locaux et fournisseurs** : bureaux, centres de données, informatique externalisée.
- **Actifs immatériels** : réputation, agréments d’exercice.

Regroupez les actifs de manière pertinente : un registre de 4 000 ordinateurs portables individuels est moins utile pour l’appréciation des risques que « ordinateurs portables du personnel (≈ 400, gérés, chiffrés) ». Ensuite, **classez** les informations selon le préjudice que causerait leur divulgation, leur modification ou leur perte. Un schéma à quatre niveaux est courant : *Public*, *Interne*, *Confidentiel*, *Restreint*. Chaque actif a un propriétaire qui décide de sa classification et approuve les accès.

## Les menaces

Une menace est tout ce qui pourrait causer un préjudice. Envisagez plusieurs sources :

| Source | Exemples |
| --- | --- |
| Malveillante | Groupes criminels (rançongiciel, fraude), collaborateurs malveillants, hacktivistes |
| Accidentelle | Erreurs du personnel, mauvaise configuration, données envoyées au mauvais destinataire |
| Structurelle | Pannes matérielles, défauts logiciels, interruptions chez un fournisseur |
| Environnementale | Inondation, incendie, coupure de courant prolongée |

Utilisez le renseignement sur les menaces propre à votre secteur et à votre région. Une fintech nigériane est confrontée à une ingénierie sociale persistante et à la fraude par échange de carte SIM ; un hôpital, à des rançongiciels qui visent la disponibilité.

## Vulnérabilités et conditions favorisantes

Les vulnérabilités sont des faiblesses qu’une menace peut exploiter : correctifs manquants, absence de MFA, personnel non formé, points de défaillance uniques, processus flous. La publication NIST SP 800-30 décrit aussi des **conditions favorisantes** (*predisposing conditions*), des caractéristiques de l’environnement qui rendent l’exploitation plus probable, comme le fait de dépendre d’une seule liaison Internet ou d’opérer dans une région sujette à de fréquentes coupures de courant.

## Rédiger des scénarios de risque

Combinez ces éléments en un **scénario** qui peut être coté :

> *Un groupe de rançongiciel (menace) s’introduit grâce à un e-mail d’hameçonnage envoyé à un collaborateur sans MFA (vulnérabilité), chiffre le serveur de fichiers et les sauvegardes qui y sont connectées (actif), et paralyse le traitement des prêts pendant une semaine (impact).*

Un bon scénario est assez précis pour être coté et pour choisir des mesures de sécurité. « Cyberattaque » n’est pas un scénario de risque ; c’est une catégorie. Visez dix à trente scénarios bien rédigés plutôt que des centaines de scénarios vagues.

## Recueillir les informations

Combinez l’examen documentaire (schémas réseau, audits précédents, historique des incidents), des entretiens avec les propriétaires d’actifs, des données techniques (scans de vulnérabilités, rapports de configuration) et des ateliers où le personnel métier et technique cote ensemble les scénarios. Consignez la source de chaque jugement pour que l’appréciation puisse être revue plus tard.
