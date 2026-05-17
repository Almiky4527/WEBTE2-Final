# Docker Setup

Dockerfile for PHP + Octave + Laravel pre-installed

`php/Dockerfile`
```Dockerfile
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add Composer global bin to PATH so we can run laravel installer anywhere
ENV PATH="/root/.config/composer/vendor/bin:${PATH}"

# Install Laravel installer globally
RUN composer global require laravel/installer
```

Laravel installer will be installed globally.

# Laravel + Vue Setup

```bash
sudo docker exec -it dev_php_custom bash
laravel new <dir>
```

Postup zadavania veci ako si ich laravel setup pyta:
- Starter kit: Vue
- Authentication provider: Laravel's built-in authentication
- Teams support: No
- Testing framework: PHPUnit (doesnt matter really)
- Laravel Boost: No
- Would you like to run `npm install` ?: Yes
	- or say No and manually:
```bash
cd <dir>
npm install
npm run build
```

However, when you open `http://localhost:8080/` in browser you will get something like `tempnam(): file created in the system's temporary directory`, bcs Laravel has **insufficient permissions**, so run:

```bash
chown -R www-data:www-data storage database bootstrap/cache/
chmod -R 777 storage database bootstrap/cache/
```


Project is now set up with **Laravel + Inertia + Vue starter kit** which has Inertia configured + bunch of stuff pre-installed to showcase things.
