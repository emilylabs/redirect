FROM webdevops/php-nginx:latest

MAINTAINER Minecraftly Inc <dev@minecraftly.com>

RUN apt-get update -qq

VOLUME /data
WORKDIR /data

COPY index.php /data
COPY index.php /application/code/
COPY index.php /application/code

EXPOSE 80

CMD ["supervisord"]
