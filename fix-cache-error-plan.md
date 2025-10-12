# Fix for "Call to undefined method Illuminate\Cache\DatabaseStore::getRedis()" Error

## Problem Summary
The error occurs because the `clearProductCache()` method in `app/Livewire/ProductTable.php` is trying to use Redis-specific methods (`getRedis()`, `keys()`, `del()`) while the application is configured to use the database cache driver (`CACHE_STORE=database` in .env).

## Solution
Replace the `clearProductCache()` method with a version that works with the database cache driver.

## Implementation Steps

### 1. Locate the Method
Find the `clearProductCache()` method in `app/Livewire/ProductTable.php` (around line 912-919).

### 2. Replace the Method
Replace the existing method with one of these options:

#### Option 1: Simple Cache Clearing (Recommended)
```php
/**
 * Clear product-related cache
 */
private function clearProductCache()
{
    // Clear specific cache keys that we know exist
    cache()->forget('product_categories');
    cache()->forget('product_units');
    
    // Clear any product listing cache by using a version key
    $versionKey = 'products_cache_version';
    cache()->increment($versionKey);
}
```

#### Option 2: Database Table Cleanup (More thorough)
```php
/**
 * Clear product-related cache
 */
private function clearProductCache()
{
    // Clear specific cache keys that we know exist
    cache()->forget('product_categories');
    cache()->forget('product_units');
    
    // For database cache, we need to manually clear entries
    try {
        // Get the cache table name from config
        $cacheTable = config('cache.stores.database.table', 'cache');
        
        // Delete cache entries with keys containing 'products'
        \DB::table($cacheTable)
            ->where('key', 'like', '%products%')
            ->delete();
            
        // Also clear any product-related cache by incrementing a version key
        $versionKey = 'products_cache_version';
        cache()->increment($versionKey);
    } catch (\Exception $e) {
        // Log error but don't fail the operation
        \Log::warning('Failed to clear product cache from database: ' . $e->getMessage());
    }
}
```

#### Option 3: Conditional Solution (Works with both drivers)
```php
/**
 * Clear product-related cache
 */
private function clearProductCache()
{
    // Clear specific cache keys that we know exist
    cache()->forget('product_categories');
    cache()->forget('product_units');
    
    // Check which cache driver is being used
    $cacheDriver = config('cache.default');
    
    if ($cacheDriver === 'redis') {
        // Redis-specific implementation
        try {
            $cacheKeys = cache()->getRedis()->keys('*products_*');
            if ($cacheKeys) {
                cache()->getRedis()->del($cacheKeys);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to clear Redis cache: ' . $e->getMessage());
        }
    } else {
        // For database and other drivers
        try {
            $cacheTable = config('cache.stores.database.table', 'cache');
            
            // Delete cache entries with keys containing 'products'
            \DB::table($cacheTable)
                ->where('key', 'like', '%products%')
                ->delete();
                
            // Increment version key to invalidate any remaining cached items
            cache()->increment('products_cache_version');
        } catch (\Exception $e) {
            \Log::warning('Failed to clear database cache: ' . $e->getMessage());
        }
    }
}
```

### 3. Update Cache Usage (Optional)
If you want to ensure cache invalidation works properly, update the methods that cache product data:

#### In `getProducts()` method (around line 1063):
```php
public function getProducts()
{
    // Get cache version to ensure fresh data when cache is cleared
    $cacheVersion = cache()->get('products_cache_version', 0);
    
    // Temporarily disable caching to fix serialization issues
    return $this->buildProductQuery()->paginate($this->perPage);
}
```

#### In `getCategories()` method (around line 1125):
```php
public function getCategories()
{
    // Cache categories for better performance
    return cache()->remember('product_categories', 300, function () {
        return Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category');
    });
}
```

#### In `getUnits()` method (around line 1138):
```php
public function getUnits()
{
    // Cache units for better performance
    $units = cache()->remember('product_units', 600, function () {
        return ProductUnit::select('id', 'name', 'abbreviation')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    });

    return $units;
}
```

## Recommendation
I recommend using **Option 1** for simplicity unless you specifically need to clear all product-related cache entries from the database. Option 1 clears the known cache keys and uses a version key to invalidate any product listing cache that might be implemented later.

## Testing
After implementing the fix:
1. Try to add, edit, or delete a product
2. Verify that no error occurs
3. Check that the product data refreshes correctly
4. Monitor the Laravel logs to ensure no new errors are introduced

## Additional Considerations
- If you plan to use Redis in the future, consider implementing **Option 3** which works with both cache drivers
- Ensure the `cache` table exists in your database if using the database cache driver
- Consider running `php artisan cache:clear` after implementing the fix to clear any existing corrupted cache entries