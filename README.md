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

    public function getDependencies(): array
    {
        return [
            Order::class,
            Product::class,
        ];
    }

    public function assert(): bool
    {
        return $this->driver->hasTable('items');
    }

    public function up(): void
    {
        $this->driver->createTable('items');
    }

    public function down(): void
    {
        $this->driver->dropTable('items');
    }
}
```

```php
use Database\Driver;
use Griffin\Migration\Migration;

$driver = new Driver();

$migration = (new Migration())
    ->withName('items')
    ->withDependencies(['orders', 'products'])
    ->withAssert(fn() => $driver->hasTable('items'))
    ->withUp(fn() => $driver->createTable('items'))
    ->withDown(fn() => $driver->dropTable('items'));

$griffin->add($migration);
```
