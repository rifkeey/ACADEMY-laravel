:::scenario Les services que tout le monde oublie
Le réseau de Kanem dispose de pare-feu, d’une segmentation et d’une détection. Mais chaque connexion commence toujours par le DNS, chaque équipement reçoit toujours son adresse du DHCP, et l’essentiel du trafic est du trafic web. Ce module sécurise ces services du quotidien et montre comment leurs journaux vous aident à décider quand une situation doit être escaladée.
:::

Le DNS transforme les noms en adresses (module 1, leçon 5). Parce que presque tout en dépend, le DNS est à la fois un service critique à protéger et l’une des sources de preuves de sécurité les plus riches. La NIST SP 800-81 Rev. 3, *Secure Domain Name System (DNS) Deployment Guide* (mars 2026), couvre son déploiement sécurisé.

## Protéger le service DNS lui-même

| Domaine | Pratique |
| --- | --- |
| **Résolveurs internes** | Les équipements du personnel et les serveurs utilisent les résolveurs de Kanem, et non n’importe quels résolveurs publics, afin que les requêtes soient journalisées et que le filtrage s’applique |
| **Séparation** | Séparez les zones internes (privées) des zones publiques ; n’exposez pas les noms internes sur les serveurs publics |
| **Serveurs faisant autorité** | Limitez les transferts de zone aux serveurs secondaires autorisés ; appliquez les correctifs ; surveillez les modifications |
| **Durcissement des résolveurs** | Les résolveurs ne répondent qu’aux clients internes ; limitation du débit lorsqu’elle est disponible |
| **Comptes chez le bureau d’enregistrement et l’hébergeur** | Authentification forte et verrouillage des domaines publics de Kanem chez le bureau d’enregistrement, car perdre le contrôle d’un domaine redirige tout |

## DNSSEC

**DNSSEC** (introduit dans la RFC 4033) ajoute des signatures numériques aux données DNS pour que les résolveurs puissent vérifier que les réponses sont authentiques et non modifiées. Il protège l’**intégrité**, pas la confidentialité.

- **Signer** vos zones publiques protège les personnes qui résolvent vos noms.
- **Valider** sur vos résolveurs protège vos utilisateurs contre les réponses falsifiées pour les domaines signés.

Il exige une gestion des clés soigneuse ; une erreur peut rendre un domaine injoignable pour les résolveurs qui valident.

## Le DNS chiffré

**DNS over TLS (DoT, RFC 7858)** et **DNS over HTTPS (DoH, RFC 8484)** chiffrent les requêtes entre le client et le résolveur, ce qui protège la vie privée sur les réseaux non fiables. Pour une organisation, la question de conception est de savoir **quel résolveur** utilisent les équipements :

- Le DNS chiffré vers les résolveurs **de Kanem** préserve à la fois la vie privée et la visibilité.
- Les navigateurs ou applications qui envoient du DNS chiffré vers des résolveurs **extérieurs** contournent la journalisation et le filtrage internes. Beaucoup d’organisations configurent les équipements gérés pour qu’ils n’utilisent que le résolveur de l’entreprise, et bloquent ou surveillent les connexions directes aux résolveurs publics.

## Le DNS protecteur et le filtrage

Les résolveurs de **DNS protecteur** refusent ou redirigent les résolutions de domaines connus comme malveillants ou indésirables. La CISA exploite un service de DNS protecteur pour les agences fédérales américaines, et des services commerciaux proposent la même idée aux organisations. Filtrer au niveau du résolveur coûte peu, bloque de nombreuses menaces avant toute connexion et produit des journaux utiles.

:::takeaway
Faites utiliser les résolveurs internes par les équipements, durcissez et séparez le DNS interne et public, protégez les comptes chez le bureau d’enregistrement, signez et validez avec DNSSEC quand vous le pouvez, utilisez le DNS chiffré vers vos propres résolveurs plutôt que pour les contourner, et ajoutez un filtrage par DNS protecteur.
:::
