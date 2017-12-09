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
        // Split base on / or \
        $classParts = preg_split("_[\\\\/]_", __CLASS__);
        // Split by camel case
        $folderNameParts = preg_split('/(?<=[a-z])(?=[A-Z])/x', end($classParts));
        // Remove 'test' if it is the last element
        // Classes other than test classes in `tests` folder should not end in the word 'Test'
        if (end($folderNameParts) == 'Test') {
            array_pop($folderNameParts);
        }
        // Convert to lower case and join with -
        return strtolower(join($folderNameParts, '-'));
    }
}