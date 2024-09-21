FROM nginx:alpine

RUN apk add --no-cache openssl && \
    mkdir -p /etc/nginx/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/nginx/ssl/server.key \
        -out /etc/nginx/ssl/server.crt \
        -subj "/C=US/ST=State/L=City/O=Company/OU=Org/CN=localhost"

COPY _Docker/nginx/conf.d/nginx.conf /etc/nginx/conf.d/default.conf
