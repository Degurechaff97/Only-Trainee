<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule("iblock")) {
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

$componentClass = new CustomNewsListComponent();
$arResult = $componentClass->executeComponent();

// Подключаем шаблон компонента
$this->IncludeComponentTemplate();