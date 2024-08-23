<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;
Loader::includeModule("iblock");

function getParserDataIblockId() {
    $iblock = \Bitrix\Iblock\IblockTable::getList([
        'filter' => ['CODE' => 'PARSER_DATA'],
        'select' => ['ID']
    ])->fetch();

    if ($iblock) {
        \Bitrix\Main\Config\Option::set("main", "PARSER_DATA_IBLOCK_ID", $iblock['ID']);
        return $iblock['ID'];
    }
    return false;
}

function parseData() {
    $sourceIblockId = 2; // ID исходного инфоблока "Статьи"
    $targetIblockId = getParserDataIblockId();
    
    if (!$targetIblockId) {
        die("Ошибка: Инфоблок 'Данные парсера' не найден. Убедитесь, что он создан и имеет символьный код 'PARSER_DATA'.");
    }
    
    echo "Целевой инфоблок: ID = {$targetIblockId}<br>";

    $elements = \CIBlockElement::GetList(
        ["SORT" => "ASC"],
        ["IBLOCK_ID" => $sourceIblockId, "ACTIVE" => "Y"],
        false,
        false,
        ["ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE", "PROPERTY_KEYWORDS", "PROPERTY_AUTHOR", "PROPERTY_FORUM_TOPIC_ID", 
         "PROPERTY_FORUM_MESSAGE_CNT", "PROPERTY_vote_count", "PROPERTY_vote_sum", "PROPERTY_rating", 
         "PROPERTY_THEMES", "PROPERTY_BROWSER_TITLE"]
    );

    $totalElements = 0;
    $addedElements = 0;

    while ($element = $elements->GetNext()) {
        $totalElements++;
        $elementFields = [
            "IBLOCK_ID" => $targetIblockId,
            "NAME" => $element["NAME"],
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => $element["PREVIEW_TEXT"],
            "DETAIL_TEXT" => $element["DETAIL_TEXT"],
            "PROPERTY_VALUES" => [
                "KEYWORDS" => $element["PROPERTY_KEYWORDS_VALUE"],
                "AUTHOR" => $element["PROPERTY_AUTHOR_VALUE"],
                "FORUM_TOPIC_ID" => $element["PROPERTY_FORUM_TOPIC_ID_VALUE"],
                "FORUM_MESSAGE_CNT" => $element["PROPERTY_FORUM_MESSAGE_CNT_VALUE"],
                "vote_count" => $element["PROPERTY_vote_count_VALUE"],
                "vote_sum" => $element["PROPERTY_vote_sum_VALUE"],
                "rating" => $element["PROPERTY_rating_VALUE"],
                "THEMES" => $element["PROPERTY_THEMES_VALUE"],
                "BROWSER_TITLE" => $element["PROPERTY_BROWSER_TITLE_VALUE"]
            ]
        ];

        if ($element["PREVIEW_PICTURE"]) {
            $elementFields["PREVIEW_PICTURE"] = CFile::MakeFileArray($element["PREVIEW_PICTURE"]);
        }
        if ($element["DETAIL_PICTURE"]) {
            $elementFields["DETAIL_PICTURE"] = CFile::MakeFileArray($element["DETAIL_PICTURE"]);
        }

        $el = new \CIBlockElement;
        if ($el->Add($elementFields)) {
            $addedElements++;
            echo "Добавлен элемент: {$element['NAME']}<br>";
            if ($element["PREVIEW_PICTURE"]) echo "- Добавлена превью картинка<br>";
            if ($element["DETAIL_PICTURE"]) echo "- Добавлена детальная картинка<br>";
            if ($element["PREVIEW_TEXT"] || $element["DETAIL_TEXT"]) echo "- Добавлен текст<br>";
            echo "<br>";
        } else {
            echo "Ошибка при добавлении элемента '{$element['NAME']}': " . $el->LAST_ERROR . "<br><br>";
        }
    }

    echo "Обработка завершена. Всего элементов: {$totalElements}, успешно добавлено: {$addedElements}<br>";
}

parseData();