# Surf School

This project contains the PHP source for managing surf school classes, payments and emails.

## Prerequisites

- [PHP](https://www.php.net/) 7.4 or higher
- [Composer](https://getcomposer.org/) (for installing or updating dependencies)

## Configuration

1. From the project root, create the file `php/school_config.php`.
   If an example file `php/school_config_example.php` is present, copy it to `php/school_config.php` and edit it. Otherwise, create the file with contents similar to:

```php
<?php
return [
    'nombre_escuela'   => 'Your Surf School',
    'direccion'        => '123 Example Street',
    'maps_url'         => 'https://maps.google.com/?q=Your+Surf+School',
    'telefono'         => '+34 000 000 000',
    'email_contacto'   => 'info@example.com',
    'email_password'   => 'app_password',
    'nombre_remitente' => 'Your Surf School'
];
```

2. Adjust the database credentials in `php/db.php` to match your MySQL setup.
3. (Optional) Run `composer install` to install or update PHPMailer if needed.

## Running a Development Server

Start PHP's built-in server from the repository root:

```bash
php -S localhost:8000
```

Then open [http://localhost:8000/login.php](http://localhost:8000/login.php) in your browser to access the application.
