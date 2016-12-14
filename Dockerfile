FROM php:7.0-cli

MAINTAINER Minecraftly <dev@minecraftly.com>

COPY /php /data
WORKDIR /data

EXPOSE 80

CMD [ "php", "./redirect.php" ]