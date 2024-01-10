FROM ghcr.io/epno-mx/base-php-image:php-8.1.3-294c539

# set composer related environment variables
ENV PATH="/composer/vendor/bin:$PATH" \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_VENDOR_DIR=/var/www/epno/vendor \
    COMPOSER_HOME=/composer

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --ansi --version --no-interaction

# install application dependencies
WORKDIR /var/www/epno
COPY ./composer.json ./composer.lock* ./
RUN composer install --no-scripts --no-autoloader --ansi --no-interaction

# copy application code
WORKDIR /var/www/epno
COPY . .

RUN composer dump-autoload -o
RUN chown -R :www-data /var/www/epno
RUN chmod -R 775 /var/www/epno/storage

RUN php artisan storage:link
RUN php artisan route:cache
RUN php artisan view:cache

RUN --mount=type=secret,id=epnoenv,dst=./.env php artisan config:cache
# RUN php artisan migrate
# RUN php artisan passport:install

# copy nginx configuration
# COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

# run supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
