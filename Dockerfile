# Base PHP avec Apache
FROM php:8.3-apache

# Installer les extensions PHP et outils nécessaires
RUN apt-get update && apt-get install -y \
    cron supervisor nano grep \
    ffmpeg \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    git \
    pure-ftpd \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mysqli ftp zip curl pcntl 

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Définir un ServerName pour éviter l’avertissement
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copier la configuration Apache
COPY ./apachedefaultconf/000-default.conf /etc/apache2/sites-available/000-default.conf

# Activer le site configuré
RUN a2ensite 000-default.conf

# Copier les fichiers PHP
COPY ./racine /var/www/html

# Copier backup.php et constantes.php dans les bons dossiers
COPY ./racine/fonctions/backup.php /var/www/html/fonctions/backup.php
COPY ./racine/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./racine/ressources/constantes.php /var/www/html/ressources/constantes.php

# Donner les droits nécessaires
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port 80    
EXPOSE 80

RUN echo "* * * * * root echo 'CRON test' >> /var/log/cron.log 2>&1" >> /etc/crontab


# Lancer cron en arrière-plan avec Apache
CMD cron && tail -f /var/log/cron.log & apache2-foreground

