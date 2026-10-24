## Définir le domaine d’application du SMSI

Avant de choisir des mesures de sécurité, une organisation doit comprendre son **contexte** : sa raison d’être, les enjeux internes et externes qui influent sur la sécurité de l’information, ainsi que les **parties intéressées** (clients, régulateurs, personnel, fournisseurs, actionnaires) et leurs exigences. Le domaine d’application en découle.

Un bon énoncé du domaine d’application précise :

- les **activités et services** couverts (par exemple, « services de traitement de la paie pour les entreprises clientes ») ;
- les **sites** (bureaux, centres de données, télétravail) ;
- les **informations, systèmes et équipes** concernés ;
- les **interfaces et dépendances** avec ce qui est hors du domaine, comme le service informatique d’une société mère ou un hébergeur cloud.

### Les pièges du domaine d’application

| Piège | Pourquoi c’est important |
| --- | --- |
| Exclure la partie qui intéresse les clients | Un certificat couvrant l’administration du siège ne dit rien de la plateforme de paie hébergée. |
| Ignorer les interfaces | Si une équipe hors domaine administre des serveurs inclus dans le domaine, le risque existe toujours et doit être géré. |
| Un domaine qui ne correspond pas aux arguments commerciaux | Dire aux clients « nous sommes certifiés ISO » alors qu’un seul site est couvert est trompeur. |

## Appréciation et traitement des risques

Dans le domaine d’application, l’organisation suit un processus défini d’appréciation des risques liés à la sécurité de l’information et produit un **plan de traitement des risques**. Les mesures de sécurité sont choisies pour traiter les risques. L’organisation compare ensuite les mesures retenues à l’ensemble de référence de l’annexe A pour s’assurer que rien de nécessaire n’a été oublié. L’annexe A sert de vérification croisée, pas de catalogue.

## La déclaration d’applicabilité

La DdA fait le lien entre le traitement des risques et les mesures qu’un auditeur testera. Avec nos mots, pour chaque mesure de l’ensemble de référence, elle consigne :

- si la mesure est **retenue** (applicable) ou **exclue** ;
- la **justification de son inclusion** (généralement les risques qu’elle traite, ou une exigence légale ou contractuelle) ;
- la **justification de son exclusion**, qui doit être crédible au regard du domaine d’application ;
- son **état de mise en œuvre** (mise en œuvre, partiellement mise en œuvre, prévue).

Beaucoup d’organisations y ajoutent aussi le responsable de la mesure et un renvoi vers les preuves, ce qui accélère les audits.

### Examiner une DdA

Lorsque vous examinez une DdA, demandez-vous :

1. **Les exclusions ont-elles un sens pour ce domaine d’application ?** Exclure les mesures de développement sécurisé est raisonnable pour une entreprise qui ne développe aucun logiciel ; ce ne l’est pas si le domaine comprend une équipe de développement interne.
2. **Les justifications renvoient-elles à des risques ?** « Bonne pratique » est faible ; « Traite R4 (violation chez un fournisseur) et le contrat C-12 » est solide.
3. **L’état correspond-il aux preuves ?** Une mesure indiquée « mise en œuvre » sans preuve, ou contredite par les notes d’entretien, donnera probablement lieu à un constat.
4. **Est-elle à jour ?** La version, le responsable et la date d’approbation doivent être récents et cohérents avec le plan de traitement des risques.

Les exclusions ne sont pas fautives en soi. Le problème, c’est une exclusion qui laisse un risque réel sans traitement ou qui contredit le domaine d’application.
