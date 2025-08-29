@echo off
:: Self-elevating one-click installer for Windows
:: Double-click this file to install the application.

:: Check for admin rights
net session >nul 2>&1
if %errorLevel% neq 0 (
  echo Requesting administrative privileges...
  powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
  exit /b
)

setlocal
set SCRIPT_DIR=%~dp0
set PS1=%SCRIPT_DIR%scripts\install\install_client.ps1

if not exist "%PS1%" (
  echo ERREUR: Script d'installation introuvable: %PS1%
  pause
  exit /b 1
)

:: Defaults (you can adjust before packaging if needed)
set XAMPP_DIR=C:\xampp
set TARGET_DIR=%ProgramData%\bulletin
set DB_NAME=ecoleprimaire

:: Run the PowerShell installer
powershell -NoProfile -ExecutionPolicy Bypass -File "%PS1%" -XamppDir "%XAMPP_DIR%" -TargetDir "%TARGET_DIR%" -DbName "%DB_NAME%" -ForceOverwrite:$false

if %errorLevel% neq 0 (
  echo L'installation a rencontre une erreur. Voir la fenetre precedente pour les details.
  pause
  exit /b %errorLevel%
)

echo Installation terminee. Des raccourcis ont ete crees sur le Bureau.
pause
endlocal
