Une analyse que personne d’autre ne peut suivre ni reproduire n’a guère d’utilité. Un bon rapport de constats permet à un collègue d’ouvrir le même fichier, d’appliquer les mêmes filtres, de voir les mêmes trames et d’arriver à la même conclusion.

## Séparer les faits de l’interprétation

| Faits (ce que montre la capture) | Interprétation (ce que vous en concluez) |
| --- | --- |
| « Les trames 12, 14 et 17 sont des SYN de 10.40.9.21 vers 10.10.0.16:443 à 0, 1 et 3 secondes ; aucune réponse n’a été capturée. » | « Quelque chose entre le client et 10.10.0.16 rejette les connexions vers le port 443, très probablement une règle de pare-feu. » |

Rédigez les faits avec précision, puis interprétez-les, et indiquez votre degré de confiance. Recommandez ce qu’il faut vérifier ensuite pour confirmer.

## La structure d’un rapport de constats

| Rubrique | Contenu |
| --- | --- |
| **Résumé** | Deux ou trois phrases : ce qui n’allait pas, la cause, ce qu’il faut faire |
| **Périmètre** | Nom du fichier, empreinte SHA-256, lieu et heure de la capture (UTC), auteur, éventuel filtre de capture |
| **Méthode** | Filtres et outils utilisés |
| **Constats** | Chaque constat avec les numéros de trame, les heures et les valeurs mesurées (par exemple RTT, délais, décomptes) |
| **Interprétation** | La cause probable, et le degré de confiance |
| **Limites** | Ce que la capture ne peut pas montrer (contenu chiffré, un seul point de capture, paquets perdus par le port miroir) |
| **Recommandations** | Les corrections et les vérifications qui les confirmeraient |

## Les bonnes habitudes

- Notez les **numéros de trame** et les **filtres** au fil de l’eau ; il est beaucoup plus difficile de les retrouver ensuite.
- Utilisez des heures en **UTC** pour qu’elles correspondent aux journaux.
- Citez des valeurs **mesurées** : « RTT de 38 ms », « réponse du serveur après 2,9 s », « 6 retransmissions ».
- Ne collez pas dans le rapport de données personnelles issues de la capture ; décrivez-les plutôt.
- Si d’autres personnes ont besoin des paquets, n’exportez que ceux qui sont pertinents (File → Export Specified Packets) et calculez l’empreinte de l’export.

:::reveal Exemple de constat
**F1 — Le service de reporting refuse les connexions.** Le client 10.40.9.30 a envoyé un SYN à 10.10.0.40:8443 (trame 21, 09:14:02.118 UTC) et a reçu un RST, ACK de 10.10.0.40 en moins de 2 ms (trame 22). La même chose s’est produite lors de deux nouvelles tentatives (trames 25–26, 31–32). *Interprétation :* l’hôte est joignable, mais rien n’écoute sur le port 8443 : le service de reporting est probablement arrêté ou écoute sur un autre port. *Confiance :* moyenne à élevée ; la réinitialisation arrive presque immédiatement depuis l’adresse du serveur, même si un pare-feu configuré pour refuser activement peut donner la même impression. *Recommandation :* vérifier l’état du service et son port configuré sur 10.10.0.40.
:::

:::takeaway
Rédigez des constats que quelqu’un d’autre peut reproduire : consignez le fichier, l’empreinte, le point de capture et les filtres ; donnez les numéros de trame, les heures UTC et les valeurs mesurées ; séparez les faits de l’interprétation ; indiquez les limites ; et recommandez comment confirmer la cause.
:::
