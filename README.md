# griffin

<div>
  <p align="center"><img src="https://raw.githubusercontent.com/griffin-php/griffin/main/icon.svg" width="200"></p>
  <p align="center"><b>WORK IN PROGRESS</b> Griffin is a Graph-Oriented Migration Framework for PHP</div>
</div>


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
```

```php
use Database\Migration\Table as TableMigration;
use Griffin\Migration\Container;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;

$container = (new Container())
    ->addMigration(new TableMigration\Item())
    ->addMigration(new TableMigration\Order())
    ->addMigration(new TableMigration\Product());

$planner = new Planner($container);
$runner  = new Runner($planner);

$runner->up(); // creates everything
$runner->down(); // destroys everthing

// creates orders and products only
$runner->up(
    TableMigration\Order::class,
    TableMigration\Product::class,
);

// creates items (orders and products added)
$runner->up(TableMigration\Item::class);

// destroys items and orders (and not products)
$runner->down(TableMigration\Order::class);
```

```php
use Database\Migration\Table\Item as ItemTableMigration;
use Griffin\Event\Migration\UpAfter;
use Griffin\Event\Migration\UpBefore;
use Griffin\Migration\Container;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;

$container = new Container();
$planner   = new Planner($container);
$runner    = new Runner($planner);

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

## License

This package is opensource and available under MIT license described in
[LICENSE](https://github.com/griffin-php/griffin/blob/main/LICENSE).

Icons made by [Freepik](https://www.freepik.com) from
[Flaticon](https://www.flaticon.com).
