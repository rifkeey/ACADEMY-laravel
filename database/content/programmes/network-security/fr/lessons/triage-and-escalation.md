Le tri détermine la gravité d’un événement et ce qui se passe ensuite. La NIST SP 800-61 Rev. 3 inscrit la réponse aux incidents dans la gestion globale des risques de cybersécurité ; en pratique, c’est au moment du tri que se fait ce jugement sur le risque, rapidement et avec des informations incomplètes.

## De l’événement à l’incident

| Terme | Signification |
| --- | --- |
| **Événement** | Tout ce qui est observable : une ligne de journal, une alerte |
| **Alerte** | Un événement qu’une détection a signalé à l’attention |
| **Incident** | Un événement ou une série d’événements qui menace la confidentialité, l’intégrité ou la disponibilité et exige une réponse coordonnée |

Déclarer un incident lance le processus formel : un responsable de l’incident, un dossier, des points réguliers. Déclarer tôt coûte peu ; déclarer tard coûte cher.

## Une matrice de gravité

| | Impact faible (un système non critique) | Moyen (un système important ou des données limitées) | Élevé (systèmes critiques, données restreintes, nombreux utilisateurs) |
| --- | --- | --- | --- |
| **Confirmé** | Moyenne | Élevée | Critique |
| **Probable** | Faible | Moyenne | Élevée |
| **Possible** | Faible | Faible | Moyenne |

Relevez la gravité quand l’hôte est exposé à Internet, détient des données restreintes ou dispose d’accès d’administration, ou quand il y a des signes que des données sortent.

## Les 30 premières minutes

1. **Confirmez** les faits essentiels à partir d’au moins deux sources (par exemple l’alerte IDS et les enregistrements de flux).
2. **Délimitez** grossièrement : quels hôtes, quels comptes, quelle fenêtre de temps, est-ce toujours en cours ?
3. **Classez et fixez la gravité** à l’aide de la matrice.
4. **Escaladez** avec un résumé clair (module 7 de Sécurité réseau, leçon 5) : quoi, qui, quand, preuves, pourquoi c’est important, ce qui reste inconnu.
5. **Recommandez** un confinement immédiat si le préjudice se poursuit (leçon suivante).

## Rôles et communication

| Rôle | Responsabilité |
| --- | --- |
| Responsable de l’incident | Décisions, priorités, chronologie |
| Équipe réseau | Modifications du pare-feu, du routage et du NAC |
| Responsables des systèmes | Connaissance des serveurs, réinitialisation des identifiants |
| Analystes du SOC | Enquête et surveillance |
| Direction et communication | Décisions métier, messages internes et externes |
| Juridique et DPO | Obligations de notification |

Tenez un dossier d’incident unique, avec la chronologie des constats et des décisions. Ne partagez les détails sensibles qu’avec les personnes qui en ont besoin ; des dispositifs comme le **Traffic Light Protocol** (TLP), maintenu par le FIRST, indiquent jusqu’où une information peut être diffusée.

:::takeaway
Le tri confirme les faits à partir de plusieurs sources, délimite grossièrement, fixe la gravité selon la confiance et l’impact, et escalade avec un résumé clair. Déclarez les incidents tôt, pilotez-les avec un responsable et un dossier unique, et contrôlez qui voit les détails sensibles.
:::
