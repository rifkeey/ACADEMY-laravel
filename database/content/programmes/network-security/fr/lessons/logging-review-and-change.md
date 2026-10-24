Un jeu de règles de pare-feu ne se conçoit pas une fois pour toutes. Les applications vont et viennent, les prestataires ont besoin d’accès, des personnes font des « corrections rapides ». Sans journalisation, revue régulière et changements maîtrisés, tout jeu de règles dérive vers « tout autoriser ». Cette leçon couvre le volet opérationnel qui garde une politique honnête.

## Que journaliser

| Journal | Pourquoi c’est important |
| --- | --- |
| **Connexions refusées**, en particulier sur le refus par défaut | Révèle les balayages (ATT&CK T1046), les logiciels malveillants qui tentent d’appeler l’extérieur et les applications mal configurées |
| **Connexions autorisées à travers les frontières sensibles** (vers les serveurs, depuis les serveurs, vers l’administration) | Permet aux enquêteurs de reconstituer ce qu’un attaquant a atteint |
| **Actions d’administration sur le pare-feu lui-même** (connexions, modifications de règles) | Montre qui a modifié la politique et quand ; le pare-feu est une cible de grande valeur |
| **Événements système** (bascule, processeur saturé, journaux perdus) | Un pare-feu qui cesse de journaliser sans prévenir est un angle mort |

Une entrée type du journal des connexions contient de quoi répondre à la question : *qui a parlé à qui, comment, et qu’a décidé le pare-feu* :

```text
2026-03-04T02:13:41Z FW-HQ-01 action=deny rule=12 proto=tcp
  src=10.10.0.14:49822 dst=203.0.113.77:4444 zone_src=servers zone_dst=internet
  bytes=0 reason=policy-deny
```

Envoyez les journaux du pare-feu vers une plateforme centrale de journaux ou un SIEM, avec une heure exacte (NTP) sur chaque équipement. La publication NIST SP 800-92 est la référence de longue date pour la gestion des journaux ; le module Surveillance de la sécurité réseau va plus loin.

:::warning Journalisation désactivée sur le refus par défaut
Certaines équipes désactivent la journalisation de la dernière règle de refus « parce qu’elle fait trop de bruit ». Ce sont précisément les événements qui montrent un attaquant en train de sonder votre réseau. Réduisez le bruit (par exemple en ne journalisant pas un trafic de diffusion connu et inoffensif) au lieu de supprimer les preuves.
:::

## Revoir le jeu de règles

Faites la revue selon un calendrier (beaucoup d’organisations le font chaque trimestre pour les pare-feu critiques) et après les changements importants. Pour chaque règle, demandez :

1. **Est-elle toujours nécessaire ?** Vérifiez le responsable et le ticket. Les règles des systèmes mis hors service doivent disparaître.
2. **Est-elle utilisée ?** Les compteurs de correspondances sur une période significative (par exemple 90 jours) identifient les règles inutilisées. Une règle d’autorisation inutilisée n’est que du risque.
3. **Est-elle aussi étroite que possible ?** Un `any` ? La source ou le port pourraient-ils être plus restreints ?
4. **Est-elle au bon endroit ?** Règles masquées et redondantes (leçon 2).
5. **Est-elle journalisée ?** Au moins les frontières sensibles et le refus par défaut.
6. **Correspond-elle à la conception ?** Par exemple, rien depuis les zones utilisateurs ou invités vers l’administration.

Consignez le résultat de la revue (règle conservée, modifiée ou supprimée, et pourquoi). Ce compte rendu sert de preuve pour les audits et, surtout, pour le prochain relecteur.

## La gestion des changements

Les changements de pare-feu peuvent bloquer l’activité (couper un flux de paiement) ou l’exposer (ouvrir le mauvais port). Traitez-les comme tout autre changement en production. La NIST SP 800-53 Rev. 5 regroupe cela sous le contrôle des changements de configuration (CM-3) : les changements sont proposés, examinés, approuvés, testés, documentés et enregistrés.

Une bonne demande de changement de pare-feu répond aux points suivants :

| Rubrique | Contenu |
| --- | --- |
| Résumé | Ce qui change, en une phrase |
| Règles concernées | Numéros des règles et définitions avant / après |
| Justification métier | Le besoin, le responsable, le ticket |
| Risque à ne rien changer | Pourquoi c’est important maintenant |
| Impact métier | Qui ou quoi pourrait être affecté, et quand le changement aura lieu |
| Tests et validation | Comment vous confirmerez que cela fonctionne et que rien d’autre n’est cassé (connexions de test, compteurs de correspondances, supervision) |
| Plan de retour arrière | Comment l’annuler exactement en cas de problème |
| Approbation | Qui l’a approuvé |

:::tip Supprimer une règle risquée dont quelque chose dépend
Une règle large avec beaucoup de correspondances ne peut pas être simplement supprimée. Servez-vous des journaux pour voir quelles sources, destinations et quels ports l’utilisent réellement, écrivez des règles étroites pour le trafic légitime, placez-les au-dessus de la règle large, puis désactivez (sans la supprimer) la règle large pendant une période convenue, journalisation activée. Si rien de légitime ne casse, supprimez-la. C’est ainsi que la « règle temporaire du prestataire » du lab doit être retirée.
:::

## Les référentiels qui l’exigent

- **CIS Critical Security Controls v8.1** : le contrôle 12 (gestion de l’infrastructure réseau) porte sur une infrastructure réseau configurée de façon sécurisée et documentée ; le contrôle 13 (surveillance et défense du réseau) porte sur la collecte des journaux du trafic réseau et les alertes associées.
- **NIST SP 800-53 Rev. 5** : SC-7 (protection des frontières), CM-3 (contrôle des changements de configuration) et la famille AU (journalisation et revue des événements).

Ces référentiels disent *à quoi ressemble* une bonne pratique ; la liste de revue ci-dessus explique *comment* la mettre en œuvre sur un vrai pare-feu.

:::takeaway
Journalisez les refus et les autorisations sensibles, revoyez chaque règle au regard de son responsable, de son usage et de la conception, et modifiez les règles selon un processus documenté, avec des tests et un plan de retour arrière.
:::
