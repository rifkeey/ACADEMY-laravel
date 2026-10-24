## Pourquoi les SOC mesurent

Les indicateurs montrent si le SOC progresse, où les efforts sont gaspillés et ce que la direction devrait financer. Mal choisis, ils créent toutefois des effets pervers. Une équipe mesurée uniquement sur « le nombre d’alertes clôturées par heure » clôturera vite et mal.

## Des indicateurs opérationnels utiles

| Indicateur | Ce qu’il vous apprend | Précaution |
| --- | --- | --- |
| Délai moyen / médian de prise en compte (MTTA) | État de la file d’attente et des effectifs | La médiane résiste mieux que la moyenne aux valeurs extrêmes |
| Délai moyen de détection (MTTD) | Écart entre le début de l’intrusion et sa détection | Nécessite une analyse post-incident pour connaître l’heure de début |
| Délai moyen de réponse / de confinement (MTTR) | Rapidité de l’action efficace | Définissez précisément quand le chronomètre s’arrête |
| Volume d’alertes par règle | Sources de bruit | Un volume élevé n’est pas mauvais en soi si la précision est élevée |
| Taux de faux positifs par règle | Priorités d’ajustement | N’a de sens que si les motifs de clôture sont consignés de façon cohérente |
| Couverture de détection (validée) | Techniques disposant de détections testées | Pas « des règles existent » : des règles testées |
| Taux d’escalade | Efficacité du niveau 1 et qualité des règles | Un taux très bas peut signifier une sous-escalade |

## L’analyse des faux positifs

Chaque mois, listez les dix règles qui produisent le plus de faux positifs. Pour chacune, consignez la cause principale et l’action d’ajustement. Cela transforme la frustration des analystes en liste de travaux d’ingénierie, et fait généralement regagner aux analystes plusieurs heures par semaine.

## Rendre compte à des publics différents

- **Analystes et ingénieurs** : détails techniques, requêtes, indicateurs, recommandations d’ajustement précises.
- **Équipes informatiques et propriétaires des systèmes** : ce qui est touché, ce qu’ils doivent faire, et pour quand.
- **Encadrement** : impact sur l’activité, risque, tendances, décisions attendues. Aucune ligne de journal brute.
- **Direction générale et conseil d’administration** : le risque en termes métier, la comparaison avec l’appétence au risque, les besoins en ressources.

## Le rapport d’incident

Un rapport d’incident professionnel comprend généralement :

1. **Synthèse pour la direction** : ce qui s’est passé, l’impact, la situation actuelle, les principales recommandations, le tout sur une page.
2. **Périmètre et chronologie** : systèmes, comptes et données touchés ; une chronologie condensée.
3. **Analyse technique** : le fonctionnement de l’attaque, avec la correspondance ATT&CK et les références aux preuves.
4. **Actions de réponse** : étapes de confinement, d’éradication et de rétablissement, avec leurs heures.
5. **Cause première et facteurs aggravants** : y compris les lacunes de contrôle.
6. **Recommandations** : hiérarchisées, chacune avec un responsable et une date cible.
7. **Annexes** : indicateurs, requêtes et registre des preuves.

Rédigez la synthèse pour la direction **en dernier**, une fois que vous savez exactement ce que les preuves établissent.
