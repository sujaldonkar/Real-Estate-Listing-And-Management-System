# Real-Estate-Listing-And-Management-System
# Real Estate Platform

A comprehensive real estate web application built with PHP and MySQL, allowing users to buy, sell, and rent properties.

## Features

- User Authentication (Signup, Login, Logout)
- Property Listing and Management
- Property Search with Filters
- Document Verification System
- Admin Panel for Property and User Management
- Responsive Design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: mysqli, fileinfo, gd

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
```

2. Create a MySQL database and import the schema:
```sql
CREATE DATABASE real_estate_db;
```

3. Configure the database connection in `config/database.php`:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'real_estate_db');
```

4. Set up the web server to point to the project directory.

5. Create necessary directories for uploads:
```bash
mkdir -p public/uploads/properties
mkdir -p public/uploads/documents
chmod 777 public/uploads/properties
chmod 777 public/uploads/documents
```

## Directory Structure

```
├── config/
│   ├── database.php
│   └── session.php
├── public/
│   ├── css/
│   │   └── style.css
│   └── uploads/
│       ├── properties/
│       └── documents/
├── admin/
│   ├── index.php
│   ├── login.php
│   └── edit_property.php
├── index.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── list_property.php
├── property.php
├── search.php
└── upload_document.php
```

## Usage

1. Register a new account or login with existing credentials.
2. Browse properties using the search functionality.
3. List your property by providing details and images.
4. Upload verification documents for admin approval.
5. Contact property owners through the provided contact information.

## Admin Features

1. Access the admin panel at `/admin/`
2. Manage user accounts and permissions
3. Approve/reject user documents
4. Monitor and manage property listings
5. Handle user reports and issues

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements to prevent SQL injection
- Input sanitization
- Session management
- File upload validation
- Role-based access control

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request
