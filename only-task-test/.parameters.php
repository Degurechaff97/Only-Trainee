<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [
        "SETTINGS" => [
            "NAME" => "Настройки компонента",
            "SORT" => 100,
        ],
    ],
    "PARAMETERS" => [
        "CARS_IBLOCK_ID" => [
            "PARENT" => "SETTINGS",
            "NAME" => "ID инфоблока автомобилей",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "BOOKINGS_IBLOCK_ID" => [
            "PARENT" => "SETTINGS",
            "NAME" => "ID инфоблока бронирований",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "POSITIONS_HLBLOCK_ID" => [
            "PARENT" => "SETTINGS",
            "NAME" => "ID highload-блока должностей",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "CACHE_TIME" => ["DEFAULT" => 3600],
    ],
];