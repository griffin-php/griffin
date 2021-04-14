# griffin

**WORK IN PROGRESS** Griffin is a Graph-Oriented Migration Framework for PHP

## Example

```php
namespace Database\Migration\Table;

use Database\Driver;
use Griffin\Migration\MigrationInterface;

class Item implements MigrationInterface
{
    public function __construct(
        private Driver $driver,
    ) {}

    public function up(): void
    {
        $this->driver->createTable('items');
    }

    public function down(): void
    {
        $this->driver->dropTable('items');
    }

    public function assert(): bool
    {
        return $this->driver->hasTable('items');
    }

    public function depends(): array
    {
        return [
            Order::class,
            Product::class,
        ];
    }
}
```

```php
use Database\Driver;
use Griffin\Migration\Migration;

$driver = new Driver();

$migration = (new Migration())
    ->withName('items')
    ->withUp(fn() => $driver->createTable('items'))
    ->withDown(fn() => $driver->dropTable('items'))
    ->withAssert(fn() => $driver->hasTable('items'))
    ->withDepends(['orders', 'products']);

$griffin->add($migration);
```
