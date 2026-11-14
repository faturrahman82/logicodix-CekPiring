FROM php:8.3-apache

RUN a2enmod rewrite headers \
    && sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/cekpiring.conf \
    && a2enconf cekpiring

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p storage/sessions storage/uploads public/assets/uploads \
    && chown -R www-data:www-data storage public/assets/uploads

ENV PORT=80
EXPOSE 80

CMD ["sh", "-c", "sed -ri \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && sed -ri \"s/:80>/:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
