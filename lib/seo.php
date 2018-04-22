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
        return 'b_powernic_seo';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'KEY' => new Main\Entity\StringField('KEY',array(
                'primary' => true)),
            'VALUE' => new Main\Entity\StringField('VALUE'),
        );
    }
}