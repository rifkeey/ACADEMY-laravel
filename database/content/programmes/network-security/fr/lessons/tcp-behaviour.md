L’essentiel du trafic applicatif de Kanem passe par TCP (RFC 9293). Savoir à quoi ressemble un TCP en bonne santé permet de distinguer un réseau lent d’un serveur lent, et une connexion bloquée d’une connexion refusée.

## Une connexion en bonne santé

| Trame | Sens | Drapeaux | Signification |
| --- | --- | --- | --- |
| 1 | client → serveur | SYN | « Peut-on se parler ? » |
| 2 | serveur → client | SYN, ACK | « Oui » |
| 3 | client → serveur | ACK | Connexion établie |
| 4… | les deux | PSH, ACK / ACK | Données et accusés de réception |
| dernière | les deux | FIN, ACK | Fermeture ordonnée |

**Temps aller-retour (RTT) :** le temps entre le SYN du client et le SYN-ACK du serveur, mesuré côté client, est une bonne estimation du délai réseau. Au sein du réseau de Kanem, il devrait être de quelques millisecondes ; d’Abuja à Lagos par le réseau étendu, peut-être quelques dizaines de millisecondes.

## Numéros de séquence et accusés de réception

Chaque côté numérote les octets qu’il envoie. Le **numéro d’ACK** du destinataire signifie « j’ai tout reçu jusqu’ici ; envoie l’octet suivant ». Wireshark affiche des numéros **relatifs** commençant à 0 pour rendre cela lisible. La valeur de **fenêtre** indique la quantité de données supplémentaires que le destinataire peut accepter.

## Les schémas et ce qu’ils signifient généralement

| Ce que vous voyez | Signification habituelle | Vérification suivante |
| --- | --- | --- |
| Un SYN, puis le SYN retransmis après environ 1 s, 2 s, 4 s…, sans réponse | Rien n’a répondu : un pare-feu qui rejette en silence, une mauvaise adresse ou un hôte éteint | Journaux du pare-feu et routes pour cette destination (module 3) |
| Un SYN auquel répond un **RST** | L’hôte est joignable mais rien n’écoute sur ce port, ou un pare-feu refuse activement | État du service ; règles de refus actif du pare-feu |
| **Retransmission** d’un segment de données | Le segment ou son ACK a été perdu ou retardé | Les compter ; voir où se produit la perte (capturer aux deux extrémités) |
| **ACK dupliqués** suivis d’une retransmission rapide | Le destinataire a reçu des données ultérieures mais a manqué un segment | Perte de paquets sur le chemin |
| Long intervalle après la requête du client, avant les premières données du serveur | Le **serveur ou l’application** est lent, pas le réseau | Performances de l’application et de la base de données |
| **Fenêtre nulle** annoncée par un côté | Ce côté ne suit plus et a demandé à l’émetteur de faire une pause | Charge de l’hôte destinataire |
| RST au milieu d’une session | L’un des côtés, ou un équipement intermédiaire, l’a interrompue | Quel côté l’a envoyé ; délais d’inactivité des pare-feu ou des répartiteurs de charge |

:::tip Réseau ou application ?
Si la négociation est rapide mais que le serveur met plusieurs secondes à envoyer sa première réponse, le réseau va généralement bien et le délai vient de l’application. Si la négociation elle-même est lente, ou s’il y a beaucoup de retransmissions, regardez du côté du réseau.
:::

## Le délai de retransmission

Quand un segment n’est pas acquitté, TCP attend l’expiration de son délai de retransmission avant de le renvoyer, et double l’attente à chaque fois (la RFC 6298 décrit le calcul). C’est pourquoi un seul paquet perdu peut ajouter une pause perceptible, et pourquoi des tentatives de connexion sans réponse font patienter une page web plusieurs secondes avant qu’elle n’abandonne.

:::takeaway
Mesurez le RTT à partir de la négociation, lisez les numéros de séquence et d’accusé de réception pour suivre les données, et reconnaissez les schémas : des SYN sans réponse signifient que quelque chose rejette le trafic, un RST signifie un refus, les retransmissions et les ACK dupliqués signifient des pertes, et un intervalle après la requête signifie une application lente.
:::
