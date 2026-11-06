<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_loads_without_fictitious_donations(): void
    {
        $this->withoutVite()->get('/')->assertOk()->assertSee('0 barang tersedia');
    }
}
