FROM php:latest

MAINTAINER Minecraftly Inc <dev@minecraftly.com>

RUN apt-get update -qq

VOLUME /data
WORKDIR /data

COPY index.php /data

EXPOSE 80

CMD [ "php" ]
