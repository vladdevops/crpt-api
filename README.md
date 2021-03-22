Реализация API, на основе [True API версии 17.2 от 19.03.2021](https://xn--80ajghhoc2aj1c8b.xn--p1ai/upload/TRUE_API.pdf), для работы с сайтом [честныйзнак.рф](https://xn--80ajghhoc2aj1c8b.xn--p1ai/)

[Глоссарий](GLOSSARY.md)

## Установка

### Composer

```sh
composer require vladdevops/crpt-api
```

### Пример

* Запрос авторизации

```php
<?php

use CrptApi\IsmpTrueApi;

$test = true;

$ismpApi = new IsmpTrueApi($test);

try {    
    $authData = $ismpApi->getAuthData();

    $uuid = $authData->getUuid();
    $data = $authData->getData();
} catch (\Exception $e) {
    $message = $e->getMessage();
}
```

* Подписываем строку `$authData->getData()`

* Получение аутентификационного токена

```php
<?php

use CrptApi\IsmpTrueApi;

$test = true;

$ismpApi = new IsmpTrueApi($test);

$uuid = '<uuid полученный из getAuthData>';
$signed = '<Подписанные данные в base64 (ЭП присоединенная)>';

try {    
    $jwt = $ismpApi->getAuthToken($uuid, $signed);

    $token = $jwt->getToken();
} catch (\Exception $e) {
    $message = $e->getMessage();
}
```