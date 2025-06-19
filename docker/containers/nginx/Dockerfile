FROM nginx:alpine

# Nginx konfiguráció másolása
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# Tárhely az alkalmazás számára
VOLUME /var/www/html