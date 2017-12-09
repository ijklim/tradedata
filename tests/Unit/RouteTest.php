<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all available routes.
     *
     * @return void
     */
    public function testRoutes()
    {
        // Find available routes: `php artisan route:list`
        $routes = [
            '/',
            'data-source', 'data-source/create',
            'stock', 'stock/create',
            'stock-price', 'stock-price/create',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200);
        }
    }
}
