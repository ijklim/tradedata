<?php

namespace Tests\Unit;

use Tests\TestCase;

class DataSourceTest extends TestCase
{
    use \Tests\Traits\Test;

    static function setUpBeforeClass()
    {
        // Declared in trait
        self::init(['domain_name', 'api_base_url']);
        
        // First: Unique values
        self::$datasets['first'] = [
            reset(self::$fieldNames) => 'exampl.com',
            next(self::$fieldNames) => 'http://exampl.com/api'
        ];
        // Second: Unique values
        self::$datasets['second'] = [
            reset(self::$fieldNames) => 'portal.com',
            next(self::$fieldNames) => 'http://portalapi.net'
        ];
        // For duplicate check, convert primary key to uppercase to test additional condition
        self::$datasets['duplicate'] = [
            reset(self::$fieldNames) => strtoupper(reset(self::$datasets['first'])),
            next(self::$fieldNames) => end(self::$datasets['second'])
        ];
        // Modify first row
        self::$datasets['edit'] = [
            reset(self::$fieldNames) => reset(self::$datasets['first']),
            next(self::$fieldNames) => next(self::$datasets['first']) . '/get'
        ];
        self::$routes = [
            '/' . self::$folderName
        ];
    }

    /**
     * Test DataSource related functions.
     *
     * @return void
     */
    public function testDataSource()
    {
        // Testing .show and .index, data should not exist
        foreach (self::$routes as $route) {
            $this->get($route)
                ->assertDontSee(self::$datasets['first'][reset(self::$fieldNames)])
                ->assertDontSee(self::$datasets['first'][next(self::$fieldNames)]);
        }

        // Testing .store, adding data to table, success will redirect to .index page
        $this->json('POST', '/' . self::$folderName, self::$datasets['first'])
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);

        // Check table
        $this->assertDatabaseHas(self::$tableName, self::$datasets['first']);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['second']);

        // Testing .show displays added data
        foreach (self::$routes as $route) {
            $this->get($route)
                ->assertSee(self::$datasets['first'][reset(self::$fieldNames)])
                ->assertSee(self::$datasets['first'][next(self::$fieldNames)]);
        }

        // Ensure duplicates cannot be entered, fails validation
        $this->json('POST', '/' . self::$folderName, self::$datasets['duplicate'])
            ->assertStatus(422);

        // Ensure second set of data is not yet in the system
        $this->get(reset(self::$routes))
            ->assertDontSee(self::$datasets['second'][reset(self::$fieldNames)])
            ->assertDontSee(self::$datasets['second'][next(self::$fieldNames)]);

        // Add second set of data, ensure success
        $this->json('POST', '/' . self::$folderName, self::$datasets['second'])
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        $this->assertDatabaseHas(self::$tableName, self::$datasets['second']);

        // Ensure second set of data is visible
        $this->get(reset(self::$routes))
            ->assertSee(self::$datasets['second'][reset(self::$fieldNames)])
            ->assertSee(self::$datasets['second'][next(self::$fieldNames)]);
    
        // Test .edit page is available, check field contains default value
        // Note: This has to run before key violation edit test, otherwise the duplicate key will show with error message, result expected
        $this->get('/' . self::$folderName . '/2/edit')
            ->assertStatus(200)
            ->assertDontSee(self::$datasets['first'][reset(self::$fieldNames)])
            ->assertDontSee(self::$datasets['first'][end(self::$fieldNames)])
            ->assertSee(self::$datasets['second'][reset(self::$fieldNames)])
            ->assertSee(self::$datasets['second'][next(self::$fieldNames)]);

        // Test edit prevent key violation
        $this->json('PUT', '/' . self::$folderName . '/2', self::$datasets['duplicate'])
            ->assertStatus(422);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['duplicate']);
        
        // Test edit can be done when valid, testing .update
        $this->json('PUT', '/' . self::$folderName . '/1', self::$datasets['edit'])
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        // New data available, old data disappear
        $this->assertDatabaseHas(self::$tableName, self::$datasets['edit']);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['first']);

        // Edited text is found on index page
        foreach (self::$routes as $route) {
            $this->get($route)
                ->assertSee(self::$datasets['edit'][reset(self::$fieldNames)])
                ->assertSee(self::$datasets['edit'][next(self::$fieldNames)]);
        }
        
        // Testing .destroy
        $this->assertDatabaseHas(self::$tableName, self::$datasets['second']);
        $this->json('DELETE', '/' . self::$folderName . '/2')
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        // Data check
        $this->assertDatabaseHas(self::$tableName, self::$datasets['edit']);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['second']);
    }
}
