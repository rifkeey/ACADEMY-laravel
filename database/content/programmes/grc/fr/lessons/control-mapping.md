## Les CIS Critical Security Controls

Les CIS Controls sont un ensemble hiérarchisé de mesures de protection tenu à jour par le Center for Internet Security. La version 8.1 les organise en 18 *Controls* qui contiennent des mesures précises (*Safeguards*), regroupées en trois **groupes de mise en œuvre** (*Implementation Groups* : IG1, IG2, IG3). L’IG1 est présenté comme l’hygiène cyber essentielle, un point de départ réaliste pour les petites organisations.

## Propriété et preuves

Chaque mesure de sécurité a besoin :

- d’un **objectif** (le risque qu’elle réduit) ;
- d’un **propriétaire** qui répond de son fonctionnement ;
- d’une description de **sa mise en œuvre** ;
- de **preuves** de son fonctionnement, comme des rapports, des configurations, des journaux ou des tickets.

Une mesure sans propriétaire a tendance à se dégrader. Une mesure sans preuve ne peut être démontrée ni à un auditeur, ni à vous-même.

## Établir des correspondances entre référentiels

Les organisations suivent rarement un seul référentiel. Une banque nigériane peut devoir démontrer son alignement sur les exigences d’un régulateur, sur l’ISO/IEC 27001 pour ses clients, et utiliser le CSF 2.0 pour rendre compte au conseil. Les correspondances permettent à une même mesure mise en œuvre de servir de preuve pour plusieurs exigences.

Les règles d’une mise en correspondance responsable :

1. **Comparez l’objet, pas les mots.** Deux exigences qui parlent toutes deux de « journalisation » peuvent demander des choses très différentes.
2. **Indiquez la relation** : équivalente, sous-ensemble, sur-ensemble ou apparentée. La plupart des correspondances sont partielles.
3. **Évitez les fausses équivalences.** « Nous respectons le CIS Control 6, donc nous respectons les exigences ISO de contrôle d’accès » est rarement entièrement vrai.
4. **Partez des correspondances publiées.** Le NIST et le CIS publient des références informatives et des correspondances ; examinez-les au regard de votre contexte.
5. **Consignez qui a établi la correspondance et quand**, car les référentiels évoluent.

## Exemple

| Mesure mise en œuvre | CIS Controls v8.1 | CSF 2.0 | Remarque sur la relation |
| --- | --- | --- | --- |
| MFA pour tous les accès à distance | Control 6 (gestion du contrôle d’accès) | PR.AA | Partielle : le résultat du CSF couvre aussi la vérification d’identité |
| Inventaire des actifs | Control 1 (inventaire des actifs de l’entreprise) | ID.AM | Étroitement apparentée |
