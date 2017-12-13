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
    static $uniqueKey;
    static $editKey;
    static $routes; // Should be removed after refactoring data-source

    /**
     * Initialize commonly used static variables.
     *
     * @param  array  $fieldNames
     * @return void
     */
    public static function init($fieldNames = []) {
        self::$folderName = self::getFolderName();
        self::$tableName = self::getTableName();
        self::$fieldNames = $fieldNames;
    }

    /**
     * Perform test related to adding new data to table.
     *
     * @return void
     */
    private function runNewDataTests() {
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
            $this->get('/' . self::$folderName . '/' . $uniqueKeyValue . '/edit')
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
            $datasetTableCheck = array_merge($dataset, [self::$uniqueKey => self::uniqueKeyTransform($dataset[self::$uniqueKey])]);
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