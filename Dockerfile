# Base PHP avec Apache
FROM php:8.3-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    ffmpeg \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    git \
    pure-ftpd \
    && docker-php-ext-install pdo pdo_mysql mysqli ftp zip curl

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Définir un ServerName pour éviter l’avertissement
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copier la configuration Apache
COPY ./apachedefaultconf/000-default.conf /etc/apache2/sites-available/000-default.conf

# Activer le site configuré
RUN a2ensite 000-default.conf

# Copier les fichiers PHP dans le DocumentRoot
COPY ./racine /var/www/html

# Donner les droits nécessaires au DocumentRoot
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html




RUN ffmpeg -version

# Exposer le port 80    
EXPOSE 80