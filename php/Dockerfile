FROM php:7.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app/public

RUN apt-get update && \
    apt-get install -y libpq-dev zip unzip vim && \
    docker-php-ext-install pdo pdo_pgsql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo 'alias ll="ls -lah"' >> ~/.bashrc

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini

ADD conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
ADD conf/php.ini /usr/local/etc/php/conf.d/app.ini

RUN a2enmod rewrite
ADD conf/vhost.conf /etc/apache2/sites-available/000-default.conf
ADD conf/apache.conf /etc/apache2/conf-available/z-app.conf
RUN a2enconf z-app
RUN chmod -R 755 /app