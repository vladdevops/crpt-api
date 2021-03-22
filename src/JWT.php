<?php

namespace CrptApi;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token\DataSet;

/**
 * Токен авторизации
 */
class JWT
{

    /* @var string Токен */
    protected $token;
    /* @var DataSet Данные токена */
    protected $data;

    public function __construct($token)
    {
        $this->data = (new Parser())->parse((string)$token)->claims();
        $this->token = $token;
    }

    /**
     * Парсинг токена
     *
     * @param string $tokenString
     *
     * @return JWT
     */
    public static function parse($tokenString)
    {
        return new JWT($tokenString);
    }

    /**
     * До какого времени действует токен
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function getValidTo()
    {
        return $this->data->get('exp');
    }

    /**
     * Действует ли еще токен
     *
     * @return bool
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Действует ли еще токен
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isValid()
    {
        return $this->getValidTo()->getTimestamp() > time();
    }
}
