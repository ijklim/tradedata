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
        $routes = [
            '/',
            'data-source',
            'stock',
            'stock-price',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200);
        }
    }
}
