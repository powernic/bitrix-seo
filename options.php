<?

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo;

$module_id = "powernic.seo";

if (!$USER->CanDoOperation('powernic_seo_settings')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$seoRight = $APPLICATION->GetGroupRight($module_id);
if ($seoRight >= "R") {
    CModule::IncludeModule('powernic.seo');
}
if ($seoRight >= "R") {
    CModule::IncludeModule('powernic.seo');
    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
    IncludeModuleLangFile(__FILE__);
    $aTabs = array(
        array("DIV" => "edit0", "TAB" => "OpenGraph", "ICON" => "currency_settings", "TITLE" => "Настройки"),
        array("DIV" => "edit1", "TAB" => "TwitterCard", "ICON" => "currency_settings", "TITLE" => "Настройки"),
    );
    $tabControl = new CAdminTabControl("seoTabControl", $aTabs, true, true);
    /* processed POST or GET queries*/
    ?>
    <?
    $paramList = Seo\SeoManager::getParamsList();
    print_r($paramList);
    ?>
    <h2>Значения по умолчанию</h2>
    <?php
    $tabControl->Begin(); ?>
    <form method="POST"
          action="<?php echo $APPLICATION->GetCurPage() ?>?lang=<?php echo LANGUAGE_ID ?>&mid=<?= $module_id ?>"
          name="powernic_seo_settings">
        <?php echo bitrix_sessid_post();
        $tabControl->BeginNextTab(); ?>
        <tr>
            <td width="40%">Locale</td>
            <td width="60%"><select name="og:locale">
                    <option value="ru_RU">ru_RU</option>
                    <option value="en_EN">en_EN</option>
                </select></td>
        </tr>
        <tr>
            <td width="40%">Type</td>
            <td width="60%"><select name="og:type">
                    <option value="article">article</option>
                    <option value="website">website</option>
                    <option value="object">object</option>
                </select></td>
        </tr>
        <tr>
            <td width="40%">Site Name</td>
            <td width="60%"><input type="text" name="og:site_name"></td>
        </tr>
        <?php
        $tabControl->BeginNextTab(); ?>
        <tr>
            <td width="40%">Card</td>
            <td width="60%"><select name="twitter:card">
                    <option value="summary_large_image">summary_large_image</option>
                    <option value="summary">summary</option>
                </select></td>
        </tr>
        <?php $tabControl->Buttons(); ?>
    </form>
    <?php
    $tabControl->End();
}
?>
