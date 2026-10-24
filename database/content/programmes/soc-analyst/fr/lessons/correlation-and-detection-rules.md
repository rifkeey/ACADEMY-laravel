## De la recherche à la détection

Une règle de détection est une requête enregistrée, assortie d’une planification et d’une logique d’alerte. Elle s’exécute selon un calendrier (par exemple toutes les 5 minutes sur les 10 dernières minutes de données) et crée une alerte lorsque ses résultats remplissent une condition.

Chaque règle doit être documentée avec :

- **Objet** : le comportement détecté, en une phrase.
- **Sources de données** : les tables ou types de journaux nécessaires.
- **Logique** : la requête et les seuils.
- **Correspondance ATT&CK** : la ou les techniques couvertes.
- **Faux positifs connus** : les activités légitimes qui y ressemblent.
- **Consignes de tri** : les trois premières vérifications à faire par l’analyste.
- **Responsable et date de revue**.

## Les types de logique de détection

| Type | Description | Exemple |
| --- | --- | --- |
| Atomique / signature | Un seul événement correspond à des attributs malveillants connus | L’empreinte d’un processus correspond à un échantillon de rançongiciel connu |
| Seuil | Un décompte dépasse une limite sur une fenêtre de temps | Plus de 20 échecs de connexion pour un même compte en 10 minutes |
| Corrélation | Des événements différents, en séquence ou en combinaison | De nombreux échecs suivis d’une réussite depuis la même IP |
| Anomalie / référence | Le comportement s’écarte de l’historique de l’entité | Un utilisateur télécharge 50 fois son volume de données habituel |

Les règles de corrélation sont en général plus fiables que les règles de seuil, car elles décrivent la *progression* de l’attaquant, et pas seulement du bruit. Par exemple, des « échecs de connexion » seuls sont bruyants ; des « échecs de connexion suivis d’une connexion réussie depuis la même source, puis de la création d’une règle de transfert dans la boîte de réception » constituent un indicateur fort de prise de contrôle de compte.

## Écrire d’abord une règle de corrélation en langage courant

Avant d’écrire une requête, rédigez la logique sous forme de phrases :

```text
Pour chaque IP source sur la dernière heure :
  compter les connexions échouées (utilisateurs cibles distincts)
  rechercher toute connexion réussie depuis cette même IP
Alerter si : utilisateurs distincts en échec >= 10 ET au moins une réussite existe
Résultat : IP source, liste des utilisateurs ciblés, l’utilisateur connecté avec succès, premier et dernier horodatage
```

Cela rend la relecture par un pair possible, et la requête devient un exercice de traduction plutôt qu’un acte d’invention.

## Les fenêtres de temps

Deux fenêtres comptent. La **période d’examen** (*lookback*, jusqu’où chaque exécution remonte) doit dépasser la **fréquence** (tous les combien la règle s’exécute) augmentée du délai d’ingestion prévu, sinon des événements passeront entre deux exécutions. Une règle exécutée toutes les 5 minutes avec une période d’examen de 5 minutes manquera les événements qui arrivent avec 3 minutes de retard. Des fenêtres qui se chevauchent peuvent créer des alertes en double ; les SIEM proposent des paramètres de suppression ou de regroupement pour y remédier.
