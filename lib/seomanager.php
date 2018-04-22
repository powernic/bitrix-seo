<?php
/**
 * Created by PhpStorm.
 * User: Powernic
 * Date: 4/20/2018
 * Time: 1:25 PM
 */
namespace Bitrix\Seo;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\LanguageTable;

Loc::loadMessages(__FILE__);
class SeoManager
{
    const CACHE_SEO_SHORT_LIST_ID = 'seo_short_list_';
    /**
     * Return SEO params list.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getParamsList()
    {
        $seoTableName = SeoTable::getTableName();
        $managedCache = Application::getInstance()->getManagedCache();

        $cacheTime = (int)(defined('SEO_CACHE_TIME') ? SEO_CACHE_TIME : SEO_CACHE_DEFAULT_TIME);
        $cacheId = self::CACHE_SEO_SHORT_LIST_ID.LANGUAGE_ID;

        if ($managedCache->read($cacheTime, $cacheId, $seoTableName))
        {
            $paramsList = $managedCache->get($cacheId);
        }
        else
        {
            $paramsList = array();
            $seoIterator = SeoTable::getList(array('select' => array('*')));
            while ($param = $seoIterator->fetch())
            {
                $paramsList[$param['KEY']] = $param['VALUE'];
            }
            unset($currency, $currencyIterator);
            $managedCache->set($cacheId, $paramsList);
        }
        return $paramsList;
    }
    /**
     * Update current params.
     *
     * @param string $updateParams	 Update currency id.
     * @return void
     * @throws Main\ArgumentException
     * @throws \Exception
     */
    public static function updateParam($param = '', $value = '')
    {
        $updateResult = SeoTable::update($param, array('VALUE' => $value));
    }

}