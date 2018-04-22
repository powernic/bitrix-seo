<?php

namespace Bitrix\Seo;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SeoTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_seo';
    }
}