@echo off
setlocal

set "PYTHON=C:\Users\Server\AppData\Local\Programs\Python\Python313\python.exe"
set "SCRIPT=%~dp0receive_sms.py"
set "LOG=%~dp0sms_worker.log"

echo.>> "%LOG%"
echo ==== %date% %time% ====>> "%LOG%"
"%PYTHON%" -u "%SCRIPT%" --daemon >> "%LOG%" 2>&1
echo Exit code: %ERRORLEVEL%>> "%LOG%"
