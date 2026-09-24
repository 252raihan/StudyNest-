<?php

namespace Tests\Feature;

use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_homepage_returns_successful_response_and_shows_studynest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('StudyNest');
    }

    public function test_about_page_returns_successful_response_and_shows_studynest(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('StudyNest');
    }

    public function test_health_endpoint_returns_running_message(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertSee('StudyNest is running.');
    }
}
