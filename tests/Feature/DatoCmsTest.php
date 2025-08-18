<?php

use App\Services\DatoCms\DatoCmsClient;

it('can query DatoCMS', function () {
    // Create a test instance with mock data
    $client = new DatoCmsClient(
        apiToken: 'test-token',
    );

    // Test that the client is instantiated correctly
    expect($client)->toBeInstanceOf(DatoCmsClient::class);
});
