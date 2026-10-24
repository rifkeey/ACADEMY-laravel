## Ce qu’est le CSF 2.0

Le NIST Cybersecurity Framework (CSF) 2.0, publié en février 2024, est un référentiel volontaire, fondé sur les résultats, pour gérer les risques de cybersécurité. Il décrit *à quoi ressemble* une bonne situation plutôt que de prescrire *comment* y parvenir, ce qui le rend utilisable par des organisations de toute taille, de tout secteur et de tout pays.

## Les six fonctions

1. **Gouverner** (*Govern*, GV) : établir et suivre la stratégie, les attentes et la politique. Nouvelle en 2.0.
2. **Identifier** (*Identify*, ID) : comprendre les actifs, les fournisseurs et les risques.
3. **Protéger** (*Protect*, PR) : les mesures de protection comme la gestion des identités, la formation, la sécurité des données et le durcissement des plateformes.
4. **Détecter** (*Detect*, DE) : repérer et analyser les attaques possibles.
5. **Répondre** (*Respond*, RS) : agir face aux incidents détectés.
6. **Rétablir** (*Recover*, RC) : restaurer les actifs et les activités.

Chaque fonction contient des **catégories** et des **sous-catégories**, qui sont des énoncés de résultats précis. Le NIST publie aussi des exemples de mise en œuvre (*Implementation Examples*) et des références informatives (*Informative References*) qui relient les résultats à d’autres référentiels.

## Les profils

Un **profil actuel** décrit les résultats que vous atteignez aujourd’hui. Un **profil cible** décrit les résultats dont vous avez besoin, compte tenu de vos risques, de vos obligations et de votre appétence. La différence constitue votre écart, et la base d’un plan d’amélioration.

```text
Résultat : PR.AA (gestion des identités, authentification et contrôle d’accès)
Actuel :   MFA pour la messagerie uniquement ; comptes administrateurs partagés sur le système bancaire central
Cible :    MFA pour tous les accès distants et à privilèges ; comptes administrateurs nominatifs
Priorité : élevée (contribue au traitement du risque R1, le mieux coté)
```

## Les niveaux (*Tiers*)

Les niveaux du CSF (*Partial*, *Risk Informed*, *Repeatable*, *Adaptive*, soit partiel, éclairé par les risques, reproductible, adaptatif) décrivent la rigueur de vos pratiques de gestion des risques. Ce n’est pas un score de maturité à maximiser. Choisissez le niveau adapté à votre risque.

## Hiérarchiser

Classez les écarts selon les risques qu’ils traitent, et non selon le nombre de sous-catégories qu’ils couvrent. Cinq améliorations bien choisies, liées aux principaux risques, valent plus que cinquante améliorations cosmétiques.
