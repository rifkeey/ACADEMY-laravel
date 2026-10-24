:::scenario 400 alertes avant midi
Le nouveau capteur réseau de Kanem a été mis en service lundi. À midi, il avait déjà levé 400 alertes. Le responsable du SOC vous pose deux questions : « Lesquelles comptent ? » et « Comment faire pour que demain soit meilleur qu’aujourd’hui ? » Ce module vous donne les réponses : comment fonctionne la détection réseau, pourquoi elle est bruyante, et comment la rendre utile.
:::

Les pare-feu décident de ce qui est **autorisé**. La détection d’intrusion pose une autre question : **parmi le trafic que nous autorisons (et une partie de celui que nous bloquons), qu’est-ce qui ressemble à une attaque ou à une compromission ?** La NIST SP 800-94, *Guide to Intrusion Detection and Prevention Systems*, décrit ces technologies ; cette leçon couvre les notions dont vous avez besoin pour travailler avec n’importe laquelle d’entre elles.

## Les méthodes de détection

| Méthode | Comment elle décide | Points forts | Points faibles |
| --- | --- | --- | --- |
| **Par signatures** | Compare le trafic à des motifs de menaces connues | Précise pour les menaces connues ; une alerte s’explique facilement | Manque les menaces nouvelles ou modifiées ; exige des mises à jour constantes |
| **Par anomalies** | Compare le trafic à une référence « normale » apprise ou définie | Peut repérer l’inconnu | Les références sont difficiles à établir ; davantage de faux positifs ; alertes plus difficiles à expliquer |
| **Analyse de protocole avec état** | Vérifie que les protocoles se comportent comme le disent leurs spécifications | Repère les détournements de protocole et les tentatives d’évasion | Exige une connaissance approfondie des protocoles ; gourmande en ressources |

Les produits réels les combinent. Exemples libres que vous rencontrerez dans de nombreux SOC : **Suricata** (détection et prévention par signatures, avec analyse des protocoles) et **Zeek** (un cadre de surveillance réseau qui écrit des journaux de protocole détaillés et permet une détection par scripts).

## Détection ou prévention ?

| | IDS (détection) | IPS (prévention) |
| --- | --- | --- |
| Positionnement | Reçoit une **copie** du trafic (depuis un port miroir ou un tap réseau) | Est placé **en coupure** : le trafic le traverse |
| Action | Alerte | Alerte **et** peut bloquer, réinitialiser ou rejeter le trafic |
| Coût d’une erreur | Un faux positif fait perdre du temps à l’analyste | Un faux positif **bloque du trafic métier légitime** |
| Comportement en cas de panne | S’il tombe, le trafic continue de circuler (sans surveillance) | Doit être conçu pour s’ouvrir ou se fermer délibérément en cas de panne |

Beaucoup d’organisations n’utilisent la prévention que pour les signatures très fiables, et la détection pour tout le reste.

## Réseau et hôte

| | Sur le réseau (NIDS/NIPS) | Sur l’hôte (HIDS, détection sur les postes) |
| --- | --- | --- |
| Voit | Le trafic des liens surveillés | Les processus, fichiers, connexions de session et connexions réseau d’un équipement |
| Aveugle à | Le contenu chiffré ; le trafic qui ne passe jamais par le capteur | Les autres équipements ; le réseau dans son ensemble |

Ils se complètent. La détection réseau est précieuse justement parce qu’elle ne dépend pas de l’équipement compromis pour dire la vérité sur lui-même.

## Ce que contient une alerte

```text
2026-03-10T09:14:22Z  sensor=NS-HQ-01  severity=medium
signature="Policy: outbound connection to newly registered domain"
src=10.20.5.12:51022  dst=203.0.113.9:443  proto=TCP  app=tls
sni=update-checker.example  action=alert
```

*(Exemple fictif et illustratif : la signature signale une connexion sortante vers un domaine récemment enregistré.)* Une alerte est une **hypothèse** : « ceci pourrait être malveillant ». Tout le contenu des leçons 2 et 4 vise à décider si c’est le cas.

:::takeaway
La détection réseau inspecte le trafic autorisé (et bloqué) à la recherche de signes d’attaque, à l’aide de signatures, d’anomalies et de l’analyse des protocoles. L’IDS observe une copie et alerte ; l’IPS est en coupure et peut bloquer, si bien que ses erreurs coûtent plus cher. Combinez détection réseau et détection sur les hôtes, et traitez chaque alerte comme une hypothèse à vérifier.
:::
