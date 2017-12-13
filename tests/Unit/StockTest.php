<?php

namespace Tests\Unit;

use Tests\TestCase;

class StockTest extends TestCase
{
    use \Tests\Traits\Test;

    /**
     * Create array with field name and value.
     *
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
     * Test Stock related functions.
     *
     * @return void
     */
    public function testStock()
    {
        foreach (self::$datasets['valid'] as $dataset) {
            $uniqueKeyValue = $dataset[self::$uniqueKey];
            foreach (self::getRoutes($uniqueKeyValue) as $route) {
                // Testing .show and .index, data should not exist
                $this->get($route)
                    ->assertDontSee($dataset[reset(self::$fieldNames)])
                    ->assertDontSee($dataset[next(self::$fieldNames)]);
            }

            // Check dataset should NOT be in table
            $this->assertDatabaseMissing(self::$tableName, $dataset);

            // Testing .store, adding data to table, success will redirect to .index page
            $this->json('POST', '/' . self::$folderName, $dataset)
                ->assertSessionHas('success')
                ->assertRedirect(self::$folderName);
            
            // Dataset should now be in the table
            $this->assertDatabaseHas(self::$tableName, $dataset);

            foreach (self::getRoutes($uniqueKeyValue) as $route) {
                // Testing .show displays added data, first 2 fields
                $this->get($route)
                    ->assertSee($dataset[reset(self::$fieldNames)])
                    ->assertSee($dataset[next(self::$fieldNames)]);
            }

            // Test .edit page is available, check field contains default value
            // Note: This has to run before key violation edit test, otherwise the duplicate key will show with error message, result expected
            $this->get('/' . self::$folderName . '/' . $uniqueKeyValue . '/edit')
                ->assertStatus(200)
                ->assertSee('value="' . $dataset[reset(self::$fieldNames)] . '"')
                ->assertSee('value="' . $dataset[next(self::$fieldNames)] . '"');
        }

        // Ensure duplicates cannot be entered, fails validation
        foreach (self::$datasets['valid'] as $dataset) {
            $this->json('POST', '/' . self::$folderName, $dataset)
                ->assertStatus(422);
        }

        // Data violates certain validation rule, should return error
        foreach (self::$datasets['invalid'] as $dataset) {
            $this->json('POST', '/' . self::$folderName, $dataset)
                ->assertStatus(422);
        }
        
        // Invalid update checks
        $route = '/' . self::$folderName . '/' . self::$editKey;
        // datasets from both 'invalid' and 'invalid-edit'
        $datasets = array_collapse(array_only(self::$datasets, ['invalid', 'invalid-edit']));
        foreach ($datasets as $dataset) {
            $this->json('PUT', $route, $dataset)
                ->assertStatus(422);
        }

        // Valid edit test, note route might change after update due to unique key update
        // Running this right after invalid update check the previous $route should still be valid
        foreach (self::$datasets['valid-edit'] as $dataset) {
            // This is required for SQLite as key is case sensitive
            $datasetTableCheck = array_merge($dataset, [self::$uniqueKey => strtoupper($dataset[self::$uniqueKey])]);
            // New data should NOT be in the table
            $this->assertDatabaseMissing(self::$tableName, $datasetTableCheck);

            $this->json('PUT', $route, $dataset)
                ->assertSessionHas('success')
                ->assertRedirect(self::$folderName);
        
            // New data should now be in the table
            $this->assertDatabaseHas(self::$tableName,  $datasetTableCheck);
            
            // Data should appear on index page
            $this->get('/' . self::$folderName)
                ->assertSee($datasetTableCheck[reset(self::$fieldNames)])
                ->assertSee($datasetTableCheck[next(self::$fieldNames)]);

            // Construct new route in case unique key value has changed
            $route = '/' . self::$folderName . '/' . $datasetTableCheck[self::$uniqueKey];
        }

        // All rows should be in the table
        foreach (array_pluck(self::$datasets['valid'], self::$uniqueKey) as $data) {
            $this->assertDatabaseHas(self::$tableName, [self::$uniqueKey => $data]);
        }
        // Testing .destroy
        $this->json('DELETE', '/' . self::$folderName . '/' . self::$editKey)
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        // Check table again
        foreach (array_pluck(self::$datasets['valid'], self::$uniqueKey) as $data) {
            if ($data == self::$editKey) {
                // Deleted row should no longer be in the table
                $this->assertDatabaseMissing(self::$tableName, [self::$uniqueKey => $data]);
            } else {
                // The other rows should remain
                $this->assertDatabaseHas(self::$tableName, [self::$uniqueKey => $data]);
            }
        }
    }
}
