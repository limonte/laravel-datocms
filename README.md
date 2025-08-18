## DatoCMS Integration

This Laravel application integrates with DatoCMS using a sophisticated caching strategy that leverages DatoCMS's native cache tags for optimal performance and cache invalidation.

### Caching Strategy

The `DatoCmsClient` implements a tag-based caching system that works with DatoCMS's Content Delivery API (CDA) cache tags:

#### How It Works

1. **Query Execution**: When a GraphQL query is executed, the client requests cache tags from DatoCMS by setting the `X-Cache-Tags: true` header.

2. **Cache Tag Mapping**: DatoCMS responds with cache tags in the `X-Cache-Tags` header (e.g., `#)[br) "s3n:@`). These tags represent the specific content that the query depends on.

3. **Dual Caching Strategy**:
   - **Query Cache**: The actual query result is cached with a unique key based on the query and variables
   - **Tag Mapping**: Each cache tag is mapped to the query cache key, enabling efficient invalidation

4. **Cache Invalidation**: When content changes in DatoCMS, webhooks notify our application which cache tags to invalidate, ensuring fresh content delivery.

#### Code Example

```php
// Execute a query that will be cached with DatoCMS tags
$result = $datoCmsClient->query('
    query {
        allPosts {
            id
            title
            content
        }
    }
');

// DatoCMS automatically provides cache tags like: #)[br), "s3n:@
// These are used to invalidate cache when those specific posts change
```

#### Cache Flow Diagram

```
1. Client Query → DatoCMS API (with X-Cache-Tags: true)
2. DatoCMS Response → Data + Cache Tags (X-Cache-Tags: xxx yyy)
3. Laravel Cache →
   - Store: query_hash → result_data
   - Store: xxx → query_hash
   - Store: yyy → query_hash
4. Webhook Invalidation →
   - DatoCMS sends: {"tags": ["xxx"]}
   - Laravel removes: xxx mapping + associated query cache
```

### Benefits

- **Precision**: Only invalidates cache for content that actually changed
- **Performance**: Avoids unnecessary API calls for unchanged content
- **Scalability**: Efficient cache management even with large datasets
- **Real-time**: Immediate cache invalidation via webhooks

### Configuration

Set up your DatoCMS integration in `.env`:

```env
DATOCMS_API_TOKEN=your_api_token_here
DATOCMS_WEBHOOK_SECRET=your_webhook_secret
```

The webhook endpoint for cache invalidation is available at:
```
POST /webhooks/datocms/cache-invalidation
```
