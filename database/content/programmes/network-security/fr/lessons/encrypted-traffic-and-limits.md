La majeure partie du trafic actuel est chiffrée, ce qui est une bonne chose pour la vie privée et la sécurité, et une vraie limite pour la détection réseau. Cette leçon porte sur ce que les capteurs réseau peuvent et ne peuvent pas faire dans ce contexte, et sur la façon dont les programmes de détection s’adaptent.

## Ce que le chiffrement cache, et ce qu’il ne cache pas

| Sans déchiffrement, un capteur **ne peut pas** voir | Il **peut** encore voir |
| --- | --- |
| Les URL, le contenu des requêtes et les transferts de fichiers dans le HTTPS | Les adresses IP, les ports, les horaires, la durée et le volume des connexions |
| L’essentiel du contenu des protocoles chiffrés | Les requêtes DNS (sauf si le DNS lui-même est chiffré) |
| | Les métadonnées TLS : version du protocole, nom de serveur demandé (SNI) dans la plupart des cas, détails du certificat qui lui sont visibles |

Des fonctions de protocole plus récentes (DNS chiffré, Encrypted Client Hello) réduisent même les métadonnées. La détection se déplace donc vers :

- **Le comportement dans les métadonnées** : des horaires de connexion réguliers, de type machine ; des connexions vers des destinations nouvelles ou rares ; des volumes inhabituels qui quittent un serveur.
- **Les journaux DNS et du proxy**, lorsque l’organisation contrôle le résolveur ou le proxy.
- **La détection sur les postes**, qui voit les processus et l’activité déchiffrée sur l’équipement.
- **L’inspection TLS sélective** sur un proxy, pour les catégories où elle est justifiée et légale.

## L’inspection TLS : une décision, pas un réglage par défaut

Déchiffrer le trafic permet aux capteurs de voir de nouveau le contenu, mais cela a un coût :

| Avantage | Coût ou risque |
| --- | --- |
| Détection fondée sur le contenu et prévention des fuites de données | Exige l’installation d’un certificat racine de confiance sur chaque équipement géré |
| Visibilité sur les menaces livrées en HTTPS | Casse certaines applications (épinglage de certificat), ajoute de la latence |
| | Questions de vie privée et questions juridiques pour des catégories comme la banque, la santé et la messagerie personnelle |
| | L’équipement d’inspection détient des données déchiffrées et devient une cible de grande valeur |

La plupart des organisations inspectent de façon sélective et documentent les exemptions.

## Les limites de la prévention

Un IPS en coupure qui bloque du trafic légitime provoque une panne. Garde-fous pratiques :

- Démarrez les nouvelles signatures en mode **détection seule** ; activez le blocage une fois qu’elles se sont révélées silencieuses.
- Ne bloquez que sur les signatures **très fiables** ; alertez pour le reste.
- Décidez délibérément, pour chaque équipement en coupure, s’il **s’ouvre ou se ferme en cas de panne**, selon ce qui compte le plus pour ce lien : la disponibilité ou la sécurité.
- Gardez un moyen rapide et documenté de **contourner** une règle qui provoque une panne, sous contrôle des changements.

## La détection est un programme, pas un équipement

Aucun capteur ne voit tout. Un programme de détection mature :

- rattache ses détections aux comportements d’attaquant qui comptent (ATT&CK) et connaît ses lacunes ;
- combine la télémétrie du réseau, des postes, des identités et du cloud ;
- revoit et règle en continu, à l’aide des indicateurs de la leçon 2 ;
- réinjecte les enseignements de chaque incident dans des détections nouvelles ou améliorées.

:::takeaway
Le chiffrement cache le contenu, pas les métadonnées. Détectez par le comportement, le DNS, les postes et une inspection TLS sélective et justifiée. Utilisez le blocage de l’IPS avec prudence (détecter d’abord, ne bloquer que les correspondances très fiables, prévoir les comportements en cas de panne), et traitez la détection comme un programme amélioré en continu sur l’ensemble de la télémétrie.
:::
