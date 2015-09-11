@echo off

:: BatchGotAdmin
:-------------------------------------
REM  --> Check for permissions
>nul 2>&1 "%SYSTEMROOT%\system32\cacls.exe" "%SYSTEMROOT%\system32\config\system"

REM --> If error flag set, we do not have admin.
if "%errorlevel%" NEQ "0" (
    echo Requesting administrative privileges...
    goto UACPrompt
) else ( goto gotAdmin )

:UACPrompt
    echo Set UAC = CreateObject^("Shell.Application"^) > "%temp%\getadmin.vbs"
    set params = %*:"=""
    echo UAC.ShellExecute "cmd.exe", "/c %~s0 %params%", "", "runas", 1 >> "%temp%\getadmin.vbs"

    "%temp%\getadmin.vbs"
    exit /B

:gotAdmin
    pushd "%CD%"
    CD /D "%~dp0\.."
:--------------------------------------

set DIR=%~dp0

if not exist %DIR%..\Vagrantfile (
    echo [bootstrap] link Vagrantfile to project root
    @mklink %DIR%..\Vagrantfile vagrant\Vagrantfile
    if "%errorlevel%" NEQ "0" (
        echo mklink is not executed correctly
        echo possible you don't use a NTFS file system
        echo simlinks only work under windows with NTFS file system
    )
) else (
    echo [bootstrap] Vagrantfile exists in the project root!
)

if not exist vagrant\.vagrant (
    echo [bootstrap] create .vagrant directory
    mkdir vagrant\.vagrant
)

if not exist %DIR%..\.vagrant (
    echo [bootstrap] link .vagrant to project root
    @mklink /D %DIR%..\.vagrant vagrant\.vagrant
    if "%errorlevel%" NEQ "0" (
        echo mklink is not executed correctly
        echo possible you don't use a NTFS file system
        echo simlinks only work under windows with NTFS file system
    )
) else (
     echo [bootstrap] .vagrant directory exists in the project root!
)

echo [bootstrap] create configs and basic hierarchy
xcopy /S /-Y vagrant\vagrant-cfg.dist %DIR%..\vagrant-cfg\

REM --> remove the get admin script if exists.
if exist %temp%\getadmin.vbs (
    rm %temp%\getadmin.vbs
)

echo Please press any key to finish
PAUSE