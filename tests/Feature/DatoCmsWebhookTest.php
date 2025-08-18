<?php

use App\Services\DatoCms\DatoCmsClient;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    // Mock the DatoCmsClient
    $this->mockClient = Mockery::mock(DatoCmsClient::class);
    $this->app->instance(DatoCmsClient::class, $this->mockClient);
});

it('can handle valid cache invalidation webhook payload', function () {
    $payload = [
        'entity_type' => 'cda_cache_tags',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => ['N*r;L', '6-KZ@', 't#k[uP']
            ]
        ],
        'related_entities' => []
    ];

    // Simulate existing cache entries
    Cache::put('N*r;L', 'query_hash_123');
    Cache::put('query_hash_123', ['data' => 'some cached data']);
    Cache::put('6-KZ@', 'query_hash_456');
    Cache::put('query_hash_456', ['data' => 'other cached data']);

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(200);
    $response->assertSeeText('Cache invalidated successfully');

    // Verify cache was invalidated
    expect(Cache::get('N*r;L'))->toBeNull();
    expect(Cache::get('query_hash_123'))->toBeNull();
    expect(Cache::get('6-KZ@'))->toBeNull();
    expect(Cache::get('query_hash_456'))->toBeNull();
});

it('rejects invalid payload structure - missing entity_type', function () {
    $payload = [
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => ['tag1', 'tag2']
            ]
        ]
    ];

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(400);
    $response->assertSeeText('Invalid payload structure');
});

it('rejects invalid payload structure - wrong entity_type', function () {
    $payload = [
        'entity_type' => 'wrong_type',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => ['tag1', 'tag2']
            ]
        ]
    ];

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(400);
    $response->assertSeeText('Invalid payload structure');
});

it('rejects payload with missing tags', function () {
    $payload = [
        'entity_type' => 'cda_cache_tags',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => []
        ]
    ];

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(400);
    $response->assertSeeText('No cache tags found');
});

it('rejects payload with empty tags array', function () {
    $payload = [
        'entity_type' => 'cda_cache_tags',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => []
            ]
        ]
    ];

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(400);
    $response->assertSeeText('No cache tags found');
});

it('handles cache invalidation for non-existent tags gracefully', function () {
    $payload = [
        'entity_type' => 'cda_cache_tags',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => ['non-existent-tag']
            ]
        ]
    ];

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(200);
    $response->assertSeeText('Cache invalidated successfully');
});

it('invalidates multiple cache entries for the same tag', function () {
    $payload = [
        'entity_type' => 'cda_cache_tags',
        'event_type' => 'invalidate',
        'entity' => [
            'id' => 'cda_cache_tags',
            'type' => 'cda_cache_tags',
            'attributes' => [
                'tags' => ['shared-tag']
            ]
        ]
    ];

    // Simulate multiple queries using the same tag
    Cache::put('shared-tag', 'query_hash_123');
    Cache::put('query_hash_123', ['data' => 'cached data 1']);

    $response = $this->postJson('/invalidate-datocms-cache', $payload);

    $response->assertStatus(200);

    // Verify both tag and query cache were invalidated
    expect(Cache::get('shared-tag'))->toBeNull();
    expect(Cache::get('query_hash_123'))->toBeNull();
});
