<?php

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

// Загрузка языковых файлов
Loc::loadMessages(__FILE__);

// Регистрация автозагрузки классов
Loader::registerAutoLoadClasses('only.phpdev', [
    'Only\Phpdevmodule\CIBlockPropertyCProp' => 'lib/only.phpdevmodule.php',
]);

// Регистрация обработчика для пользовательских полей
EventManager::getInstance()->addEventHandler(
    'main', 
    'OnUserTypeBuildList', 
    ['Only\Phpdevmodule\CIBlockPropertyCProp', 'getUserTypeDescription']
);

// Регистрация обработчика для свойств инфоблоков
EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['Only\Phpdevmodule\CIBlockPropertyCProp', 'getUserTypeDescription']
);