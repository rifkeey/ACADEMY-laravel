## Une réponse structurée

La réponse aux incidents transforme un incident confirmé en une situation maîtrisée. La publication NIST SP 800-61 Rev. 3 (2025) organise la réponse aux incidents autour des fonctions du NIST Cybersecurity Framework 2.0 : les activités de préparation relèvent de **Govern, Identify et Protect** ; la détection et l’analyse, de **Detect** ; le travail de réponse et de rétablissement, de **Respond** et **Recover**. La révision 2, plus ancienne, décrivait un cycle de vie en quatre phases (préparation ; détection et analyse ; confinement, éradication et rétablissement ; activités post-incident) que vous retrouverez encore dans les plans de nombreuses organisations. Les notions ci-dessous valent pour les deux.

## Le confinement

Le confinement limite les dégâts pendant que vous investiguez. Choisissez une stratégie en fonction de la menace et des preuves dont vous avez besoin :

| Action | Effet | Contrepartie |
| --- | --- | --- |
| Isoler le poste via l’EDR | Coupe l’accès réseau, sauf vers la console EDR | Rapide ; préserve le disque et la mémoire ; l’utilisateur perd son travail en cours |
| Désactiver le compte / révoquer les sessions | Empêche l’usage des identifiants volés | Peut perturber l’activité ; l’attaquant peut détenir d’autres comptes |
| Bloquer une IP ou un domaine | Interrompt le C2 | L’attaquant peut facilement changer d’infrastructure |
| Réinitialiser les identifiants | Invalide les mots de passe volés | Doit inclure les comptes de service et les jetons ; à faire après avoir établi le périmètre |
| Segmenter le réseau | Arrête le déplacement latéral | Nécessite l’équipe réseau ; impact étendu |

**Établissez le périmètre avant de réinitialiser.** Si un attaquant détient cinq comptes et que vous en réinitialisez un, il apprend qu’il a été découvert et garde les quatre autres. Coordonnez les actions de confinement pour qu’elles aient lieu simultanément lorsque c’est possible.

## L’éradication

Supprimez les points d’appui de l’attaquant : logiciels malveillants, mécanismes de persistance, comptes non autorisés, règles de messagerie malveillantes, applications OAuth, ainsi que la vulnérabilité ou la mauvaise configuration qui lui a permis d’entrer. Utilisez systématiquement la liste de contrôle de persistance de la leçon précédente.

## Le rétablissement

Remettez les systèmes en fonctionnement normal à partir de sources saines connues, surveillez de près toute nouvelle intrusion, et ne déclarez le rétablissement terminé que lorsque la surveillance ne montre plus d’activité malveillante pendant une période convenue. Les sauvegardes doivent être vérifiées saines avant toute restauration.

## Les activités post-incident

Peu après la clôture, organisez un retour d’expérience sans recherche de coupable :

- Que s’est-il passé, et quand l’avons-nous détecté par rapport à son début ?
- Qu’est-ce qui a bien fonctionné ?
- Quelles lacunes de détection, de journalisation, de processus ou de contrôle avons-nous trouvées ?
- Quelles actions, avec des responsables et des dates, vont les combler ?

C’est à cette étape que la réponse aux incidents produit sa valeur. Les organisations qui la sautent ont tendance à revivre le même incident.

## Considérations juridiques et réglementaires

Certains incidents déclenchent des obligations légales, comme la notification à une autorité de protection des données ou aux personnes concernées dans un délai défini, ou le signalement à un régulateur sectoriel. Au Nigeria, le Nigeria Data Protection Act 2023 prévoit des obligations de notification des violations, contrôlées par la Nigeria Data Protection Commission. Les analystes ne prennent pas ces décisions, mais ils doivent escalader rapidement et préserver les preuves dont les équipes juridiques et conformité auront besoin.
