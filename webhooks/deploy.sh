#!/bin/bash

# Переходим в каталог проекта
cd /var/www/html

# Обновляем проект из репозитория main ветки на GitHub
git pull origin main

# Перезапускаем веб-сервер, если это необходимо
systemctl restart apache2