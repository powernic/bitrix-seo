<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

use Bitrix\Main\Text\HtmlFilter;

/********************** Check user access rights ***********************/
if (!$USER->CanDoOperation('powernic_seo_tools'))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$io = CBXVirtualIo::GetInstance();

$arFilemanProperties = array();
if (CModule::IncludeModule("fileman") && is_callable(array("CFileMan", "GetPropstypes")))
    $arFilemanProperties = CFileMan::GetPropstypes($site);

$path = "/";
if (isset($_REQUEST["path"]) && strlen($_REQUEST["path"]) > 0) {
    $path = $_REQUEST["path"];
    $path = $io->CombinePath("/", $path);
}

//Page path
$documentRoot = CSite::GetSiteDocRoot($_REQUEST['site']);
$absoluteFilePath = $documentRoot . $path;

if (false !== ($pos = strrpos($absoluteFilePath, '/'))) {
    $absoluteDirPath = substr($absoluteFilePath, 0, $pos);
}

$bReadOnly = false;

IncludeModuleLangFile(__FILE__);


//Check permissions
if (!$io->FileExists($absoluteFilePath)) {
    CAdminMessage::ShowMessage(GetMessage('SEO_TOOLS_ERROR_FILE_NOT_FOUND') . " (" . HtmlFilter::encode($path) . ")");
    die();
} elseif (!$USER->CanDoFileOperation('fm_edit_existent_file', array($_REQUEST['site'], $path))) {
    $bReadOnly = true;
}

function SeoShowHelp($topic)
{
    $msg = GetMessage('POWERNIC_SEO_HELP_' . $topic);
    if (strlen($msg) > 0) {
        $msg = ShowJSHint($msg, array('return' => true));
    }

    return $msg;
}

if (!isset($_REQUEST["lang"]) || strlen($_REQUEST["lang"]) <= 0)
    $lang = LANGUAGE_ID;

$fileContent = $APPLICATION->GetFileContent($absoluteFilePath);
/************************** GET/POST processing ***************************************/
$strWarning = '';
$success = true;
if (!check_bitrix_sessid()) {
    CUtil::JSPostUnescape();
    $strWarning = GetMessage("MAIN_SESSION_EXPIRED");
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"])) {

    if (!$bReadOnly) {
        //Properties
        if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"])) {
            foreach ($_POST["PROPERTY"] as $arProperty) {
                $arProperty["CODE"] = (isset($arProperty["CODE"]) ? trim($arProperty["CODE"]) : "");
                $arProperty["VALUE"] = (isset($arProperty["VALUE"]) ? trim($arProperty["VALUE"]) : "");

                if (preg_match("/[a-zA-Z_-~]+/i", $arProperty["CODE"])) {
                    $fileContent = SetPrologProperty($fileContent, $arProperty["CODE"], $arProperty["VALUE"]);
                }
            }
        }
        $success = $APPLICATION->SaveFileContent($absoluteFilePath, $fileContent);

        if ($success === false && ($exception = $APPLICATION->GetException()))
            $strWarning = $exception->msg;
    }

    LocalRedirect("/" . ltrim($original_backurl, "/"));
    die();
}

$arFilemanProperties = Array(
    'og:title' => GetMessage("POWERNIC_SEO_PROPS_OG_TITLE"),
    'og:description' => GetMessage("POWERNIC_SEO_PROPS_OG_DESCRIPTION"),
    'og:url' => GetMessage("POWERNIC_SEO_PROPS_OG_URL"),
    'og:image' => GetMessage("POWERNIC_SEO_PROPS_OG_IMAGE"),
    'og:image:width' => GetMessage("POWERNIC_SEO_PROPS_OG_IMAGE_WIDTH"),
    'og:image:height' => GetMessage("POWERNIC_SEO_PROPS_OG_IMAGE_HEIGHT"),
    'twitter:description' => GetMessage("POWERNIC_SEO_PROPS_TC_DESCRIPTION"),
    'twitter:title' => GetMessage("POWERNIC_SEO_PROPS_TC_TITLE"),
    'twitter:image' => GetMessage("POWERNIC_SEO_PROPS_TC_IMAGE"),
);
//Properties from page
$arPageSlice = ParseFileContent($fileContent);
$arDirProperties = $arPageSlice["PROPERTIES"];
$pageTitle = $arPageSlice["TITLE"];

//All properties for file. Includes properties from root folders
$arInheritProperties = $APPLICATION->GetDirPropertyList(array($site, $path));
if ($arInheritProperties === false)
    $arInheritProperties = array();

//Delete equal properties
$arGlobalProperties = array();

if(is_array($arFilemanProperties))
{
    foreach ($arFilemanProperties as $propertyCode => $propertyDesc)
    {
        if (array_key_exists($propertyCode, $arDirProperties))
            $arGlobalProperties[$propertyCode] = $arDirProperties[$propertyCode];
        else
            $arGlobalProperties[$propertyCode] = "";

        unset($arDirProperties[$propertyCode]);
        unset($arInheritProperties[strtoupper($propertyCode)]);
    }
}
foreach ($arDirProperties as $propertyCode => $propertyValue)
{
    unset($arInheritProperties[strtoupper($propertyCode)]);
}

//back url processing
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");
$original_backurl = $back_url;

$back_url = CSeoUtils::CleanURL($back_url);
//HTML output
$aTabs = array(
    array("DIV" => "seo_edit1", "TAB" => 'OpenGraph', "ICON" => "main_settings", "TITLE" => 'OpenGraph'),
    array("DIV" => "seo_edit2", "TAB" => 'TwitterCard', "ICON" => "main_settings", "TITLE" => 'TwitterCard'),
);
$tabControl = new CAdminTabControl("seoTabControl", $aTabs, true, true);

$APPLICATION->SetTitle(GetMessage('POWERNIC_SEO_TOOLS_TITLE'));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
//Properties from fileman settings
?>
    <form name="seo_form" method="POST" action="/bitrix/admin/public_powernic_seo_tools.php" enctype="multipart/form-data">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>"/>
        <input type="hidden" name="site" value="<?= $site ?>"/>
        <input type="hidden" name="path" value="<? echo htmlspecialcharsEx($path) ?>"/>
        <input type="hidden" name="back_url" value="<? echo htmlspecialcharsEx($original_backurl) ?>"/>
        <?= bitrix_sessid_post() ?>
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <table>
            <?
            $arEditProperties = array();
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:title')) $arEditProperties['og:title'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:description')) $arEditProperties['og:description'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:url')) $arEditProperties['og:url'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:image')) $arEditProperties['og:image'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:image:width')) $arEditProperties['og:image:width'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'og:image:height')) $arEditProperties['og:image:height'] = HtmlFilter::encode($prop_code);
            foreach ($arEditProperties as $key => $prop_code):
                $value = $arGlobalProperties[$prop_code];
                ?>
                <tr>
                    <td><?echo $arFilemanProperties[$prop_code]?></td>
                    <td><input type="hidden" name="PROPERTY[<?= $prop_code ?>][CODE]"
                               value="<?= htmlspecialcharsEx($prop_code) ?>"/>
                        <?
                        if (strlen($value) <= 0):
                            $value = $APPLICATION->GetDirProperty($prop_code, array($site, $path));
                            ?>
                            <div id="bx_view_property_<?= $prop_code ?>"
                                 style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid #F8F9FC; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat; height: 22px;"
                                 onclick="BXEditProperty('<?= $prop_code ?>')"
                                 onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50';"
                                 onmouseout="this.style.borderColor = '#F8F9FC'"
                                 class="edit-field"><?= htmlspecialcharsEx($value) ?></div>

                            <div id="bx_edit_property_<?= $prop_code ?>" style="display:none;"></div>
                        <?
                        else:
                        ?>
                        <input type="text" name="PROPERTY[<?= $prop_code ?>][VALUE]"
                               value="<?= htmlspecialcharsEx($value) ?>" size="50"/></td>
                    <?
                    endif;
                    ?>
                </tr>
            <?
            endforeach;
            ?>
        </table>
        <?php
        $tabControl->BeginNextTab();
        ?>
        <table>
            <?
            $arEditProperties = array();
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'twitter:description')) $arEditProperties['twitter:description'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'twitter:title')) $arEditProperties['twitter:title'] = HtmlFilter::encode($prop_code);
            if ($prop_code = COption::GetOptionString('powernic.seo', 'property_og_title', 'twitter:image')) $arEditProperties['twitter:image'] = HtmlFilter::encode($prop_code);
            foreach ($arEditProperties as $key => $prop_code):
                $value = $arGlobalProperties[$prop_code];
                ?>
                <tr>
                    <td><?echo $arFilemanProperties[$prop_code]?></td>
                    <td><input type="hidden" name="PROPERTY[<?= $prop_code ?>][CODE]"
                               value="<?= htmlspecialcharsEx($prop_code) ?>"/>
                        <?
                        if (strlen($value) <= 0):
                            $value = $APPLICATION->GetDirProperty($prop_code, array($site, $path));
                            ?>
                            <div id="bx_view_property_<?= $prop_code ?>"
                                 style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid #F8F9FC; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat; height: 22px;"
                                 onclick="BXEditProperty('<?= $prop_code ?>')"
                                 onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50';"
                                 onmouseout="this.style.borderColor = '#F8F9FC'"
                                 class="edit-field"><?= htmlspecialcharsEx($value) ?></div>

                            <div id="bx_edit_property_<?= $prop_code ?>" style="display:none;"></div>
                        <?
                        else:
                        ?>
                        <input type="text" name="PROPERTY[<?= $prop_code ?>][VALUE]"
                               value="<?= htmlspecialcharsEx($value) ?>" size="50"/></td>
                    <?
                    endif;
                    ?>
                </tr>
            <?
            endforeach;
            ?>
        </table>
        <?

        $tabControl->Buttons(array("disabled" => $bReadOnly));
        $tabControl->End();
        ?>
    </form>
    <script>

        window.BXBlurProperty = function (element, propertyIndex) {
            var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

            if (element.value == "" || element.value == viewProperty.innerHTML) {
                var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);

                viewProperty.style.display = "block";
                editProperty.style.display = "none";

                while (editProperty.firstChild)
                    editProperty.removeChild(editProperty.firstChild);
            }
        }

        window.BXEditProperty = function (propertyIndex) {
            if (document.getElementById("bx_property_input_" + propertyIndex))
                return;

            var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);
            var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

            viewProperty.style.display = "none";
            editProperty.style.display = "block";

            var input = document.createElement("INPUT");

            input.type = "text";
            input.name = "PROPERTY[" + propertyIndex + "][VALUE]";

            input.style.width = "90%";
            input.style.padding = "2px";
            input.id = "bx_property_input_" + propertyIndex;
            input.onblur = function () {
                BXBlurProperty(input, propertyIndex)
            };
            input.value = viewProperty.innerHTML;

            editProperty.appendChild(input);
            input.focus();
            input.select();

            return input;
        }
    </script>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>