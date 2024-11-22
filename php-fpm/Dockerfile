FROM php:8.4-fpm

RUN apt-get update
RUN apt-get -y dist-upgrade
RUN apt-get -y install monitoring-plugins-basic libmemcached-dev zlib1g-dev libssl-dev libpng-dev
RUN apt-get -y autoremove
RUN apt-get -y purge $(dpkg -l | grep '^rc' | awk '{print $2}')
RUN apt-get clean

RUN pecl install memcached && docker-php-ext-enable memcached
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install gd && docker-php-ext-enable gd

COPY torstatus.ini /usr/local/etc/php/conf.d/

