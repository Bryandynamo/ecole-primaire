# Guide d'installation locale (Windows) + Lancement en 1 clic

Ce guide explique comment installer l'application Laravel en local sur des PC Windows et offrir un lancement **en un clic** via une icône.

## Résumé rapide (1‑clic)
- \[Éditeur\] Préparez le package (voir "Préparer le package – Checklist").
- \[Client\] Ouvrir le dossier livré et double‑cliquer `Install.cmd`.
- L’installateur fait tout (XAMPP portable, base, migrations, données, chiffrement, raccourci Bureau).
- Pour lancer l’app ensuite: double‑cliquer l’icône Bureau `bulletin`.

## 1) Prérequis
- Windows 10/11 64‑bits
- Compte Administrateur (accepter l’UAC au lancement)
- Espace disque ~2–4 Go
- Internet non requis si vous livrez XAMPP portable (recommandé)
- Navigateur (Chrome/Edge)

## 2) Préparer le package – Checklist (Éditeur)
Placez TOUT dans `webapp-laravel/` puis compressez ce dossier.

- __Code Laravel__ (vos sources, idéalement encodées ionCube)
- __vendor/__ complet (pour éviter Composer chez le client)
- __scripts/install/install_client.ps1__ (installateur principal)
- __Install.cmd__ (lanceur 1‑clic – double‑clic par le client)
- __scripts/ioncube/__ avec les DLLs ionCube correspondantes à votre version PHP (TS/NTS)
- __scripts/install/xampp-portable.zip__ (archive du dossier `xampp` fonctionnel)
  - Recommandé: zippez un `C:\xampp` déjà configuré chez vous.
- __database/dump.sql__ (optionnel) si vous livrez des données initiales
- .env n’est pas obligatoire: l’installeur en crée/ajuste un automatiquement (APP_KEY, DATA_KEY, DB_*)

## 3) Installation – côté Client (1‑clic)
1) Dézipper le package livré puis ouvrir le dossier `webapp-laravel/`.

2) Double‑cliquer `Install.cmd` et accepter l’UAC.

3) Attendre la fin. L’installateur:
- Installe XAMPP depuis `scripts/install/xampp-portable.zip` si `C:\xampp` est absent
- Configure ionCube Loader si `scripts/ioncube/` est fourni
- Copie l’app vers `C:\ProgramData\bulletin`
- Crée/ajuste `.env` (APP_KEY, DATA_KEY, DB_*)
- Crée la base, importe `database/dump.sql` si présent
- Exécute migrations + seeders
- Chiffre automatiquement les données existantes (Artisan `data:encrypt`)
- Crée une icône Bureau `bulletin`

## 4) Lancement de l’application
- Sur le Bureau, double‑cliquer l’icône `bulletin` créée par l’installeur.
- L’installeur gère déjà MySQL + démarrage de l’app.

## 5) Variante Apache (sans `artisan serve`)
- Configurer un VirtualHost Apache pointant vers `.../webapp-laravel/public`.
- Démarrer `apache_start.bat` + `mysql_start.bat` (XAMPP), puis ouvrir `http://localhost`.
- Je peux fournir un modèle de vhost sur demande.

## 6) Dépannage
- **Erreur 500 / écran blanc**: vérifier `.env`, puis `"C:\xampp\php\php.exe" artisan key:generate`.
- **Base introuvable**: créer la base `bulletin` dans phpMyAdmin, importer `dump.sql` si fourni.
- **Port 8000 occupé**: changer `--port=8080` dans le script et l'URL en conséquence.
- **PHP introuvable**: ajuster `$PhpExe` vers la bonne installation PHP.
- **Zéros absents dans Excel**: l'export force désormais l'affichage de '0'. Si besoin, contactez le support.
- **Avertissement ionCube**: placez les DLLs dans `scripts/ioncube/`.
- **XAMPP manquant**: ajoutez `scripts/install/xampp-portable.zip` (archive du dossier `xampp`).

## 7) Check‑list finale
- Package contient: `Install.cmd`, `scripts/install/install_client.ps1`, `scripts/install/xampp-portable.zip`, `scripts/ioncube/`, `vendor/`, `database/` (et `dump.sql` si livré)
- Sur le poste client: double‑clic `Install.cmd`
- Icône Bureau `bulletin` créée → double‑clic = application lancée

je veux aue toutcomme auqnd tu click sur une application sur play pour lintisaller via un setup ou un apk linstallation se charge de tous seul et automatiquement jaimerai egalement auen un click la pplication sinstalle seul sur le poste du client de A a z ET AUELLE FONCTIONNE NORMALEMENT 

Revert
Thought Process








Pour répondre à votre demande d’installation en un clic, je vais ajouter au projet un script d’installation automatisé pour Windows. Il créera l’environnement local (base MySQL, clés, migrations), copiera l’application à l’emplacement cible, et installera les raccourcis 1‑clic. J’ajoute:

scripts/install/install_client.ps1 — installe automatiquement l’app sur un poste client
scripts/install/install_client.bat — lanceur simple pour double‑clic
Ensuite, vous pourrez distribuer le projet + lancer install_client.bat sur chaque PC. Je procède à la création des fichiers maintenant.

Created
install_client.ps1
+177
-0
 0
Open diff
Created
install_client.bat
+9
-0
 0
Open diff
Thought Process


Parfait — j’ai ajouté un installateur “tout‑en‑un” pour déployer l’application en 1 clic sur d’autres PC Windows.

Fichiers ajoutés
scripts/install/install_client.ps1
scripts/install/install_client.bat
 (lanceur double‑clic)
Ce que fait l’installateur
Vérifie XAMPP et PHP (C:\xampp par défaut).
Copie le projet vers un dossier cible (par défaut C:\Users\Public\bulletin\webapp-laravel).
Crée la base MySQL (par défaut bulletin).
Importe database/dump.sql si présent, sinon exécute migrate + db:seed.
Génère APP_KEY si nécessaire.
Dépose sur le Bureau:
start_app.ps1 + start_app.vbs
Le raccourci 1‑clic bulletin.lnk (icône “bulletin”).
À la fin: vos clients double‑cliquent sur “bulletin” pour ouvrir l’app.
Comment distribuer et installer (sur un nouveau poste)
Copier le dossier du projet (avec vendor/ si possible) sur le PC cible.
Ouvrir webapp-laravel\scripts\install\.
Double‑cliquer install_client.bat.
Attendre “Installation terminée. Icône créée: …\bulletin.lnk”.
Sur le Bureau: double‑cliquer l’icône “bulletin” pour lancer l’application.
Paramètres (optionnels)
Vous pouvez passer des paramètres au script PowerShell si besoin:

TargetDir (dossier d’installation)
XamppDir (chemin XAMPP)
DbName (nom de base)
-ForceOverwrite (écraser une installation existante)
Exemple:

install_client.bat -TargetDir "C:\Apps\bulletin\webapp-laravel" -XamppDir "D:\Tools\xampp" -DbName "bulletin_prod" -ForceOverwrite
Pré-requis
XAMPP installé (par défaut C:\xampp). Si différent, passer -XamppDir.
Si vous livrez des données initiales, placez un database\dump.sql dans le projet.
Si vendor/ n’est pas livré: PHP/Composer requis ou fournissez vendor/.
Utilisation par vos clients (après install)
1 clic sur l’icône Bureau “bulletin”.
Le script démarre MySQL, lance artisan serve, et ouvre http://127.0.0.1:8000.
Où trouver les scripts
webapp-laravel\scripts\install\install_client.ps1
webapp-laravel\scripts\install\install_client.bat
Statut
Installateur automatisé créé et prêt. Vous pouvez tester sur un autre PC; si des chemins XAMPP diffèrent, utilisez les paramètres ci‑dessus, et je peux adapter par défaut selon votre parc.
Feedback submitted
Close
8 days ago
27 minutes ago
5 minutes ago
Press Enter again to interrupt and send a new message
