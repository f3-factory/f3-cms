# Fat-Free CMS Demo Project
This content management system is designed for instructional purposes and help you get started on the use of the Fat-Free Framework. It is not intended for deployment on production servers.

Licensed under the terms of the GPL v3. Copyright © 2009-2020 F3::Factory

## Getting Started
Install Composer and then run 

```bash
composer require bcosca/fatfree-core
```

You can then quickly get this CMS demo up and running by running the internal PHP web server

```bash
php -S localhost:8000 -t public/
```

## Admin Login
You can login to the CMS demo by navigating to `/login` The user_id and password by default are `admin` and `secret`. This can be changed in the `app/config.ini` file.