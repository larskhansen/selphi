FROM composer:2

FROM php:8.1-apache as builder

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

RUN composer update --no-cache

# Now to the actual docker image
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/web

COPY --from=builder /var/www /var/www

# Setup Opcache
RUN { \
		echo 'opcache.enable=1'; \
		echo 'opcache.memory_consumption=192'; \
		echo 'opcache.interned_strings_buffer=16'; \
		echo 'opcache.max_accelerated_files=10000'; \
		echo 'opcache.revalidate_freq=0'; \
		echo 'opcache.validate_timestamp=1'; \
		echo 'opcache.max_wasted_percentage=10'; \
		echo 'opcache.fast_shutdown=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Setup Apache remoteip
RUN { \ 
		echo 'RemoteIPHeader CF-Connecting-IP'; \
		echo 'RemoteIPInternalProxy 172.17.0.1'; \
		echo 'RemoteIPTrustedProxy 172.17.0.0/20'; \
		echo 'RemoteIPHeader X-Real-IP'; \
	} > /etc/apache2/mods-available/remoteip.conf

# Changing the apache_document_root to remove the unused html folder.
RUN sed -i "s|/var/www/html|$APACHE_DOCUMENT_ROOT|" /etc/apache2/sites-available/000-default.conf \
    && sed -i "s|\*:80>|\*:8080>|" /etc/apache2/sites-available/000-default.conf \
    && sed -i '/Listen 80/d' /etc/apache2/ports.conf \
    && sed -i '1 i\Listen 8080' /etc/apache2/ports.conf \
    && a2enmod rewrite remoteip \
    && rm -rf /var/www/html \
    && chown -Rf www-data:www-data /var/www/web

USER www-data

EXPOSE 8080
    
# Set the working directory.
WORKDIR /var/www