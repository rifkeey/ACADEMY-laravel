Le tri transforme une alerte en décision. Le réglage transforme de nombreuses décisions en une meilleure détection. Menés ensemble, ils permettent aux 400 alertes du lundi de devenir une file d’attente gérable.

## Une routine de tri

Pour chaque alerte, répondez à ces questions dans l’ordre et notez les réponses :

1. **Que détecte la règle, et à quoi ressemblerait un vrai positif ?** Lisez la description de la règle avant les données.
2. **Qui est concerné ?** Identifiez la source et la destination : système, responsable, zone, criticité (plan d’adressage, inventaire des actifs).
3. **Est-ce attendu ?** La source est-elle un scanner autorisé, un système de supervision, une application métier connue ? Existe-t-il un ticket de changement ?
4. **Que s’est-il passé d’autre autour ?** Consultez les journaux de flux, le DNS, l’authentification et les données des postes pour les mêmes hôtes sur la même période.
5. **Décision** : vrai positif (escalader), vrai positif bénin (documenter pourquoi c’est autorisé) ou faux positif (documenter pourquoi la règle s’est trompée).

| Qualification | Étape suivante |
| --- | --- |
| Vrai positif | Escalader vers la gestion des incidents avec les preuves recueillies (module 9) |
| Vrai positif bénin | Clôturer avec la raison ; envisager une exception de réglage pour cette source et cette règle précises |
| Faux positif | Clôturer avec la raison ; proposer une modification de la règle |
| Impossible de trancher | Escalader avec ce que vous avez trouvé ; ne pas clôturer comme « rien » |

## Régler sans créer d’angles morts

Le réglage réduit le bruit. Mal fait, il supprime aussi les alertes dont vous aviez besoin. Préférez la modification la **plus étroite** qui règle le problème :

| Technique | Exemple | Risque si c’est mal fait |
| --- | --- | --- |
| **Exception ciblée** | Supprimer « balayage de ports interne » uniquement quand la source est le scanner de vulnérabilités 10.99.0.20 pendant sa plage planifiée | Périmètre trop large → un attaquant sur un autre hôte est aussi masqué |
| **Seuils** | Alerter à partir de 20 connexions échouées par minute, et non d’une seule | Seuil trop élevé → une activité lente et discrète est manquée |
| **Enrichissement du contexte** | Relever la gravité quand la destination se trouve dans la zone cards | Aucun, si les données sur les actifs sont exactes |
| **Réécriture de la règle** | Rendre le motif plus précis | Exige des tests sur des exemples connus comme sains et comme malveillants |
| **Désactivation** | Désactiver la règle | Crée un angle mort : seulement avec une raison documentée et un remplacement, ou un risque accepté |

Chaque modification de réglage doit être **documentée** (quoi, pourquoi, qui l’a approuvée, date de revue) et **revue** : une exception pour un scanner qui a depuis changé d’adresse est une façon classique de perdre en visibilité.

## Démonstration : trois alertes

| Alerte | Éléments | Décision |
| --- | --- | --- |
| Balayage de ports interne depuis 10.99.0.20 vers la zone des serveurs, 02:00 | 10.99.0.20 est le scanner de vulnérabilités ; le ticket de changement CHG-310 le planifie chaque nuit de 01:30 à 03:30 | Vrai positif bénin → exception ciblée pour cette source, cette règle et cette plage |
| Connexion sortante vers un domaine récemment enregistré depuis 10.20.5.12 | Ordinateur portable d’un utilisateur du marketing ; le domaine appartient à un nouveau partenaire annoncé la semaine dernière | Bénin ; ajouter le domaine du partenaire à la liste d’autorisation après vérification de son propriétaire |
| Balayage de ports interne depuis 10.20.7.44, 02:55 | Adresse du pool VPN ; aucun ticket ; le même hôte se connecte en sortie, de façon répétée, à une adresse externe inconnue toutes les cinq minutes | **Vrai positif** → escalader |

La règle déclenchée pour la première et la troisième alerte est la même. Seul le contexte les distingue, et c’est pourquoi l’exception doit cibler le scanner, et non la règle.

:::takeaway
Triez chaque alerte avec un jeu fixe de questions, consignez la qualification et sa raison, et escaladez en cas de doute. Réglez avec la modification la plus étroite qui supprime le bruit, documentez et revoyez chaque exception, et ne désactivez jamais une règle sans accepter explicitement l’angle mort qu’elle crée.
:::
