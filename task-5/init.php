<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
// Подключаем обработчик событий
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/event_register.php");

// Подключаем агент очистки логов
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/log_cleanup_agent.php");