Le problème le plus difficile de la détection n’est pas de trouver les attaques. C’est de les trouver **sans être submergé par des alertes qui n’en sont pas**. Cette leçon vous donne le vocabulaire pour mesurer et discuter la qualité des alertes.

## Quatre issues possibles

|  | Alerte levée | Pas d’alerte |
| --- | --- | --- |
| **Activité réellement malveillante** | **Vrai positif (VP)** : la détection a fonctionné | **Faux négatif (FN)** : une attaque manquée, l’issue la plus dangereuse |
| **Activité bénigne** | **Faux positif (FP)** : des efforts perdus et une confiance entamée dans l’outil | **Vrai négatif (VN)** : un silence justifié |

Une cinquième catégorie est utile en pratique : le **vrai positif bénin**. La règle s’est déclenchée correctement sur ce qu’elle devait détecter, mais l’activité était autorisée (le scanner de vulnérabilités qui balaie, un administrateur qui utilise un outil de gestion à distance). Ce n’est pas une erreur de la règle ; c’est un manque de contexte.

## Pourquoi les alertes sont surtout du bruit

Le trafic malveillant est rare comparé au trafic normal. Même une règle qui a raison 99 % du temps sur le trafic bénin, appliquée à des millions de connexions, produit des milliers de faux positifs pour chaque attaque réelle. C’est ce qu’on appelle le **problème du taux de base**, et c’est pourquoi le réglage (leçon 4) compte davantage que l’ajout de nouvelles règles.

Le coût humain a un nom : la **fatigue d’alerte**. Quand les analystes voient des centaines de faux positifs, ils se mettent à survoler, et l’attaque réelle est clôturée avec le reste.

## Mesurer une détection

Pas besoin de statistiques avancées, mais il vous faut des chiffres :

| Mesure | Question | Comment l’estimer |
| --- | --- | --- |
| **Volume** | Combien d’alertes par jour cette règle produit-elle ? | Compter |
| **Taux de vrais positifs** des alertes | Parmi les alertes clôturées le mois dernier, combien étaient réelles ou méritaient une enquête ? | Alertes clôturées par qualification |
| **Couverture** | Quels comportements d’attaquant détectons-nous, tout simplement ? | Rattacher les détections aux techniques ATT&CK |
| **Délai de tri** | De combien de temps un analyste a-t-il besoin pour décider ? | D’après l’horodatage des tickets |

Consigner une **qualification** (vrai positif, vrai positif bénin, faux positif) avec une brève raison à la clôture de chaque alerte, c’est ce qui rend ces chiffres possibles.

## Les attaques manquées sont invisibles

Les faux positifs sont visibles et agaçants. **Les faux négatifs sont silencieux** : vous ne les découvrez que plus tard, lors d’un incident. Des moyens de les trouver avant un attaquant :

- **La cartographie de la couverture** : quelles techniques ATT&CK pertinentes pour votre environnement n’ont aucune détection ?
- **Les tests** : rejouer un trafic de test connu et sans danger, ou utiliser des outils de test autorisés, pour confirmer qu’une détection se déclenche.
- **La revue rétrospective** : après chaque incident, demandez-vous quelles alertes auraient dû se déclencher plus tôt.

:::tip Régler pour la précision, tester pour la couverture
Un programme de détection progresse dans deux directions à la fois : retirer le bruit des règles qui se déclenchent trop souvent (précision) et ajouter ou tester des règles pour les comportements que personne ne détecte (couverture). Ne faire que la première rend le SOC plus silencieux, pas plus sûr.
:::

:::takeaway
Classez l’issue de chaque alerte (vrai positif, vrai positif bénin, faux positif), consignez-la à la clôture, et mesurez le volume, l’exactitude et la couverture. Parce que les attaques sont rares, de faibles taux d’erreur créent beaucoup de bruit, et les erreurs les plus dangereuses sont les faux négatifs silencieux.
:::
