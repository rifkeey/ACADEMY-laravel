TLS protège le trafic web, de nombreux VPN, les API et la messagerie entre serveurs. Sa sécurité dépend de la **configuration** et de **certificats** correctement émis, validés et renouvelés. La NIST SP 800-52 Rev. 2 donne des recommandations pour choisir et configurer TLS.

## Bien configurer TLS

| Paramètre | Recommandation |
| --- | --- |
| Versions du protocole | TLS 1.2 et 1.3 ; versions plus anciennes (SSL, TLS 1.0, 1.1) désactivées |
| Suites de chiffrement | Des suites robustes et actuelles avec confidentialité persistante ; suivez la NIST SP 800-52 Rev. 2 et les recommandations du fournisseur |
| Certificats | Émis pour les bons noms, par une autorité de confiance, avec des longueurs de clé appropriées |
| HTTP Strict Transport Security (HSTS) | Pour les sites web, afin que les navigateurs utilisent toujours HTTPS |
| Trafic interne | Chiffrez-le aussi, en particulier à travers les frontières de zones et dans le cloud |

## Comment fonctionne la validation des certificats

Lorsqu’un client se connecte, il vérifie le certificat du serveur (le format X.509 est défini dans la RFC 5280) :

1. **La chaîne** : le certificat est signé par un émetteur auquel le client fait confiance, directement ou par des certificats intermédiaires.
2. **Le nom** : le certificat est valide pour le nom demandé par le client (par exemple `corebanking.kanem.example`).
3. **Les dates** : l’heure actuelle se situe dans la période de validité.
4. **La révocation et la politique** : le certificat n’a pas été révoqué et respecte la politique du client.

Si une vérification échoue, le client doit refuser la connexion. **Les applications qui sautent la validation** (fréquent dans les scripts rapides et certains outils internes) perdent l’authentification du serveur que TLS est censé apporter.

## La gestion des certificats

La plupart des incidents liés aux certificats sont opérationnels, pas cryptographiques :

| Problème | Prévention |
| --- | --- |
| Un certificat expiré met un service en panne | Un inventaire de tous les certificats avec leur date d’expiration et leur responsable ; renouvellement automatisé quand c’est possible ; alertes bien avant l’expiration |
| Certificat émis pour de mauvais noms | Revoir les demandes ; utiliser des conventions de nommage |
| Clé privée exposée | Stocker les clés dans des magasins de clés protégés ; restreindre l’accès ; révoquer et remplacer au moindre soupçon |
| Des certificats inconnus apparaissent | Surveiller les journaux Certificate Transparency pour repérer les certificats émis pour vos domaines |

Les organisations automatisent de plus en plus l’émission et le renouvellement, car la durée de vie des certificats raccourcit dans tout le secteur.

## Le TLS mutuel

Avec le **TLS mutuel (mTLS)**, le client présente lui aussi un certificat : les deux extrémités s’authentifient. On l’utilise entre services (par exemple dans les maillages de services, programme Sécurité du cloud, module 6), pour les API des partenaires et pour l’authentification des équipements.

:::takeaway
Configurez TLS 1.2/1.3 avec des suites robustes, validez les certificats (chaîne, nom, dates, révocation) dans chaque client, tenez un inventaire avec des responsables et un renouvellement automatisé, protégez les clés privées, et utilisez le TLS mutuel lorsque les deux extrémités doivent être authentifiées.
:::
