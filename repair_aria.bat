@echo off
echo ==========================================
echo      MARIADB/MYSQL TABLE REPAIR TOOL
echo ==========================================
echo.
echo Please Stop MySQL in XAMPP before running this!
echo.
pause

echo Repairing system tables...
cd /d "c:\xampp\mysql\data\mysql"
"c:\xampp\mysql\bin\aria_chk.exe" -r *.MAI

echo.
echo Repairing database tables...
if exist "c:\xampp\mysql\data\hotel_booking" (
    cd /d "c:\xampp\mysql\data\hotel_booking"
    "c:\xampp\mysql\bin\aria_chk.exe" -r *.MAI
)

echo.
echo ==========================================
echo REPAIR COMPLETE.
echo ==========================================
echo 1. Start MySQL in XAMPP.
echo 2. Try the website again.
echo.
pause
