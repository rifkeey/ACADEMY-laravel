## Ce que l’EDR apporte à l’analyste

Les outils de détection et de réponse sur les terminaux (EDR, *Endpoint Detection and Response*) enregistrent une activité détaillée sur chaque poste (démarrages de processus, lignes de commande, connexions réseau par processus, écritures de fichiers, modifications du registre) et offrent des actions de réponse comme l’isolement d’un poste ou l’arrêt d’un processus. Parmi les exemples : Microsoft Defender for Endpoint, CrowdStrike Falcon, SentinelOne et des solutions libres comme Wazuh associé à Sysmon.

La compétence centrale de l’analyste est de lire un **arbre de processus** : qui a lancé quoi, avec quels arguments, sous quel utilisateur.

## Les schémas de processus suspects

| Schéma | Pourquoi c’est suspect | Exemple |
| --- | --- | --- |
| Une application Office ou un lecteur PDF qui lance un shell | Un document malveillant qui exécute du code | `WINWORD.EXE -> powershell.exe -enc ...` |
| Un moteur de script qui télécharge du contenu | Téléchargement au moyen d’outils déjà présents (*living off the land*) | `powershell.exe -c "iwr http://... -OutFile ..."` |
| Un binaire système signé utilisé comme relais d’exécution | Contournement des défenses (LOLBins) | `rundll32.exe` qui charge une DLL depuis `%TEMP%` ; `mshta.exe` avec une URL |
| Des processus exécutés depuis des emplacements modifiables par l’utilisateur | Les logiciels malveillants s’installent rarement dans Program Files | `C:\Users\Public\svch0st.exe` |
| Des outils de vol d’identifiants | Accès aux identifiants | Accès à la mémoire de `lsass.exe` par un processus inhabituel |
| Renommage massif de fichiers avec de nouvelles extensions | Chiffrement par rançongiciel | Des milliers d’écritures `*.docx.locked` par minute |

Repérez les **fautes d’orthographe** dans les noms de binaires système réels (`svch0st.exe`, `explorer .exe`) et les vrais noms au mauvais endroit (`svchost.exe` exécuté depuis le dossier Téléchargements d’un utilisateur).

## La persistance : comment les attaquants survivent à un redémarrage

Les mécanismes de persistance sont des détections à forte valeur, car les logiciels légitimes les modifient relativement rarement :

- **Clés Run du registre** : `HKCU\Software\Microsoft\Windows\CurrentVersion\Run` et l’équivalent sous `HKLM` (ATT&CK T1547.001).
- **Tâches planifiées** (T1053.005) : `schtasks /create` avec une tâche qui exécute un script depuis un dossier utilisateur.
- **Services** (T1543.003) : un nouveau service qui pointe vers un binaire inhabituel.
- **Abonnements aux événements WMI** (T1546.003).
- **Nouveaux comptes locaux ou de domaine** (T1136).
- Sous Linux : tâches cron, unités systemd, modifications de `authorized_keys` et des profils de shell.

```text
Sysmon Event 13 (RegistryValueSet)
Image: C:\Users\a.bello\AppData\Roaming\upd\updater.exe
TargetObject: HKCU\Software\Microsoft\Windows\CurrentVersion\Run\OneDriveSync
Details: C:\Users\a.bello\AppData\Roaming\upd\updater.exe
```

Le nom de la valeur imite un produit légitime (`OneDriveSync`), mais le chemin du binaire n’appartient pas à OneDrive. C’est cette incohérence qui constitue la constatation.

## Les indicateurs de rançongiciel

Les opérations de rançongiciel modernes suivent généralement une séquence : accès initial, vol d’identifiants, déplacement latéral, désactivation des sauvegardes et des outils de sécurité, exfiltration des données, et seulement ensuite chiffrement. Les indicateurs précoces comptent donc davantage que le chiffrement lui-même :

- `vssadmin delete shadows`, `wbadmin delete catalog` ou des modifications avec `bcdedit` ;
- des tentatives d’arrêt des services de sécurité ;
- des outils d’exécution à distance inhabituels (PsExec, WMI à distance) qui se propagent depuis un poste ;
- d’importants transferts sortants vers des services de partage de fichiers ou de stockage cloud avant le chiffrement.
