## L’état d’esprit du tri

Le tri est une décision disciplinée prise sous la pression du temps : *cette alerte est-elle réelle, quelle est sa gravité et que fait-on ensuite ?* Un bon tri est rapide parce qu’il suit chaque fois la même séquence, et non parce que l’analyste devine bien.

## Une séquence de tri reproductible

1. **Lisez la définition de l’alerte.** Quel comportement la règle est-elle censée détecter ? Quels sont ses faux positifs connus ? Si la règle comporte des consignes de tri, suivez-les.
2. **Identifiez les entités.** Utilisateur, poste, adresse IP, processus, empreinte de fichier, URL, ressource cloud. Toutes les étapes suivantes s’appuient sur elles.
3. **Validez le déclencheur.** Ouvrez les événements bruts à l’origine de l’alerte. Montrent-ils vraiment le comportement décrit ? Une règle peut se déclencher sur des données mal formées ou partielles.
4. **Recueillez le contexte.** Qui est l’utilisateur et quel est son rôle ? Le poste est-il un serveur ou un ordinateur portable ? Existe-t-il un ticket de changement ? Cette entité a-t-elle déjà déclenché des alertes ?
5. **Regardez avant et après dans le temps.** Que s’est-il passé sur le même poste ou le même compte dans les 30 minutes qui précèdent et qui suivent ?
6. **Décidez et documentez.** Classez l’issue, fixez la sévérité, puis clôturez avec un motif ou escaladez.

## Ce qui constitue une preuve

Une preuve est une information que vous pouvez montrer et qu’une autre personne peut vérifier. Pour chaque conclusion, consignez :

- la **requête ou l’outil** utilisé ;
- la **période** examinée ;
- le **résultat** (y compris « aucun résultat ») ;
- votre **interprétation**, signalée comme telle.

```text
14:05 UTC – Requête sur SigninLogs pour IPAddress == "203.0.113.45", 2026-03-04 00:00–06:00 UTC.
          Résultat : 212 échecs sur 15 utilisateurs, 1 réussite (f.manager@example.org, 02:14:09).
          Interprétation : pulvérisation de mots de passe avec une compromission réussie.
```

Séparer les faits de l’interprétation rend votre travail vérifiable. Si une constatation ultérieure modifie l’interprétation, les faits restent valables.

## Préserver avant de modifier

Certaines actions de réponse détruisent des preuves. Redémarrer un poste efface la mémoire ; le réinstaller efface le disque ; supprimer un e-mail malveillant fait disparaître les en-têtes dont vous aviez besoin. Avant un confinement qui modifie un système, vérifiez avec votre responsable d’incident si des preuves doivent d’abord être collectées. La publication NIST SP 800-61 Rev. 3 inscrit la réponse aux incidents dans la gestion globale des risques de cybersécurité. L’accent qu’elle met sur la préparation et les procédures documentées est précisément ce qui évite de perdre des preuves dans la précipitation.

## Les pièges courants du tri

- **L’ancrage** : décider très tôt qu’il s’agit d’un faux positif et ne chercher que les éléments qui le confirment.
- **Un périmètre limité à l’alerte** : n’investiguer que l’entité de l’alerte, alors que l’attaquant est déjà passé ailleurs.
- **La confiance dans une source unique** : un statut « corrigé » de l’EDR ne prouve pas que l’accès de l’attaquant a pris fin.
- **La clôture silencieuse** : clôturer sans motif, ce qui rend tout ajustement impossible.
