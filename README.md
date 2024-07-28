# Inventory System Backend

This project is a Laravel application with JWT (JSON Web Token) authentication for user registration and login.

## Requirements

- PHP >= 7.4
- Composer
- Laravel 8 or 9
- MySQL / Postgres (or any other supported database)

## Installation

Follow these steps to set up the project on your local machine.

### Step 1: Clone the Repository

Clone the repository to your local machine.

```bash
git clone https://github.com/Leap-Chanvuthy/inventory-system-backend.git
cd inventory-system-backend
```

Copy the example environment file and configure it.
```bash 
cp .env.example .env
```

Edit the .env file to match your database configuration and other settings. Make sure to set the following variables:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Generate the application key.

```bash
php artisan key:generate
```

Generate the JWT secret key.

```bash 
php artisan jwt:secret
```

Run the database migrations to create the necessary tables.

```bash
php artisan migrate
```


Start the local development server.

```bash
php artisan serve
```




