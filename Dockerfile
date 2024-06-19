FROM php:8.3-cli-alpine as sio_test
RUN apk add --no-cache git zip bash build-base libpq-dev
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pgsql pdo_pgsql

# Setup php app user
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app
USER app

COPY --chown=app . /app
WORKDIR /app

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]