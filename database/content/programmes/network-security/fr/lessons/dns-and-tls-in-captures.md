Presque chaque session commence par le DNS et, pour le trafic web, se poursuit par une négociation TLS. Tous deux laissent dans une capture des informations utiles et lisibles, même lorsque les données applicatives sont chiffrées.

## Le DNS dans une capture

| Champ | Filtre | Montre |
| --- | --- | --- |
| Nom demandé | `dns.qry.name` | Ce que le client a résolu |
| Type de requête | `dns.qry.type` | A (IPv4), AAAA (IPv6), TXT, MX… |
| Code de réponse | `dns.flags.rcode` | 0 = pas d’erreur, 3 = nom inexistant (NXDOMAIN) |
| Réponse | `dns.a` | L’adresse renvoyée |
| Temps de réponse | `dns.time` | Le temps mis par le résolveur pour répondre |

Wireshark relie chaque réponse à sa requête : vous voyez donc d’un coup d’œil les résolutions lentes ou en échec. Une réponse DNS lente retarde tout ce qui suit. Une résolution qui renvoie une adresse **erronée** ou **ancienne** envoie le client vers le mauvais serveur, ce qui est une cause fréquente de problèmes après une migration.

## TLS dans une capture

Une négociation TLS 1.3 (RFC 8446) se présente ainsi :

| Trame | Émetteur | Message | Lisible ? |
| --- | --- | --- | --- |
| 1 | client | **Client Hello** : versions et suites de chiffrement prises en charge, et le **nom du serveur (SNI)** | Oui |
| 2 | serveur | **Server Hello** : version et suite de chiffrement choisies | Oui |
| 2 (suite) | serveur | Change Cipher Spec (pour la compatibilité), puis des messages de négociation chiffrés, dont le certificat | Certificat **non** lisible en TLS 1.3 |
| 3… | les deux | Application Data | Non |

Filtres utiles :

```text
tls.handshake.type == 1                    Client Hello
tls.handshake.extensions_server_name       la valeur du SNI
tls.handshake.type == 2                    Server Hello
tls.alert_message                          alertes TLS (échecs de négociation)
```

:::note Quelle version a été négociée ?
En TLS 1.3, l’en-tête d’enregistrement indique encore TLS 1.2 pour des raisons de compatibilité. La vraie version se trouve dans l’extension **supported_versions** du Server Hello. Wireshark affiche le résultat dans la colonne Protocol sous la forme TLSv1.3.
:::

## Ce que l’on peut et ne peut pas apprendre du trafic chiffré

| Vous pouvez voir | Vous ne pouvez pas voir (sans déchiffrement) |
| --- | --- |
| Qui a parlé à qui, quand, combien de temps, quel volume | Les URL, les en-têtes, les données des formulaires |
| Le nom du serveur (SNI) | Le contenu des pages ou des fichiers |
| La version TLS et la suite de chiffrement | Le certificat (en TLS 1.3) |
| Les échecs de négociation et les alertes | Les erreurs applicatives |
| Les horaires : rapidité de la négociation, intervalles, retransmissions | |

C’est souvent suffisant : dans de nombreux cas de performance, les horaires racontent toute l’histoire. Lorsque le contenu est nécessaire, le déchiffrement doit être planifié et autorisé (module 5, leçon 5), par exemple en utilisant les journaux du serveur ou un point d’inspection autorisé.

:::takeaway
Le DNS montre ce qui a été résolu, ce qui a été renvoyé et à quelle vitesse. Le Client Hello TLS montre le nom du serveur et les versions proposées, et le Server Hello montre ce qui a été négocié. Même avec un contenu chiffré, les horaires et les métadonnées expliquent souvent le problème.
:::
