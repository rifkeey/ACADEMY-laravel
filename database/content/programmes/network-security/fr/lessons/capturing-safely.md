:::scenario « Le portail est lent »
Les chargés de prêts de Kanem à Abuja disent que le portail interne met une éternité à se charger. Les journaux ne montrent rien d’anormal. Quand les journaux et les tableaux de bord ne suffisent plus, les ingénieurs réseau et les analystes se tournent vers les **captures de paquets** : l’enregistrement exact de ce qui est passé sur le fil. Ce module vous apprend à capturer en toute sécurité, à lire des captures dans Wireshark et à rédiger ce que vous trouvez.
:::

Une capture de paquets enregistre chaque trame vue sur une interface, y compris les en-têtes et, pour le trafic non chiffré, le contenu. C’est donc une preuve puissante, et aussi une donnée sensible.

## Avant de capturer : autorisation et vie privée

| Question | Pourquoi c’est important |
| --- | --- |
| **Suis-je autorisé ?** | Ne capturez que sur les réseaux et systèmes que vous avez le droit de surveiller, conformément à la politique de Kanem et avec l’accord du responsable. Capturer le trafic d’autrui sans autorisation peut être illégal |
| **Quelles données personnelles risqué-je de collecter ?** | Les captures peuvent contenir des noms, des numéros de compte, des cookies de session et, sur les protocoles non chiffrés, des mots de passe. Traitez-les selon les règles de protection des données de Kanem |
| **De quoi ai-je besoin au minimum ?** | Ne capturez que les hôtes, les ports et la plage horaire nécessaires pour répondre à la question |
| **Où le fichier sera-t-il stocké, et pour combien de temps ?** | Stockez les captures dans un emplacement restreint, chiffrez-les et supprimez-les quand le dossier est clos |

## Où capturer

| Point | Voit | Remarques |
| --- | --- | --- |
| **Sur l’hôte** (Wireshark, `tcpdump`) | Le trafic de cet hôte | Le plus simple ; subit les problèmes propres à l’hôte |
| **Port miroir de commutateur (SPAN)** | Des copies du trafic de ports ou de VLAN choisis | Peut perdre des paquets sous forte charge |
| **Tap réseau** | Une copie complète d’un lien | Le plus fidèle ; nécessite du matériel |
| **Mise en miroir du trafic cloud** | Des copies du trafic du réseau virtuel | Fonction du fournisseur ; coûts et limites applicables |

Capturez au plus près de l’endroit où le problème apparaît, et parfois **aux deux extrémités** pour voir où les paquets se perdent.

## Filtres de capture et filtres d’affichage

- Un **filtre de capture** (syntaxe BPF, par exemple `host 10.40.9.21 and port 443`) décide de ce qui est **enregistré**. Tout ce qui est exclu est perdu définitivement.
- Un **filtre d’affichage** (syntaxe Wireshark, par exemple `ip.addr == 10.40.9.21 && tcp.port == 443`) ne fait que masquer des paquets à l’écran ; vous pouvez le modifier à tout moment.

Utilisez les filtres de capture pour respecter la vie privée et limiter la taille des fichiers. Utilisez les filtres d’affichage pour explorer.

## Traiter les captures comme des preuves

Si une capture peut servir de preuve dans un incident ou une procédure disciplinaire (module 9) :

1. Consignez **qui** a capturé, **où**, **quand** (UTC) et **pourquoi**.
2. Calculez immédiatement une **empreinte** (par exemple SHA-256) du fichier et consignez-la.
3. Travaillez sur une **copie** ; gardez l’original en lecture seule.
4. Consignez chaque transfert du fichier (chaîne de possession).

La NIST SP 800-86, *Guide to Integrating Forensic Techniques into Incident Response*, explique ces pratiques plus en détail.

:::takeaway
Ne capturez qu’avec une autorisation, dans un but clair et sur le périmètre minimal. Choisissez délibérément le point de capture, utilisez des filtres de capture pour limiter ce qui est enregistré, protégez les captures comme des données sensibles, et calculez leur empreinte et consignez-les si elles peuvent devenir des preuves.
:::
