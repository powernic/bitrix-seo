<?
use Bitrix\Main\Loader;

global $DB, $APPLICATION, $MESS, $DBType;

IncludeModuleLangFile(__FILE__);

Loader::registerAutoLoadClasses(
    'seo',
    array(
        '\Bitrix\Seo\SeoManager' => 'lib/seomanager.php',
        '\Bitrix\Seo\SeoTable' => 'lib/seo.php',
    )
);

define('SEO_CACHE_DEFAULT_TIME', 10800);

class CPowernicSeoEventHandlers
{
    function SeoOnPanelCreate()
    {
        global $APPLICATION, $USER;

        if (!$USER->CanDoOperation('powernic_seo_tools'))
            return false;

        if (isset($_SERVER["REAL_FILE_PATH"]) && $_SERVER["REAL_FILE_PATH"] != "")
        {
            $currentDirPath = dirname($_SERVER["REAL_FILE_PATH"]);
            $currentFilePath = $_SERVER["REAL_FILE_PATH"];
        }
        else
        {
            $currentDirPath = $APPLICATION->GetCurDir();
            $currentFilePath = $APPLICATION->GetCurPage(true);
        }

        $encCurrentDirPath = urlencode($currentDirPath);
        $encCurrentFilePath = urlencode($currentFilePath);
        $encRequestUri = urlencode($_SERVER["REQUEST_URI"]);

        $encTitleChangerLink = '';
        $encWinTitleChangerLink = '';
        $encTitleChangerName = '';
        $encWinTitleChangerName = '';
        if (is_array($APPLICATION->sDocTitleChanger))
        {
            if (isset($APPLICATION->sDocTitleChanger['PUBLIC_EDIT_LINK']))
                $encTitleChangerLink = urlencode(base64_encode($APPLICATION->sDocTitleChanger['PUBLIC_EDIT_LINK']));
            if (isset($APPLICATION->sDocTitleChanger['COMPONENT_NAME']))
                $encTitleChangerName = urlencode($APPLICATION->sDocTitleChanger['COMPONENT_NAME']);
        }

        $prop_code = ToUpper(COption::GetOptionString('seo', 'property_window_title', 'title'));

        if (is_array($APPLICATION->arPagePropertiesChanger[$prop_code]))
        {
            if (isset($APPLICATION->arPagePropertiesChanger[$prop_code]['PUBLIC_EDIT_LINK']))
                $encWinTitleChangerLink = urlencode(base64_encode($APPLICATION->arPagePropertiesChanger[$prop_code]['PUBLIC_EDIT_LINK']));
            if (isset($APPLICATION->arPagePropertiesChanger[$prop_code]['COMPONENT_NAME']))
                $encWinTitleChangerName = urlencode($APPLICATION->arPagePropertiesChanger[$prop_code]['COMPONENT_NAME']);
        }

        $encTitle = urlencode(base64_encode($APPLICATION->sDocTitle));
        $encWinTitle = urlencode(base64_encode($APPLICATION->arPageProperties[$prop_code]));

        $APPLICATION->AddPanelButton(array(
            "HREF"=> 'javascript:'.$APPLICATION->GetPopupLink(
                    array(
                        "URL"=>"/bitrix/admin/public_powernic_seo_tools.php?lang=".LANGUAGE_ID."&bxpublic=Y&from_module=powernic.seo&site=".SITE_ID
                            ."&path=".$encCurrentFilePath
                            ."&title_final=".$encTitle."&title_changer_name=".$encTitleChangerName.'&title_changer_link='.$encTitleChangerLink
                            ."&title_win_final=".$encWinTitle."&title_win_changer_name=".$encWinTitleChangerName.'&title_win_changer_link='.$encWinTitleChangerLink
                            ."&".bitrix_sessid_get()
                            ."&back_url=".$encRequestUri,
                        "PARAMS"=> Array("width"=>920, "height" => 400, 'resize' => false)
                    )),
            "ID"=>"powernic-seo",
            "ICON" => "bx-panel-seo-icon",
            "ALT"=>GetMessage('POWERNIC_SEO_ICON_ALT'),
            "TEXT"=>GetMessage('POWERNIC_SEO_ICON_TEXT'),
            "MAIN_SORT"=>"300",
            "SORT"=> 50,
            "HINT" => array(
                "TITLE" => GetMessage('POWERNIC_SEO_ICON_TEXT'),
                "TEXT" => GetMessage('POWERNIC_SEO_ICON_HINT')
            ),
        ));
    }
}
?>