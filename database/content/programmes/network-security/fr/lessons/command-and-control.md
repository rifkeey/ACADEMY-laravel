Une fois qu’un logiciel malveillant s’exécute dans un réseau, il a généralement besoin de **communiquer avec son opérateur** : pour recevoir des instructions et renvoyer des résultats. MITRE ATT&CK appelle cela **Command and Control** (C2, commande et contrôle), tactique TA0011. Comme ce trafic doit quitter votre réseau, le C2 est l’une des meilleures occasions qu’ont les défenseurs de repérer une intrusion, même lorsqu’ils ont manqué son début.

## Comment le C2 cherche à passer inaperçu

| Approche (ATT&CK) | Pourquoi c’est difficile à repérer |
| --- | --- |
| **Protocoles web** (T1071.001) | Le HTTPS vers Internet est le trafic le plus courant qui soit |
| **DNS** (T1071.004) | Le DNS est autorisé presque partout et rarement inspecté (module 7) |
| **Tunnel de protocole** (T1572) | Des données cachées dans un autre protocole autorisé |
| **Résolution dynamique** (T1568) | Des noms de domaine ou des adresses qui changent, si bien que les listes de blocage prennent du retard |
| **Canaux chiffrés** (T1573) | Le contenu ne peut pas être inspecté sans déchiffrement |
| **Services web** (T1102) | Des services cloud et collaboratifs légitimes utilisés comme relais |

## Les schémas qui le trahissent

Même lorsque le contenu est caché, le comportement ne l’est souvent pas :

| Schéma | Ce qu’il faut rechercher | Comportements bénins similaires |
| --- | --- | --- |
| **Balisage** (*beaconing*) | Des connexions vers la même destination à intervalles réguliers, souvent de tailles semblables | Vérificateurs de mises à jour, agents de supervision, messageries instantanées |
| **Destinations nouvelles ou rares** | Des domaines vus pour la première fois récemment, ou contactés par un seul hôte de l’organisation | Nouveaux outils SaaS ou nouveaux partenaires |
| **Anomalies DNS** | De longs sous-domaines d’apparence aléatoire, de gros volumes de requêtes TXT, de nombreuses résolutions en échec (module 7) | Certains services de sécurité et de CDN |
| **Horaires et volumes étranges** | De l’activité la nuit depuis un poste de travail, ou un envoi régulier supérieur à la réception | Sauvegardes, clients de synchronisation |
| **Incohérences** | Un processus autre qu’un navigateur qui fait des requêtes web, des empreintes de clients TLS inhabituelles, des connexions directes à des adresses IP sans requête DNS | Certains logiciels légitimes |
| **Contournement des mesures** | Des connexions qui évitent le proxy ou les résolveurs internes | Équipements mal configurés |

Aucun schéma ne prouve à lui seul l’existence d’un C2. Plusieurs schémas réunis sur un même hôte (par exemple des balises régulières vers un domaine vieux de trois jours, depuis un ordinateur portable, à 02:00, en contournant le proxy) doivent être escaladés (module 7, leçon 5).

## Rendre le C2 difficile et visible

| Mesure | Effet |
| --- | --- |
| **Filtrage en sortie** : refus par défaut du trafic sortant, uniquement via les proxys et les résolveurs (module 3) | Le C2 doit emprunter les routes que vous surveillez |
| **Proxy ou passerelle web sécurisée avec catégorisation** | Les domaines nouvellement enregistrés ou non catégorisés peuvent être bloqués ou signalés |
| **DNS protecteur** et résolveurs uniquement internes (module 7) | Les domaines malveillants connus sont bloqués ; le DNS est journalisé |
| **Inspection TLS sélective** là où elle est justifiée (module 5) | Détection fondée sur le contenu pour les catégories à haut risque |
| **Détection sur les postes** | Relie les connexions réseau au processus qui les a créées |
| **Détections du balisage et des destinations rares** dans le SIEM (module 9) | Trouve les comportements que les signatures manquent |
| **Flux de renseignement sur les menaces** | Bloque l’infrastructure de C2 connue, avec la limite qu’elle change rapidement |

:::takeaway
Le C2 doit quitter votre réseau : surveillez les sorties. Attendez-vous à ce qu’il se cache dans le trafic web et DNS, utilise le chiffrement et change de destinations. Recherchez le balisage, les destinations nouvelles et rares, les anomalies DNS, les horaires et volumes étranges, les incohérences et les contournements, et escaladez lorsque plusieurs signes concordent. Forcez le trafic sortant à passer par des proxys et des résolveurs que vous surveillez.
:::
