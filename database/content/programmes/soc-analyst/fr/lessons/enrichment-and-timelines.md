## L’enrichissement : donner du sens aux entités

L’enrichissement répond, pour chaque entité d’une alerte, à la question : « que savons-nous de cet élément ? »

| Entité | Enrichissement interne | Enrichissement externe |
| --- | --- | --- |
| Utilisateur | Rôle, service, responsable, groupes à privilèges, réinitialisations récentes du mot de passe, lieux de connexion habituels | Services de vérification d’exposition à des fuites (lorsque la politique le permet) |
| Poste | Propriétaire, fonction, criticité, système d’exploitation, niveau de correctifs, état de l’EDR | sans objet |
| Adresse IP | Nous appartient-elle ? Plage VPN ? Sortie cloud ? Déjà vue ? | Géolocalisation, ASN, réputation, DNS passif |
| Domaine / URL | Déjà visité par d’autres postes ? Catégorie dans le proxy | Ancienneté de l’enregistrement, réputation, résultats de bac à sable |
| Empreinte de fichier | Vue sur d’autres postes ? Date de première apparition | Services de réputation, rapports de bac à sable |

Considérez la réputation externe comme un élément parmi d’autres, pas comme un verdict. Une réputation « saine » pour un domaine enregistré récemment signifie souvent simplement que personne ne l’a encore signalé.

> **Vie privée et sécurité opérationnelle :** soumettre un fichier interne à un bac à sable public peut divulguer des données confidentielles et avertir l’attaquant que vous l’avez repéré. Suivez la politique de votre organisation sur les services d’enrichissement autorisés à recevoir telle ou telle donnée.

## Construire une chronologie

La chronologie est le support d’investigation le plus utile qui soit. Elle transforme des preuves éparses en un récit que l’on peut mettre à l’épreuve.

Les règles d’une bonne chronologie :

1. **Un seul fuseau horaire**, normalement UTC, indiqué en tête.
2. **Un événement par ligne** : heure, source, entité, ce qui s’est passé et référence de la preuve.
3. **Uniquement des faits dans les colonnes principales** ; l’interprétation va dans une colonne de notes distincte.
4. **Signalez les lacunes.** « Aucun journal de proxy disponible de 02:00 à 02:30 (panne du collecteur) » est une information importante.

```text
Chronologie – INC-4471 – toutes les heures en UTC
02:01:15  Entra ID   203.0.113.45   Première connexion échouée (début de la pulvérisation)          Q-01
02:14:09  Entra ID   f.manager      Connexion réussie depuis 203.0.113.45                           Q-01
02:16:40  Exchange   f.manager      Règle de boîte de réception créée : transfert vers l’extérieur  Q-03
02:21:05  SharePoint f.manager      312 fichiers téléchargés depuis /Finance                         Q-04
02:40:00  Entra ID   f.manager      Connexion depuis l’appareil habituel à Lagos (utilisateur)       Q-01
```

À la lecture de cette chronologie, le récit est clair : pulvérisation de mots de passe, prise de contrôle du compte, persistance au moyen d’une règle de messagerie et collecte de données, le tout en 20 minutes. La connexion de 02:40 est probablement celle du véritable utilisateur, un contexte utile lorsque vous le contacterez.

## Mettre le récit à l’épreuve

Une fois que vous avez une hypothèse, cherchez les éléments qui la **réfuteraient**. Si vous pensez que la connexion de 02:14 était celle de l’attaquant, comparez son appareil et son agent utilisateur aux valeurs habituelles de l’utilisateur. S’ils correspondent exactement à l’ordinateur portable de l’utilisateur, votre hypothèse doit être revue.
