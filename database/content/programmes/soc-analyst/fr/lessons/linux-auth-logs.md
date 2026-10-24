## Où Linux enregistre l’authentification

Sur les systèmes Debian et Ubuntu, l’activité d’authentification est écrite par `rsyslog` dans `/var/log/auth.log`. Sur les systèmes de la famille RHEL, l’équivalent est `/var/log/secure`. Sur les systèmes qui n’utilisent que le journal systemd, interrogez-le avec `journalctl`.

```bash
# Recent SSH activity from the journal
journalctl -u ssh --since "2026-03-04 00:00" --until "2026-03-04 06:00"

# Failed password attempts in a classic log file
grep "Failed password" /var/log/auth.log | tail -n 20

# Count failures per source address
grep "Failed password" /var/log/auth.log | grep -oE "from [0-9.]+" | sort | uniq -c | sort -rn | head
```

Ces trois commandes affichent respectivement l’activité SSH récente dans le journal systemd, les dernières tentatives de mot de passe échouées dans un fichier de journal classique, et le nombre d’échecs par adresse source.

## Les lignes à reconnaître

```text
Mar  4 03:01:12 web-01 sshd[2211]: Failed password for invalid user admin from 198.51.100.23 port 51122 ssh2
Mar  4 03:01:15 web-01 sshd[2213]: Failed password for root from 198.51.100.23 port 51130 ssh2
Mar  4 03:07:40 web-01 sshd[2290]: Accepted publickey for deploy from 10.20.1.5 port 40022 ssh2: ED25519 SHA256:...
Mar  4 03:09:02 web-01 sudo:   deploy : TTY=pts/0 ; PWD=/srv/app ; USER=root ; COMMAND=/usr/bin/systemctl restart app
```

- `invalid user` signifie que le nom d’utilisateur n’existe pas sur l’hôte. Un grand nombre de ces lignes depuis une même adresse traduit une attaque par force brute opportuniste.
- `Accepted publickey` ou `Accepted password` vous indique quelle méthode d’authentification a réussi. Si votre politique impose les clés uniquement, une réussite par mot de passe constitue en soi une constatation.
- Les lignes `sudo` enregistrent l’utilisateur qui a lancé la commande, le répertoire de travail, l’utilisateur cible et la commande exacte. Elles sont indispensables pour reconstituer ce qu’un intrus a fait après sa connexion.

## Horodatage et fuseaux horaires

L’horodatage syslog classique (`Mar  4 03:01:12`) ne comporte **ni l’année ni le fuseau horaire**. Il est écrit à l’heure locale de l’hôte. Avant de le corréler avec des journaux cloud (normalement en UTC), vous devez déterminer le fuseau horaire de l’hôte et convertir. Beaucoup d’erreurs d’investigation viennent d’un décalage d’une heure que personne n’a remarqué. Le Nigeria vit toute l’année à l’heure d’Afrique de l’Ouest (UTC+1) : 03:01 à l’heure locale d’un serveur de Lagos correspond donc à 02:01 UTC.

Les configurations récentes de `rsyslog` et de journald peuvent produire des horodatages RFC 3339 avec décalage, par exemple `2026-03-04T03:01:12+01:00`. Préférez ce format partout où vous maîtrisez la configuration.

## Tentative de force brute ou compromission

Une *tentative* de force brute est courante et généralement de faible sévérité sur un serveur SSH exposé à Internet. La question qui compte est de savoir si une tentative a **réussi**. Recherchez toujours les lignes `Accepted` provenant de la même adresse source, ainsi que les connexions réussies suivies d’un usage inhabituel de `sudo`, de nouvelles clés SSH dans `~/.ssh/authorized_keys` ou de nouvelles entrées cron.
