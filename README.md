# N3XTSTAY

A modern hotel booking system built with PHP, MySQL, and vanilla JavaScript. Features domestic and international destination bookings with multiple payment gateway integrations.

## Features

- **User Authentication**: Registration and login system
- **Destination Management**: Browse domestic and international destinations
- **Hotel Listings**: View hotels with details, amenities, and images
- **Room Booking**: Search and book rooms with real-time availability
- **Payment Integration**: Support for bKash, Nagad, and SSLCommerz
- **Admin Panel**: Manage hotels, rooms, bookings, and users
- **User Dashboard**: View booking history and profile management
- **Responsive Design**: Mobile-friendly interface

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Payment Gateways**: bKash, Nagad, SSLCommerz

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx) or PHP built-in server

## Installation

1. **Clone or download the project**

2. **Configure Database**
   - Create a new MySQL database named `hotel_booking`
   - Import the database schema:
     ```bash
     mysql -u your_username -p hotel_booking < database/hotel_booking.sql
     ```
   - Import sample data (optional):
     ```bash
     mysql -u your_username -p hotel_booking < database/sample_data.sql
     ```

3. **Update Configuration**
   - Edit `config/config.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'hotel_booking');
     ```

4. **Run the Application**
   - Using PHP built-in server:
     ```bash
     php -S 127.0.0.1:8000
     ```
   - Or configure your web server (Apache/Nginx) to point to the project directory

## Admin Credentials

- **Email**: admin@nextstay.com
- **Password**: admin123

**Note**: For direct login access, visit `/login.php` if the modal login on the homepage has issues.

## Project Structure

```
N3XTSTAY/
├── admin/              # Admin panel pages
├── api/                # API endpoints
├── assets/             # Static assets (CSS, JS, images)
├── auth/               # Authentication pages
├── config/             # Configuration files
├── database/           # SQL schema and sample data
├── includes/           # Reusable components
├── index.php           # Homepage
├── hotels.php          # Hotels listing
├── hotel-details.php   # Individual hotel details
├── booking.php         # Booking page
├── payment.php         # Payment processing
├── profile.php         # User profile
└── my-bookings.php     # User booking history
```

## Payment Gateway Configuration

Update payment gateway credentials in `config/config.php`:

- **bKash**: Update `BKASH_APP_KEY`, `BKASH_APP_SECRET`, `BKASH_USERNAME`, `BKASH_PASSWORD`
- **Nagad**: Update `NAGAD_MERCHANT_ID`, `NAGAD_MERCHANT_NUMBER`, `NAGAD_PUBLIC_KEY`, `NAGAD_PRIVATE_KEY`
- **SSLCommerz**: Update `SSLC_STORE_ID`, `SSLC_STORE_PASSWORD`

## Security Notes

- Change default admin password after first login
- Use HTTPS in production
- Remove development scripts before deployment
- Set proper file permissions for `assets/uploads` directory
- Keep PHP and MySQL updated

## Deployment

### Deploy to Railway (Recommended)

Railway provides free hosting with automatic GitHub integration:

1. **Push your code to GitHub**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/yourusername/N3XTSTAY.git
   git push -u origin main
   ```

2. **Deploy on Railway**
   - Go to [railway.app](https://railway.app) and sign up
   - Click "New Project" → "Deploy from GitHub repo"
   - Select your N3XTSTAY repository
   - Railway will automatically detect the PHP configuration

3. **Add MySQL Database**
   - In your Railway project, click "New Service"
   - Select "Database" → "MySQL"
   - Railway will provide connection details

4. **Configure Environment Variables**
   - Go to your PHP service in Railway
   - Click "Variables" tab
   - Add the following variables (get values from your MySQL service):
     ```
     DB_HOST = your-mysql-service.railway.internal
     DB_USER = root
     DB_PASS = your-mysql-password
     DB_NAME = railway
     SITE_URL = https://your-app-name.railway.app
     SITE_NAME = N3XTSTAY
     ADMIN_EMAIL = admin@hotelbooking.com
     APP_ENV = production
     ```

5. **Import Database Schema**
   - Use Railway's MySQL shell or connect with a MySQL client
   - Import `database/hotel_booking.sql` and `database/sample_data.sql`

6. **Update Destination Images**
   - Import `database/update_destination_images.sql` to add destination images

7. **Your app will be live at**: `https://your-app-name.railway.app`

### Traditional Deployment

For detailed traditional deployment instructions, see `DEPLOYMENT.md`

## License

This project is for educational purposes.
