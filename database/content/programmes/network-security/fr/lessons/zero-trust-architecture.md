La segmentation suppose encore, dans une certaine mesure, qu’être sur le bon réseau signifie être autorisé. Le **zero trust** supprime cette hypothèse : aucun utilisateur, équipement ou charge de travail n’est considéré comme fiable en raison de l’endroit *où* il se trouve ; chaque accès est décidé en fonction de *qui* et de *ce* qu’il est, à cet instant.

## Ce que dit la NIST SP 800-207

La NIST SP 800-207, *Zero Trust Architecture*, décrit le zero trust comme un ensemble de principes plutôt que comme un produit. Ses principes fondamentaux comprennent, en résumé :

- Toutes les sources de données et tous les services informatiques sont traités comme des **ressources**.
- Toutes les communications sont sécurisées **quel que soit l’emplacement réseau**.
- L’accès est accordé **par session**, ressource par ressource.
- L’accès est décidé par une **politique dynamique** : identité, état de l’équipement, comportement et autres attributs.
- L’organisation **surveille l’intégrité et le niveau de sécurité** de tous ses actifs.
- L’authentification et l’autorisation sont **dynamiques et strictement appliquées** avant tout accès.
- L’organisation **collecte des informations** sur ses actifs, le trafic réseau et les demandes d’accès, et s’en sert pour améliorer la sécurité.

## L’architecture logique

```text
   user + device ──request──► PEP (Policy Enforcement Point) ───► resource (application, data)
                                   ▲  allow / deny
                                   │
                         PA (Policy Administrator)
                                   ▲
                         PE (Policy Engine)  ◄── identity provider, device management,
                                                 threat intelligence, logs, data classification
```

- Le **moteur de politique** (PE, *Policy Engine*) prend la décision, à partir de nombreux signaux (fournisseur d’identité, gestion des équipements, renseignement sur les menaces, journaux, classification des données).
- L’**administrateur de politique** (PA, *Policy Administrator*) indique au point d’application d’ouvrir ou de fermer la session.
- Le **point d’application de la politique** (PEP, *Policy Enforcement Point*) se place devant la ressource : une passerelle, un proxy, un agent ou l’application elle-même.

## En pratique : ce qui change

| Approche traditionnelle | Zero trust |
| --- | --- |
| Le VPN place l’utilisateur « sur le réseau » ; il peut alors atteindre de nombreux systèmes | L’**accès réseau zero trust (ZTNA)** ne donne à l’utilisateur accès qu’à des applications précises, après vérification de l’identité et de l’équipement |
| Confiance accordée une fois, à la connexion | Réévaluation continue (l’équipement n’est plus conforme → accès réduit) |
| Règles de pare-feu par plage d’IP | Politiques par identité, équipement et application |
| Trafic interne souvent non chiffré | Chiffrer et authentifier partout, y compris à l’intérieur |

## Un parcours de maturité, pas un interrupteur

Le **Zero Trust Maturity Model** de la CISA (version 2.0) organise le travail en cinq piliers (**identité, équipements, réseaux, applications et charges de travail, données**), plus des capacités transversales (visibilité et analyse, automatisation et orchestration, gouvernance), et quatre stades : **traditionnel, initial, avancé, optimal**. C’est un outil de planification utile : peu d’organisations sont « optimales » où que ce soit, et les progrès se font pilier par pilier.

Une séquence réaliste pour Kanem :

1. **L’identité d’abord** : authentification unique et MFA résistante à l’hameçonnage pour tout le personnel, la plus forte pour les administrateurs.
2. **La santé des équipements** : seuls les équipements gérés et conformes peuvent atteindre les applications sensibles.
3. **L’accès aux applications** : remplacer le « VPN complet » des prestataires par un accès au niveau des applications (ZTNA) aux systèmes dont ils ont besoin.
4. **La segmentation et la microsegmentation** (leçons 2 et 3), pour limiter ce qu’une identité ou un équipement peut atteindre si la politique échoue.
5. **La visibilité** : chaque décision d’accès est journalisée et réinjectée dans la détection.

:::note Zero trust et segmentation vont ensemble
Le zero trust ne rend pas la segmentation réseau obsolète. Les recommandations du NIST elles-mêmes considèrent les mesures réseau comme l’une des couches d’application. Si une identité est compromise, la segmentation limite encore ce qui peut être atteint ; si une mesure réseau échoue, les vérifications d’identité s’appliquent toujours.
:::

:::reveal Exercice : la demande du prestataire
Un prestataire informatique doit assurer le support de l’application des prêts pendant trois mois. La pratique actuelle consiste à donner aux prestataires un compte VPN complet. À quoi ressemblerait une approche zero trust ?

*Réponse :* un compte nominatif dans le fournisseur d’identité de Kanem avec MFA, un accès accordé uniquement à l’application des prêts (via une passerelle ZTNA ou le contrôle d’accès propre à l’application), uniquement depuis un équipement conforme ou un bureau virtuel géré, limité à la durée du contrat, journalisé et revu à la fin. Aucun accès au niveau réseau à quoi que ce soit d’autre.
:::

:::takeaway
Le zero trust signifie aucune confiance implicite fondée sur l’emplacement réseau : chaque accès est décidé par session à partir de l’identité, de l’équipement et du contexte, par un moteur de politique, et appliqué devant la ressource. Abordez-le comme un parcours couvrant l’identité, les équipements, les réseaux, les applications et les données, et gardez la segmentation comme deuxième ligne de défense.
:::
