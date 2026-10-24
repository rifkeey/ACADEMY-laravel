## Pourquoi un ensemble hiérarchisé de mesures est utile

Des référentiels comme le CSF 2.0 décrivent des résultats ; l’ISO/IEC 27001 décrit un système de management. Aucun des deux ne dit à une petite équipe informatique quelles sont les dix choses à faire en premier lundi matin. Les CIS Critical Security Controls comblent ce manque avec un ensemble **hiérarchisé et prescriptif** de mesures de protection, fondé sur ce que les attaquants exploitent réellement.

## Les 18 Controls de la version 8.1

| N° | Control | N° | Control |
| --- | --- | --- | --- |
| 1 | Inventaire et maîtrise des actifs de l’entreprise | 10 | Défenses contre les logiciels malveillants |
| 2 | Inventaire et maîtrise des actifs logiciels | 11 | Récupération des données |
| 3 | Protection des données | 12 | Gestion de l’infrastructure réseau |
| 4 | Configuration sécurisée des actifs et logiciels de l’entreprise | 13 | Surveillance et défense du réseau |
| 5 | Gestion des comptes | 14 | Sensibilisation et formation à la sécurité |
| 6 | Gestion du contrôle d’accès | 15 | Gestion des prestataires de services |
| 7 | Gestion continue des vulnérabilités | 16 | Sécurité des logiciels applicatifs |
| 8 | Gestion des journaux d’audit | 17 | Gestion de la réponse aux incidents |
| 9 | Protection de la messagerie et des navigateurs web | 18 | Tests d’intrusion |

Chaque Control contient des **mesures** (*Safeguards*) numérotées (par exemple, la 5.1 porte sur la tenue d’un inventaire des comptes). Chaque mesure est associée à un type d’actif et à une fonction de sécurité. La version 8.1, publiée en 2024, a mis à jour ces associations et ces définitions pour les aligner sur le NIST CSF 2.0, y compris la fonction Gouverner.

L’ordre est délibéré : on ne peut pas protéger des actifs ou des logiciels qu’on n’a pas inventoriés ; les Controls 1 et 2 viennent donc en premier.

## Les groupes de mise en œuvre

| Groupe | Organisation type | Priorité |
| --- | --- | --- |
| IG1 | Petite à moyenne, expertise informatique limitée, protège surtout des données d’activité | Hygiène cyber essentielle : environ 56 mesures qui protègent contre les attaques courantes et non ciblées |
| IG2 | Plusieurs services, personnel informatique dédié, quelques obligations réglementaires | L’IG1 plus des mesures pour des environnements plus complexes et des données sensibles |
| IG3 | Spécialistes de la sécurité en interne, données sensibles, exposition aux attaques ciblées | Toutes les mesures, y compris celles qui exigent des compétences spécialisées |

Chaque groupe inclut le précédent. Une organisation doit atteindre l’IG1 avant de revendiquer l’IG2.

## Choisir un groupe de mise en œuvre

Tenez compte de la sensibilité des données, des obligations réglementaires, de la taille et des compétences de l’équipe informatique, et des attaquants auxquels vous êtes réellement exposé. Une société de microfinance de 40 personnes dont le système bancaire central est externalisé viserait raisonnablement l’IG1 dès maintenant, plus certaines mesures de l’IG2 (par exemple sur la gestion des prestataires et les journaux d’audit) dictées par son registre des risques.

## Mesurer les progrès

Suivez chaque mesure comme *non mise en œuvre*, *partiellement mise en œuvre*, *mise en œuvre sur certains actifs* ou *mise en œuvre sur tous les actifs*, preuves à l’appui. La couverture compte : une MFA « mise en œuvre » pour 60 % des comptes n’équivaut pas à 100 %. Le CIS publie aussi gratuitement sur son site des outils et des guides complémentaires, comme une méthode d’appréciation des risques et des correspondances avec d’autres référentiels.

## Forces et limites

Les Controls sont concrets et opérationnels, ce qui les rend excellents pour les feuilles de route techniques. Ils s’intéressent moins à la gouvernance, à l’appétence au risque ou à la revue de direction ; la plupart des organisations les associent donc à un référentiel de gouvernance comme le CSF 2.0 ou à un SMSI.
