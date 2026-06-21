@echo off
chcp 65001 > nul
cd /d "%~dp0"
echo 正在啟動輿情更新程式...
PowerShell -NoProfile -ExecutionPolicy Bypass -Command "& '.\爬蟲腳本_手動更新輿情.ps1'"
