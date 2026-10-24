Une attaque par **déni de service** (DoS) cherche à rendre un service indisponible pour les personnes qui en ont besoin. Lorsque le trafic provient de nombreuses sources à la fois, généralement des équipements compromis, on parle de déni de service **distribué** (DDoS). Pour Kanem, un portail de paiement indisponible le jour de la paie est un incident métier, même si aucune donnée n’est touchée. MITRE ATT&CK les répertorie sous *Network Denial of Service* (T1498) et *Endpoint Denial of Service* (T1499).

## Trois grandes catégories

| Catégorie | Ce qui est épuisé | Exemple d’effet |
| --- | --- | --- |
| **Volumétrique** | La bande passante de la liaison Internet | La liaison est saturée : plus rien ne passe, y compris les utilisateurs légitimes |
| **Protocole / épuisement des états** | Les tables de connexions des pare-feu, répartiteurs de charge ou serveurs | Les équipements ne peuvent plus accepter de nouvelles connexions, même si la liaison n’est pas saturée |
| **Couche application** | Les ressources du serveur : processeur, base de données, pages coûteuses | Le site web est lent ou en échec, alors que le volume de trafic paraît modeste |

Certaines attaques **réfléchissent et amplifient** le trafic : elles envoient de petites requêtes avec une adresse source falsifiée (leçon 2) à des services ouverts sur Internet, qui renvoient des réponses bien plus volumineuses à la victime. C’est l’une des raisons pour lesquelles tout réseau doit bloquer les adresses source falsifiées et éviter d’exposer sur Internet des services ouverts qui répondent à n’importe qui (par exemple des résolveurs DNS mal configurés).

## Les signes à rechercher

| Signal | Où |
| --- | --- |
| Une utilisation de la liaison proche de 100 % sans activité métier correspondante | Supervision des routeurs et de l’opérateur |
| Une hausse soudaine des connexions ou des connexions semi-ouvertes | Tables d’états des pare-feu et des répartiteurs de charge |
| De nombreuses requêtes vers la même URL coûteuse depuis de nombreuses adresses | Journaux web et du CDN |
| Des taux d’erreur et des temps de réponse en hausse | Supervision applicative |
| Du trafic venant de pays ou de réseaux inhabituels, ou aux schémas étranges (même agent utilisateur, aucun cookie) | Analyses du pare-feu applicatif web et du CDN |

Le plus difficile est de distinguer une attaque d’un pic légitime, comme une campagne marketing ou le jour de la paie. Des références et un calendrier des événements métier aident.

## Se préparer et réagir

| Mesure | Objet |
| --- | --- |
| **Protection en amont** | Les attaques volumétriques doivent être absorbées avant d’atteindre votre liaison : service de nettoyage du fournisseur d’accès ou fournisseur cloud de protection contre les DDoS |
| **CDN et pare-feu applicatif web** devant les sites web publics | Absorber la charge, mettre en cache le contenu et filtrer les inondations applicatives |
| **Limitation du débit** et des connexions | Sur les répartiteurs de charge, les API et les pages de connexion |
| **Capacité et mise à l’échelle automatique** | De la marge pour absorber les pics ; les services cloud peuvent s’étendre |
| **Équipements durcis** | Protections contre l’épuisement des états (par exemple les SYN cookies) activées sur les pare-feu et les serveurs |
| **Supprimer les amplificateurs** | Aucun résolveur ouvert ni autre service répondant à tout Internet ; filtres anti-usurpation (BCP 38) |
| **Procédure opérationnelle** | Qui appelle le fournisseur, comment activer le nettoyage, comment communiquer avec les clients |

:::tip Répétez l’appel téléphonique
Les premières minutes sont souvent perdues à chercher le contact d’urgence du fournisseur et le numéro de contrat. Gardez-les dans la procédure et testez celle-ci avec le fournisseur.
:::

:::takeaway
Les attaques par déni de service épuisent la bande passante, les états de connexion ou les ressources applicatives. Surveillez l’utilisation des liaisons, les tables d’états, les taux d’erreur et les schémas de requêtes par rapport à une référence. Préparez le nettoyage en amont, un CDN et un WAF, des limites de débit, de la capacité, des équipements durcis et une procédure répétée, et assurez-vous que votre propre réseau ne peut pas servir d’amplificateur.
:::
