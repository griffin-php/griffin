# griffin

Griffin is a Graph-Oriented Migration Framework for PHP

## Example

```php
namespace Database\Migration\Table;

use Database\Driver;
use Griffin\Migration\AbstractMigration;

class Item extends AbstractMigration
{
    public function __construct(
        private Driver $driver
    ) {}

    public function depends(): array
    {
        return [
            Order::class,
            Product::class,
        ];
    }

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
}
```

```php
use Database\Driver;
use Griffin\Migration\Migration;

$driver = new Driver();

$migration = (new Migration())
    ->withUp(fn() => $driver->createTable('items'))
    ->withDown(fn() => $driver->dropTable('items'))
    ->withAssert(fn() => $driver->hasTable('items'))
    ->withDepends(['orders', 'products']);
```
