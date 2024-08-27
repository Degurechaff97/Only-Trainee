<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('iblock');

function getOrCreateLogInfoblock()
{
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
    }
    return $iblockId;
}

function getOrCreateSection($iblockId, $name, $code)
{
    $section = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, ['ID'])->Fetch();
    if (!$section) {
        $bs = new CIBlockSection;
        $sectionId = $bs->Add([
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'CODE' => $code,
        ]);
        return $sectionId;
    }
    return $section['ID'];
}

function getElementPath($iblockId, $elementId)
{
    $path = [];
    $iblock = CIBlock::GetByID($iblockId)->Fetch();
    $element = CIBlockElement::GetByID($elementId)->Fetch();
    
    if (!$iblock || !$element) {
        return "Неизвестный путь";
    }
    $path[] = $iblock['NAME'];
    $sectionId = $element['IBLOCK_SECTION_ID'];
    while ($sectionId) {
        $section = CIBlockSection::GetByID($sectionId)->Fetch();
        array_unshift($path, $section['NAME']);
        $sectionId = $section['IBLOCK_SECTION_ID'];
    }
    $path[] = $element['NAME'];
    return implode(' -> ', $path);
}

function OnAfterIBlockElementHandler($arFields)
{
    $logIblockId = getOrCreateLogInfoblock();
    if ($arFields['IBLOCK_ID'] == $logIblockId) return;
    $iblock = CIBlock::GetByID($arFields['IBLOCK_ID'])->Fetch();
    $sectionId = getOrCreateSection($logIblockId, $iblock['NAME'], $iblock['CODE']);
    $el = new CIBlockElement;
    $el->Add([
        'IBLOCK_ID' => $logIblockId,
        'IBLOCK_SECTION_ID' => $sectionId,
        'NAME' => $arFields['ID'],
        'ACTIVE_FROM' => ConvertTimeStamp(time(), 'FULL'),
        'PREVIEW_TEXT' => getElementPath($arFields['IBLOCK_ID'], $arFields['ID']),
    ]);
}

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementHandler");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "OnAfterIBlockElementHandler");