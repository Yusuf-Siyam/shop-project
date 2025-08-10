# Shop Project Setup Guide

## Prerequisites
- XAMPP installed and running
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Make sure both services show green status

## Step 2: Database Setup
1. Open your browser and go to: `http://localhost/siyam/shop-project-main/setup_database.php`
2. This will:
   - Create the `shop_project` database
   - Create all necessary tables
   - Insert sample data
   - Create test users

## Step 3: Test Database Connection
1. Visit: `http://localhost/siyam/shop-project-main/test_connection.php`
2. Verify all tables exist and have correct structure
3. Check that sample data is loaded

## Step 4: Test the Application
1. Visit: `http://localhost/siyam/shop-project-main/`
2. Test registration with a new account
3. Test login with existing accounts

## Test User Credentials
- **Admin**: admin@example.com / admin123
- **User**: user@example.com / user123

## Project Structure
```
shop-project-main/
├── connect.php              # Database connection
├── setup_database.php       # Database setup script
├── test_connection.php      # Connection test
├── index.php               # Login/Registration page
├── homepage.php            # Main homepage
├── shop.php                # Product shop
├── admin.php               # Admin panel
└── uploads/                # Product images
```

## Database Tables
- **users**: User accounts and authentication
- **categories**: Product categories
- **products**: Product information
- **cart**: Shopping cart items
- **orders**: Customer orders
- **order_items**: Order details

## Troubleshooting

### Database Connection Issues
- Check if MySQL service is running
- Verify database credentials in `connect.php`
- Make sure database `shop_project` exists

### Table Creation Issues
- Run `setup_database.php` again
- Check MySQL error logs
- Verify user permissions

### Login Issues
- Check if users table has data
- Verify password hashing is working
- Check session configuration

## Security Features
- Password hashing using `password_hash()`
- Prepared statements to prevent SQL injection
- Session management
- Input validation and sanitization

## Next Steps
1. Customize the design and layout
2. Add more products and categories
3. Implement payment gateway
4. Add user profile management
5. Implement order tracking

## Support
If you encounter issues:
1. Check XAMPP error logs
2. Verify all services are running
3. Test database connection separately
4. Check file permissions
