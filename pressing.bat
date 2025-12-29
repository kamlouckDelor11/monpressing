@echo off
title Lancement Pressing Manager
echo Demarrage de l'application en cours...

:: Optionnel : Si vous utilisez Vite (npm), lancez-le aussi
start /min npm run dev

:: Lancer PHP Artisan Serve en arriere-plan
start /min php artisan serve --port=8000 

:: Attendre 3 secondes que le serveur demarre
timeout /t 7 /nobreak > nul

:: Ouvrir le navigateur sur l'application
start chrome "http://localhost:8000/"

echo Application lancee avec succes !
exit