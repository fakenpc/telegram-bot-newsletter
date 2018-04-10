# Описание
Бот для организации платной или бесплатной рассылки. Возможность подписываться на различные виды рассылок. @news0_bot
# Установка
1. Скачать, распаковать файлы бота  
2. Для установки необходимо использовать composer.
Если composer не установлен установить его коммандой. 
```
curl -sS https://getcomposer.org/installer | php
```
Установить зависимости коммандой
```
php composer.phar install
```
3. Переименовать example_config.php в config.php
4. Задать настроки в config.php
5. Установить db.sql и /vendor/longman/telegram-bot/structure.sql
6. Выставить права на выполнение. Если будет использоваться webhook, пропускаем.
```
chmod u+x /path/to/bot-dir/cron-run-bot.sh
```
7. Запустить через консоль или поставить на cron на любой промежуток времени. Если будет использоваться webhook, пропускаем.
```
/path/to/bot-dir/cron-run-bot.sh
```
8. Если будет использоваться webhook.
  * Установить self-signed сертификат на сервер.
  * Указать путь до публичного ключа в config.php
  * Запустить set.php
9. Поставить на крон, выполняться каждую минуту файл cron-send-out-a-newsletter.php
```
php /path/to/bot-dir/cron-send-out-a-newsletter.php
```

<a href="//www.free-kassa.ru/"><img src="//www.free-kassa.ru/img/fk_btn/17.png"></a>
