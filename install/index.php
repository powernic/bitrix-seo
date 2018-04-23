<?
IncludeModuleLangFile(__FILE__);

if(class_exists("powernic_seo")) return;
Class powernic_seo extends CModule
{
    var $MODULE_ID = "powernic.seo";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function powernic_seo()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "СЕО";
        $this->MODULE_DESCRIPTION = "Инструменты для помощи в СЕО";
        $this->PARTNER_NAME = "Ефименко Николай";
        $this->PARTNER_URI = "https://github.com/powernic";
    }

    function InstallFiles()
    {
        if($_ENV["COMPUTERNAME"]!='BX')
        {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/powernic.seo/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        }
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/powernic.seo/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }
    function InstallDB()
    {
        global $DB, $APPLICATION;

        $this->errors = false;

        RegisterModule("powernic.seo");

        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->registerEventHandler('main', 'OnPanelCreate', $this->MODULE_ID, 'CPowernicSeoEventHandlers', 'SeoOnPanelCreate');
        $eventManager->registerEventHandler('main', 'OnEpilog', $this->MODULE_ID, 'CPowernicSeoEventHandlers', 'SeoOnEpilog');

        if (!$DB->Query("SELECT COUNT(*) FROM b_powernic_seo", true)):
            $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/powernic.seo/install/db/".strtolower($DB->type)."/install.sql");
        endif;

        if ($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }
        return true;
    }
    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $APPLICATION->IncludeAdminFile("Установка модуля powernic_seo", $DOCUMENT_ROOT."/bitrix/modules/powernic_seo/install/step.php");
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        UnRegisterModule("powernic.seo");
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля powernic_seo", $DOCUMENT_ROOT."/bitrix/modules/powernic_seo/install/unstep.php");
    }
}
?>