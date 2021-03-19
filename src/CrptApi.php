<?php

namespace CrptApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use CrptApi\Exception\NotAuthException;
use CrptApi\Exception\TokenExpiredException;
use \Exception;

class CrptApi
{

    /* @var boolean */
    private $test;
    /* @var JWT */
    private $jwt;
    /* @var Client */
    private $httpClient;

    /**
     * @param bool $test Отправлять запросы на тестовый контур ЦРПТ
     */
    public function __construct($test = false)
    {
        $this->test = $test;
        $this->httpClient = new Client([
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json; charset=UTF-8',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @return AuthData
     *
     * @throws Exception
     */
    public function getAuthData()
    {
        $response = json_decode($this->httpClient->get($this->getUrl('auth/cert/key/'))->getBody(), true);
        if (!$response || !isset($response['data'])) {
            throw new Exception('Невозможно получить данные для подписи для аутентификации в ЦРПТ');
        }
        return new AuthData($response);
    }


    /**
     * Проверить подписанные данные для авторизации
     *
     * @param string $uuid
     * @param string $signedData
     *
     * @return JWT
     *
     * @throws Exception
     */
    public function checkAuthData($uuid, $signedData)
    {
        //Отправляем подписанные данные для JWT-токена
        try {
            $response = json_decode($this->httpClient->post($this->getUrl('auth/cert/'), [
                RequestOptions::JSON => [
                    'uuid' => $uuid,
                    'data' => str_replace(["\r", "\n"], '', $signedData),
                ],
            ])->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $error = json_decode($e->getResponse()->getBody(), true);
            $error_message = (
                isset($error['error_message'])
                ? $error['error_message']
                : (
                    isset($error['description'])
                ? $error['description']
                : (
                    isset($error['code'])
                ? $error['code']
                : (
                    isset($error['error_description'])
                ? $error['error_description']
                : (
                    isset($error['error'])
                ? $error['error']
                : 'Ошибка не определенна'
                )
                )
                )
                )
            );
            throw new Exception("Ошибка ответа от сервера ЦРПТ: {$error_message}", 500, $e);
        }
        if (!$response || !isset($response['token'])) {
            throw new Exception('Невозможно получить JWT-токен в ЦРПТ с использованием указанной подписи');
        }

        //Парсим JWT
        return JWT::parse($response['token']);
    }

    /**
     * Домен для запросов к ЦРПТ
     *
     * @param string $urn
     *
     * @return string
     */
    private function getUrl($urn)
    {
        if ($this->test) {
            $url = 'https://demo.lp.crpt.tech/api/v3';
        } else {
            $url = 'https://ismp.crpt.ru/api/v3';
        }
        return "{$url}/{$urn}";
    }

    /**
     * Авторизация на сервере
     *
     * @param JWT $jwt
     *
     * @throws Exception
     * @throws TokenExpiredException
     */
    public function auth($jwt)
    {
        if (!$jwt->isValid()) {
            throw new TokenExpiredException("JWT-токен валиден только до {$jwt->getValidTo()->format('d.m.Y H:i:s')}");
        }
        $this->jwt = $jwt;
    }

    /**
     * Получить информацию о коде
     *
     * @param $code
     *
     * @return CodeInfo|null
     *
     * @throws Exception
     * @throws NotAuthException
     * @throws TokenExpiredException
     */
    public function getCodeInfo($code)
    {
        $this->checkJwt();
        $code = substr($code, 0, 31);

        if (strlen($code) < 31) {
            throw new Exception("Код после обрезки при получении информации из ЦРПТ слишком короткий: `{$code}`", 500);
        }
        try {
            $info = json_decode($this->httpClient->get(
                $this->getUrl('facade/identifytools/' . urlencode($code)),
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->jwt->getToken()}",
                    ],
                ]
            )->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getCode() == 404 || $e->getCode() == 400) {

                //Пробуем через другой адрес
                $jsonData = $this->httpClient->get(
                    $this->getUrl('facade/cis/cis_list?cis=&cis=' . urlencode($code)),
                    [
                        'headers' => [
                            'Authorization' => "Bearer {$this->jwt->getToken()}",
                        ],
                    ]
                )->getBody()->getContents();

                $info = json_decode($jsonData, true);

                if ($info) {
                    if (!isset($info[$code])) {
                        throw new Exception('Ошибка при получении информации о коде из альтернативного источника. Получен ответ ' . print_r($info), 500, $e);
                    }
                    $info = $info[$code];
                    $info['emissionDate'] = (new \DateTime('@' . substr($info['emissionDate'], 0, strlen($info['emissionDate']) - 3)))->format('Y-m-dTH:i:s.vZ');
                } else {
                    throw new Exception("Не получается расшифровать ответ от ЦРПТ. Ожидается JSON, получен ответ {$jsonData}", 500, $e);
                }
            } else {
                throw $e;
            }
        }

        if (!$info) {
            return null;
        }

        $return = new CodeInfo();

        foreach ($info as $key => $value) {
            $return->{$key} = $value;
        }

        return $return;
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
