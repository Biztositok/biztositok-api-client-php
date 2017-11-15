<?php

namespace Biztositok\Api\Tests;

use Biztositok\Api\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetResponse()
    {
        $rawResponse = [
            'success' => 1,
        ];
        $response = new Response($rawResponse);
        $this->assertSame($rawResponse, $response->getResponse());
    }

    public function testIsSuccess()
    {
        $response = new Response([
            'success' => 1,
        ]);
        $this->assertTrue($response->isSuccess());

        $response = new Response([
            'success' => 0,
        ]);
        $this->assertFalse($response->isSuccess());

        $response = new Response(null);
        $this->assertFalse($response->isSuccess());
    }

    public function testGetMessage()
    {
        $response = new Response([
            'message' => 'test',
        ]);
        $this->assertSame('test', $response->getMessage());
    }

    public function testGetErrors()
    {
        $response = $this->createResponseWithErrors();
        $this->assertCount(2, $response->getErrors());

        foreach ($response->getErrors() as $error) {
            $this->assertArrayHasKey('field', $error);
            $this->assertArrayHasKey('error_message', $error);
        }
    }

    public function testGetErrorMessages()
    {
        $response = $this->createResponseWithErrors();
        $this->assertCount(2, $response->getErrorMessages());

        foreach ($response->getErrorMessages() as $message) {
            $this->assertContains('message', $message);
        }
    }

    public function testGetErrorsCombined()
    {
        $response = $this->createResponseWithErrors();
        $this->assertCount(2, $response->getErrorsCombined());

        foreach ($response->getErrorsCombined() as $message) {
            $this->assertContains('message', $message);
            $this->assertContains('field', $message);
        }
    }

    public function testGet()
    {
        $response = new Response([
            'value' => 1,
            'user' => [
                'name' => 'Test User',
                'address' => [
                    'zip' => 1234,
                    'Street' => 'Test',
                ],
            ],
        ]);

        $this->assertSame(1, $response->get('value'));
        $this->assertSame(-1, $response->get('undefined', -1));
        $this->assertSame('Test User', $response->get('user.name'));
        $this->assertSame(1234, $response->get('user.address.zip'));
    }

    /**
     * @return Response
     */
    private function createResponseWithErrors()
    {
        return new Response([
            'errors' => [
                [
                    'field' => 'field1',
                    'error_message' => 'message 1',
                ],
                [
                    'field' => 'field2',
                    'error_message' => 'message 2',
                ],
            ],
        ]);
    }
}
