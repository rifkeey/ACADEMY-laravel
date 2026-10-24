La plupart des intrusions graves comportent deux étapes après la première prise de pied : **obtenir des identifiants valides**, et **passer du premier système à des systèmes plus précieux** (le déplacement latéral). Les deux utilisent des protocoles et des comptes légitimes, et peuvent donc ressembler à du travail normal. C’est souvent sur le réseau qu’ils deviennent visibles.

## Les attaques sur les identifiants en un coup d’œil

| Technique (ATT&CK) | Ce que cela signifie | Preuves habituelles |
| --- | --- | --- |
| **Brute Force** (T1110) : deviner | De nombreux mots de passe essayés contre un seul compte | Rafales d’échecs de connexion pour un compte, depuis une source |
| **Pulvérisation de mots de passe** (*Password Spraying*, T1110.003) | Quelques mots de passe courants essayés contre de nombreux comptes, lentement, pour éviter les verrouillages | Échecs de connexion répartis sur de nombreux comptes depuis une ou quelques sources |
| **Bourrage d’identifiants** (*Credential Stuffing*, T1110.004) | Des couples nom d’utilisateur et mot de passe divulgués par d’autres sites web sont essayés | Connexions sur de nombreux comptes, dont certaines réussissent, souvent depuis de nombreuses adresses |
| **Comptes valides** (*Valid Accounts*, T1078) | Des identifiants volés ou réutilisés sont utilisés avec succès | Une connexion réussie inhabituelle pour cet utilisateur : heure, lieu, équipement ou système |

## Le déplacement latéral en un coup d’œil

Après une prise de pied, un intrus utilise les protocoles d’administration habituels pour atteindre d’autres systèmes. MITRE ATT&CK les regroupe sous *Remote Services* (T1021), par exemple le bureau à distance (T1021.001), les partages d’administration SMB/Windows (T1021.002) et SSH (T1021.004).

Ce qui le rend détectable, c’est **qui se connecte à quoi** :

| Signal | Pourquoi c’est important |
| --- | --- |
| Un poste de travail qui se connecte à de nombreux autres postes | Les utilisateurs en ont rarement besoin ; les administrateurs travaillent depuis la zone d’administration |
| Un compte utilisateur qui ouvre des sessions sur de nombreux serveurs en peu de temps | Inhabituel pour la plupart des rôles |
| Des protocoles d’administration (RDP, SMB, SSH, administration à distance) depuis des zones qui ne devraient pas les utiliser | Contredit la conception du module 4 |
| Un compte de service qui ouvre une session interactive, ou depuis un nouvel hôte | Les comptes de service doivent suivre des schémas fixes |
| Des hôtes de la DMZ ou du VPN qui atteignent des zones restreintes | Le schéma de l’incident du module 9 |

## Le détecter

1. **Journaux d’authentification** : rafales d’échecs de connexion, schémas de pulvérisation, connexions réussies après des échecs, déplacements impossibles, nouveaux équipements (journaux du fournisseur d’identité et des contrôleurs de domaine).
2. **Preuves réseau** : enregistrements de flux et alertes IDS pour les protocoles d’administration entre zones (modules 5 et 9).
3. **Références** : quels comptes utilisent normalement quels systèmes, pour qu’une nouvelle combinaison se remarque.
4. **Corrélation** : une connexion réussie suivie de connexions à plusieurs serveurs depuis le même hôte est une preuve bien plus forte que chacun des deux éléments pris isolément.

## Réduire le risque

| Mesure | Effet |
| --- | --- |
| **MFA résistante à l’hameçonnage** pour l’accès à distance, les administrateurs et les consoles cloud (module 6) | Un mot de passe volé ne suffit plus |
| **Verrouillage et limitation des tentatives**, plus la vérification des nouveaux mots de passe contre des listes de mots de passe compromis | Ralentit la devinette et la pulvérisation |
| **Segmentation et règles de moindre privilège** (modules 3 et 4) | Protocoles d’administration autorisés uniquement depuis la zone d’administration |
| **Administration par niveaux et serveur de rebond** | Les identifiants d’administrateur ne sont jamais utilisés sur les postes ordinaires |
| **Mots de passe d’administrateur local uniques**, gérés par un outil | Un mot de passe capturé n’ouvre pas toutes les machines |
| **Supprimer les privilèges permanents** | Administration juste-à-temps |
| **Surveillance et alertes** sur les signaux ci-dessus | Détection plus précoce |

:::takeaway
Les attaques sur les identifiants apparaissent dans les journaux d’authentification sous forme de devinettes, de pulvérisations ou de réussites inhabituelles ; le déplacement latéral apparaît dans les données réseau et de connexion sous forme de comptes et d’hôtes qui atteignent des systèmes qu’ils n’utilisent pas d’habitude. Détectez les deux grâce aux références et à la corrélation, et réduisez-les par une MFA résistante à l’hameçonnage, la limitation des tentatives, la segmentation, l’administration par niveaux, des mots de passe locaux uniques et moins de privilèges permanents.
:::
