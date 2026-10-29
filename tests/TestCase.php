<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Les tests ne doivent jamais joindre un service externe (passerelle de paiement, taux de change…).
        Http::preventStrayRequests();

        // Les tests ne dépendent pas du build front-end (manifest Vite absent en CI).
        $this->withoutVite();
    }
}
