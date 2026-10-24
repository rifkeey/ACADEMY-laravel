:::scenario Un équipement qui n’appartient à personne
Lors d’une visite d’agence, le responsable informatique d’Abuja trouve un petit mini-PC branché sur une prise réseau libre de la salle de réunion. Il a une adresse IP dans la plage du personnel et communique avec Internet depuis trois semaines. La politique du pare-feu est excellente, mais l’équipement se trouvait déjà *à l’intérieur* de la zone users.
:::

Les pare-feu contrôlent le trafic **entre** les zones. Le **contrôle d’accès au réseau** (NAC, *Network Access Control*) décide si un équipement est autorisé à **entrer** dans une zone, et dans laquelle. Sans lui, quiconque peut brancher un câble ou deviner un mot de passe Wi-Fi hérite de toute la confiance accordée à ce segment de réseau.

## IEEE 802.1X : le contrôle d’accès par port

IEEE 802.1X (édition actuelle 802.1X-2020) est la norme qui authentifie les équipements avant qu’un port de commutateur ou une connexion Wi-Fi ne transporte leur trafic. Elle définit trois rôles :

| Rôle | Ce que c’est | Chez Kanem |
| --- | --- | --- |
| **Supplicant** (demandeur) | Le logiciel de l’équipement qui prouve son identité | Le client 802.1X intégré à Windows et macOS |
| **Authenticator** (authentificateur) | Le commutateur ou le point d’accès sans fil qui garde le port fermé jusqu’à la réussite de l’authentification | Les commutateurs d’accès et les points d’accès Wi-Fi des agences |
| **Serveur d’authentification** | Décide si l’identité est valide et quel accès elle obtient, généralement via RADIUS | Un serveur RADIUS relié à l’annuaire |

Le déroulement, simplifié :

```text
laptop (supplicant)        switch (authenticator)        RADIUS server
      | -- link up --------------> |                              |
      | <- "who are you?" (EAP) -- |                              |
      | -- credentials/cert (EAP) -> | -- forwards (RADIUS) -------> |
      |                            | <-- accept + VLAN 20 ------- |
      | <======= port opens, placed in VLAN 20 (users) ========> |
```

Tant que l’authentification n’a pas réussi, le port ne transporte que les messages d’authentification. En cas d’échec, le port reste fermé ou l’équipement est placé dans un VLAN restreint.

### Quel identifiant ?

- **Les certificats (EAP-TLS)** sont le choix courant le plus solide : chaque équipement géré a son propre certificat, il n’y a rien à hameçonner ni à deviner, et les équipements peuvent être révoqués individuellement. Cela exige une infrastructure de certificats opérationnelle.
- **Un nom d’utilisateur et un mot de passe dans un tunnel TLS (par exemple PEAP)** sont plus faciles à déployer, mais dépendent de la robustesse des mots de passe et de la validation du certificat du serveur par les clients. Si les clients ne le valident pas, des attaquants peuvent installer un faux point d’accès et récolter des identifiants.

## Les équipements incapables de faire du 802.1X

Imprimantes, caméras, lecteurs de badges et équipements anciens n’ont souvent pas de supplicant. La solution de repli habituelle est le **contournement par adresse MAC** (MAB, *MAC Authentication Bypass*) : le commutateur envoie l’adresse MAC de l’équipement au serveur RADIUS en guise d’identité. Les adresses MAC se copient facilement : les équipements en MAB doivent donc être placés dans des segments très restreints (les imprimantes n’atteignent que le serveur d’impression) et surveillés pour détecter tout comportement inhabituel.

## Conformité, quarantaine et placement dynamique

Les produits de NAC vont souvent au-delà de « cette identité est-elle valide ? » :

- **Contrôles de conformité** (*posture*) : l’antivirus fonctionne-t-il, le disque est-il chiffré, le système est-il à jour ? Les équipements non conformes vont dans un **VLAN de quarantaine** qui n’atteint que les services de remédiation.
- **Placement dynamique** : la réponse RADIUS attribue le VLAN ou la liste d’accès, si bien qu’un ordinateur portable des finances et une imprimante branchés sur la même prise se retrouvent dans des zones différentes.
- **Accueil des invités** : les visiteurs s’enregistrent sur un portail et sont placés dans la zone guest, qui n’atteint qu’Internet.

## La place du NAC

Le NAC est une couche, pas toute la réponse :

| Mesure | Question à laquelle elle répond |
| --- | --- |
| NAC (802.1X / MAB) | Cet équipement est-il autorisé sur le réseau, et dans quel segment ? |
| Pare-feu entre les zones | Avec quoi ce segment peut-il communiquer ? |
| Pare-feu d’hôte et protection des postes | Qu’est-ce que cet équipement peut accepter et exécuter ? |
| Accès zero trust (module 4) | Cet utilisateur, sur cet équipement, à cet instant, doit-il atteindre cette application ? |

L’architecture zero trust (NIST SP 800-207) va plus loin et cesse de faire confiance à l’emplacement réseau. Le NAC reste utile dans ce cadre : il tient à l’écart les équipements inconnus et fournit à la politique d’accès de meilleures informations sur chaque équipement.

:::reveal Retour au scénario : qu’est-ce qui aurait arrêté le mini-PC ?
Avec le 802.1X sur la prise de la salle de réunion, le mini-PC n’aurait eu ni supplicant ni certificat : le port serait resté fermé (ou l’aurait placé dans un VLAN de quarantaine sans Internet). Les prises inutilisées doivent aussi être désactivées sur le commutateur. Les règles de sortie du pare-feu auraient constitué la deuxième ligne de défense : trois semaines de connexions vers un hôte Internet inconnu sur un port inhabituel auraient dû produire des refus journalisés et une alerte.
:::

:::takeaway
Les pare-feu décident de ce que les zones peuvent se dire ; le NAC décide de qui entre dans une zone. Utilisez le 802.1X avec certificats pour les équipements gérés, isolez les équipements qui nécessitent le MAB, et considérez le NAC comme une couche d’une défense en profondeur.
:::
