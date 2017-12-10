<?php
/* Note: Trait is used rather than abstract class due to __CLASS__ being bound to abstract class */
namespace Tests\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait Test
{
    use RefreshDatabase;
    use \App\Traits\Model;

    static $folderName;
    static $tableName;
    static $datasets;
    static $fieldNames;
    static $routes;

    public static function init($fieldNames = []) {
        self::$folderName = self::getFolderName();
        self::$tableName = self::getTableName();
        self::$fieldNames = $fieldNames;
    }
}