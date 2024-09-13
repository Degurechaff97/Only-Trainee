<?php

namespace Only\Phpdevmodule;

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

class CIBlockPropertyCProp extends BaseType
{
    private static $showedCss = false;
    private static $showedJs = false;

    public const USER_TYPE_ID = 'complex';

    public static function getUserTypeDescription(): array
    {
        return [
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => self::class,
            "DESCRIPTION" => Loc::getMessage('ONLY_PHPDEV_PROP_DESC'),
            "BASE_TYPE" => CUserTypeManager::BASE_TYPE_STRING,
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => self::USER_TYPE_ID,
            "GetPropertyFieldHtml" => [self::class, 'GetPropertyFieldHtml'],
            "ConvertToDB" => [self::class, 'ConvertToDB'],
            "ConvertFromDB" => [self::class, 'ConvertFromDB'],
            "GetSettingsHtml" => [self::class, 'getSettingsHtml'],
            "PrepareSettings" => [self::class, 'prepareSettings'],
            "GetLength" => [self::class, 'GetLength'],
            "GetAdminListViewHTML" => [self::class, 'getAdminListViewHTML'],
            "GetAdminFilterHTML" => [self::class, 'getFilterHtml'],
            "GetFilterHTML" => [self::class, 'getFilterHtml'],
        ];
    }
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName): string
    {
        self::showCss();
        self::showJs();

        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        $result = '<div class="mf-gray"><a class="cl mf-toggle">'.Loc::getMessage('ONLY_PHPDEV_HIDE_TEXT').'</a></div>';
        $result .= '<table class="mf-fields-list active">';

        foreach ($arFields as $code => $arItem) {
            if ($arItem['TYPE'] === 'html') {
                $result .= self::showHtmlEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'string') {
                $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }

        $result .= '</table>';

        return $result;
    }

    private static function showHtmlEditor($code, $title, $value, $strHTMLControlName): string
    {
        $fieldName = $strHTMLControlName['VALUE'].'['.$code.']';
        $fieldValue = $value['VALUE'][$code] ?? '';
    
        $rows = $arUserField["SETTINGS"]["ROWS"] ?? $arProperty["ROWS"] ?? 8;
        $rows = max(4, intval($rows));
    
        ob_start();
        
        \CFileMan::AddHTMLEditorFrame(
            $fieldName,
            $fieldValue,
            $fieldName."_TYPE",
            strlen($fieldValue) ? "html" : "text",
            [
                'height' => $rows * 10,
                'width' => '100%'
            ]
        );
        
        $html = ob_get_clean();
    
        return '<tr><td>'.$title.':</td><td>'.$html.'</td></tr>';
    }

    private static function showString($code, $title, $value, $strHTMLControlName): string
    {
        $v = !empty($value['VALUE'][$code]) ? $value['VALUE'][$code] : '';
        return '<tr>
                <td align="right">'.$title.': </td>
                <td><input type="text" name="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.$v.'" size="50"></td>
            </tr>';
    }

    public static function getSettingsHtml($userField, $additionalParameters, $varsFromForm): string
    {
        $result = '';
        $arFields = self::prepareSetting($userField['SETTINGS']);
    
        $result .= '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                   <td>'.Loc::getMessage('ONLY_PHPDEV_SETTING_FIELD_CODE').'</td>
                   <td>'.Loc::getMessage('ONLY_PHPDEV_SETTING_FIELD_TITLE').'</td>
                   <td>'.Loc::getMessage('ONLY_PHPDEV_SETTING_FIELD_TYPE').'</td>
                </tr>';    
    
        foreach ($arFields as $code => $arItem) {
            $result .= '
                <tr valign="top">
                    <td><input type="text" name="'.$additionalParameters['NAME'].'['.$code.'_CODE]" value="'.$code.'" size="20"></td>
                    <td><input type="text" name="'.$additionalParameters['NAME'].'['.$code.'_TITLE]" value="'.$arItem['TITLE'].'" size="35"></td>
                    <td>
                        <select name="'.$additionalParameters['NAME'].'['.$code.'_TYPE]">
                            <option value="string" '.($arItem['TYPE']=='string'?'selected':'').'>'.Loc::getMessage('ONLY_PHPDEV_SETTING_FIELD_TYPE_STRING').'</option>
                            <option value="html" '.($arItem['TYPE']=='html'?'selected':'').'>'.Loc::getMessage('ONLY_PHPDEV_SETTING_FIELD_TYPE_HTML').'</option>
                        </select>
                    </td>
                </tr>';
        }
    
        $result .= '</table></td></tr>';
    
        return $result;
    }

    public static function prepareSettings($userField): array
    {
        return [
            'FIELDS' => is_array($userField['SETTINGS']['FIELDS']) ? $userField['SETTINGS']['FIELDS'] : [],
        ];
    }

    public static function GetLength($arProperty, $arValue): bool
    {
        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
        $result = false;
        foreach ($arValue['VALUE'] as $code => $value) {
            if (!empty($value)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public static function ConvertToDB($arProperty, $arValue): array
    {
        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        $isEmpty = true;
        foreach ($arValue['VALUE'] as $v) {
            if (!empty($v)) {
                $isEmpty = false;
                break;
            }
        }

        if ($isEmpty === false) {
            $arResult['VALUE'] = json_encode($arValue['VALUE']);
        } else {
            $arResult = ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        return $arResult;
    }

    public static function ConvertFromDB($arProperty, $arValue): array
    {
        $return = [];

        if (!empty($arValue['VALUE'])) {
            $arData = json_decode($arValue['VALUE'], true);

            foreach ($arData as $code => $value) {
                $return['VALUE'][$code] = $value;
            }
        }
        return $return;
    }

    private static function prepareSetting($arSetting): array
    {
        $arResult = [];

        foreach ($arSetting as $key => $value) {
            if (strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            } else if (strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        return $arResult;
    }

    private static function showCss(): void
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            echo <<<HTML
            <style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
            </style>
    HTML;
        }
    }

    private static function showJs(): void
    {
        if (!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                BX.ready(function(){
                    BX.bindDelegate(
                        document.body, 'click', {className: 'mf-toggle'},
                        function(e){
                            var target = e.target || e.srcElement;
                            var parent = BX.findParent(target, {tag: 'div'});
                            var list = BX.findChild(parent.nextSibling, {className: 'mf-fields-list'}, true, false);
                            if (list) {
                                BX.toggleClass(list, 'active');
                            }
                        }
                    );
                });
            </script>
            <?php
        }
    }

    public static function getDbColumnType(): string
    {
        return 'text';
    }

    public static function getFilterHtml($arUserField, $arHtmlControl): string
    {
        return '';
    }

    public static function getAdminListViewHTML($arUserField, $arHtmlControl): string
    {
        $value = static::normalizeFieldValue($arHtmlControl['VALUE']);
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[] = $key . ': ' . $item;
            }
            return implode('<br>', $result);
        }
        return (string)$value;
    }

    private static function normalizeFieldValue($value): array
    {
        if (!is_array($value)) {
            $value = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $value = [];
            }
        }
        return $value;
    }

    public static function OnBeforeSave($arUserField, $value)
    {
        if ($arUserField['MULTIPLE'] === 'Y') {
            foreach ($_POST as $key => $val) {
                if (preg_match("/{$arUserField['FIELD_NAME']}_([0-9]+)_$/i", $key, $m)) {
                    $value = $val;
                    unset($_POST[$key]);
                    break;
                }
            }
        }
        return $value;
    }
}