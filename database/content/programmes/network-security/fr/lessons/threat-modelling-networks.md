:::scenario Après l’incident
Après l’incident nocturne du module 9, le conseil d’administration de Kanem a approuvé un programme de sécurité réseau. Il veut une **architecture cible**, un registre des risques et un plan qu’il peut financer, pas une liste de retouches du pare-feu. Dans ce projet de synthèse, vous les produisez en mobilisant tout ce que vous avez vu dans les modules 1 à 9.
:::

La modélisation des menaces d’un réseau repose sur les quatre mêmes questions que pour n’importe quel système (le Threat Modeling Manifesto) : sur quoi travaillons-nous, qu’est-ce qui peut mal tourner, qu’allons-nous faire, et avons-nous fait du bon travail ? Pour un réseau, le « schéma » est constitué des **zones, frontières de confiance et flux**.

## Étape 1 : décrire le réseau

| Élément | Pour le siège de Kanem |
| --- | --- |
| **Zones** | Internet, DMZ, utilisateurs, serveurs, administration, Wi-Fi invités ; agences (Abuja, Kano) ; utilisateurs à distance ; cloud (le VPC de facturation) |
| **Joyaux de la couronne** | Les serveurs et la base de données de l’application bancaire centrale, le serveur de fichiers des finances, la zone d’administration, les services d’identité |
| **Frontières de confiance** | Chaque endroit où le trafic passe d’une zone à une autre, en particulier internet vers DMZ, DMZ vers serveurs, utilisateurs vers serveurs, tout vers l’administration, et les liaisons des agences et des accès distants |
| **Flux** | Quelles zones doivent communiquer, sur quels ports, dans quel but métier (module 4) |

## Étape 2 : qu’est-ce qui peut mal tourner ?

Parcourez chaque frontière et demandez-vous, à haut niveau :

| Question | Exemple chez Kanem |
| --- | --- |
| **Exposition** : qu’est-ce qui est accessible depuis une zone moins fiable ? | Une règle temporaire de prestataire qui expose la DMZ (module 9) |
| **Chemins vers les joyaux de la couronne** : si un hôte d’une zone est compromis, que peut-il atteindre ? | La DMZ vers tous les ports des serveurs via la règle 4 |
| **Identité à la frontière** : l’accès est-il lié à une identité forte ? | Un accès distant sans vérification des équipements (module 6) |
| **Visibilité** : verrions-nous un abus à cette frontière ? | Des règles d’autorisation sans journalisation ; le contournement du DNS (modules 3 et 7) |
| **Disponibilité** : y a-t-il un point de défaillance unique ? | Un seul pare-feu périmétrique, un seul lien Internet |
| **Plan d’administration** : l’administration des équipements est-elle accessible depuis les zones utilisateurs ? | La règle 11, des utilisateurs vers la console du pare-feu (module 3) |

STRIDE (usurpation, altération, répudiation, divulgation d’informations, déni de service, élévation de privilèges) est aussi une liste de contrôle utile pour chaque frontière.

## Étape 3 : décider quoi faire

Pour chaque menace, choisissez de la **réduire** (une mesure), de l’**accepter** (avec un responsable nommé), de la **transférer** (par exemple à une assurance ou à un service géré) ou de l’**éviter** (cesser l’activité risquée). La plupart des menaces réseau se réduisent avec les mesures des modules précédents : segmentation, règles de moindre privilège, accès distant robuste, surveillance.

## Étape 4 : vérifier le modèle

Revoyez le modèle avec les personnes qui exploitent le réseau et les systèmes. Reprenez-le dès que le réseau change de façon importante : une nouvelle agence, une migration vers le cloud, une nouvelle méthode d’accès distant.

:::takeaway
Modélisez les menaces d’un réseau en cartographiant les zones, les joyaux de la couronne, les frontières de confiance et les flux ; interrogez à chaque frontière l’exposition, les chemins vers les joyaux de la couronne, l’identité, la visibilité, la disponibilité et le plan d’administration ; choisissez de réduire, d’accepter, de transférer ou d’éviter chaque menace ; et revoyez le modèle quand le réseau change.
:::
