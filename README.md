<p align="center"><a>ДЗ. / It's just homework.</a></p>

## Изначально, что нужно делать:

Качаем проект -> распаковываем и далее:

1. cd 'путь'
2. composer update
3. copy .env.example .env
3. php artisan key:generate
4. Потом заходим в .env и меняем сначала localhost на порт 8000, т.е. "APP_URL=http://localhost:8000". Данные от сервера s3, т.е.:
FILESYSTEM_DISK меняем с локал на s3, чтобы получилось: FILESYSTEM_DISK=s3
Далее, спускаемся ниже и видим это: 
AWS_ACCESS_KEY_ID=</br>
AWS_SECRET_ACCESS_KEY=</br>
AWS_DEFAULT_REGION=us-east-1</br>
AWS_BUCKET=</br>
AWS_USE_PATH_STYLE_ENDPOINT=false</br>

меняем это все на это:</br>

AWS_ACCESS_KEY_ID=</br>
AWS_SECRET_ACCESS_KEY=</br>
AWS_DEFAULT_REGION=</br>
AWS_BUCKET=</br>
AWS_URL=</br>
AWS_ENDPOINT=</br>
AWS_USE_PATH_STYLE_ENDPOINT=true</br>

И заполняем данные, которые были в том самом загадочном ворд-файле.</br>

После заполнения - сохраняем (если нет авто-сейва), и всоп, идем дальше.</br>

5. php artisan migrate --force --seed
6. После этого всего, нужно сделать: php artisan config:clear и php artisan cache:clear
7. php artisan serve
И все держим открытым до конца

7.1. Делаем еще одно окно терминала OSPanel, и там пишем:
1. cd 'путь'
2. php artisan queue:work
И все держим открытым до конца и проверяем работу.
