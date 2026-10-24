:::scenario Samedi matin
Samedi à 09:00, la revue hebdomadaire du SOC repère un gros transfert de données nocturne depuis un serveur de la DMZ vers une adresse Internet inconnue. Les journaux du pare-feu ne montrent absolument rien pour ce serveur. Ce module explique comment les preuves réseau sont collectées, corrélées et exploitées, et pourquoi des lacunes comme celle-ci comptent. Dans le lab, vous enquêtez sur cette nuit-là.
:::

La surveillance de la sécurité réseau rassemble des preuves venant de nombreux équipements. Chacun répond à des questions différentes, et chacun a ses angles morts.

## Les principales sources de preuves réseau

| Source | Répond à | Angles morts |
| --- | --- | --- |
| **Journaux du pare-feu** (module 3) | Quelle règle a autorisé ou refusé une connexion | Uniquement les règles dont la **journalisation est activée** ; le trafic autorisé n’est souvent pas journalisé pour économiser de l’espace |
| **Enregistrements de flux** (NetFlow, IPFIX, journaux de flux cloud) | Qui a parlé à qui, sur quel port, pendant combien de temps, combien d’octets dans chaque sens | Aucun contenu ; les flux échantillonnés peuvent manquer les connexions courtes |
| **Alertes IDS/IPS** (module 5) | Les motifs connus et les violations de politique | Uniquement ce que reconnaissent les règles ; le contenu chiffré |
| **Journaux DNS** (module 7) | Quels noms chaque hôte a résolus | Les hôtes qui contournent les résolveurs internes |
| **Journaux DHCP et VPN** | Quel équipement ou quel utilisateur avait une adresse à un moment donné | Les adresses statiques |
| **Journaux du proxy** (module 7) | Quel utilisateur a visité quel site | Le trafic qui n’utilise pas le proxy |
| **Captures de paquets** (module 8) | Exactement ce qui est passé sur le fil | Fenêtres de temps courtes ; coût du stockage ; vie privée |

## Les enregistrements de flux en détail

Un enregistrement de flux résume une conversation : adresse et port source et de destination, protocole, heure de début, durée, et paquets et octets dans chaque sens. Les routeurs, commutateurs et pare-feu les exportent à l’aide de protocoles comme **IPFIX** (RFC 7011) ou NetFlow ; les fournisseurs de cloud proposent des journaux de flux (programme Cloud, module 3).

Les données de flux coûtent peu à conserver pendant des mois : elles sont donc souvent les seules preuves restantes quand un incident est découvert tardivement. Elles excellent en particulier pour :

- montrer le **volume** : un serveur qui envoie normalement des mégaoctets et se met soudain à envoyer des gigaoctets ;
- montrer les **nouvelles conversations** : un hôte de la DMZ qui communique avec la zone des serveurs sur un port qu’il n’avait jamais utilisé ;
- combler les lacunes là où les règles du pare-feu ne journalisaient pas.

## Rendre les preuves exploitables

| Pratique | Pourquoi |
| --- | --- |
| **Synchronisation de l’heure** (NTP) sur chaque équipement, et UTC dans les journaux | Les événements de sources différentes peuvent être alignés |
| **Journalisation de chaque règle d’autorisation** qui franchit une frontière de confiance | Sinon, les connexions les plus importantes ne laissent aucune trace dans le pare-feu |
| **Collecte centralisée** dans un SIEM ou une plateforme de journaux | Un seul endroit où chercher ; les journaux survivent si un équipement est compromis |
| **Durée de conservation** suffisante pour une découverte tardive (souvent 12 mois pour les flux et les journaux du pare-feu) | De nombreux incidents sont découverts des semaines plus tard |
| **Inventaire des actifs** avec responsables et zones | Transforme une adresse IP en un système et en une personne à appeler |

Le guide de la CISA et de ses agences partenaires *Best Practices for Event Logging and Threat Detection* (2024) et la NIST SP 800-92 décrivent plus en détail les programmes de gestion des journaux.

:::takeaway
Combinez les preuves du pare-feu, des flux, de l’IDS, du DNS, du DHCP, du VPN et du proxy, en connaissant les angles morts de chacune. Les enregistrements de flux sont peu coûteux, durables et souvent décisifs. Synchronisez les horloges, journalisez chaque règle d’autorisation qui franchit une frontière, collectez de façon centralisée et conservez les preuves assez longtemps pour une découverte tardive.
:::
