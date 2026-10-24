## Les éléments de base

- **Actif** : tout ce qui a de la valeur : informations, systèmes, personnes, réputation.
- **Menace** : une cause potentielle de préjudice (un groupe criminel, une inondation, un collaborateur négligent).
- **Vulnérabilité** : une faiblesse qu’une menace pourrait exploiter (absence de MFA, sauvegarde jamais testée).
- **Impact** : le préjudice si le risque se réalise.
- **Vraisemblance** : la probabilité qu’il se réalise sur une période définie.

Un énoncé de risque les relie : *« Comme le personnel partage le mot de passe d’administration du système bancaire central (vulnérabilité), un attaquant par hameçonnage (menace) pourrait obtenir un accès administrateur aux comptes clients (actif), causant des pertes par fraude et des sanctions réglementaires (impact). »*

## Coter le risque

De nombreuses organisations utilisent une matrice 5×5 et multiplient la vraisemblance par l’impact pour obtenir un score de 1 à 25. C’est simple et largement compris, mais n’oubliez pas que l’échelle est ordinale : un score de 20 n’est pas « deux fois plus risqué » qu’un score de 10. Définissez chaque niveau en termes simples et mesurables, en argent, en nombre de clients ou en conséquences réglementaires, pour que deux évaluateurs aboutissent à des réponses proches. La publication NIST SP 800-30 Rev. 1 décrit en détail les approches qualitative, semi-quantitative et quantitative.

## Risque inhérent et risque résiduel

Le **risque inhérent** est la cotation avant prise en compte des mesures de sécurité existantes. Le **risque résiduel** est la cotation après ces mesures. L’écart montre ce que valent vos mesures, et il n’est réel que si leur efficacité est prouvée.

## Les options de traitement

| Option | Signification | Exemple |
| --- | --- | --- |
| Réduire (*treat*) | Diminuer la vraisemblance ou l’impact par des mesures de sécurité | Imposer la MFA |
| Transférer | Reporter l’impact financier sur un tiers | Assurance cyber, responsabilité contractuelle |
| Éviter | Arrêter l’activité | Retirer un système qui n’est plus pris en charge |
| Accepter | Tolérer en connaissance de cause, dans les limites de l’appétence | Risques mineurs et de faible valeur |

L’acceptation doit être une décision consignée, prise par une personne qui en a l’autorité, avec une date de revue. Ce n’est pas la même chose qu’ignorer un risque. Le transfert déplace de l’argent, pas la responsabilité : une violation chez votre fournisseur concerne toujours les données de vos clients.
