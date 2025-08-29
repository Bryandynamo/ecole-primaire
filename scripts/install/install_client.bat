@echo off
REM Lance l'installateur PowerShell avec ExecutionPolicy Bypass
set SCRIPT_DIR=%~dp0
powershell.exe -ExecutionPolicy Bypass -File "%SCRIPT_DIR%install_client.ps1" %*
if %errorlevel% neq 0 (
  echo Une erreur est survenue pendant l'installation. Voir la console pour les details.
  pause
)
