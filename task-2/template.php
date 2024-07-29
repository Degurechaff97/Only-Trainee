<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalCss($templateFolder."/css/common.css");
?>

<div class="contact-form-container">
    <div class="contact-form">
        <h2 class="contact-form__title">Связаться</h2>
        <p class="contact-form__description">Наши сотрудники помогут выполнить подбор услуги и расчет цены с учетом ваших требований</p>

        <?if ($arResult["isFormErrors"] == "Y"):?>
            <div class="error-message"><?=$arResult["FORM_ERRORS_TEXT"];?></div>
        <?endif;?>

        <?=$arResult["FORM_NOTE"]?>

        <?if ($arResult["isFormNote"] != "Y"):?>
            <?=$arResult["FORM_HEADER"]?>
            <div class="contact-form__inputs">
                <div class="form-group">
                    <label class="form-label" for="form_text_16">Ваше имя*</label>
                    <input type="text" id="form_text_16" name="form_text_16" value="<?=$arResult["QUESTIONS"]["form_text_16"]["VALUE"]?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="form_text_17">Компания/Должность*</label>
                    <input type="text" id="form_text_17" name="form_text_17" value="<?=$arResult["QUESTIONS"]["form_text_17"]["VALUE"]?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="form_email_18">Email*</label>
                    <input type="email" id="form_email_18" name="form_email_18" value="<?=$arResult["QUESTIONS"]["form_email_18"]["VALUE"]?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="form_text_19">Номер телефона*</label>
                    <input type="text" id="form_text_19" name="form_text_19" value="<?=$arResult["QUESTIONS"]["form_text_19"]["VALUE"]?>" required>
                </div>
                <div class="form-group full-width">
                    <label class="form-label" for="form_textarea_20">Сообщение</label>
                    <textarea id="form_textarea_20" name="form_textarea_20"><?=$arResult["QUESTIONS"]["form_textarea_20"]["VALUE"]?></textarea>
                </div>
            </div>

            <div class="form-footer">
                <p class="form-agreement">
                    Нажимая «Отправить», Вы подтверждаете, что ознакомлены, полностью согласны и принимаете условия «Согласия на обработку персональных данных».
                </p>
                <input class="submit-btn" type="submit" name="web_form_submit" value="Оставить заявку" />
            </div>

            <?=$arResult["FORM_FOOTER"]?>
        <?endif;?>
    </div>
</div>
