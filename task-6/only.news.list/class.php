<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class OnlyNewsList extends CBitrixComponent
{
    protected $errors = [];

    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
        
        $arParams['ELEMENTS_COUNT'] = intval($arParams['ELEMENTS_COUNT']);
        if ($arParams['ELEMENTS_COUNT'] <= 0) {
            $arParams['ELEMENTS_COUNT'] = 20;
        }
        
        $arParams['SORT_BY1'] = trim($arParams['SORT_BY1']);
        if (empty($arParams['SORT_BY1'])) {
            $arParams['SORT_BY1'] = 'ACTIVE_FROM';
        }
        
        $arParams['SORT_ORDER1'] = strtoupper($arParams['SORT_ORDER1']);
        if ($arParams['SORT_ORDER1'] != 'ASC') {
            $arParams['SORT_ORDER1'] = 'DESC';
        }
        
        $arParams['SORT_BY2'] = trim($arParams['SORT_BY2']);
        if (empty($arParams['SORT_BY2'])) {
            $arParams['SORT_BY2'] = 'SORT';
        }
        
        $arParams['SORT_ORDER2'] = strtoupper($arParams['SORT_ORDER2']);
        if ($arParams['SORT_ORDER2'] != 'DESC') {
            $arParams['SORT_ORDER2'] = 'ASC';
        }
    
        return $arParams;
    }

    protected function checkRequirements()
    {
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = GetMessage('IBLOCK_MODULE_NOT_INSTALLED');
            return false;
        }

        return true;
    }

    protected function fetchElements()
    {
        $arOrder = [
            $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
            $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
        ];

        $arFilter = [
            'ACTIVE' => 'Y',
        ];

        if (!empty($this->arParams['IBLOCK_ID'])) {
            $arFilter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        } else {
            $arFilter['IBLOCK_TYPE'] = $this->arParams['IBLOCK_TYPE'];
        }

        $arSelect = [
            'ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_PAGE_URL', 'IBLOCK_SECTION_ID',
        ];

        $rsElements = CIBlockElement::GetList($arOrder, $arFilter, false, ['nTopCount' => $this->arParams['ELEMENTS_COUNT']], $arSelect);

        $this->arResult['ITEMS'] = [];
        while ($ob = $rsElements->GetNextElement()) {
            $arFields = $ob->GetFields();
            $this->arResult['ITEMS'][] = $arFields;
        }

        if (empty($this->arResult['ITEMS'])) {
            $this->errors[] = GetMessage('NO_ITEMS_FOUND');
        }
    }

    protected function groupElements()
    {
        $groupedItems = [];
        foreach ($this->arResult['ITEMS'] as $item) {
            $iblockId = $item['IBLOCK_ID'];
            if (!isset($groupedItems[$iblockId])) {
                $groupedItems[$iblockId] = [];
            }
            $groupedItems[$iblockId][] = $item;
        }
        $this->arResult['ITEMS'] = $groupedItems;
    }

    public function executeComponent()
    {
        if (!$this->checkRequirements()) {
            $this->showErrors();
            return;
        }

        if ($this->startResultCache()) {
            $this->fetchElements();
            $this->groupElements();
            
            $this->includeComponentTemplate();
        }
        $this->showErrors();

        return $this->arResult;
    }

    protected function showErrors()
    {
        foreach ($this->errors as $error) {
            ShowError($error);
        }
    }
}