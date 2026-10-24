Deux sujets terminent ce module. D’abord, le segment le plus précieux de tous : le **plan d’administration**, c’est-à-dire les interfaces qui servent à configurer le réseau et les serveurs. Ensuite, la **validation** : prouver que la segmentation que vous avez conçue est bien celle que vous avez.

## Protéger le plan d’administration

Chaque commutateur, routeur, pare-feu, hyperviseur et serveur possède une interface d’administration : SSH, une console web, une API, une carte de gestion à distance. Qui les contrôle contrôle tout. Traitez-les comme une zone à part entière, la mieux protégée.

| Mesure | Ce que cela signifie chez Kanem |
| --- | --- |
| **Réseau d’administration dédié** | Les interfaces d’administration ne sont accessibles que depuis le VLAN 199, jamais depuis les zones utilisateurs, invités ou DMZ |
| **Hôtes de rebond / postes d’administration privilégiés** | Les administrateurs se connectent d’abord à un hôte de rebond durci et surveillé (ou utilisent un poste d’administration dédié), et seulement de là à l’infrastructure |
| **Authentification forte** | Comptes nominatifs (pas de compte « admin » partagé), MFA, authentification centralisée (RADIUS/TACACS+ ou l’annuaire) |
| **Protocoles sécurisés uniquement** | SSH et HTTPS ; Telnet, HTTP et les anciennes versions de SNMP désactivés |
| **Accès hors bande** | Un chemin distinct (serveur de consoles, lien dédié) pour reprendre la main sur les équipements si le réseau principal tombe, protégé tout aussi fortement |
| **Journalisation et contrôle des changements** | Chaque connexion et chaque modification de configuration journalisées de façon centralisée et rattachées à un ticket de changement (module 3) |
| **Identités d’administration distinctes** | Les administrateurs utilisent un compte privilégié distinct, et non leur compte de messagerie et de navigation |

Souvenez-vous de la revue du pare-feu du module 3 : la règle 11 exposait la console d’administration du pare-feu à la zone users. Cette seule règle sape toutes les autres mesures que le pare-feu applique.

## Valider la segmentation

Un schéma est une affirmation. La validation en est la preuve. La NIST SP 800-115 décrit des méthodes de test de sécurité ; pour la segmentation, l’essentiel est :

1. **Tester les flux depuis chaque zone.** Pour chaque zone, tentez les connexions qui doivent réussir et un échantillon de celles qui doivent échouer, avec une autorisation et un périmètre défini. Consignez le résultat, comme le font les correcteurs des labs de ce cours.
2. **Revoir la configuration d’application** : politiques des pare-feu, ACL des commutateurs, configurations de référence des pare-feu d’hôte. Recherchez les règles `any`, les refus masqués et les exceptions non documentées.
3. **Comparer avec le trafic observé** : les journaux de flux ne doivent montrer aucun trafic sur les chemins censés être fermés. Toute correspondance est soit une erreur de configuration, soit une dépendance inconnue.
4. **Recommencer après les changements et selon un calendrier.** La segmentation se dégrade à mesure que des règles sont ajoutées. De nombreux régimes réglementaires applicables aux environnements de cartes de paiement, par exemple, exigent des tests de segmentation réguliers.

```text
Segmentation test record (extract, synthetic)
From          To            Port   Expected  Result   Evidence
users         database      1433   deny      deny ✔   FW-DC-01 rule 40 hit
dmz           database      1433   deny      ALLOW ✘  rule 17 "temp migration" still active → ticket SEC-418
management    cards         22     allow     allow ✔
guest         application   443    deny      deny ✔
```

*Extrait (fictif) d’un compte rendu de test de segmentation : la deuxième ligne échoue, car la règle 17 « temp migration » (migration temporaire) est toujours active.*

Une ligne en échec est un constat, avec un ticket, un responsable et une date de nouveau test.

:::tip Construire les cas de test à partir des besoins
Si chaque flux autorisé provient d’un besoin écrit (module 3, leçon 3), les cas de test s’écrivent d’eux-mêmes : chaque besoin est un test « autorisation attendue », et chaque autre couple de zones est un test « refus attendu ». Le lab de ce module évalue votre conception exactement de cette façon.
:::

:::takeaway
Placez les interfaces d’administration dans leur propre zone, la plus restreinte, accessible uniquement par des hôtes de rebond durcis, avec une authentification forte et une journalisation complète. Validez ensuite la segmentation par des tests consignés depuis chaque zone, une revue de la configuration et une comparaison avec les journaux de flux, après chaque changement important.
:::
