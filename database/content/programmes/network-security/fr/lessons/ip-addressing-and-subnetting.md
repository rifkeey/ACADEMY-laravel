Les règles de pare-feu, les tables de routage, les schémas de segmentation et les investigations sur les journaux s’écrivent tous en adresses IP et en préfixes. Si vous ne lisez pas `10.40.8.0/22` instantanément, chaque module suivant sera plus difficile. Cette leçon vous y amène.

## Adresses IPv4 et préfixes

Une adresse IPv4 fait 32 bits, écrits sous la forme de quatre nombres décimaux (octets) de 0 à 255 : `10.40.9.77`. La **longueur de préfixe** (notation CIDR, RFC 4632) indique combien de ces bits identifient le **réseau** ; les autres identifient l’**hôte** dans ce réseau.

| Notation | Masque | Bits réseau | Bits hôte | Adresses | Hôtes utilisables |
| --- | --- | --- | --- | --- | --- |
| /24 | 255.255.255.0 | 24 | 8 | 256 | 254 |
| /23 | 255.255.254.0 | 23 | 9 | 512 | 510 |
| /22 | 255.255.252.0 | 22 | 10 | 1 024 | 1 022 |
| /26 | 255.255.255.192 | 26 | 6 | 64 | 62 |
| /30 | 255.255.255.252 | 30 | 2 | 4 | 2 |

**Hôtes utilisables = 2^(bits hôte) − 2** : la première adresse est l’**adresse réseau** et la dernière l’**adresse de diffusion** (*broadcast*). (Un /31, utilisé sur les liaisons point à point, et un /32, un hôte unique, sont des cas particuliers.)

## Calculer un sous-réseau à la main

Trouvez le réseau qui contient `10.40.9.77/22` :

1. /22 signifie que les 22 premiers bits forment le réseau. 22 = 8 + 8 + 6 : les deux premiers octets sont entièrement réseau, ainsi que **6 bits du troisième octet**.
2. Taille de bloc dans le troisième octet = 2^(8−6) = **4**. Les réseaux commencent aux multiples de 4 : 0, 4, 8, 12…
3. 9 tombe dans le bloc qui commence à **8**. Le réseau est donc `10.40.8.0/22`.
4. Le bloc couvre les valeurs 8, 9, 10 et 11 du troisième octet. Plage : `10.40.8.0` – `10.40.11.255`.
5. Adresse réseau `10.40.8.0`, diffusion `10.40.11.255`, hôtes utilisables `10.40.8.1` – `10.40.11.254` (1 022 hôtes).

:::reveal Exercice : 10.40.12.5 est-il dans le même /22 ?
Non. Le bloc du troisième octet 12 commence un nouveau /22 (`10.40.12.0/22`, qui couvre 12 à 15). Deux hôtes ne sont sur le même sous-réseau que si leurs adresses réseau sont identiques.
:::

:::tip Rappel sur les sous-réseaux
Si le binaire est un peu rouillé, retenez les « nombres magiques » de l’octet concerné : /17 à /24 → 128, 64, 32, 16, 8, 4, 2, 1 (troisième octet) ; /25 à /32 → les mêmes dans le quatrième octet. La taille de bloc vous indique où commence chaque sous-réseau.
:::

## Adresses privées et particulières

La RFC 1918 réserve trois plages aux réseaux privés. Elles ne sont pas routées sur Internet : le trafic qui en provient doit être traduit (NAT, leçon 5) pour l’atteindre.

| Plage | CIDR | Usage courant |
| --- | --- | --- |
| 10.0.0.0 – 10.255.255.255 | 10.0.0.0/8 | Grandes entreprises (Kanem l’utilise) |
| 172.16.0.0 – 172.31.255.255 | 172.16.0.0/12 | Réseaux moyens, souvent réseaux invités ou de laboratoire |
| 192.168.0.0 – 192.168.255.255 | 192.168.0.0/16 | Domiciles et petits bureaux |

Autres adresses que vous verrez dans les journaux : `127.0.0.1` (boucle locale, l’hôte lui-même), `169.254.x.x` (lien local : l’hôte a essayé DHCP sans obtenir de réponse, un indice classique de dépannage), et les plages de documentation comme `192.0.2.0/24`, `198.51.100.0/24` et `203.0.113.0/24`, utilisées pour les exemples (y compris dans ce cours).

## IPv6 en bref

IPv6 (RFC 8200, adressage dans la RFC 4291) utilise des adresses de **128 bits** écrites en groupes hexadécimaux : `2001:db8:40:9::4d`. `::` remplace une suite de groupes à zéro. Ce qu’un professionnel de la sécurité doit savoir :

- Les réseaux locaux sont normalement en **/64**, ce qui représente beaucoup trop d’adresses pour être balayées exhaustivement comme les sous-réseaux IPv4.
- Chaque interface a une adresse de **lien local** (`fe80::/10`) même si personne n’a configuré IPv6 : IPv6 peut donc être actif sur votre réseau, que vous l’ayez prévu ou non. Les pare-feu et la surveillance doivent aussi le couvrir.
- IPv6 est conçu pour un adressage de bout en bout ; la NAT n’est normalement pas utilisée. Ne supposez pas que « adresses privées » signifie « injoignable » : c’est le filtrage qui joue ce rôle.
- `2001:db8::/32` est la plage de documentation.

## Pourquoi c’est important pour la sécurité

| Situation | Ce que le découpage en sous-réseaux vous apprend |
| --- | --- |
| Une règle de pare-feu autorise `10.40.0.0/16` | Elle couvre 65 536 adresses : toutes les agences, pas seulement Abuja |
| Un hôte des journaux est `10.40.9.77` | Il appartient au sous-réseau du personnel d’Abuja `10.40.8.0/22` (d’après le plan d’adressage) |
| Un ordinateur portable affiche `169.254.20.11` | Il n’a jamais reçu d’adresse du serveur DHCP |
| Un concepteur propose un `/16` pour le réseau invités | Bien plus grand que nécessaire, et facile à faire chevaucher des plages internes |

:::takeaway
Lisez chaque adresse avec son préfixe. Le préfixe définit le réseau, le réseau définit ce qui est « local », et chaque règle, chaque route et chaque investigation dépend de ce point.
:::
