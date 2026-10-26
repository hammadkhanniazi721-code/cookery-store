# Cookery Store

A web-based crockery shopping website built with PHP, MySQL, HTML, CSS, and JavaScript. The project provides a foundation for browsing crockery products and managing store operations through an admin panel.

## About the Project

Cookery Store is a PHP-based e-commerce project designed for an online crockery store. It separates administrative functionality from the customer-facing website and uses MySQL to store application data.

## Features

- Customer-facing store interface
- Admin panel for store management
- PHP backend functionality
- MySQL database integration
- JavaScript-based frontend interactions
- CSS styling for the website interface
- SQL database file for setting up the database

Note: The features available in the running application depend on the current implementation of the project.

## Technologies Used

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Apache
- XAMPP

## Project Structure

```text
cookery-store/
├── admin/
├── api/
├── uploads/
├── cookery_store.sql
├── index.html
├── script.js
├── script-backup.js
└── style.css
```

## Requirements

- PHP
- MySQL or MariaDB
- Apache web server
- XAMPP or another compatible local development environment
- Web browser

## Installation and Setup

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/cookery-store.git
```

Replace `YOUR_USERNAME` with your GitHub username.

### 2. Move the Project into XAMPP

Place the project inside the XAMPP web directory.

For example:

```text
/opt/lampp/htdocs/Back/cookery-store
```

### 3. Start XAMPP

```bash
sudo /opt/lampp/lampp start
```

### 4. Create the Database

1. Open phpMyAdmin in your browser.
2. Create the database required by the application.
3. Import `cookery_store.sql`.
4. Configure the database connection using your local database credentials.

phpMyAdmin is commonly available at:

```text
http://localhost/phpmyadmin
```

### 5. Configure the Application

Review the database connection settings in the PHP configuration file. Set the correct database name, username, and password for your local environment.

Do not publish real passwords or secret credentials.

### 6. Run the Website

Open the following address in your browser:

```text
http://localhost/Back/cookery-store/
```

## Development

This project can be modified using Visual Studio Code or another code editor. Run it locally with a compatible PHP and MySQL environment.

## Future Improvements

- Product search and filtering
- Shopping cart and checkout
- Customer authentication
- Order management
- Product categories
- Responsive design improvements
- Payment gateway integration

These are possible future improvements and may not be implemented in the current version.

## Author

Hammad Ahmad Khan


