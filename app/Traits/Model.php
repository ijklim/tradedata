<?php
/* Note: Trait is used rather than abstract class due to __CLASS__ being bound to abstract class */
namespace App\Traits;

trait Model
{
    /**
     * Return value of primary key.
     *
     * @return (type of primary key)
     */
    public function getKeyValue() {
        $primaryKey = $this->getKeyName();
        return $this->$primaryKey;
    }   

    /**
     * Return folder name of where the views are stored.
     * e.g. Class `MyClass` should be stored in `my-class`
     *
     * @return string
     */
    public static function getFolderName() {
        // Remove '-test' if it is the last part of the folder name
        return preg_replace('/-test$/', '', kebab_case(class_basename(__CLASS__)));
    }

    public static function getTableName() {
        return str_replace("-", '_', self::getFolderName()) . 's';
    }
}