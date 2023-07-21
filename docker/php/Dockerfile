FROM php:8.2-apache
ARG USER
ARG UID

# PHP extensions
ENV APCU_VERSION 5.1.18

RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      procps \
      nano \
      git \
      unzip \
      libicu-dev \
      zlib1g-dev \
      cmake \
      libxml2 \
      libxml2-dev \
      libreadline-dev \
      supervisor \
      cron \
      sudo \
      libzip-dev \
      wget \
      librabbitmq-dev \
    && pecl install amqp \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
      pdo_mysql \
      sockets \
      intl \
      opcache \
      zip \
    && docker-php-ext-enable amqp \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

ENV LANG fr_FR.UTF-8
ENV LC_ALL fr_FR.UTF-8

RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pcntl
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd
RUN docker-php-ext-configure intl
RUN docker-php-source delete

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install RabbitMQ C AMQP client library
WORKDIR /tmp
RUN git clone https://github.com/alanxz/rabbitmq-c.git \
  && cd rabbitmq-c \
  && mkdir build && cd build \
  && cmake -DENABLE_SSL_SUPPORT=OFF .. \
  && cmake --build . --target install  \
  && pecl install amqp igbinary redis swoole \
  && rm -rf ../rabbitmq-c

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions amqp

RUN pecl install apcu-$APCU_VERSION && docker-php-ext-enable --ini-name 05-opcache.ini opcache && docker-php-ext-enable --ini-name 20-apcu.ini apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer global require hirak/prestissimo \
    && chmod +x /usr/local/bin/composer || /bin/true \
    && composer self-update

COPY config/apache_project.conf /etc/apache2/sites-available/apache_project.conf
COPY config/php.ini /usr/local/etc/php/
COPY config/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN a2dissite 000-default
RUN a2ensite apache_project
RUN a2enmod rewrite

USER root

ADD scripts /scripts
RUN chmod +x /scripts/*.sh
CMD /scripts/run.sh