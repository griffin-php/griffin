# griffin

<div>
  <p align="center"><img src="https://raw.githubusercontent.com/griffin-php/griffin/main/icon.svg" width="200"></p>
  <p align="center"><b>WORK IN PROGRESS</b> Griffin is a Graph-Oriented Migration Framework for PHP</div>
</div>

[![Build Status](https://github.com/griffin-php/griffin/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/griffin-php/griffin/actions/workflows/test.yml?query=branch%3Amain)
[![Latest Stable Version](https://poser.pugx.org/griffin/griffin/v/stable?format=flat)](https://packagist.org/packages/griffin/griffin)
[![Codecov](https://codecov.io/gh/griffin-php/griffin/branch/main/graph/badge.svg)](https://codecov.io/gh/griffin-php/griffin)
[![License](https://poser.pugx.org/griffin/griffin/license?format=flat)](https://packagist.org/packages/griffin/griffin)

## tl;dr

Griffin is a generic migration framework that uses graph theory to provision
anything. It plans your execution based on migration dependencies and runs them
in the correct order.

```php
use FooBar\Database\Driver;
use Griffin\Migration\Container;
use Griffin\Migration\Migration;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;

$orders = (new Migration())
    ->withName('orders')
    ->withAssert(fn() => $driver->table->has('orders'))
    ->withUp(fn() => $driver->table->create('orders'))
    ->withDown(fn() => $driver->table->drop('orders'));

$items = (new Migration())
    ->withName('items')
    ->withAssert(fn() => $driver->table->has('items'))
    ->withUp(fn() => $driver->table->create('items'))
    ->withDown(fn() => $driver->table->drop('items'));

$container = (new Container())
    ->addMigration($orders)
    ->addMigration($items);

$planner = new Planner($container);
$runner  = new Runner($planner);

$runner->up('items'); // create orders and items
$runner->down('orders'); // destroy orders and items
```

## Installation

This package uses Composer as default repository. You can install it adding the
name of package in `require` section of `composer.json`, pointing to the latest
stable version.

```json
{
  "require": {
    "griffin/griffin": "^1.0"
  }
}
```

## Introduction

Migrations are tools to change system current state, adding features based on
previous state. Generally, they are used to create database structures from
scratch, provisioning tables or columns using a step-by-step approach. There are
standalone tools to run migrations with PHP, like Phinx. Also, there are other
ones embedded into frameworks like Laravel or Doctrine.

If we check them, they use a linear approach, where next state must *migrate*
from current state. Migrations can be rolled back, so if we want to revert some
changes, we must *rollback* from current state to previous state. Each migration
knows how to create itself and destroy itself.

For example, we have three migrations `A`, `B` and `C` created sequentially. If
our current state is `A` and we must migrate to `C`, we must execute migrations
`A`, `B` and `C`, in that order, respectively. If we want to rollback from `C`
to `A`, we must execute them backwards, `C`, `B` and `A`. But if you want to
execute migrations `A` and `C`, because they are dependent, and ignore `B` for
some reason, you can't. Even, if you want to rollback `C` and `A` ignoring `B`,
you are locked.

Bringing to the world of database migrations, you can create migration `Orders`
that create table into schema. Right after that, other developer create a
migration called `Messages` without any dependency from `Orders`. Next, you
create a migration named `Items` with a foreign key to `Orders`. Everything
works fine and you deploy them to *stage* environment on friday.

```
./migrations/001_Orders.php
./migrations/002_Messages.php
./migrations/003_Items.php
```

On monday you find a problem with your migrations and you want to rollback. But
you don't want to remove `Messages` table because other developer are presenting
the newest features to Product Owner.

And here comes Griffin.

## Description

Griffin is a migration framework based on directed graphs, where each migration
can be migrated and rolled back independently. Also, you can define dependencies
for each migration and Griffin is responsible to plan the execution priority.

Based on provisioning tools like Puppet and Terraform, Griffin can plan
execution and run it using graph theory, where each migration works like a
vertice and dependencies define directed paths. Griffin searches for circular
dependencies on planning and can automatically rollback changes if some
migration found errors during its execution.

Griffin is a generic migration framework and it is not database focused. You are
free to use Griffin to provisioning what needed, like directory structures,
packages and even database schemas.

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
