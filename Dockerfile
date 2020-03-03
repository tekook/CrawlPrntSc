FROM php:7.4-cli
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apt-get update && \
        apt-get install -y --no-install-recommends libzip-dev zlib1g-dev unzip \
        && docker-php-ext-install zip \
        && apt-get purge -y libzip-dev zlib1g-dev
WORKDIR /app