FROM php:8.3-fpm

ARG user_id=1000
ARG group_id=1000

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libxml2-dev \
    libpcre3-dev \
    libonig-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libsqlite3-dev \
    libicu-dev \
    git \
    curl \
    libpng-dev \
    zip \
    unzip \
    supervisor \
    build-essential \
    cron

RUN docker-php-ext-configure gd \
  --with-jpeg

RUN docker-php-ext-install \
    curl \
    fileinfo \
    gd \
    intl \
    mbstring \
    exif \
    mysqli \
    pdo_mysql \
    xml \
    opcache

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage

COPY _Docker/app/php/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
COPY _Docker/app/php/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY _Docker/supervisor/supervisor.ini /etc/supervisor/supervisor.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY _Docker/cronjob/cronjob /etc/cron.d/cronjob
RUN chmod 0644 /etc/cron.d/cronjob && crontab /etc/cron.d/cronjob

COPY _Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
