@echo off
REM === WinSCP Safe Sync Deploy Script ===

REM Path to WinSCP.com (no quotes here)
set WINSCP_PATH="C:\Users\bened\Downloads\WinSCP-6.5.6-Portable\WinSCP.com"

REM FTP connection details
set "FTP_USER=if0_42397405"
set "FTP_PASS=Byyc0619"
set "FTP_HOST=ftpupload.net"

REM Local project folder
set "LOCAL_DIR=C:\xampp\htdocs\WebDesign\HTML local\CommunityConnect"

REM Remote folder on InfinityFree
set "REMOTE_DIR=/htdocs"

"%WINSCP_PATH%" ^
  /command ^
    "open ftp://%FTP_USER%:%FTP_PASS%@%FTP_HOST%/ -explicit -passive=on" ^
    "lcd ""%LOCAL_DIR%""" ^
    "cd %REMOTE_DIR%" ^
    "synchronize remote -criteria=time -transfer=automatic" ^
    "exit"

"synchronize remote -criteria=time -transfer=automatic -filemask=|deploy.bat"

pause