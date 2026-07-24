@echo off
echo ==========================================
echo      HOTEL BOOKING DATABASE FIXER
echo ==========================================
echo.
echo Attempting to fix MySQL permissions...
echo.

c:\xampp\mysql\bin\mysql.exe -u root -e "UPDATE mysql.user SET Host='localhost' WHERE User='root' AND Host='127.0.0.1'; CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY ''; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; CREATE USER IF NOT EXISTS 'hotel_admin'@'localhost' IDENTIFIED BY 'password123'; GRANT ALL PRIVILEGES ON *.* TO 'hotel_admin'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;"

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Could not connect to MySQL!
    echo Please make sure:
    echo 1. XAMPP MySQL is RUNNING.
    echo 2. You have added 'skip-grant-tables' to my.ini file.
    echo.
) else (
    echo.
    echo [SUCCESS] Database permissions fixed!
    echo.
    echo 1. Stop MySQL in XAMPP.
    echo 2. Remove 'skip-grant-tables' from my.ini.
    echo 3. Start MySQL.
    echo 4. Refresh the website.
)

pause
