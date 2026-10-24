Le confinement empêche le préjudice de s’étendre. Mené sans précaution, il détruit les preuves dont vous avez besoin pour comprendre ce qui s’est passé, ou alerte l’intrus alors que d’autres points d’appui subsistent. Un bon confinement est rapide, réfléchi et coordonné.

## Les options de confinement réseau

| Option | Effet | Contrepartie |
| --- | --- | --- |
| **Bloquer une adresse ou un domaine externe** (pare-feu, *sinkhole* DNS ou DNS protecteur) | Coupe les connexions externes connues | Facile à contourner si l’intrus dispose d’autres adresses |
| **Désactiver ou resserrer la règle qui a permis l’accès** | Ferme le point d’entrée | Peut affecter les utilisateurs légitimes de la règle |
| **Isoler l’hôte** (VLAN de quarantaine du NAC, désactivation du port du commutateur, pare-feu d’hôte, groupe de quarantaine cloud) | Arrête tout le trafic vers et depuis l’hôte | Le service est indisponible ; les preuves volatiles peuvent être perdues si l’hôte est éteint |
| **Restreindre une zone** (règles temporaires entre zones) | Limite la propagation depuis une zone compromise | Impact large sur l’activité |
| **Désactiver des comptes et réinitialiser les identifiants** | Retire l’accès volé | Doit couvrir en même temps tous les comptes concernés |

Choisissez l’action la plus étroite qui arrête le préjudice, et convenez-en avec le responsable de l’incident et le responsable du système. Si des données sont en train de sortir, le confinement passe en premier.

## Préserver les preuves avant et pendant le confinement

| Preuve | Action |
| --- | --- |
| Journaux du pare-feu, des flux, de l’IDS, du DNS et du proxy | Exportez la fenêtre de l’incident depuis le SIEM et consignez les empreintes ; vérifiez que la politique de conservation ne les supprimera pas |
| État de l’hôte | Demandez au responsable du système de réaliser des images de la mémoire et du disque **avant** la reconstruction ; **isolez plutôt que d’éteindre** |
| Trafic réseau | Lancez une capture de paquets ciblée si l’activité se poursuit (module 8) |
| Configuration | Sauvegardez le jeu de règles du pare-feu et l’historique des changements dans leur état du moment |
| Vos propres actions | Consignez chaque modification de confinement, avec l’heure et son auteur |

La NIST SP 800-86 et la SP 800-61r3 décrivent le traitement des preuves. Maintenez une chaîne de possession pour tout ce qui pourrait servir dans une procédure disciplinaire ou judiciaire.

## Éradication et rétablissement

Après le confinement :

1. **Supprimez la cause** : fermez ou corrigez la règle, appliquez les correctifs au service exposé, supprimez les comptes non autorisés.
2. **Reconstruisez** les hôtes compromis à partir d’images saines connues plutôt que de les nettoyer.
3. **Réinitialisez les identifiants** présents sur les systèmes compromis ou utilisés par eux.
4. **Rétablissez** les services et **surveillez étroitement** toute récidive.
5. **Vérifiez** : relancez les requêtes qui ont permis de découvrir l’incident ; rien de nouveau ne doit apparaître.

:::warning Ne détruisez pas la scène de crime
Effacer et reconstruire un serveur dans la première heure donne une impression de fermeté, mais cela peut rendre impossible de savoir ce qui a été pris, ou comment. Isolez d’abord, préservez, puis reconstruisez.
:::

:::takeaway
Confinez avec l’action efficace la plus étroite (bloquer, fermer la règle, isoler, réinitialiser), convenue avec le responsable de l’incident, et rapidement si des données sortent. Préservez d’abord les journaux, les images des hôtes, la configuration et la trace de vos propres changements, puis éradiquez, reconstruisez, réinitialisez, rétablissez et vérifiez.
:::
