<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable database cleanup for this test class
        $this->withoutMiddleware(['Illuminate\Foundation\Http\Middleware\VerifyCsrfToken']);
        $this->withoutMiddleware(['Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance']);
    }
}
