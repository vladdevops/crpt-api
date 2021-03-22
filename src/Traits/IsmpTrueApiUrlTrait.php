<?php

namespace CrptApi\Traits;

trait IsmpTrueApiUrlTrait
{

    /* @var string */
    protected $url;

    /**
     * @param string $urn
     *
     * @return string
     */
    protected function getUrl($urn)
    {
        return "{$this->url}/{$urn}";
    }
}
