IPsec est la norme de protection du trafic IP entre passerelles et hôtes, et la technologie qui sous-tend la plupart des VPN site à site. La NIST SP 800-77 Rev. 1, *Guide to IPsec VPNs*, est la référence pour le planifier et le configurer. Cette leçon couvre les notions nécessaires pour lire une configuration VPN et dépanner un tunnel.

## Ce qu’apporte IPsec

- **La confidentialité** : le trafic est chiffré.
- **L’intégrité** : toute altération est détectée.
- **L’authentification des pairs** : chaque extrémité prouve son identité, par certificat ou par clé prépartagée.
- **La protection contre le rejeu** : des paquets capturés ne peuvent pas être renvoyés plus tard.

## Deux phases

Les tunnels IPsec sont établis avec **IKE** (*Internet Key Exchange* ; IKEv2 est la version actuelle) :

| Phase | Ce qui se passe | Ce qui doit correspondre des deux côtés |
| --- | --- | --- |
| **IKE SA** (phase 1) | Les deux passerelles s’authentifient mutuellement et établissent un canal sécurisé pour la négociation | Méthode d’authentification et identifiants, algorithmes de chiffrement et d’intégrité, groupe d’échange de clés |
| **IPsec / Child SA** (phase 2) | Elles s’accordent sur la façon de protéger le trafic réel et sur **le trafic** qui passe par le tunnel | Algorithmes, et sélecteurs de trafic (quels sous-réseaux de chaque côté) |

La plupart des problèmes de « tunnel qui ne monte pas » viennent d’une **incohérence** sur l’un de ces points : algorithmes différents, mauvais identifiants, ou sélecteurs de trafic qui ne sont pas le reflet l’un de l’autre (le siège dit qu’Abuja est `10.40.8.0/22`, Abuja dit qu’il est `10.40.0.0/16`).

## Les choix de configuration

| Choix | Recommandation |
| --- | --- |
| Version d’IKE | IKEv2 |
| Authentification | Les certificats sont plus solides et plus faciles à gérer à grande échelle que les clés partagées ; si des clés prépartagées sont utilisées, qu’elles soient longues, aléatoires et propres à chaque tunnel |
| Algorithmes | Des algorithmes actuels et robustes, selon les recommandations de la NIST SP 800-77 Rev. 1 et de votre fournisseur ; désactivez les options obsolètes |
| Confidentialité persistante (*perfect forward secrecy*) | Activée, pour qu’une clé à long terme compromise n’expose pas le trafic passé |
| Durées de vie | Des intervalles de renouvellement des clés raisonnables, identiques des deux côtés |
| Résilience | Deux tunnels vers des passerelles différentes, supervisés |

## Mode tunnel et mode transport

- Le **mode tunnel** chiffre tout le paquet d’origine et ajoute un nouvel en-tête entre les passerelles. Les VPN site à site l’utilisent.
- Le **mode transport** protège la charge utile entre deux hôtes et conserve l’en-tête d’origine. Il est moins courant dans les VPN d’entreprise.

## Ce qu’un VPN ne fait pas

Un tunnel ne protège le trafic que **pendant son transit entre les passerelles**. Il ne décide pas du trafic autorisé une fois arrivé. Le trafic issu du tunnel d’une agence doit toujours passer par la politique du pare-feu (modules 3 et 4) : un PC d’agence compromis ne doit pas avoir un accès libre au centre de données au seul motif que le lien est chiffré.

:::takeaway
IPsec apporte le chiffrement, l’intégrité, l’authentification et la protection contre le rejeu, négociés en deux phases par IKEv2. La plupart des pannes de tunnel sont des incohérences d’authentification, d’algorithmes ou de sélecteurs de trafic. Utilisez des certificats ou des clés robustes et uniques, des algorithmes actuels et la confidentialité persistante, et maintenez la politique du pare-feu sur le trafic qui sort du tunnel.
:::
