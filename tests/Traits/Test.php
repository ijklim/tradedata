<?php
/* Note: Trait is used rather than abstract class due to __CLASS__ being bound to abstract class */
namespace Tests\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait Test
{
    use RefreshDatabase;
    use \App\Traits\Model;

    static $className;
    static $folderName;
    static $tableName;
    static $datasets;
    static $fieldNames;
    static $primaryKey;
    static $uniqueKey;
    static $editKeyValue;

    /**
     * Initialize commonly used static variables.
     *
     * @param  array  $fieldNames
     * @return void
     */
    public static function init($fieldNames = []) {
        self::$className = '\App\\' . self::getBaseClassName();
        self::$folderName = self::getFolderName();
        self::$tableName = self::getTableName();
        self::$fieldNames = $fieldNames;
    }

    /**
     * Create array with field name and value.
     *
     * @param  primary  $parameter1, $parameter2, $parameter3...
     * @return array
     */
    public static function constructDataset(...$parameters) {
        $dataset = [];
        foreach ($parameters as $index => $parameter) {
            $dataset[self::$fieldNames[$index]] = $parameter;
        }
        return $dataset;
    }

    /**
     * Create array with testing routes.
     *
     * @param  string  $primaryKeyValue
     * @return array
     */
    static function getRoutes($primaryKeyValue) {
        return [
            '/' . self::$folderName,
            '/' . self::$folderName . '/' . $primaryKeyValue
        ];
    }
    
    /**
     * Perform test related to adding new data to table.
     *
     * @return void
     */
    private function runNewDataTests() {
        foreach (self::$datasets['valid'] as $index => $dataset) {
            $primaryKeyValue = (self::$primaryKey == 'id' ? $index + 1 : $dataset[self::$primaryKey]);
            foreach (self::getRoutes($primaryKeyValue) as $route) {
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

            foreach (self::getRoutes($primaryKeyValue) as $route) {
                // Testing .show displays added data, first 2 fields
                $this->get($route)
                    ->assertSee($dataset[reset(self::$fieldNames)])
                    ->assertSee($dataset[next(self::$fieldNames)]);
            }

            // Test .edit page is available, check field contains default value
            $this->get('/' . self::$folderName . '/' . $primaryKeyValue . '/edit')
                ->assertStatus(200)
                ->assertSee('value="' . $dataset[reset(self::$fieldNames)] . '"')
                ->assertSee('value="' . $dataset[next(self::$fieldNames)] . '"');
        }

        // Ensure duplicates and data that violates certain validation rule cannot be entered, fails validation
        // $datasets = array_merge($validDatasets, $invalidDatasets);
        $datasets = array_collapse(array_only(self::$datasets, ['valid', 'invalid']));
        foreach ($datasets as $dataset) {
            $this->json('POST', '/' . self::$folderName, $dataset)
                ->assertStatus(422);
        }
    }

    /**
     * Perform test related to update data in table.
     *
     * @return void
     */
    private function runUpdateDataTests() {
        // Invalid update checks
        $route = '/' . self::$folderName . '/' . self::$editKeyValue;
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
            $uniqueKeySetting = [self::$uniqueKey => self::$className::formatField(self::$uniqueKey, $dataset[self::$uniqueKey])];
            $datasetTableCheck = array_merge($dataset, $uniqueKeySetting);
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

            if (self::$primaryKey != 'id') {
                // Construct new route in case unique key value has changed
                // Only necessary if primary key can be modified
                $route = '/' . self::$folderName . '/' . $datasetTableCheck[self::$uniqueKey];
            }
        }
    }

    /**
     * Perform test related to deleting data in table.
     *
     * @return void
     */
    private function runDeleteDataTests() {
        // All rows should be in the table
        foreach (array_pluck(self::$datasets['valid'], self::$uniqueKey) as $data) {
            $this->assertDatabaseHas(self::$tableName, [self::$uniqueKey => $data]);
        }
        // Testing .destroy
        $this->json('DELETE', '/' . self::$folderName . '/' . self::$editKeyValue)
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        // Check table again
        foreach (array_pluck(self::$datasets['valid'], self::$uniqueKey) as $index => $data) {
            if (
                $data == self::$editKeyValue || 
                (self::$primaryKey == 'id' && $index == self::$editKeyValue - 1)
            ) {
                // Deleted row should no longer be in the table
                $this->assertDatabaseMissing(self::$tableName, [self::$uniqueKey => $data]);
            } else {
                // The other rows should remain
                $this->assertDatabaseHas(self::$tableName, [self::$uniqueKey => $data]);
            }
        }
    }
}