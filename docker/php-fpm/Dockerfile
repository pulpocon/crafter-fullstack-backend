FROM php:8.0.17-fpm-bullseye
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
ARG LOCAL_UID=1000
ARG LOCAL_GID=1000

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends libssh2-1 libssh2-1-dev \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
    && pecl install ssh2-1.3.1 && docker-php-ext-enable ssh2

# install xdebug
RUN pecl install xdebug redis && docker-php-ext-enable xdebug redis

RUN apt-get update \
    && apt-get install -y git zip libzip-dev vim libicu-dev zlib1g-dev mariadb-client libpng-dev libjpeg-dev libpq-dev libonig-dev \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
 	&& docker-php-ext-configure gd --with-jpeg=/usr \
    && docker-php-ext-install intl zip mysqli gd mbstring pdo pdo_mysql pdo_pgsql

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install supervisor
RUN apt-get update \
    && apt-get -y install supervisor \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install node and yarn
RUN apt-get update && \
    apt-get install -y --no-install-recommends gnupg && \
    curl -sL https://deb.nodesource.com/setup_10.x | bash - && \
    apt-get update && \
    apt-get install -y --no-install-recommends nodejs && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && \
    apt-get install -y --no-install-recommends yarn && \
    apt-get install -y --no-install-recommends python-dev && \
    npm install -g npm && \
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install wkhtmltopdf
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y wkhtmltopdf \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

COPY ./php-fpm/pool-www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./php-fpm/craftersvigo-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/craftersvigo-entrypoint.sh

RUN groupadd -g $LOCAL_GID craftersvigo \
    && useradd -d /home/craftersvigo -s /bin/bash -u $LOCAL_UID -g $LOCAL_GID craftersvigo \
    && mkdir /home/craftersvigo && chown -R craftersvigo. /home/craftersvigo

RUN mkdir -p /var/log/supervisor && chown -R craftersvigo. /var/log/supervisor

RUN mkdir /opt/phpstorm-coverage && chown -R craftersvigo. /opt/phpstorm-coverage

RUN touch /run/php-fpm.pid \
 && chown -R craftersvigo. /run/php-fpm.pid

COPY --from=composer:2.1.6 /usr/bin/composer /usr/bin/composer

USER craftersvigo
ENTRYPOINT ["craftersvigo-entrypoint.sh"]
CMD ["/usr/local/sbin/php-fpm"]
