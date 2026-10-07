<?php
namespace Spring\Import\Model;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;

class TaskTable extends DataManager{
    public static function getTableName(){
        return 'spring_import_tasks';
    }
    public static function getMap(){
        $map = array(
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new StringField('FILE_PATH', ['required' => true]),
            new StringField('STATUS', ['default_value' => 'NEW']),
            new IntegerField('CAT_ID', ['required' => true]),
            new TextField('POST_PARAMS', ['required' => true])
        );
        return $map;
    }
}