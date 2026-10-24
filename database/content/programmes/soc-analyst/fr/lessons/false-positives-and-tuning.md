## La qualité des alertes est une mesure de sécurité

Un SOC qui reçoit plus d’alertes qu’il ne peut en trier est de fait aveugle : les analystes se mettent à survoler, à clôturer en masse ou à ignorer des familles de règles entières. La qualité des alertes influe donc directement sur le niveau de sécurité.

Des mesures utiles de la qualité des alertes :

- **Précision** : parmi les alertes produites par une règle, quelle part étaient de vrais positifs (vrais positifs bénins compris) ?
- **Volume** : nombre d’alertes par jour et par règle.
- **Temps de tri** : nombre médian de minutes pour classer une alerte de cette règle.
- **Exploitabilité** : l’alerte a-t-elle conduit à une action, ou seulement à une clôture ?

## Les techniques d’ajustement

Ajustez dans cet ordre, de la plus sûre à la plus risquée :

1. **Corrigez la logique.** Beaucoup de faux positifs viennent d’une règle qui détecte plus que prévu, par exemple un `contains "admin"` qui correspond aussi à `badminton`.
2. **Ajoutez du contexte plutôt que des exclusions.** Croisez avec une liste d’actifs pour que « un processus serveur a lancé un shell » ignore les serveurs de compilation qui le font légitimement, mais seulement ces hôtes listés.
3. **Ajustez les seuils** à partir de distributions réelles. Regardez à quoi ressemble la normale sur 30 jours avant de choisir « plus de 20 ».
4. **Ajoutez des exclusions étroites**, avec un responsable, un motif et une date d’expiration.
5. **Abaissez la sévérité ou transformez la règle en requête de chasse** si elle est utile mais ne peut pas devenir assez précise pour une alerte en temps réel.

## Le danger des exclusions larges

Une exclusion est un angle mort permanent. Comparez :

```kql
// Too broad: anything from the IT subnet is now invisible
| where SourceIP !startswith "10.20.9."

// Narrow: one scanner, one behaviour, documented
| where not(SourceIP == "10.20.9.15" and ProcessName == "nessusd")
```

La première exclusion est trop large : tout ce qui vient du sous-réseau informatique devient invisible. La seconde est étroite : un scanner, un comportement, documentés. Un attaquant qui compromet le poste d’un administrateur informatique profite énormément de la première version. Consignez chaque exclusion dans la documentation de la règle, avec une date de revue.

## Tester les détections

Testez une règle sur deux jeux de données avant de l’activer :

- **Malveillant connu** : des événements malveillants fictifs ou rejoués. La règle doit se déclencher.
- **Sain connu** : un échantillon représentatif de l’activité normale. La règle doit rester silencieuse.

C’est exactement ce que vous ferez dans le lab 4, où votre détection devra renvoyer chaque événement malveillant d’un jeu de données fictif et aucun des événements bénins.
