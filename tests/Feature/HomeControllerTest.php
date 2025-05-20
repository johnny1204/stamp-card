<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_correctly(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Home')
        );
    }

    public function test_home_page_returns_inertia_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    }
}
