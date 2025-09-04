# Microweber Installation Guide

## Prerequisites

* PHP 8.3+
* Composer 2.0+
* Node.js 16+
* Database (MySQL/SQLite/PostgreSQL)

## Installation Methods

### Via Composer (Recommended)

```bash
composer create-project microweber/microweber:dev-filament your_project_name
cd your_project_name
```

### From Zip File

Download from [microweber.com/download.php](https://microweber.com/download.php)

```bash
unzip microweber.zip -d your_project_name
cd your_project_name
```

## Command Line Installation

Microweber provides a powerful CLI installer for automated deployments. Here's the basic syntax:

```bash
php artisan microweber:install \
  --email=admin@example.com \
  --username=admin \
  --password=password \
  --db-name=storage/database.sqlite \
  --db-password=nopass \
  --db-driver=sqlite \
  --db-prefix=site_ \
  --template=Bootstrap \
  --default-content=1
```

### Post-Installation Setup

1. Install frontend dependencies:

```bash
npm install && npm run build
```

2. File permissions:

    * At minimum, the **`storage`** folder must be writable.
    * It’s recommended to also make **`bootstrap/cache`** writable.
    * If you plan to use the standalone updater, you can allow write permissions on the project root as well.

   Example:

   ```bash
   chmod -R 755 storage/ bootstrap/cache/
   ```

3. Link storage to public directory (for media uploads):

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`, ensuring uploaded media is accessible.

---

## Running Tests

Microweber includes a comprehensive test suite. To run tests:

1. Install testing dependencies:

```bash
composer require --dev phpunit/phpunit
```

2. Run all tests:

```bash
php artisan test
```

3. Run specific test groups:

```bash
# Run contact form tests
php artisan test --filter ContactFormTest

# Run module tests
php artisan test --group modules

# Run with coverage report
php artisan test --coverage-html coverage/
```

---

### Installation Options

#### Required Arguments

| Argument  | Description                        |
| --------- | ---------------------------------- |
| email     | Administrator email address        |
| username  | Administrator username             |
| password  | Administrator password             |
| db-name   | Database name/path                 |
| db-driver | Database type (mysql/sqlite/pgsql) |

#### Optional Arguments

| Argument        | Description                          |
| --------------- | ------------------------------------ |
| db-host         | Database host (default: localhost)   |
| db-user         | Database username                    |
| db-pass         | Database password                    |
| db-prefix       | Table prefix                         |
| template        | Default template to install          |
| default-content | Install demo content (1/0)           |
| config-only     | Prepare config without install (1/0) |

#### Command Options

| Option       | Description              |
| ------------ | ------------------------ |
| --help (-h)  | Show help message        |
| --quiet (-q) | Suppress output messages |
| --env        | Set environment name     |
| --debug      | Show debug information   |

**SQLite Note**: For SQLite databases, specify path as:
`--db-name=storage/database.sqlite`

---

#### Install Examples

##### Sqlite

```bash
php artisan microweber:install --email=admin@example.com --username=admin --password=mypassword --db-name=storage/database.sqlite --db-password=nopass --db-driver=sqlite --db-prefix=site_ --template=Bootstrap --default-content=1
```

##### Mysql

```bash
php artisan microweber:install --email=admin@example.com --username=admin --password=mypassword --db-host=127.0.0.1 --db-name=microweber --db-username=dbuser --db-password=dbpass --db-driver=mysql --db-prefix=site_ --template=Bootstrap --default-content=1
```

---

#### Config only (user completes installation via browser)

```bash
php artisan microweber:install --config-only=1 --email=admin@example.com --username=admin --password=mypassword --db-name=storage/database.sqlite --db-password=nopass --db-driver=sqlite --db-prefix=site_ --template=Bootstrap --default-content=1
```

#### Multi-domain scripted installation

Create an empty folder inside `config` with the name of your domain and an empty file at `config/example.com/microweber.php`.

Then run:

```bash
php artisan microweber:install --env=example.com --config-only=1 --email=admin@example.com --username=admin --password=mypassword --db-name=storage/database.sqlite --db-password=nopass --db-driver=sqlite --db-prefix=site_ --template=Bootstrap --default-content=1
```

---

#### Update command

Update from stable branch:

```bash
php artisan microweber:update
```

Update from dev branch:

```bash
php artisan microweber:update --branch=dev
```

---

## Troubleshooting

* Check the error log at `storage/logs/laravel.log`
* Ensure `storage` and `bootstrap/cache` have correct write permissions
* Run `php artisan storage:link` if media is not loading

