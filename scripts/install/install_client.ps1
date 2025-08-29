# Installer automatique de l'application Laravel (Windows + XAMPP)
# Exécuter par double-clic via install_client.bat (fournit ExecutionPolicy Bypass)
# - Copie l'app vers un dossier cible
# - Vérifie XAMPP (MySQL/PHP)
# - Crée la base, importe dump.sql OU lance migrations/seeders
# - Génère APP_KEY si nécessaire
# - Crée les scripts de lancement 1-clic et l'icône "bulletin" sur le Bureau

param(
  [string]$TargetDir = "C:\Users\Public\bulletin\webapp-laravel",
  [string]$XamppDir  = "C:\xampp",
  [string]$DbName    = "bulletin",
  [switch]$ForceOverwrite
)

$ErrorActionPreference = 'Stop'

function Write-Info($msg){ Write-Host "[INFO] $msg" -ForegroundColor Cyan }
function Write-Warn($msg){ Write-Host "[WARN] $msg" -ForegroundColor Yellow }
function Write-Err($msg){  Write-Host "[ERREUR] $msg" -ForegroundColor Red }

function Ensure-Dir($path){ if (!(Test-Path $path)) { New-Item -ItemType Directory -Path $path | Out-Null } }

# 0) Contexte
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path   # répertoire scripts/install
$ProjectRoot = Resolve-Path (Join-Path $ScriptRoot '..\..')    # racine du projet

Write-Info "Racine projet: $ProjectRoot"
Write-Info "Cible installation: $TargetDir"
Write-Info "XAMPP: $XamppDir"
Write-Info "Base: $DbName"

# 1) Pré-requis XAMPP + PHP
$PhpExe = Join-Path $XamppDir 'php\php.exe'
$MysqlBin = Join-Path $XamppDir 'mysql\bin\mysql.exe'
$MysqlStart = Join-Path $XamppDir 'mysql_start.bat'

if (!(Test-Path $XamppDir)) { Write-Err "XAMPP introuvable: $XamppDir"; throw }
if (!(Test-Path $PhpExe))   { Write-Err "PHP introuvable: $PhpExe"; throw }
if (!(Test-Path $MysqlBin)) { Write-Err "mysql.exe introuvable: $MysqlBin"; throw }

# 2) Copier le projet
if (Test-Path $TargetDir) {
  if ($ForceOverwrite) { Write-Warn "Suppression de $TargetDir"; Remove-Item -Recurse -Force $TargetDir }
}
Ensure-Dir (Split-Path $TargetDir)
Write-Info "Copie du projet vers $TargetDir ..."
Copy-Item -Path (Join-Path $ProjectRoot '*') -Destination $TargetDir -Recurse -Force -Exclude 'vendor' | Out-Null
# Copier vendor s'il existe (évite Composer)
if (Test-Path (Join-Path $ProjectRoot 'vendor')) {
  Write-Info "Copie du dossier vendor (peut être long)..."
  Copy-Item -Path (Join-Path $ProjectRoot 'vendor') -Destination (Join-Path $TargetDir 'vendor') -Recurse -Force | Out-Null
} else {
  Write-Warn "vendor/ absent. Assurez-vous d'avoir Composer ou fournissez vendor/ dans le package."
}

# 3) .env
$EnvFile = Join-Path $TargetDir '.env'
if (!(Test-Path $EnvFile)) {
  if (Test-Path (Join-Path $TargetDir '.env.example')) {
    Copy-Item (Join-Path $TargetDir '.env.example') $EnvFile
  } else {
    Write-Warn ".env manquant et .env.example absent — création minimale"
    @"
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DbName
DB_USERNAME=root
DB_PASSWORD=
"@ | Set-Content -Path $EnvFile -Encoding UTF8
  }
}
# Remap DB dans .env
$envText = Get-Content $EnvFile -Raw
$envText = $envText -replace '(?m)^DB_DATABASE=.*', "DB_DATABASE=$DbName"
$envText = $envText -replace '(?m)^DB_HOST=.*', 'DB_HOST=127.0.0.1'
$envText = $envText -replace '(?m)^DB_PORT=.*', 'DB_PORT=3306'
$envText = $envText -replace '(?m)^DB_USERNAME=.*', 'DB_USERNAME=root'
$envText = $envText -replace '(?m)^DB_PASSWORD=.*', 'DB_PASSWORD='
Set-Content -Path $EnvFile -Value $envText -Encoding UTF8

# 4) Démarrer MySQL
Write-Info "Démarrage MySQL ..."
try {
  $mysqlRunning = Get-Process -Name mysqld -ErrorAction SilentlyContinue
  if (-not $mysqlRunning) { Start-Process -FilePath $MysqlStart -WindowStyle Minimized }
} catch {}

function Wait-Port($port, $timeoutSec=30){
  $sw=[Diagnostics.Stopwatch]::StartNew()
  while($sw.Elapsed.TotalSeconds -lt $timeoutSec){
    try{ $ok=Test-NetConnection -ComputerName '127.0.0.1' -Port $port -WarningAction SilentlyContinue
      if($ok.TcpTestSucceeded){ return $true } } catch {}
    Start-Sleep -Milliseconds 500
  }
  return $false
}
[void](Wait-Port 3306)

# 5) Créer DB si besoin
Write-Info "Création base si absente: $DbName"
& $MysqlBin -u root -e "CREATE DATABASE IF NOT EXISTS `$DbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" | Out-Null

# 6) Import dump.sql si présent, sinon migrations/seeders
$DumpFile = Join-Path $TargetDir 'database\dump.sql'
if (Test-Path $DumpFile) {
  Write-Info "Import de dump.sql ..."
  & $MysqlBin -u root $DbName < $DumpFile
} else {
  Write-Info "Pas de dump.sql — exécution des migrations/seeders"
  Push-Location $TargetDir
  & $PhpExe artisan key:generate | Out-Null
  & $PhpExe artisan migrate --force
  try { & $PhpExe artisan db:seed --force } catch { Write-Warn "Seeders ignorés: $($_.Exception.Message)" }
  Pop-Location
}

# 7) Clé APP si vide
$envText = Get-Content $EnvFile -Raw
if ($envText -notmatch '(?m)^APP_KEY=base64:') {
  Write-Info "Génération APP_KEY"
  Push-Location $TargetDir
  & $PhpExe artisan key:generate | Out-Null
  Pop-Location
}

# 8) Créer scripts 1-clic et icône sur le Bureau
$Desktop = [Environment]::GetFolderPath('Desktop')
$StartPs1 = Join-Path $Desktop 'start_app.ps1'
$StartVbs = Join-Path $Desktop 'start_app.vbs'
$Shortcut = Join-Path $Desktop 'bulletin.lnk'

# start_app.ps1
@"
# One-click launcher (installé)
$ErrorActionPreference='SilentlyContinue'
$Xampp  = "$XamppDir"
$AppDir = "$TargetDir"
$Url    = "http://127.0.0.1:8000"
$PhpExe = Join-Path $Xampp 'php\php.exe'
function Wait-Port($p,$t=25){ $sw=[Diagnostics.Stopwatch]::StartNew(); while($sw.Elapsed.TotalSeconds -lt $t){ $ok=Test-NetConnection -ComputerName '127.0.0.1' -Port $p -WarningAction SilentlyContinue; if($ok.TcpTestSucceeded){ return $true }; Start-Sleep -Milliseconds 400 }; return $false }
try{ if(-not (Get-Process mysqld -ErrorAction SilentlyContinue)){ Start-Process -FilePath (Join-Path $Xampp 'mysql_start.bat') -WindowStyle Minimized } }catch{}
Wait-Port 3306 | Out-Null
$psi=New-Object System.Diagnostics.ProcessStartInfo; $psi.FileName=$PhpExe; $psi.ArgumentList.Add('artisan'); $psi.ArgumentList.Add('serve'); $psi.ArgumentList.Add('--host=127.0.0.1'); $psi.ArgumentList.Add('--port=8000'); $psi.WorkingDirectory=$AppDir; $psi.UseShellExecute=$true; $psi.WindowStyle='Minimized'; [System.Diagnostics.Process]::Start($psi) | Out-Null
if(Wait-Port 8000){ Start-Process $Url } else { [Console]::Error.WriteLine('Serveur non joignable 127.0.0.1:8000') }
"@ | Set-Content -Path $StartPs1 -Encoding UTF8

# start_app.vbs
@"
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "powershell.exe -ExecutionPolicy Bypass -File ""$StartPs1""", 0, False
"@ | Set-Content -Path $StartVbs -Encoding ASCII

# Raccourci
$w = New-Object -ComObject WScript.Shell
$s = $w.CreateShortcut($Shortcut)
$s.TargetPath = $StartVbs
$s.WorkingDirectory = Split-Path $StartVbs
$s.IconLocation = "$env:SystemRoot\System32\shell32.dll,220"
$s.Save()

# Débloquer PS1
try { Unblock-File -Path $StartPs1 -ErrorAction SilentlyContinue } catch {}

Write-Host "\nInstallation terminée. Icône créée: $Shortcut" -ForegroundColor Green
Write-Host "Double-cliquez sur 'bulletin' pour lancer l'application." -ForegroundColor Green
