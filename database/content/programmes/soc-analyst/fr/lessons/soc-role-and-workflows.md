## Pourquoi un SOC existe

Un centre des opérations de sécurité (SOC, *Security Operations Centre*) est la fonction qui surveille en continu le parc informatique d’une organisation, décide quelles activités sont dangereuses et coordonne la réponse. C’est une *fonction*, pas une salle : une équipe de deux personnes qui s’appuie sur un SIEM managé et un planning d’astreinte est un SOC, tout comme une équipe de 60 analystes qui se relaient autour du globe.

Tout SOC existe pour répondre rapidement, et de façon justifiable, à trois questions :

1. **Se passe-t-il quelque chose de malveillant ?** (détection)
2. **Quelle est la gravité et qu’est-ce qui est touché ?** (tri et investigation)
3. **Que faisons-nous, et qui doit être informé ?** (réponse et communication)

## Responsabilités principales

| Responsabilité | Ce que cela signifie en pratique |
| --- | --- |
| Surveillance | Examiner les alertes générées par les règles de détection sur la télémétrie des postes, des identités, du réseau et du cloud |
| Tri | Valider chaque alerte, écarter les faux positifs et attribuer une sévérité |
| Investigation | Recueillir les éléments de preuve, établir une chronologie et déterminer le périmètre |
| Coordination de la réponse | Contenir directement les menaces ou passer le relais aux équipes de réponse à incident et aux propriétaires des systèmes |
| Ingénierie de détection | Écrire et ajuster les règles qui génèrent les alertes |
| Chasse aux menaces (*threat hunting*) | Rechercher de manière proactive les activités que les règles n’ont pas détectées |
| Reporting | Documenter les dossiers et produire des indicateurs pour la direction |

## Niveaux et rôles

De nombreux SOC organisent leurs analystes par niveaux. Les analystes de niveau 1 traitent la file d’alertes et le tri de premier niveau. Les analystes de niveau 2 prennent en charge les dossiers escaladés et mènent des investigations plus approfondies. Le niveau 3 ou les spécialistes s’occupent de l’ingénierie de détection, de la chasse aux menaces, de l’analyse de logiciels malveillants et des incidents majeurs. Certaines équipes refusent les niveaux stricts et préfèrent des cellules qui suivent un dossier de bout en bout. Les deux modèles fonctionnent si les chemins d’escalade sont clairs.

Le NIST NICE Framework décrit les rôles de la cyberdéfense ainsi que les connaissances et compétences que chacun exige. C’est une carte utile pour planifier votre propre progression, car il emploie un vocabulaire indépendant des éditeurs.

## Le cycle de vie d’une alerte

Un processus type ressemble à ceci :

```text
Telemetry -> Detection rule fires -> Alert created -> Analyst triage
   -> False positive: close with reason, feed back to tuning
   -> Benign true positive: close, document (e.g. approved admin activity)
   -> True positive: open incident -> investigate -> contain -> eradicate -> recover -> lessons learned
```

Autrement dit : la télémétrie déclenche une règle de détection, qui crée une alerte, que l’analyste trie. Un faux positif est clôturé avec un motif et signalé pour l’ajustement des règles ; un vrai positif bénin est clôturé et documenté (par exemple une activité d’administration autorisée) ; un vrai positif ouvre un incident, qui est investigué, contenu, éradiqué, puis suivi d’une remise en service et d’un retour d’expérience.

Deux habitudes rendent ce cycle fiable :

- **Chaque clôture a un motif.** « Clôturé – faux positif : scanner de vulnérabilités 10.20.0.15, planification autorisée » est utile. « Clôturé » ne l’est pas.
- **Le retour d’information remonte la chaîne.** Des faux positifs répétés sont un problème d’ingénierie de détection, pas un problème de patience des analystes.

## Accords de fonctionnement

Un SOC a besoin d’accords écrits avec le reste de l’organisation : qui peut isoler un serveur, qui autorise la désactivation du compte d’un dirigeant, quels sont les contacts d’escalade en dehors des heures ouvrées, et dans quel délai chaque niveau de sévérité doit être pris en compte. Sans ces accords, les analystes perdent du temps à demander des autorisations en plein incident.

> **Conseil pratique :** dès vos premières semaines dans un SOC, trouvez les procédures opérationnelles (*runbooks*), la matrice d’escalade et l’inventaire des actifs. La plupart des erreurs de débutant viennent du fait de ne pas savoir à quoi ressemble la « normale » ni à qui appartient un système.
