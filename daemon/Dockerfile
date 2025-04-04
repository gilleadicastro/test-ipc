FROM php:8.2-cli

# Instala bibliotecas necessárias para sysvmsg e pdo_sqlite
RUN apt-get update && \
    apt-get install -y libsqlite3-dev && \
    docker-php-ext-install sysvmsg pdo_sqlite

WORKDIR /app

COPY daemon.php .

# A pasta db será montada via volume
CMD ["php", "/app/daemon.php"]
