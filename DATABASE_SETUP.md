# Database Setup Note

## Current Status

The application is configured for MySQL but needs a running database server.

## Options

### Option 1: Install MySQL

1. Download MySQL Community Server from https://dev.mysql.com/downloads/mysql/
2. Install and start the MySQL service
3. Create the database:

```sql
CREATE DATABASE sport_training CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Update `.env` with your MySQL credentials
5. Run migrations: `php artisan migrate`

### Option 2: Install PostgreSQL (Recommended)

1. Download PostgreSQL from https://www.postgresql.org/download/windows/
2. Install and start the PostgreSQL service
3. Create the database:

```sql
CREATE DATABASE sport_training WITH ENCODING 'UTF8';
```

4. Update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sport_training
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

5. Enable the `php_pdo_pgsql` extension in `php.ini`
6. Run migrations: `php artisan migrate`

### Option 3: Use Laravel Sail (Docker)

1. Install Docker Desktop for Windows
2. Install Sail: `composer require laravel/sail --dev`
3. Install Sail configuration: `php artisan sail:install`
4. Start Sail: `./vendor/bin/sail up`
5. Run migrations: `./vendor/bin/sail artisan migrate`

### Option 4: Use SQLite (Simplest for development)

1. Enable `php_pdo_sqlite` and `php_sqlite3` extensions in `php.ini`
2. Update `.env`:

```env
DB_CONNECTION=sqlite
# Comment out other DB_ lines
```

3. Create database file: `touch database/database.sqlite` (or create empty file manually)
4. Run migrations: `php artisan migrate`

## Once Database is Running

After setting up your database, run:

```bash
php artisan migrate
```

This will create all the authentication tables from Laravel Breeze.

Then you can start the development servers:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Visit http://localhost:8000 to see your application!
