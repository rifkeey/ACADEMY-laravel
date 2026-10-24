L’accès distant ne vaut que ce que vaut son authentification. Cette leçon couvre la MFA et l’accès conditionnel, puis une méthode structurée pour dépanner les échecs d’accès distant que toute équipe réseau rencontre.

## L’authentification multifacteur

La MFA combine des facteurs de catégories différentes : ce que vous savez (mot de passe), ce que vous possédez (un équipement ou une clé de sécurité), ce que vous êtes (biométrie). La NIST SP 800-63B, qui fait partie des Digital Identity Guidelines, décrit les types d’authentificateurs et les niveaux d’assurance.

Toutes les MFA ne se valent pas :

| Méthode | Résistance à l’hameçonnage |
| --- | --- |
| Codes par SMS ou appel vocal | Faible : les codes peuvent être interceptés ou hameçonnés |
| Codes d’application d’authentification (fondés sur le temps) | Moyenne : les codes peuvent encore être hameçonnés en temps réel |
| Approbations par notification | Moyenne : vulnérables aux utilisateurs qui approuvent des demandes qu’ils n’ont pas lancées ; la saisie d’un nombre affiché aide |
| **Clés de sécurité et authentificateurs intégrés FIDO2 / WebAuthn**, cartes à puce | **Élevée** : liés au site authentique, ils résistent à l’hameçonnage |

Les recommandations de la CISA préconisent une **MFA résistante à l’hameçonnage**, en commençant par les administrateurs et l’accès distant.

## L’accès conditionnel

Les politiques d’accès conditionnel décident **si et comment** une connexion est autorisée, à partir de signaux tels que :

| Signal | Exemple de politique |
| --- | --- |
| Utilisateur et groupe | Les administrateurs ont toujours besoin d’une MFA résistante à l’hameçonnage |
| Équipement | Seuls les équipements gérés et conformes peuvent atteindre l’application des prêts |
| Lieu et réseau | Bloquer les connexions depuis les pays où Kanem n’a pas de personnel |
| Sensibilité de l’application | Des exigences plus fortes pour l’application bancaire centrale que pour le guide du personnel |
| Risque | Exiger une nouvelle authentification ou bloquer quand le fournisseur d’identité signale une connexion à risque |

Microsoft Entra Conditional Access en est un exemple ; d’autres fournisseurs d’identité proposent des équivalents. C’est l’idée du moteur de politique du zero trust (module 4) appliquée à la connexion.

## Dépanner l’accès distant

Quand une connexion échoue, parcourez les couches dans l’ordre et recueillez des preuves à chaque étape :

| Étape | Question | Preuves |
| --- | --- | --- |
| 1. Joignabilité | Le client peut-il seulement atteindre le VPN ou la passerelle d’accès ? | État du réseau du client ; passerelle joignable sur son port |
| 2. Authentification | L’utilisateur ou l’équipement s’est-il authentifié ? Quel facteur a échoué ? | Journaux de connexion du fournisseur d’identité ; journaux d’authentification de la passerelle |
| 3. Politique | L’accès conditionnel ou la conformité de l’équipement l’ont-ils bloqué ? | Résultat dans le journal de connexion et nom de la politique |
| 4. Tunnel / session | Le tunnel ou la session s’est-il établi ? | Journaux de la passerelle : erreurs de négociation, paramètres incohérents |
| 5. Routage et accès | Une fois connecté, le trafic atteint-il la ressource ? | Adresse attribuée, routes, journaux du pare-feu pour la destination |
| 6. Résolution de noms | Le nom interne se résout-il correctement ? | Paramètres DNS et résolutions sur le client |

Causes fréquentes : un certificat expiré (leçon 3), un mot de passe ou un équipement MFA changé, un équipement devenu non conforme, des paramètres de tunnel incohérents (leçon 2), des plages d’adresses qui se chevauchent entre le réseau domestique et celui de l’entreprise, et des routes ou des règles de pare-feu manquantes pour le pool d’adresses du VPN.

:::tip Uniquement des corrections sûres
Sous pression, il est tentant de « réparer » l’accès distant en assouplissant la sécurité : désactiver la vérification des certificats, exempter un utilisateur de MFA, ou élargir une règle de pare-feu à tout le pool VPN. Chacune de ces mesures échange un ticket de support contre une faiblesse durable. Corrigez la cause profonde et, si une exception temporaire est inévitable, donnez-lui un responsable et une date d’expiration.
:::

:::takeaway
Utilisez une MFA résistante à l’hameçonnage pour les administrateurs et l’accès distant, et l’accès conditionnel pour décider des connexions selon l’utilisateur, l’équipement, le lieu, l’application et le risque. Dépannez l’accès distant couche par couche, preuves à l’appui, et corrigez les causes profondes au lieu d’affaiblir les mesures.
:::
