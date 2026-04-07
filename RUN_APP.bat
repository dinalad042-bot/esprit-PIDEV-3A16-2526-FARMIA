@echo off
set PHP_BIN="C:\xampp\New folder\php82\php.exe"
set SYMFONY_BIN=symfony

echo [1/4] Cleaning Cache...
%PHP_BIN% bin/console cache:clear

echo [2/4] Setting up Demo Data (Admin, Analyse #1, Councils)...
%PHP_BIN% setup_demo_data.php

echo [3/4] Checking Database Schema...
%PHP_BIN% check_schema.php

echo [4/4] Starting Local Web Server...
echo ----------------------------------------------------
echo YOUR APP WILL BE AT: http://localhost:8000
echo DEMO DASHBOARD: http://localhost:8000/admin/demo/quick-ref
echo ----------------------------------------------------
%SYMFONY_BIN% serve
