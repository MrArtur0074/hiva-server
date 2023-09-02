#!/bin/bash

# Определение директории, где хранится скрипт
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Путь к файлу журнала
LOG_FILE="$SCRIPT_DIR/deploy.log"

# Логируем начало выполнения скрипта
echo "Deployment started: $(date)" >> "$LOG_FILE"

# Переходим в каталог проекта
cd /var/www/html

# Логируем выполнение команды git pull
echo "Running git pull..." >> "$LOG_FILE"
# Обновляем проект из репозитория main ветки на GitHub
export GIT_SSH_COMMAND="ssh -i /var/www/.ssh/id_rsa"
git pull origin main > "$LOG_FILE" 2>&1

echo "Finished git pull..." >> "$LOG_FILE"
echo "Running composer update..." >> "$LOG_FILE"
# Устанавливаем зависимости (Если они есть)
composer update

echo "Finished composer update..." >> "$LOG_FILE"
echo "Running migrate..." >> "$LOG_FILE"

# Делаем миграцию базы данных
php artisan migrate

echo "Finished migrate..." >> "$LOG_FILE"

# Обновляем маршруты
php artisan route:clear
php artisan route:cache

# Перезапускаем веб-сервер, если это необходимо
systemctl restart apache2

# Логируем завершение выполнения скрипта
echo "Deployment finished: $(date)" >> "$LOG_FILE"


