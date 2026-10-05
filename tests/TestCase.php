<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Le message d'erreur flashé (withErrors([...]) sans nom de champ) est exactement celui attendu
     */
    protected function assertFlashedError(TestResponse $response, string $message): void
    {
        $response->assertSessionHasErrors();
        $this->assertSame([$message], $response->getSession()->get('errors')->all());
    }
}
