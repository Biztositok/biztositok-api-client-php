# Biztositok.hu API Client

SDK for the biztositok.hu API. 

## Install

Via Composer

``` bash
$ composer require biztositok/biztositok-api-client-php
```

## Usage

``` php
use Biztositok\Api\Client;

$client = new Client([
    'api_endpoint' => 'http://example.com',
    'username' => 'test',
    'password' => '1234567',
]);


$response = $client->api('/test', ['param' => 123]);

echo $response->get('key');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Coding style

Use the `composer cs-check` command to check the source for errors. Use the `composer cs-fix` command to
fix the source.
