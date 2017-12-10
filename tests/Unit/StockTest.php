<?php

namespace Tests\Unit;

use Tests\TestCase;

class StockTest extends TestCase
{
    use \Tests\Traits\Test;

    static function setUpBeforeClass()
    {
        // Declared in trait
        self::init(['symbol', 'name']);

        // First: Unique values
        self::$datasets['first'] = [
            reset(self::$fieldNames) => 'GOOGL',
            next(self::$fieldNames) => 'The Alphabet Company'
        ];
        // Second: Unique values
        self::$datasets['second'] = [
            reset(self::$fieldNames) => 'AAPL',
            next(self::$fieldNames) => 'Apple company in the cloud'
        ];
        // For duplicate check, convert primary key to lowercase to test additional condition
        self::$datasets['duplicate'] = [
            reset(self::$fieldNames) => strtolower(reset(self::$datasets['first'])),
            next(self::$fieldNames) => end(self::$datasets['second'])
        ];
        self::$datasets['edit'] = [
            reset(self::$fieldNames) => reset(self::$datasets['first']),
            next(self::$fieldNames) => next(self::$datasets['first']) . ' (2018)'
        ];
        self::$routes = [
            '/' . self::$folderName,
            '/' . self::$folderName . '/' . self::$datasets['first'][reset(self::$fieldNames)]
        ];
    }

    /**
     * Test Stock related functions.
     *
     * @return void
     */
    public function testStock()
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
        $this->get('/' . self::$folderName . '/' . self::$datasets['second'][reset(self::$fieldNames)] . '/edit')
            ->assertStatus(200)
            ->assertDontSee(self::$datasets['first'][reset(self::$fieldNames)])
            ->assertDontSee(self::$datasets['first'][end(self::$fieldNames)])
            ->assertSee(self::$datasets['second'][reset(self::$fieldNames)])
            ->assertSee(self::$datasets['second'][next(self::$fieldNames)]);

        // Test edit prevent key violation, including primary key in different case
        $this->json('PUT', '/' . self::$folderName . '/' . self::$datasets['second'][reset(self::$fieldNames)], self::$datasets['duplicate'])
            ->assertStatus(422);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['duplicate']);
        
        // Test edit can be done when valid, testing .update
        $this->json('PUT', '/' . self::$folderName . '/' . self::$datasets['edit'][reset(self::$fieldNames)], self::$datasets['edit'])
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
        $this->json('DELETE', '/' . self::$folderName . '/' . self::$datasets['second'][reset(self::$fieldNames)])
            ->assertSessionHas('success')
            ->assertRedirect(self::$folderName);
        // Data check
        $this->assertDatabaseHas(self::$tableName, self::$datasets['edit']);
        $this->assertDatabaseMissing(self::$tableName, self::$datasets['second']);
    }
}
