:::scenario Lundi, 02:55
Le SIEM de Kanem affiche une rafale de refus du pare-feu provenant de **10.20.7.44**, une adresse de la plage du VPN d’accès à distance, vers la zone des serveurs : des dizaines de ports différents en moins de cinq minutes. Est-ce un administrateur informatique qui fait un test, une application mal configurée, ou un attaquant qui cartographie le réseau ? Ce module vous apprend à faire la différence, et à relier ce que vous voyez passer sur le réseau à ce que l’adversaire cherche à obtenir.
:::

Avant d’attaquer, les adversaires ont besoin de savoir **ce qui existe et ce qui est joignable**. MITRE ATT&CK classe cela dans la reconnaissance et la découverte : *Active Scanning* (T1595) lorsque cela vous vise depuis l’extérieur, et *Network Service Discovery* (T1046) une fois l’attaquant à l’intérieur. Les défenseurs qui comprennent le balayage peuvent le repérer tôt, souvent avant tout dommage.

## Reconnaissance passive et active

| Type | Ce que fait l’attaquant | Le voyez-vous ? |
| --- | --- | --- |
| **Passive** | Il consulte des sources publiques : enregistrements DNS, journaux de transparence des certificats, offres d’emploi qui citent vos technologies, profils du personnel, identifiants divulgués | Rarement. Elle ne touche jamais votre réseau |
| **Active, externe** | Il sonde vos adresses publiques à la recherche de ports et de services ouverts (T1595) | Oui : dans les journaux du pare-feu périmétrique et de l’IDS, généralement sous forme de bruit de fond permanent venu d’Internet |
| **Active, interne** | Depuis un équipement compromis, il cartographie les hôtes et services internes (T1046) | Oui, et c’est **très significatif** : un balayage interne depuis un poste utilisateur est rarement légitime |

Les systèmes exposés à Internet sont balayés en permanence, aussi bien par des chercheurs et des moteurs de recherche d’équipements exposés que par des attaquants. Le balayage interne est différent : il signifie que quelque chose, à l’intérieur de votre réseau, se comporte comme un attaquant.

## À quoi ressemble un balayage dans les journaux

Deux formes sont à retenir :

| Schéma | Forme | Exemple dans les journaux du pare-feu |
| --- | --- | --- |
| **Balayage de ports** (vertical) | Une source → **une** destination, **de nombreux ports** | `10.20.7.44 → 10.10.0.20` sur 21, 22, 23, 80, 135, 139, 443, 445, 1433, 3306, 3389… en quelques secondes |
| **Balayage d’hôtes** (horizontal) | Une source → **de nombreuses** destinations, **un ou quelques ports** | `10.20.7.44 → 10.10.0.11 … 10.10.0.30` sur 445 |

```text
2026-03-09T02:55:04Z deny  tcp 10.20.7.44:51101 → 10.10.0.20:21
2026-03-09T02:55:04Z deny  tcp 10.20.7.44:51102 → 10.10.0.20:22
2026-03-09T02:55:05Z deny  tcp 10.20.7.44:51103 → 10.10.0.20:23
2026-03-09T02:55:05Z allow tcp 10.20.7.44:51108 → 10.10.0.20:443
2026-03-09T02:55:05Z deny  tcp 10.20.7.44:51109 → 10.10.0.20:445
...
```

*(Données fictives.)* Remarquez les **ports source qui se suivent**, le **rythme inférieur à la seconde** et la dispersion des ports de destination, sans aucun schéma métier. Dans une capture, vous verriez aussi les signatures TCP du module 1 : des SYN suivis d’un SYN-ACK (ouvert), d’un RST (fermé) ou de rien (filtré).

## Le détecter

La question à poser aux données est simple : **combien de ports (ou d’hôtes) distincts chaque source a-t-elle touchés sur une courte période ?**

```kusto
FirewallLogs
| where Action == "deny"
| summarize Ports = dcount(DstPort), Hosts = dcount(DstIp) by SrcIp
| where Ports >= 10 or Hosts >= 20
```

Ajustez les seuils à votre environnement, et tenez une **liste des scanners autorisés** (le scanner de vulnérabilités, le système de supervision informatique) pour que leur activité soit attendue et documentée. Tout autre dépassement de seuil depuis une adresse interne mérite un tri.

:::warning Le balayage est aussi une activité autorisée
Le scan de vulnérabilités est une mesure de sécurité normale (la publication NIST SP 800-115 traite des tests autorisés). Ce qui distingue un test de sécurité d’une attaque, c’est l’**autorisation** : un périmètre, un calendrier, un responsable désigné. Ne balayez jamais des systèmes que vous n’êtes pas autorisé à tester, y compris dans ce cours : tous les balayages y sont réalisés sur des données fictives.
:::

## Réduire ce que révèle la reconnaissance

- **Réduisez la surface d’attaque** : n’exposez que les services qui doivent être publics, et rien sur les ports d’administration.
- **Le refus par défaut avec journalisation** (module 3) transforme les balayages en preuves.
- **Segmentez** (module 4) pour qu’un ordinateur portable compromis ne puisse même pas joindre la plupart des ports des serveurs.
- **Surveillez spécifiquement le balayage interne** : c’est une détection à forte valeur et à faible bruit.

:::takeaway
La reconnaissance précède presque toute attaque. Un balayage de ports va d’une source vers de nombreux ports ; un balayage d’hôtes, d’une source vers de nombreux hôtes. Un balayage interne depuis un poste utilisateur est rarement légitime : comptez les ports et hôtes distincts par source et investiguez tout ce qui reste inexpliqué.
:::
