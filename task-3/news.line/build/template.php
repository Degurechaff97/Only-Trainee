<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->addExternalCss($templateFolder."/css/news-line.css");
?>

<div class="news-line">
	<?foreach($arResult["ITEMS"] as $arItem):?>
		<div class="news-item">
			<span class="news-date-time"><?echo $arItem["ACTIVE_FROM"]?></span>
			<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>" class="news-title"><?echo $arItem["NAME"]?></a>
		</div>
	<?endforeach;?>
</div>