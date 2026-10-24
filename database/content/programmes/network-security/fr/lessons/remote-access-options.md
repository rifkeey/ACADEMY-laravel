:::scenario Travailler de n’importe où
Les chargés de prêts de Kanem rendent désormais visite à leurs clients à Abuja et à Kano avec des tablettes, les administrateurs informatiques assurent le support des agences depuis chez eux, et deux agences se connectent au siège via Internet. Chacune de ces connexions traverse des réseaux que Kanem ne contrôle pas. Ce module explique comment les sécuriser, et comment les réparer quand elles échouent.
:::

L’accès distant relie des personnes et des sites à des ressources à travers des réseaux non fiables. Choisir la bonne méthode est la première décision de sécurité.

## Les principales options

| Option | Relie | Technologie habituelle | Caractéristique de sécurité |
| --- | --- | --- | --- |
| **VPN site à site** | Des réseaux entiers (agence ↔ siège, siège ↔ cloud) | IPsec | Tunnel permanent entre passerelles ; les utilisateurs ne remarquent rien |
| **VPN d’accès distant** | Un équipement au réseau de l’entreprise | Clients VPN IPsec ou fondés sur TLS | L’équipement rejoint le réseau ; l’accès dépend de ce que le réseau autorise |
| **Accès réseau zero trust (ZTNA)** | Un utilisateur et son équipement à des applications précises | Proxys et connecteurs qui tiennent compte de l’identité | Décisions par application et par session (module 4 de Sécurité réseau) |
| **Applications publiées** | Les utilisateurs directement à une application web | HTTPS avec authentification forte | Aucun accès au réseau ; uniquement l’application |
| **Services d’administration à distance** | Les administrateurs aux serveurs | Bastions, services de session des fournisseurs | Étroits, journalisés, fondés sur l’identité |

La tendance va de l’accès au niveau du réseau (VPN) vers l’accès au niveau des applications (ZTNA, applications publiées), parce qu’il limite ce qu’un compte ou un équipement compromis peut atteindre. Les VPN restent indispensables pour les liaisons site à site et pour de nombreuses applications anciennes.

## Les questions à poser avant de choisir

1. **Qui ou quoi a besoin d’un accès ?** Personnel, prestataires, partenaires, équipements, sites ?
2. **À quoi ?** Quelques applications, ou tout le réseau ?
3. **Depuis quels équipements ?** Gérés et conformes, ou personnels ?
4. **Quelle force d’authentification faut-il ?** Une MFA résistante à l’hameçonnage pour tout ce qui est sensible.
5. **Que faut-il journaliser ?** Chaque connexion, avec l’utilisateur, l’équipement et l’heure.

## La conception de l’accès distant de Kanem

| Besoin | Choix | Pourquoi |
| --- | --- | --- |
| Agences d’Abuja et de Kano vers le siège | VPN IPsec site à site, deux tunnels par agence | Permanent, chiffré, résilient |
| Tablettes des chargés de prêts vers l’application des prêts | Application publiée avec MFA et conformité de l’équipement | Ils ont besoin d’une application, pas du réseau |
| Administrateurs informatiques vers l’infrastructure | VPN avec MFA vers la seule zone d’administration, puis l’hôte de rebond | L’accès d’administration reste dans le plan d’administration |
| Support des prestataires | ZTNA vers l’application précise, limité dans le temps | Moindre privilège pour les tiers |

:::takeaway
Adaptez la méthode d’accès distant au besoin : VPN site à site pour les réseaux, accès au niveau des applications pour la plupart des utilisateurs, chemins étroits fondés sur l’identité pour les administrateurs et les prestataires. L’authentification forte, la vérification des équipements et la journalisation s’appliquent à tous.
:::
