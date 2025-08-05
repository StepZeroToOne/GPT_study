# ------------------------------------------------------------------
# Stage 1 : ランタイム用 PHP（Alpine ベース）
# ------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# ── OS パッケージを追加（git は composer install で必要になる場合がある）
RUN apk add --no-cache \
        git \
        unzip \
        libzip-dev \
        oniguruma-dev

# ── PHP 拡張をビルド
RUN docker-php-ext-install -j$(nproc) \
        mbstring \
        pdo \
        pdo_mysql \
        zip \
        opcache

# ── 本番寄りの php.ini を採用し、最低限のチューニング
COPY --from=php:8.3-fpm-alpine /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN { \
      echo "opcache.enable=1"         ; \
      echo "opcache.enable_cli=1"     ; \
      echo "memory_limit=512M"        ; \
    } >> /usr/local/etc/php/php.ini

# ── Composer をコピー（公式 composer イメージを利用）
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ── 作業ディレクトリ
WORKDIR /var/www/html

COPY . /var/www/html

# ── 権限を事前に付与
RUN set -eux; \
    mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# SQLite の権限付与
RUN set -eux; \
    mkdir -p storage bootstrap/cache database \ 
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache \
    && touch database/database.sqlite \
    && chown www-data:www-data database/database.sqlite \
    && chmod 664 database/database.sqlite

# ── FPM は 9000 番で待機
EXPOSE 9000
CMD ["php-fpm"]
