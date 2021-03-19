Реализация API для работы с сайтом Честныйзнак

## Установка

### Composer

```sh
composer require vladdevops/crpt-api
```

### Пример

* Запрос авторизации

```php
<?php
use CrptApi\CrptApi;

$test = true;

$crptApi = new CrptApi($this->test);

try {    
    $authData = $crptApi->getAuthData();

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
use CrptApi\CrptApi;

$test = true;

$crptApi = new CrptApi($this->test);

$uuid = '<uuid полученный из getAuthData>';
$signed = '<Подписанные данные в base64 (ЭП присоединенная)>';

try {    
    $jwt = $crptApi->checkAuthData($uuid, $signed);

    $token = $jwt->getToken();
} catch (\Exception $e) {
    $message = $e->getMessage();
}
```