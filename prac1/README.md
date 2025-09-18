## Запуск веб-сервера
# 1 шаг - переход в нужную директорию
```
cd путь до директории, например /c/Users/moise/Desktop/backend
```
# 2 шаг - поднятие контейнеров в фоне
```
docker compose up -d
```
или
```
docker-compose up -d
```
# 3 шаг - инициализация БД
```
docker exec -i mysql-db mysql -uroot -prootpass < init.sql
```
