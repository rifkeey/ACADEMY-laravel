Une bonne architecture de sécurité survit aux changements de personnel, aux audits et aux incidents parce qu’elle est écrite. La documentation transforme votre conception en quelque chose que d’autres peuvent construire, exploiter, tester et défendre.

## Le dossier d’architecture

| Document | Contenu |
| --- | --- |
| **État actuel** | Zones, adressage, équipements, flux clés et faiblesses connues, avec un schéma |
| **Architecture cible** | Zones et frontières de confiance cibles, flux nécessaires à chaque zone, mesures à chaque frontière, avec un schéma |
| **Justification de la segmentation** | Pourquoi chaque zone existe et pourquoi chaque flux autorisé est nécessaire (responsable, objectif métier) |
| **Politique de sécurité réseau** | Les règles que tout le monde suit : refus par défaut, moindre privilège, journalisation des règles aux frontières, contrôle des changements, périodicité de revue des règles, accès distants et des prestataires, standards de durcissement des équipements |
| **Plan de surveillance** | Quels journaux de quels équipements vont au SIEM, durée de conservation, détections importantes pour cette conception, qui répond (modules 5 et 9) |
| **Registre des risques et feuille de route** | Leçon 4 |
| **Liste de validation** | Les tests qui prouvent que la conception fonctionne (leçon 3) |
| **Registres de décisions** | Les choix majeurs, les options et les raisons |

## Des schémas utiles

- Représentez les **zones par des cadres** et les **frontières de confiance** clairement ; indiquez les plages d’adresses.
- Représentez les **flux par des flèches**, avec le port et l’objectif pour les plus importants.
- Indiquez où se trouvent les **mesures** : pare-feu, NAC, capteurs IDS, proxys.
- Portez un numéro de version et une date sur chaque schéma ; les schémas obsolètes provoquent des erreurs.

## Rattacher la conception à un référentiel

Rattacher la conception à un référentiel reconnu aide les auditeurs et le conseil d’administration à voir la couverture. Le NIST CSF 2.0 comporte six fonctions :

| Fonction | Exemples réseau |
| --- | --- |
| **Gouverner** (Govern) | Politique de sécurité réseau, rôles, acceptation des risques, règles d’accès des fournisseurs (prestataires) |
| **Identifier** (Identify) | Inventaire des actifs et des flux, registre des risques |
| **Protéger** (Protect) | Segmentation, règles de moindre privilège, NAC, durcissement, accès distant sécurisé |
| **Détecter** (Detect) | Journalisation, IDS, corrélation dans le SIEM |
| **Répondre** (Respond) | Procédures d’incident, options de confinement (module 9) |
| **Rétablir** (Recover) | Sauvegardes des configurations, redondance, restauration testée |

Les CIS Controls v8.1 (en particulier les contrôles 12 et 13, sur la gestion de l’infrastructure réseau et sur la surveillance et la défense du réseau) offrent une liste de contrôle plus détaillée.

## Le faire vivre

- Désignez un **responsable** du dossier d’architecture.
- Mettez-le à jour dans le cadre du contrôle des changements : aucun changement réseau important n’est terminé tant que les documents ne sont pas à jour.
- Revoyez-le entièrement une fois par an, et après chaque incident majeur.

:::takeaway
Produisez un dossier d’architecture : état actuel et cible avec schémas, justification de la segmentation, politique de sécurité réseau, plan de surveillance, registre des risques et feuille de route, liste de validation et registres de décisions. Rattachez-le au CSF 2.0 (et aux CIS Controls pour le détail), donnez-lui un responsable, et mettez-le à jour à chaque changement important.
:::
