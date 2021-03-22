<?php

namespace CrptApi;

use CrptApi\Traits\IsmpTrueApiAuthTrait;
use CrptApi\Traits\IsmpTrueApiUrlTrait;
use GuzzleHttp\Client;

/**
 * ГИС МТ (Государственная информационная система мониторинга товаров)
 */
class IsmpTrueApi
{
    use IsmpTrueApiUrlTrait;
    use IsmpTrueApiAuthTrait;

    /* @var string */
    protected $url;
    /* @var JWT */
    protected $jwt;
    /* @var Client */
    protected $httpClient;

    /**
     * @param bool $test Отправлять запросы на тестовый контур ЦРПТ
     */
    public function __construct($test = false)
    {
        if ($test) {
            $this->url = 'https://int01.gismt.crpt.tech/api/v3/true-api';
        } else {
            $this->url = 'https://ismotp.crptech.ru/api/v3/true-api';
            // "Табачная продукция" и "Альтернативная табачная продукция"
            // $this->url = https://markirovka.crpt.ru/api/v3/trueapi;
        }

        $this->httpClient = new Client([
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json; charset=UTF-8',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
