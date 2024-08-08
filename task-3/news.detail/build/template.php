<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->addExternalCss($templateFolder."/css/common.css");
?>

<div class="news-detail">
    <h1><?=$arResult["NAME"]?></h1>

    <?if(strlen($arResult["ACTIVE_FROM"])):?>
        <span class="news-date-time"><?=$arResult["ACTIVE_FROM"]?></span>
    <?endif;?>

    <?if($arResult["DETAIL_PICTURE"]):?>
        <div class="news-image">
            <img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" 
                 alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>" 
                 title="<?=$arResult["DETAIL_PICTURE"]["TITLE"]?>">
        </div>
    <?endif;?>

    <div class="news-text">
        <?if(strlen($arResult["DETAIL_TEXT"])>0):?>
            <?=$arResult["DETAIL_TEXT"];?>
        <?else:?>
            <?=$arResult["PREVIEW_TEXT"];?>
        <?endif?>
    </div>

    <?if(isset($arResult["DISPLAY_PROPERTIES"]) && !empty($arResult["DISPLAY_PROPERTIES"])):?>
        <div class="news-properties">
            <?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
                <div class="news-property">
                    <strong><?=$arProperty["NAME"]?>:</strong> 
                    <?if(is_array($arProperty["DISPLAY_VALUE"])):?>
                        <?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
                    <?else:?>
                        <?=$arProperty["DISPLAY_VALUE"];?>
                    <?endif?>
                </div>
            <?endforeach;?>
        </div>
    <?endif;?>

    <a href="<?=$arResult["LIST_PAGE_URL"]?>" class="news-back-link"><?=GetMessage("RETURN_TO_LIST")?></a>
</div>