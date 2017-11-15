<?php

namespace Biztositok\Api\Tests;

use Biztositok\Api\Client;
use Biztositok\Api\ApiException;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $client = new Client([
            'api_endpoint' => 'http://example.com',
            'username' => 'test',
            'password' => '1234567',
        ]);

        $this->assertSame('http://example.com', $client->getApiEndpoint());
        $this->assertSame('test', $client->getUsername());
        $this->assertSame('1234567', $client->getPassword());
    }

    public function testInvalidResponse()
    {
        $client = new Client([
            'api_endpoint' => 'http://127.0.0.1:9999',
            'username' => 'test',
            'password' => '1234567',
        ]);

        $this->expectException(ApiException::class);
        $client->api('/test');
    }
}
