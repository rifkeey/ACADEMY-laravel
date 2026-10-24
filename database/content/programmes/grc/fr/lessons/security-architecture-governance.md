## Gouverner le changement, pas seulement les documents

Les politiques ne sont efficaces que si les nouveaux systèmes et les modifications sont conçus pour les respecter. La **gouvernance de l’architecture de sécurité** est l’ensemble des processus qui garantissent que les exigences de sécurité sont prises en compte lorsque les systèmes sont conçus, achetés, modifiés et retirés.

## Où la sécurité entre dans le cycle de vie

| Étape | Activité de gouvernance |
| --- | --- |
| Idée et dossier d’opportunité | Classer les données concernées ; identifier les obligations légales et contractuelles |
| Conception ou achat | Définir les exigences de sécurité à partir de la politique et des normes internes ; diligence raisonnable sur le fournisseur |
| Revue d’architecture | Examiner la conception au regard des modèles et principes approuvés |
| Développement et tests | Vérifier que les exigences sont respectées : contrôles de configuration, tests de sécurité |
| Autorisation de mise en production | Confirmer que les risques résiduels sont acceptés par le bon propriétaire |
| Exploitation et évolutions | La gestion des changements vérifie l’impact des modifications sur la sécurité |
| Retrait | Restitution ou suppression sécurisée des données ; retrait des accès |

Ajouter la sécurité au moment de la mise en production coûte cher. L’ajouter dès la conception coûte peu.

## Les comités d’architecture

De nombreuses organisations s’appuient sur un **comité d’architecture** (*architecture review board*, ARB) ou une autorité de conception. Un représentant de la sécurité examine les projets importants au regard :

- des **principes de sécurité**, comme le moindre privilège, la défense en profondeur, les paramètres sûrs par défaut, la séparation des environnements et la minimisation des données collectées ;
- des **modèles approuvés**, comme une manière standard d’exposer une API, de se connecter à un environnement cloud ou d’intégrer l’authentification unique ;
- des **normes internes**, comme la norme de chiffrement ou de journalisation.

Les écarts sont soit corrigés dans la conception, soit consignés comme dérogations avec l’approbation d’un propriétaire du risque.

## Les exigences de sécurité

Dérivez les exigences de la politique pour qu’elles soient traçables. La liste d’exigences d’une nouvelle application destinée aux clients pourrait comprendre : authentification via le fournisseur d’identité de l’entreprise avec MFA pour le personnel ; chiffrement des données personnelles en transit et au repos ; journaux d’audit envoyés vers la plateforme centrale de journalisation ; scan de vulnérabilités avant la mise en production ; conservation des données alignée sur le calendrier de conservation.

## La matrice de traçabilité

Une matrice de traçabilité relie chaque énoncé de politique aux normes internes, mesures et preuves qui le mettent en œuvre :

| Énoncé de politique | Norme interne | Mesure | Preuves |
| --- | --- | --- | --- |
| P-4.2 L’accès à distance doit utiliser la MFA | Norme d’authentification §3 | VPN et accès conditionnel via SSO | Export de la politique d’accès conditionnel ; rapport mensuel de couverture MFA |
| P-6.1 Les systèmes critiques doivent pouvoir être restaurés | Norme de sauvegarde §2 | Sauvegardes quotidiennes ; tests de restauration trimestriels | Rapports des tâches de sauvegarde ; comptes rendus des tests de restauration |

Les lacunes apparaissent de deux façons : un énoncé de politique sans mesure (une intention non mise en œuvre), ou une mesure sans politique (un travail que personne n’a demandé, qui risque de ne pas être financé ou d’être incohérent).

## La gestion des changements

Les changements ordinaires doivent comporter une question sur l’impact de sécurité : ce changement touche-t-il l’authentification, l’exposition réseau, la journalisation, les flux de données ou les accès à privilèges ? Si oui, il nécessite une revue de sécurité. Les changements urgents doivent être revus a posteriori. Les fiches de changement sont de précieuses preuves d’audit, et souvent le premier document que lisent les enquêteurs après un incident.
