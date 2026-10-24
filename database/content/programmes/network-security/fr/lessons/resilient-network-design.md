Un réseau sûr doit aussi continuer de fonctionner. Les pannes, les changements ratés et les attaques mettent tous à l’épreuve la même chose : la capacité des services critiques à survivre à la perte d’un composant.

## Trouver les points de défaillance uniques

| Composant | Kanem aujourd’hui | Conception résiliente |
| --- | --- | --- |
| Lien Internet | Un seul fournisseur | Deux fournisseurs, des chemins physiques différents, un basculement testé |
| Pare-feu périmétrique | Un seul FW-HQ-01 | Une paire en haute disponibilité, configuration synchronisée |
| Commutation de cœur | Un seul commutateur de cœur | Un cœur redondant avec agrégation de liens |
| Liaisons des agences | Un seul tunnel VPN chacune | Des tunnels principal et de secours, idéalement chez des fournisseurs différents |
| DNS et DHCP | Un seul serveur pour chacun | Deux de chaque (module 7) |
| Services d’identité | Un seul contrôleur de domaine par site | Au moins deux, sur des sites différents |
| Accès d’administration | Uniquement depuis la zone d’administration du siège | Un chemin hors bande sécurisé pour les moments où le réseau principal est indisponible |

## Fixer des objectifs de rétablissement

Comme au module 10 du programme Cloud, convenez avec l’activité d’un **RTO** (délai de rétablissement) et d’un **RPO** (perte de données acceptable) pour chaque service critique, puis concevez de façon à les respecter. La NIST SP 800-34 Rev. 1 décrit l’analyse d’impact sur l’activité et la planification de la continuité.

## La résilience face aux attaques

La NIST SP 800-160 Vol. 2 Rev. 1 décrit la cyberrésilience comme la capacité à **anticiper les attaques, à y résister, à s’en remettre et à s’y adapter**. En termes de réseau :

| Objectif | Pratique réseau |
| --- | --- |
| **Anticiper** | Modélisation des menaces ; surveillance ; procédures d’incident testées (module 9) |
| **Résister** | Une segmentation qui empêche une zone compromise d’entraîner les autres (module 4) ; limitation du débit et protection contre les DDoS pour les services exposés à Internet |
| **Se rétablir** | Sauvegardes de la configuration de chaque équipement, stockées hors de l’équipement ; procédures de reconstruction documentées ; matériel de rechange ou contrats |
| **S’adapter** | Des revues des enseignements qui modifient la conception |

## La gestion des configurations

- Sauvegardez automatiquement les configurations des équipements après chaque changement, et conservez les versions.
- Utilisez des modèles et un standard de configuration par type d’équipement (leçon 3).
- Effectuez les changements via le contrôle des changements, avec un retour arrière testé (module 3).
- Comparez régulièrement les configurations en cours avec la référence approuvée, et alertez en cas de dérive.

:::warning Un basculement jamais testé n’est pas un basculement
Planifiez des tests de basculement pour les liens, les pare-feu et le DNS au moins une fois par an, dans une fenêtre de maintenance, avec un plan de retour arrière.
:::

:::takeaway
Supprimez les points de défaillance uniques dans les liens, les pare-feu, la commutation de cœur, le DNS, le DHCP et l’identité ; fixez un RTO et un RPO par service ; concevez pour anticiper, résister, se rétablir et s’adapter ; sauvegardez chaque configuration d’équipement et comparez-la à une référence ; et testez régulièrement le basculement.
:::
