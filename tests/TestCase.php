<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The suite exercises the application, not the asset pipeline: without
        // this, every test that renders the root view fails on a machine where
        // `npm run build` has not been run. CI builds the frontend separately.
        $this->withoutVite();
    }
}
