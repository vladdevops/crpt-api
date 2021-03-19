<?php

namespace CrptApi;

/**
 * Данные для авторизации
 */
class AuthData
{

    /* @var string Строка из нескольких байт, которую нужно подписать */
    private $data;

    /* @var string Идентификатор запроса, который нужно вернуть */
    private $uuid;

    public function __construct(array $response)
    {
        $this->data = $response['data'];
        $this->uuid = $response['uuid'];
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getData()
    {
        return $this->data;
    }
}
