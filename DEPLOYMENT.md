# Deployment Guide for N3XTSTAY

This guide provides instructions for deploying the N3XTSTAY hotel booking system to a production web server.

## Prerequisites
- A web server with PHP 7.4+ and MySQL/MariaDB.
- SSL Certificate (recommended for production).
- Access to a database management tool (like phpMyAdmin) or CLI.

## Deployment Steps

### 1. Upload Files
Upload all project files from the `N3XTSTAY` directory to your server's web root (e.g., `public_html` or `www`).

### 2. Configure Environment
1.  Locate the `.env.example` file in the root directory.
2.  Rename it to `.env` (or set these as system environment variables on your server).
3.  Update the values in `.env` with your production details:
    -   `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`: Your production database credentials.
    -   `SITE_URL`: Your production URL (e.g., `https://yourdomain.com`).
    -   `SSL_PATH`: Set to `1` if using HTTPS.
    -   `DISPLAY_ERRORS`: Set to `0` for production.

### 3. Setup Database
1.  Create a new MySQL database on your server.
2.  Import the SQL schema and initial data from `database/hotel_booking.sql` into your new database.

### 4. Security Recommendations
-   **Permissions**: Ensure that the `assets/uploads` directory has write permissions for the web server user.
-   **Remove Unused Files**: Delete any development-only scripts (like `test_*.php`, `fix_*.php`, `debug_*.php`) before going live.
-   **HTTPS**: Force HTTPS by updating your `.htaccess` file or server configuration.

## Troubleshooting
-   If you see a blank page, check the server's error logs.
-   Ensure all database credentials in `.env` are correct.
-   Verify that the `SITE_URL` in `.env` matches your actual domain exactly.
