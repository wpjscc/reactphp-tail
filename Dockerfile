FROM php:7.4-cli


RUN pecl install inotify ev

RUN cp  /usr/local/etc/php/php.ini-production  /usr/local/etc/php/php.ini &&  echo "extension=inotify.so" >> /usr/local/etc/php/php.ini && echo "extension=ev.so" >> /usr/local/etc/php/php.ini

COPY . /var/www

WORKDIR /var/www