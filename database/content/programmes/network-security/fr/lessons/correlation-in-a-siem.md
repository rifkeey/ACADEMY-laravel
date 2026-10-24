Un SIEM (système de gestion des informations et des événements de sécurité) collecte les journaux de nombreuses sources, les normalise et permet aux analystes de les rechercher et de les corréler. C’est par la corrélation que des signaux faibles isolés deviennent une histoire claire.

## La normalisation

Des équipements différents nomment la même chose différemment : `src`, `SrcIP`, `source_address`. Un SIEM les rattache à des champs communs pour qu’une seule requête puisse interroger toutes les sources. Quand vous écrivez des requêtes, vérifiez les noms de champs de chaque table.

## Corréler par adresse et par heure

La plupart des corrélations réseau se font sur l’**adresse IP** et une **fenêtre de temps** :

| Étape | Question | Source |
| --- | --- | --- |
| 1 | Qu’est-ce qui a déclenché l’alerte, et sur quel hôte ? | Alerte IDS |
| 2 | Qu’a fait d’autre cet hôte, avant et après ? | Flux, pare-feu, DNS |
| 3 | Qui ou qu’est-ce que cet hôte ? | Inventaire des actifs, DHCP |
| 4 | Est-ce nouveau pour cet hôte ? | Les mêmes sources pour les semaines précédentes |
| 5 | Un autre hôte a-t-il fait la même chose ? | Rechercher sur tous les hôtes la même destination ou le même domaine |

## Des modèles de requêtes en KQL

Ces modèles fonctionnent dans la console KQL du lab (et, avec de légères adaptations, dans la plupart des SIEM). Les commentaires restent en anglais, comme dans la console :

```text
// Everything one host did, in time order
FlowRecords
| where SrcIP == "10.30.0.12" or DstIP == "10.30.0.12"
| sort by TimeGenerated asc

// Which destinations did it contact, and how much did it send?
FlowRecords
| where SrcIP == "10.30.0.12"
| summarize Flows = count(), Sent = sum(BytesToDst) by DstIP, DstPort

// Biggest senders to the internet overnight
FlowRecords
| where not(DstIP startswith "10.")
| summarize Sent = sum(BytesToDst) by SrcIP
| top 5 by Sent

// Who else looked up the suspicious domain?
DnsLogs
| where Query == "suspicious-domain.example"
| distinct ClientIP
```

Dans l’ordre : toute l’activité d’un hôte, par ordre chronologique ; les destinations qu’il a contactées et le volume envoyé ; les plus gros émetteurs vers Internet pendant la nuit ; les autres hôtes qui ont résolu le domaine suspect.

## Règles de corrélation et références

| Type de règle | Exemple |
| --- | --- |
| **Séquence** | Une alerte IDS sur un hôte, suivie dans l’heure d’une nouvelle connexion interne depuis cet hôte |
| **Seuil** | Plus de 1 Go envoyé en une heure par un serveur vers une seule adresse externe |
| **Nouveau comportement** | Un hôte qui contacte un port ou une zone qu’il n’a pas utilisés au cours des 30 derniers jours |
| **Politique** | Toute connexion de la DMZ vers la zone des serveurs sur un port absent de la liste approuvée |

Les règles ont besoin de **références** (ce qui est normal pour chaque hôte ou zone) et de **réglages** (module 5) pour trouver les vrais problèmes sans submerger les analystes.

:::tip Surveillez le sens des octets
Les enregistrements de flux comptent les octets dans chaque sens. Pour une session entrante, les données qui quittent votre réseau apparaissent comme des octets renvoyés **vers la source**. Un volume important d’« octets vers la source » sur une connexion entrante peut signifier que des données sont extraites par ce biais.
:::

:::takeaway
Normalisez, puis corrélez par adresse et par heure : alerte, activité environnante, actif, historique et autres hôtes. Utilisez des modèles de requêtes simples pour lister l’activité, totaliser les octets et trouver qui d’autre est concerné, et construisez des règles de corrélation sur les séquences, les seuils, les nouveaux comportements et la politique, avec des références et des réglages.
:::
