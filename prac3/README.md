# Практическая работа №3: nginx + Apache(PHP) + MariaDB

Запуск:

```bash
docker compose up -d --build
```

Маршруты:
- http://localhost:8080/ — список профилей (динамика)
- http://localhost:8080/product.php?id=1 — карточка
- http://localhost:8080/static/about.html — статика
- http://localhost:8080/static/contact.html — статика
- http://localhost:8080/admin/ — админка (Basic Auth: admin/admin123)

Структура:
- nginx/nginx.conf — статика и проксирование
- apache/Dockerfile — сборка образа с pdo_mysql и mod_authn_dbd
- apache/apache2.conf, apache/000-default.conf — Apache, DocumentRoot=/var/www/dynamic
- data/static — статический корень (/var/www/static)
- data/dynamic — PHP
- data/dynamic/admin — защищённая зона
- db/init.sql — схема + данные
