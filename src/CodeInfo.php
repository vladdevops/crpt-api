<?php

namespace CrptApi;

/**
 * Информация о коде маркировки
 */
class CodeInfo
{
    /* @var string Код маркировки */
    public $uit;

    /* @var string Уникальный идентификатор (допускается как полное совпадение, так и частичное) */
    public $cis;

    /* @var string Global Trade Item Number */
    public $gtin;

    /* @var string */
    public $sgtin;

    /* @var string Наименование продукции */
    public $productName;
    /* @var string Дата эмиссии, от. Задается в формате yyyy-MM-dd'T'HH:mm:ss.SSS'Z Пример: 2019-01-01T03:00:00.000Z */
    public $emissionDate;
    /* @var string */
    public $participantName;
    /* @var string */
    public $participantInn;

    /* @var string Наименование собственника товара */
    public $ownerName;

    /* @var string ИНН производителя */
    public $producerInn;

    /* @var string ИНН собственника товара */
    public $ownerInn;

    /* @var string */
    public $lastDocId;
    /* @var string Тип производства (LOCAL - производство РФ, FOREIGN - ввезен в РФ) */
    public $emissionType;

    /* @var string[] */
    public $prevCises;
    /* @var string[] */
    public $nextCises;
    /* @var string */
    public $status;
    /* @var string */
    public $packType;
    /* @var int */
    public $countChildren;

    /* @return string ИНН владельца или производителя */
    public function getInn()
    {
        if ($this->ownerInn) {
            return $this->ownerInn;
        }
        return $this->producerInn;
    }
}
