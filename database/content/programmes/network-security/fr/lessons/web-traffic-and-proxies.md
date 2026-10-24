Le trafic web (HTTP et HTTPS) représente l’essentiel de ce qui franchit le périmètre de Kanem. Le contrôler et l’observer, généralement au moyen d’un **proxy** ou d’une **passerelle web sécurisée**, est l’un des moyens les plus efficaces de réduire les risques liés aux utilisateurs et aux logiciels indésirables.

## Lire le HTTP d’un coup d’œil

La sémantique de HTTP est définie dans la RFC 9110. Une requête indique une **méthode**, une **cible** et des **en-têtes** ; une réponse renvoie un **code d’état** :

| Élément | Exemples | Intérêt pour la sécurité |
| --- | --- | --- |
| Méthode | GET, POST, PUT | Des envois inattendus (POST/PUT) vers des sites inconnus peuvent signaler des données qui sortent |
| Hôte et chemin | `portal.kanem.example/loans/apply` | Où va l’utilisateur ou le logiciel |
| User-Agent | Identifiant du navigateur ou de l’application | Des valeurs inhabituelles ou absentes peuvent identifier un logiciel indésirable |
| Code d’état | 200 OK, redirection 301/302, 403 interdit, 404 introuvable | Des séries d’erreurs peuvent révéler un balayage ou une erreur de configuration |
| Octets entrants et sortants | Tailles des requêtes et des réponses | De gros envois vers des destinations inhabituelles méritent une revue |

Avec HTTPS, un capteur sans déchiffrement ne voit que les métadonnées du module 5 (adresses, horaires, volume, généralement le nom du serveur). Un proxy qui termine la session TLS (pour les catégories autorisées) peut voir la requête complète.

## Proxys et passerelles web sécurisées

| Fonction | Avantage |
| --- | --- |
| **Point de sortie central** | Seul le proxy communique avec Internet ; le trafic web sortant direct des serveurs et des ordinateurs portables peut alors être bloqué (module 3) |
| **Filtrage par URL et par catégorie** | Bloquer les catégories connues comme malveillantes ou indésirables ; autoriser les catégories métier |
| **Authentification** | Les journaux indiquent quel **utilisateur**, et pas seulement quelle adresse IP |
| **Analyse antimalware et bac à sable** | Les fichiers sont vérifiés avant d’atteindre les équipements |
| **Inspection TLS sélective** | Inspection du contenu lorsqu’elle est justifiée et légale (module 5, leçon 5) |
| **Journalisation** | Un historique complet de l’activité web pour les enquêtes |

Pour les utilisateurs à distance, des passerelles fournies depuis le cloud appliquent la même politique, où que se trouve l’utilisateur.

## Des choix de protocoles sécurisés

| Usage | À privilégier | À éviter |
| --- | --- | --- |
| Web | HTTPS partout, y compris pour les applications internes | HTTP en clair pour tout ce qui comporte des identifiants ou des données personnelles |
| Transport de la messagerie entre serveurs | TLS entre serveurs de messagerie lorsqu’il est pris en charge | Relais non chiffrés pour le courrier sensible |
| Transfert de fichiers | SFTP, HTTPS | FTP |

## L’authentification de la messagerie en bref

La sécurité du web et celle de la messagerie se rejoignent dans l’hameçonnage. Trois mécanismes publiés dans le DNS aident les serveurs de messagerie destinataires à décider si un message provient réellement du domaine de Kanem (la NIST SP 800-177 Rev. 1, *Trustworthy Email*, les couvre) :

| Mécanisme | Ce qu’il fait |
| --- | --- |
| **SPF** (RFC 7208) | Liste les serveurs autorisés à envoyer du courrier pour le domaine |
| **DKIM** (RFC 6376) | Ajoute une signature qui prouve que le message a été autorisé par le domaine et n’a pas été modifié |
| **DMARC** (RFC 9989, qui a remplacé la RFC 7489) | Indique aux destinataires que faire quand les vérifications SPF/DKIM échouent, et renvoie des rapports au propriétaire du domaine |

Publier les trois, et faire évoluer DMARC vers une politique contraignante, rend beaucoup plus difficile l’envoi de courriels se faisant passer de façon crédible pour Kanem.

:::takeaway
Faites passer le trafic web par un proxy ou une passerelle web sécurisée pour le filtrage, l’attribution aux utilisateurs, l’analyse antimalware et la journalisation ; privilégiez partout les protocoles sécurisés ; et protégez le domaine de messagerie de Kanem avec SPF, DKIM et DMARC.
:::
