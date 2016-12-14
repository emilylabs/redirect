FROM php:7.0-cli
COPY . /php
WORKDIR /php
CMD [ "php", "./redirect.php" ]