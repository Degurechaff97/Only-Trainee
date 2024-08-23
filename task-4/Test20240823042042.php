<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

class Test20240823042042 extends Version
{
    protected $description = "Универсальная миграция для создания инфоблока парсера";

    const SOURCE_IBLOCK_ID = 2; // ID исходного инфоблока "Статьи"
    const TARGET_IBLOCK_CODE = 'PARSER_DATA';
    const TARGET_IBLOCK_TYPE = 'articles';

    public function up()
    {
        Loader::includeModule("iblock");

        $targetIblockId = $this->createTargetIblock();
        $sourceProperties = $this->getSourceIblockProperties();
        $this->createTargetIblockProperties($targetIblockId, $sourceProperties);

        return true;
    }

    private function createTargetIblock()
    {
        $iblockFields = [
            "ACTIVE" => "Y",
            "NAME" => "Данные парсера",
            "CODE" => self::TARGET_IBLOCK_CODE,
            "IBLOCK_TYPE_ID" => self::TARGET_IBLOCK_TYPE,
            "SITE_ID" => ["s1"],
            "SORT" => 500,
            "GROUP_ID" => ["2" => "R"],
        ];

        $iblock = new \CIBlock;
        $iblockId = $iblock->Add($iblockFields);

        if (!$iblockId) {
            throw new \Exception($iblock->LAST_ERROR);
        }

        return $iblockId;
    }

    private function getSourceIblockProperties()
    {
        $properties = [];
        $rsProperties = PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => self::SOURCE_IBLOCK_ID],
            'select' => ['*']
        ]);

        while ($prop = $rsProperties->fetch()) {
            $properties[] = $prop;
        }

        return $properties;
    }

    private function createTargetIblockProperties($targetIblockId, $sourceProperties)
    {
        $ibp = new \CIBlockProperty;
        foreach ($sourceProperties as $prop) {
            $newProp = [
                "NAME" => $prop['NAME'],
                "ACTIVE" => $prop['ACTIVE'],
                "SORT" => $prop['SORT'],
                "CODE" => $prop['CODE'],
                "PROPERTY_TYPE" => $prop['PROPERTY_TYPE'],
                "IBLOCK_ID" => $targetIblockId,
                "LIST_TYPE" => $prop['LIST_TYPE'],
                "MULTIPLE" => $prop['MULTIPLE'],
                "IS_REQUIRED" => $prop['IS_REQUIRED'],
            ];

            $propId = $ibp->Add($newProp);
            if (!$propId) {
                throw new \Exception($ibp->LAST_ERROR);
            }
        }
    }

    public function down()
    {
        Loader::includeModule("iblock");

        $iblock = IblockTable::getList([
            'filter' => ['CODE' => self::TARGET_IBLOCK_CODE],
            'select' => ['ID']
        ])->fetch();

        if ($iblock) {
            \CIBlock::Delete($iblock['ID']);
        }
    }
}