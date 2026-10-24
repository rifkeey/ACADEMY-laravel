## À quoi sert le renseignement sur les menaces

Le renseignement sur les menaces (*threat intelligence*) est une information sur les menaces qui a été analysée pour permettre à quelqu’un de prendre une meilleure décision. Un flux brut d’adresses IP est une *donnée* ; le renseignement explique qui les utilise, comment, contre qui, et avec quel degré de confiance.

On distingue couramment trois niveaux :

- **Stratégique** : les tendances et les motivations des acteurs, à l’intention de la direction (par exemple, des groupes de rançongiciel qui ciblent les services financiers en Afrique de l’Ouest).
- **Opérationnel** : les campagnes et leurs tactiques, utiles pour planifier les défenses et les chasses.
- **Tactique** : les indicateurs et les techniques précises, utilisés directement pour la détection et le blocage.

## Les indicateurs de compromission (IOC)

| Type d’indicateur | Exemple | Durée de vie |
| --- | --- | --- |
| Empreinte de fichier (SHA-256) | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` (c’est l’empreinte d’un fichier vide, un faux IOC classique) | Longue, mais un attaquant la change sans effort |
| Adresse IP | `198.51.100.77` | Courte : l’infrastructure est louée et recyclée |
| Domaine | `cdn-update-check.example` | Moyenne |
| URL | `https://files.example/inv-2026.zip` | Courte à moyenne |
| Expéditeur / objet d’e-mail | `Subject: Outstanding invoice March` | Courte |

La **pyramide de la douleur** (*Pyramid of Pain*, David Bianco) résume une idée essentielle : les indicateurs du bas (empreintes, IP) sont faciles à changer pour un attaquant ; les comportements du haut (outils et TTP) sont pénibles à changer. Les détections fondées sur les comportements durent plus longtemps que celles fondées sur les indicateurs.

## Juger la qualité du renseignement

Pour chaque indicateur, demandez-vous :

1. **Source** : qui l’a signalé, et avec quelle fiabilité jusqu’ici ?
2. **Contexte** : quelle campagne ou quel acteur, et quel rôle l’indicateur jouait-il (C2, livraison, exfiltration) ?
3. **Confiance** : est-il confirmé, évalué ou hypothétique ?
4. **Ancienneté** : quand a-t-il été observé ? Les indicateurs IP de plus de quelques semaines sont souvent réattribués à des clients innocents.
5. **Pertinence** : cible-t-il notre secteur, notre région ou nos technologies ?

## L’hygiène des IOC

- Donnez à chaque indicateur une **date d’expiration** dans votre plateforme. Les anciennes IP provoquent des faux positifs sur des clients cloud innocents.
- Ne bloquez jamais une infrastructure partagée (la plage d’un grand CDN ou d’un grand fournisseur cloud) sur la base d’un seul indicateur.
- Consignez les **observations** (*sightings*) : un IOC qui a correspondu dans votre environnement est bien plus important qu’un IOC qui n’a rien donné.
- Repérez les formats « désamorcés » dans les rapports (`hxxps://files[.]example`) et ne les « réamorcez » pas là où quelqu’un pourrait cliquer dessus.

## Le pivot

À partir d’un indicateur, on peut souvent en trouver d’autres : le domaine se résout vers une IP ; d’autres domaines se résolvent vers la même IP (DNS passif) ; le rapport de bac à sable d’une empreinte de fichier liste les URL contactées. Chaque pivot doit être consigné avec son niveau de confiance, car le partage d’infrastructure peut vous mener vers une activité sans rapport.
