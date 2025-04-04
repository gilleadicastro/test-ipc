FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y libpq-dev supervisor && \
    docker-php-ext-install sysvmsg pdo_pgsql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY index.php /var/www/html/
COPY daemon.php /var/www/html/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]
