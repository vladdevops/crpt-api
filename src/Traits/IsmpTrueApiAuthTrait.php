<?php

namespace CrptApi\Traits;

use \Exception;
use CrptApi\AuthData;
use CrptApi\Exception\AuthSignInException;
use CrptApi\Exception\NotAuthException;
use CrptApi\Exception\TokenExpiredException;
use CrptApi\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

/**
 * ГИС МТ (Государственная информационная система мониторинга товаров)
 */
trait IsmpTrueApiAuthTrait
{

    /* @var JWT */
    protected $jwt;
    /* @var Client */
    protected $httpClient;

    /**
     * Запрос авторизации при единой аутентификации
     *
     * @return AuthData
     *
     * @throws Exception
     */
    public function getAuthData()
    {
        $response = json_decode($this->httpClient->get($this->getUrl('/auth/key'))->getBody(), true);
        if (!$response || !isset($response['data'])) {
            throw new Exception('Невозможно получить данные для подписи для аутентификации в ЦРПТ');
        }
        return new AuthData($response);
    }


    /**
     * Получение ключа сессии при единой аутентификации
     *
     * @param string $uuid
     * @param string $signedData
     *
     * @return JWT
     *
     * @throws Exception
     * @throws AuthSignInException
     */
    public function getAuthToken($uuid, $signedData)
    {
        try {
            $response = json_decode($this->httpClient->post($this->getUrl('auth/simpleSignIn'), [
                RequestOptions::JSON => [
                    'uuid' => $uuid,
                    'data' => str_replace(["\r", "\n"], '', $signedData),
                ],
            ])->getBody()->getContents(), true);
        } catch (RequestException $e) {
            try {
                $error = json_decode($e->getResponse()->getBody(), true);

                $error_message = $error['error_message'];
                $error_description = $error['description'];
                $error_code = $error['code'];
            } catch (Exception $e) {
                throw new Exception('Ошибка при чтении ошибки ответа ЦРПТ', 500, $e);
            }
            throw new AuthSignInException($error_message, $error_description, $error_code, $e);
        }
        if (!$response || !isset($response['token'])) {
            throw new Exception('Невозможно получить JWT-токен в ЦРПТ с использованием указанной подписи', 500);
        }

        return $this->getJwtByToken($response['token']);
    }

    /**
     * @param string $token
     *
     * @return JWT
     */
    private function getJwtByToken($token)
    {
        return JWT::parse($token);
    }

    /**
     * Авторизация на сервере
     *
     * @param string $token
     *
     * @throws Exception
     * @throws TokenExpiredException
     */
    public function authByToken($token)
    {
        $jwt = $this->getJwtByToken($token);
        if (!$jwt->isValid()) {
            throw new TokenExpiredException("JWT-токен валиден только до {$jwt->getValidTo()->format('d.m.Y H:i:s')}");
        }
        $this->jwt = $jwt;
    }

    /**
     * @throws Exception
     * @throws NotAuthException
     * @throws TokenExpiredException
     */
    private function checkJwt()
    {
        if (!$this->jwt) {
            throw new NotAuthException();
        }
        if (!$this->jwt->isValid()) {
            throw new TokenExpiredException();
        }
    }
}
