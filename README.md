# Парсер Laravel

Каждые 30 минут обращается к странице новостей, и сохраняет новости.

Каждый запрос логируется в базу данных.

Подключена административная панель SleepingOwl Admin

## **Установка**
composer instal

создать .env и указать подключение к БД

php artisan migrate

Добавить запись в конфигурацию cron:
`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`
