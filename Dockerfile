FROM jkaninda/nginx-php-fpm:8.3
# Copy Laravel project files
COPY . /var/www/html
# Storage Volume
VOLUME /var/www/html/storage

WORKDIR /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html && php artisan storage:link

USER www-data
