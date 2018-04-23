<?

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo;

$module_id = "powernic.seo";

if (!$USER->CanDoOperation('powernic_seo_settings')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
if ($moduleAccessLevel >= "R") {
    CModule::IncludeModule('powernic.seo');
    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
    IncludeModuleLangFile(__FILE__);
    $aTabs = array(
        array("DIV" => "edit0", "TAB" => "OpenGraph", "ICON" => "currency_settings", "TITLE" => "Настройки"),
        array("DIV" => "edit1", "TAB" => "TwitterCard", "ICON" => "currency_settings", "TITLE" => "Настройки"),
    );
    $tabControl = new CAdminTabControl("seoTabControl", $aTabs, true, true);
    /* processed POST or GET queries*/
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $moduleAccessLevel == "W" && check_bitrix_sessid()) {
        if (isset($_POST['Update']) && $_POST['Update'] === 'Y') {
            $listParam = array('og:locale', 'og:type', 'twitter:card', 'og:site_name');
            foreach ($listParam as $param) {
                if (isset($_POST[$param])) {
                    Seo\SeoManager::updateParam($param, (string)$_POST[$param]);
                }
            }
            LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $module_id . '&' . $tabControl->ActiveTabParam());
        }
    }
    ?>
    <?
    $paramList = Seo\SeoManager::getParamsList();
    $params = array('og:locale' => array('ru_RU', 'en_EN'),
        'og:type' => array('article', 'website', 'object'),
        'twitter:card' => array('summary_large_image', 'summary'));
    function selectHtml($params, $paramName, $value)
    {
        if (!isset($params[$paramName])) {
            return false;
        }
        echo '<select name="' . $paramName . '">';
        foreach ($params[$paramName] as $variable) {
            echo '<option ' . ($variable == $value ? 'selected="selected"' : "") . ' value="' . $variable . '">' . $variable . '</option>';
        }
        echo "</select>";
    }

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
            <td width="60%"><?
                selectHtml($params, "og:locale", $paramList['og:locale']) ?>
        </tr>
        <tr>
            <td width="40%">Type</td>
            <td width="60%"><?
                selectHtml($params, "og:type", $paramList['og:type']) ?>
        </tr>
        <tr>
            <td width="40%">Site Name</td>
            <td width="60%"><input type="text" name="og:site_name" value="<?=$paramList['og:site_name']?>"></td>
        </tr>
        <?php
        $tabControl->BeginNextTab(); ?>
        <tr>
            <td width="40%">Card</td>
            <td width="60%"><?
                selectHtml($params, "twitter:card", $paramList['twitter:card']) ?>
        </tr>
        <?php $tabControl->Buttons(); ?>
        <input type="submit"<?= ($moduleAccessLevel < 'W' ? ' disabled' : ''); ?> name="Update"
               value="<?= Loc::getMessage('SEO_OPTIONS_BTN_SAVE') ?>" class="adm-btn-save"
               title="<?= Loc::getMessage('SEO_OPTIONS_BTN_SAVE_TITLE'); ?>">
        <input type="hidden" name="Update" value="Y">
    </form>
    <?php
    $tabControl->End();
}
?>
