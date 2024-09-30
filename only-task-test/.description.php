<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    "NAME" => "Доступные автомобили",
    "DESCRIPTION" => "Выводит список доступных автомобилей для текущего сотрудника на указанные даты",
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "only",
        "NAME" => "Only",
        "CHILD" => [
            "ID" => "available_cars",
            "NAME" => "Доступные автомобили",
        ]
    ],
];