<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

function agentLog($message) {
    $logDir = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/logs_agent_cleanup/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . 'log_' . date('Y-m-d') . '.log';
    $logMessage = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if (!function_exists('getOrCreateLogInfoblock')) {
    function getOrCreateLogInfoblock()
    {
        CModule::IncludeModule('iblock');
        
        $iblockId = CIBlock::GetList([], ['CODE' => 'LOG'])->Fetch()['ID'];
        if (!$iblockId) {
            $iblockTypeId = 'LOG';
            if (!CIBlockType::GetByID($iblockTypeId)->Fetch()) {
                $obBlocktype = new CIBlockType;
                $obBlocktype->Add([
                    'ID' => $iblockTypeId,
                    'SECTIONS' => 'Y',
                    'IN_RSS' => 'N',
                    'SORT' => 500,
                    'LANG' => [
                        'ru' => ['NAME' => 'Логи системы', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Элементы'],
                        'en' => ['NAME' => 'System Logs', 'SECTION_NAME' => 'Sections', 'ELEMENT_NAME' => 'Elements']
                    ]
                ]);
            }

            $ib = new CIBlock;
            $iblockId = $ib->Add([
                "ACTIVE" => "Y",
                "NAME" => "Логи",
                "CODE" => "LOG",
                "IBLOCK_TYPE_ID" => $iblockTypeId,
                "SITE_ID" => ["s1"],
                "SORT" => 500,
                "GROUP_ID" => ["2" => "R"],
            ]);
            
            if (!$iblockId) {
                AddMessage2Log("Ошибка при создании инфоблока LOG: " . $ib->LAST_ERROR, "log_cleanup_agent");
            }
        }
        return $iblockId;
    }
}

function cleanupLogElements()
{
    global $USER;
    $USER->Authorize(1);

    agentLog("cleanupLogElements начал выполнение");

    $logIblockId = getOrCreateLogInfoblock();
    agentLog("ID инфоблока LOG: " . $logIblockId);

    if (!$logIblockId) {
        agentLog("Ошибка: Инфоблок LOG не найден");
        return "cleanupLogElements();";
    }


    // Получаем все разделы инфоблока LOG
    $rsSections = CIBlockSection::GetList(
        [],
        ["IBLOCK_ID" => $logIblockId],
        false,
        ["ID", "NAME"]
    );

    $el = new CIBlockElement;
    $totalDeletedCount = 0;

    while ($arSection = $rsSections->Fetch()) {
        $arKeepIds = [];
        $rsElements = CIBlockElement::GetList(
            ["DATE_CREATE" => "DESC"],
            ["IBLOCK_ID" => $logIblockId, "SECTION_ID" => $arSection["ID"]],
            false,
            ["nTopCount" => 10],
            ["ID"]
        );
        while ($arElement = $rsElements->Fetch()) {
            $arKeepIds[] = $arElement["ID"];
        }

        $rsElementsToDelete = CIBlockElement::GetList(
            [],
            ["IBLOCK_ID" => $logIblockId, "SECTION_ID" => $arSection["ID"], "!ID" => $arKeepIds],
            false,
            false,
            ["ID"]
        );

        $deletedCount = 0;
        while ($arElement = $rsElementsToDelete->Fetch()) {
            if ($el->Delete($arElement["ID"])) {
                $deletedCount++;
                $totalDeletedCount++;
            } else {
                agentLog("Ошибка при удалении элемента лога ID: " . $arElement["ID"] . " в разделе " . $arSection["NAME"]);
            }
        }

        agentLog("Удалено " . $deletedCount . " элементов в разделе " . $arSection["NAME"]);
    }

    agentLog("cleanupLogElements завершил выполнение. Всего удалено элементов: " . $totalDeletedCount);
    return "cleanupLogElements();";
}

// Код регистрации агента
$existingAgent = CAgent::GetList([], ["NAME" => "cleanupLogElements();"])->Fetch();

if (!$existingAgent) {
    $cleanupInterval = 3600; // 1 час
    $result = CAgent::AddAgent(
        "cleanupLogElements();",
        "",
        "N",
        $cleanupInterval,
        "",
        "Y",
        ConvertTimeStamp(time()+CTimeZone::GetOffset()+$cleanupInterval, "FULL")
    );
    agentLog("Агент cleanupLogElements " . ($result ? "успешно добавлен" : "не удалось добавить"));
} elseif ($existingAgent['AGENT_INTERVAL'] != 3600) {
    CAgent::Update($existingAgent['ID'], ["AGENT_INTERVAL" => 3600]);
    agentLog("Интервал агента cleanupLogElements обновлен до 3600 секунд");
}