<?php

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\FeatureTest;

class HomeControllerTest extends FeatureTest
{
    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(__('Start free trial'), false);
        $response->assertSee(__('Pricing'), false);
    }

    public function test_home_page_shows_marketing_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="features"', false);
        $response->assertSee('id="pricing"', false);
        $response->assertSee('id="faq"', false);
    }
}
