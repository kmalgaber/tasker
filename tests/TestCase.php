<?php

namespace Tests;

use DevAdamlar\LaravelOidc\Testing\ActingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use ActingAs;
    public function json($method, $uri, array $data = [], array $headers = [], $options = 0)
    {
        if (!Str::startsWith($uri, '/api/v1')) {
            return parent::json($method, '/api/v1/' . $uri, $data, $headers, $options);
        }

        return parent::json($method, $uri, $data, $headers, $options);
    }
}
