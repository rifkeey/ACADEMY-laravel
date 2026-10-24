Une architecture cible ne vaut que ce que valent ses composants, et n’est digne de confiance que si elle est testée. Le durcissement sécurise chaque équipement ; l’évaluation prouve que l’ensemble de la conception fait ce qu’elle prétend.

## Durcir les équipements réseau

| Domaine | Pratique |
| --- | --- |
| **Référence** | Appliquer un référentiel de configuration reconnu (par exemple les CIS Benchmarks pour le type d’équipement) et documenter les écarts |
| **Plan d’administration** | Administration uniquement depuis la zone d’administration ou l’hôte de rebond ; SSH et HTTPS uniquement ; ni Telnet ni HTTP (module 6) |
| **Authentification** | Authentification centralisée (RADIUS ou TACACS+) avec MFA pour les administrateurs ; comptes locaux réservés aux accès d’urgence, avec des mots de passe robustes et uniques stockés de façon sécurisée |
| **Services** | Désactiver les services, ports et protocoles inutilisés ; utiliser SNMPv3 plutôt que v1/v2c |
| **Journalisation et heure** | Envoyer les journaux au collecteur central ; synchroniser l’heure avec NTP (module 9) |
| **Micrologiciel** | Uniquement des versions prises en charge ; correctifs selon la gravité et l’exposition ; suivi des dates de fin de vie |
| **Physique** | Salles et baies verrouillées ; ports de commutateur inutilisés désactivés ou placés dans un VLAN isolé |

## L’évaluation de la sécurité

La NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment*, décrit trois groupes de techniques :

| Groupe | Exemples | Objectif |
| --- | --- | --- |
| **Revue** | Revue de la documentation, des jeux de règles et des configurations, revue des journaux | Trouver des faiblesses sans toucher aux systèmes en production (labs des modules 3 et 7) |
| **Identification et analyse des cibles** | Découverte du réseau, identification des ports et des services, recherche de vulnérabilités | Confirmer ce qui est réellement exposé |
| **Validation des vulnérabilités des cibles** | Tests d’intrusion par des testeurs autorisés | Montrer si les faiblesses peuvent réellement être exploitées, et jusqu’où |

## Les règles d’engagement

Tester des réseaux en production exige une **autorisation écrite** avant toute action :

| Élément | Contenu |
| --- | --- |
| Périmètre | Exactement quels réseaux, systèmes et adresses sont inclus et exclus |
| Calendrier | Dates, heures et périodes d’interdiction |
| Méthodes | Quelles techniques sont permises ; tout ce qui pourrait perturber les services exige une approbation explicite |
| Contacts | Qui appeler si quelque chose casse ou si un incident réel est découvert |
| Traitement des données | Comment les constats et toute donnée observée sont protégés puis détruits |
| Validation | Le responsable du système et une personne ayant autorité sur les systèmes |

Le personnel de Kanem peut revoir les configurations et utiliser des scanners approuvés sur les réseaux qu’il gère. Les tests d’intrusion sont normalement confiés à un prestataire indépendant et qualifié, sous contrat.

## Valider l’architecture cible

Chaque affirmation de la conception doit avoir son test, par exemple :

- Depuis un hôte du Wi-Fi invités, aucune adresse interne ne répond (test approuvé depuis un ordinateur portable invité).
- Depuis la DMZ, seuls les ports documentés des serveurs documentés sont accessibles.
- Depuis la zone utilisateurs, la zone d’administration est inaccessible, sauf par l’hôte de rebond.
- Chaque règle d’autorisation qui franchit une frontière produit une entrée de journal dans le SIEM.
- Le basculement de la paire de pare-feu périmétriques s’effectue dans le délai convenu.

:::takeaway
Durcissez chaque équipement selon un référentiel : plan d’administration, authentification centralisée avec MFA, services minimaux, journalisation, NTP, micrologiciel pris en charge et sécurité physique. Évaluez avec des techniques de revue, d’identification et de validation (SP 800-115), toujours dans le cadre de règles d’engagement écrites, et transformez chaque affirmation de la conception en test.
:::
