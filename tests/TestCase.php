<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $config = $app->make('config');

        // Configuration is resolved here, before RefreshDatabase/migration traits run.
        if (
            ! $app->environment('testing') ||
            $config->get('database.default') !== 'sqlite' ||
            $config->get('database.connections.sqlite.database') !== ':memory:' ||
            ! in_array($config->get('database.connections.sqlite.url'), [null, ''], true)
        ) {
            throw new \RuntimeException(
                'Unsafe test database configuration: tests require APP_ENV=testing, '
                .'the sqlite default connection, database=:memory:, and an empty SQLite URL. '
                .'Database refresh/migrations have been blocked.'
            );
        }

        return $app;
    }
}
