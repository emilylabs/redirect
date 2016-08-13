# Minecraftly Proxy Dockerfile

FROM php:latest

MAINTAINER Minecraftly Inc <dev@minecraftly.com>

# Use APT (Advanced Packaging Tool) built in the Linux distro to download Java, a dependency
# to run Minecraft.
RUN apt-get update -qq
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y php5 php5-gd
RUN mkdir /data/proxy

COPY . /src/http
WORKDIR /usr/src/http

EXPOSE 80

#Start Minecraftly proxy
CMD [ "php", "./index.php" ]