FROM php:8.2-apache

ARG WWW_ROOT=/var/www/html
ENV APACHE_DOCUMENT_ROOT=${WWW_ROOT}/public
ENV TZ=Asia/Jakarta

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libonig-dev \
    libldap-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli intl mbstring gd zip ftp ldap \
 && docker-php-ext-enable opcache \
 && { \
     echo 'opcache.enable=1'; \
     echo 'opcache.memory_consumption=256'; \
     echo 'opcache.interned_strings_buffer=16'; \
     echo 'opcache.max_accelerated_files=100000'; \
     echo 'opcache.validate_timestamps=1'; \
     echo 'opcache.revalidate_freq=0'; \
   } > /usr/local/etc/php/conf.d/opcache.ini \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && a2enmod rewrite ssl headers \
 && printf "expose_php=Off\n" > /usr/local/etc/php/conf.d/99-security.ini \
 && printf "ServerTokens Prod\nServerSignature Off\nHeader always unset X-Powered-By\n" > /etc/apache2/conf-available/security.conf \
 && a2enconf security \
 && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
      /etc/apache2/sites-available/000-default.conf \
      /etc/apache2/sites-available/default-ssl.conf \
&& echo "ServerName penomoran.kemenkopmk.go.id" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername \
 && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y git unzip curl \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && rm -rf /var/lib/apt/lists/*

RUN printf "%s\n" \
"<VirtualHost *:80>" \
"    ServerName penomoran.kemenkopmk.go.id" \
"    DocumentRoot ${APACHE_DOCUMENT_ROOT}" \
"    <Directory ${APACHE_DOCUMENT_ROOT}>" \
"        AllowOverride All" \
"        Require all granted" \
"    </Directory>" \
"    ErrorLog \${APACHE_LOG_DIR}/error.log" \
"    CustomLog \${APACHE_LOG_DIR}/access.log combined" \
"</VirtualHost>" \
> /etc/apache2/sites-available/penomoran.conf \
 && a2ensite penomoran

WORKDIR ${WWW_ROOT}

COPY . ${WWW_ROOT}

RUN mkdir -p ${WWW_ROOT}/public/uploads/icons ${WWW_ROOT}/public/uploads/lampiran \
 && chown -R www-data:www-data ${WWW_ROOT} \
 && chmod -R 755 ${WWW_ROOT} \
 && chmod -R 777 ${WWW_ROOT}/writable

RUN git config --global --add safe.directory ${WWW_ROOT} \
 && composer install --no-dev --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=php-64bit

CMD ["apache2-foreground"]
