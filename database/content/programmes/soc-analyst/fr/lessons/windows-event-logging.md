## Le journal de sécurité Windows

Windows enregistre les activités liées à la sécurité dans le journal d’événements **Security** lorsque l’audit est activé par stratégie. Les analystes retiennent par cœur une poignée d’identifiants d’événement, car ils apparaissent dans presque toutes les investigations.

| ID d’événement | Signification | Pourquoi c’est important |
| --- | --- | --- |
| 4624 | Ouverture de session réussie | Qui a accédé à quoi, d’où et comment |
| 4625 | Échec d’ouverture de session | Force brute et pulvérisation de mots de passe |
| 4634 / 4647 | Fermeture de session | Durée des sessions |
| 4648 | Ouverture de session avec des identifiants explicites | `runas`, outils de déplacement latéral |
| 4672 | Attribution de privilèges spéciaux | Ouverture de session équivalente administrateur |
| 4688 | Création de processus | Ce qui a été exécuté (la ligne de commande exige un paramètre de stratégie supplémentaire) |
| 4720 | Création d’un compte utilisateur | Persistance par création de comptes |
| 4728 / 4732 / 4756 | Ajout d’un membre à un groupe de sécurité | Élévation de privilèges |
| 1102 | Journal d’audit effacé | Possible manœuvre anti-investigation |

### Les types d’ouverture de session

Le champ `LogonType` des événements 4624 et 4625 indique *comment* la session a été ouverte :

- **2** : interactive (clavier sur la machine)
- **3** : réseau (par exemple l’accès à un partage de fichiers)
- **4** : traitement par lots (tâche planifiée)
- **5** : service
- **7** : déverrouillage
- **10** : RemoteInteractive (RDP)
- **11** : interactive mise en cache

Une rafale d’échecs de type 3 sur de nombreux comptes depuis une même source évoque une pulvérisation de mots de passe contre un service réseau. Des réussites de type 10 depuis un poste inconnu évoquent un déplacement latéral par RDP.

### Les motifs d’échec

L’événement 4625 comporte les codes `Status` et `SubStatus`. Deux sont à reconnaître : `0xC000006A` signifie que le nom d’utilisateur était valide mais le mot de passe erroné, et `0xC0000064` que le nom d’utilisateur n’existe pas. Un attaquant qui obtient beaucoup de résultats `0xC0000064` devine des noms d’utilisateur ; beaucoup de résultats `0xC000006A` laissent penser qu’il dispose déjà d’une liste d’utilisateurs valides.

## Au-delà du journal de sécurité

Le journal Security ne suffit pas à lui seul. Microsoft Sysmon, un outil gratuit de la suite Sysinternals, ajoute une télémétrie riche sur les postes lorsqu’il est déployé avec une configuration ajustée : création de processus avec ligne de commande complète et empreintes (Sysmon Event ID 1), connexions réseau par processus (3) et modifications du registre (12 à 14), entre autres. Microsoft Defender for Endpoint et les autres produits EDR collectent des données comparables.

## Lire un événement avec esprit critique

```text
EventID: 4624  TimeCreated: 2026-03-04T02:14:09Z  Computer: FIN-WS-07
TargetUserName: f.manager  LogonType: 3  IpAddress: 203.0.113.45
AuthenticationPackageName: NTLM  WorkstationName: -
```

Une ouverture de session réseau NTLM depuis une adresse externe, directement sur un poste du service financier, ne devrait pas être possible dans un réseau bien conçu. Ce constat amène une seconde question pour l’équipe réseau : pourquoi ce poste était-il seulement accessible depuis Internet ? Un bon analyste consigne à la fois la constatation de sécurité et la lacune de contrôle qui l’a rendue possible.
