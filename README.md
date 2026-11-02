# Laravel Scout Upstash Search

A Laravel Scout driver for Upstash Search.

## Installation

```bash
composer require laravel-upstash-search/laravel-scout-upstash-search
```

## Configuration

Add your Upstash Search credentials to your `.env`:

```env
UPSTASH_SEARCH_URL=https://your-search-url
UPSTASH_SEARCH_TOKEN=your-upstash-token
```

Publish the config file:

```bash
php artisan vendor:publish --provider="LaravelUpstashSearch\Scout\UpstashSearchServiceProvider" --tag="config"
```

## Setup

Configure your model to use the Upstash driver:

```php
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    protected $searchableAs = 'posts';

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
```

Update `config/scout.php`:

```php
'driver' => env('SCOUT_DRIVER', 'upstash'),
```

## Usage

```php
// Search
$results = Post::search('laravel')->get();

// Paginate
$results = Post::search('laravel')->paginate(15);

// Import all records
php artisan scout:upstash-import "App\Models\Post"
```

## Documentation

For more information on Upstash Search, visit the [Upstash Search Documentation](https://upstash.com/docs/search).

## License

MIT

## Support

Support my open source work and help fund this project on [FLOSS Africa](https://flossafrica.com/m/ossycodes).
