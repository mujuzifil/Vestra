<?php

namespace Tests\Unit;

use Tests\TestCase;

class DebugbarProductionTest extends TestCase
{
    public function test_debugbar_is_a_development_dependency_only(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertArrayHasKey('require-dev', $composer);
        $this->assertArrayHasKey('barryvdh/laravel-debugbar', $composer['require-dev']);

        $this->assertArrayHasKey('require', $composer);
        $this->assertArrayNotHasKey('barryvdh/laravel-debugbar', $composer['require']);
    }

    public function test_environment_example_disables_debugbar(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('DEBUGBAR_ENABLED=false', $envExample);
    }
}
