<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->addExternalCss($templateFolder."/css/common.css");
?>

<div class="contact-form-container">
    <div class="contact-form">
        <h2 class="contact-form__title">Связаться</h2>
        <p class="contact-form__description">Наши сотрудники помогут выполнить подбор услуги и расчет цены с учетом ваших требований</p>

        <?=$arResult["FORM_HEADER"]?>
        
        <?if ($arResult["isFormErrors"] == "Y"):?>
            <div class="error-message"><?=$arResult["FORM_ERRORS_TEXT"];?></div>
        <?endif;?>

        <?foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
            <div class="form-group">
                <label class="form-label" for="<?=$FIELD_SID?>"><?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?>*<?endif;?></label>
                <?=$arQuestion["HTML_CODE"]?>
            </div>
        <?endforeach;?>

        <div class="form-footer">
            <p class="form-agreement">
                Нажимая «Отправить», Вы подтверждаете, что ознакомлены, полностью согласны и принимаете условия «Согласия на обработку персональных данных».
            </p>
            <input class="submit-btn" type="submit" name="web_form_submit" value="<?=$arResult["arForm"]["BUTTON"]?>" />
        </div>

        <?=$arResult["FORM_FOOTER"]?>
    </div>
</div>
