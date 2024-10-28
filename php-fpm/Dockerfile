FROM php:8.2-fpm

RUN apt-get update
# TODO

RUN apt-get install -y libmemcached-dev zlib1g-dev libssl-dev
RUN pecl install memcached && docker-php-ext-enable memcached
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

