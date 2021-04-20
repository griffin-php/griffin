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

$griffin->addMigration($migration);
```

```php
use Database\Migration\Table as TableMigration;
use Griffin\Runner\Runner;

$runner = (new Runner())
    ->addMigration(new TableMigration\Item())
    ->addMigration(new TableMigration\Order())
    ->addMigration(new TableMigration\Product());

$runner->up(); // creates everything
$runner->down(); // destroys everthing
```

```php
use Database\Migration\Table\Item as ItemTableMigration;
use Griffin\Event\Migration\UpAfter;
use Griffin\Event\Migration\UpBefore;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;

$runner = new Runner();

$logger = fn($event)
    => printf("%s::%s\n", get_class($event), get_class($event->getMigration()));

$dispatcher = new EventDispatcher(); // PSR-14

$dispatcher->subscribeTo(UpBefore::class, $logger);
$dispatcher->subscribeTo(UpAfter::class, $logger);

$runner
    ->setEventDispatcher($dispatcher)
    ->addMigration(new ItemTableMigration());

$runner->up();

// Griffin\Event\Migration\UpBefore::Database\Migration\Table\Item
// Griffin\Event\Migration\UpAfter::Database\Migration\Table\Item
```
