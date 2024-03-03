# === Stage 1: Base image ===
FROM php:8.4-cli AS base

# Install necessary system packages and PHP extensions
RUN apt-get update && \
    apt-get install -y --no-install-recommends cron curl libpq-dev postgresql-client && \
    docker-php-ext-install pgsql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Copy custom PHP configuration
COPY ./docker/custom-php.ini /usr/local/etc/php/conf.d/

WORKDIR /app


# === Stage 2: Install dependencies ===
FROM base AS dependencies

# Install necessary system packages and PHP extensions
RUN apt-get update && \
    apt-get install -y --no-install-recommends cron git zip unzip curl libpq-dev postgresql-client && \
    docker-php-ext-install pgsql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Copy application code
COPY . /app

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer \
    --version=2.5.8

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist


# === Stage 3: Final image ===
FROM base as final

# Copy installed dependencies from the first stage
COPY --from=dependencies /app /app

# Create symbolic links for scripts
COPY ./migrations/migrate.sh /usr/local/bin/migrate.sh
RUN for file in /app/scripts/*.php; do \
        base=$(basename "$file" .php); \
        ln -s "$file" /usr/local/bin/"$base"; \
    done && chmod +x /usr/local/bin/*

# Set up cron
RUN { cat ./.env; cat ./docker/crontab; } > /etc/cron.d/mycron && \
    chmod +x /etc/cron.d/mycron && \
    crontab /etc/cron.d/mycron && \
    touch /var/log/cron.log

# Run migrations before the main process
ENTRYPOINT ["/usr/local/bin/migrate.sh"]

# Start cron and stream its logs
CMD ["sh", "-c", "cron && tail -f /var/log/cron.log"]