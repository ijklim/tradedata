<?php

namespace Tests\Unit;

use Tests\TestCase;

class StockTest extends TestCase
{
    use \Tests\Traits\Test;

    /**
     * Create array with field name and value.
     *
     * @param  primary  $parameter1, $parameter2, $parameter3...
     * @return array
     */
    static function constructDataset(...$parameters) {
        $dataset = [];
        foreach ($parameters as $index => $parameter) {
            $dataset[self::$fieldNames[$index]] = $parameter;
        }
        return $dataset;
    }

    /**
     * Create array with valid and invalid datasets for testing.
     *
     * @return void
     */
    static function createDatasets() {
        // Valid data
        self::$datasets['valid'] = [
            self::constructDataset('GOOGL', 'The Alphabet Company', 1),
            self::constructDataset(self::$editKey, 'Apple company in the cloud', 1),
            self::constructDataset('TSLA', 'Tesla Inc.', 1),
        ];

        // Invalid data
        self::$datasets['invalid'] = [
            self::constructDataset('', 'The blank company', 1),     // missing symbol
            self::constructDataset('AAA', '', 1),                   // missing name
            self::constructDataset('AAA', 'The', 1),                // name too short
            self::constructDataset('BBB', 'ABCDEFG HIJK', -1),      // data_source_id < 0
            self::constructDataset('CCCCCC', 'Tesla Inc.'),         // symbol too long
            self::constructDataset('tsla', 'App in the cloud', 1),  // lowercase should still violate key check, must be different from $editKey
        ];


        // Valid data for edit test, search for index of row which contains $editKey
        // self::$editRowIndex = array_search(self::$editKey, array_pluck(self::$datasets['valid'], self::$uniqueKey));
        self::$datasets['valid-edit'] = [
            self::constructDataset('aapl', 'The new apple company', 1),
            self::constructDataset('aapL', 'Apple with no data source'),
            self::constructDataset('aap', 'Apple with new symbol', 1),
            self::constructDataset('aApL', 'This will be deleted'),         // Change key back to original to support subsequent .destroy testing
        ];

        // Invalid data for edit only, updating the 2nd row
        self::$datasets['invalid-edit'] = [
            // Key violation: using 1st row unique key, check case insensitivity as well
            self::constructDataset(
                strtolower(self::$datasets['valid'][0][self::$fieldNames[0]]),
                'Apple a day',
                1
            ),
        ];
    }

    /**
     * Create array with testing routes.
     *
     * @param  string  $uniqueKeyValue
     * @return array
     */
    static function getRoutes($uniqueKeyValue) {
        return [
            '/' . self::$folderName,
            '/' . self::$folderName . '/' . $uniqueKeyValue
        ];
    }

    /**
     * Pretest initialization.
     *
     * @return void
     */
    static function setUpBeforeClass()
    {
        // Declared in trait
        self::init(['symbol', 'name', 'data_source_id']);
        self::$uniqueKey = 'symbol';
        // For convenience the editKey will be used for deletion test as well, deletion must be the last test
        self::$editKey = 'AAPL';

        self::createDatasets();
    }

    /**
     * Special transformation required for the unique key.
     *
     * @param  string  $value
     * @return void
     */
    static function uniqueKeyTransform($value) {
        return strtoupper($value);
    }

    /**
     * Test Stock related functions.
     *
     * @return void
     */
    public function testStock()
    {
        $this->runNewDataTests();
        $this->runUpdateDataTests();
        $this->runDeleteDataTests();
    }
}
