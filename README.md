# griffin

<div>
  <p align="center"><img src="https://raw.githubusercontent.com/griffin-php/griffin/main/icon.svg" width="200"></p>
  <p align="center">Griffin is a Graph-Oriented Migration Framework for PHP</div>
</div>

[![Build Status](https://github.com/griffin-php/griffin/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/griffin-php/griffin/actions/workflows/test.yml?query=branch%3Amain)
[![Latest Stable Version](https://poser.pugx.org/griffin/griffin/v/stable?format=flat)](https://packagist.org/packages/griffin/griffin)
[![Codecov](https://codecov.io/gh/griffin-php/griffin/branch/main/graph/badge.svg)](https://codecov.io/gh/griffin-php/griffin)
[![License](https://poser.pugx.org/griffin/griffin/license?format=flat)](https://packagist.org/packages/griffin/griffin)

## TL;DR

Griffin is a generic migration framework that uses graph theory to provision
anything. It plans execution based on migration dependencies and runs them in
the correct order.

```php
use FooBar\Database\Driver;
use Griffin\Migration\Container;
use Griffin\Migration\Migration;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;

$driver = new Driver(); // Pseudo Database Driver

$orders = (new Migration())
    ->withName('orders')
    ->withAssert(fn() => $driver->table->has('orders'))
    ->withUp(fn() => $driver->table->create('orders'))
    ->withDown(fn() => $driver->table->drop('orders'));

$items = (new Migration())
    ->withName('items')
    ->withDependencies(['orders'])
    ->withAssert(fn() => $driver->table->has('items'))
    ->withUp(fn() => $driver->table->create('items'))
    ->withDown(fn() => $driver->table->drop('items'));

$container = (new Container())
    ->addMigration($orders)
    ->addMigration($items);

$planner = new Planner($container);
$runner  = new Runner($planner);

$runner->up(); // create everything
$runner->down(); // destroy everything

$runner->up('items'); // create orders and items
$runner->down('orders'); // destroy orders and items

// create orders and items
// regardless the order of elements informed
$runner->up('items', 'orders');
```

You might want to check
[more examples](https://github.com/griffin-php/griffin-examples) to learn how to
define migrations using Griffin.

## Installation

This package uses [Composer](https://packagist.org/packages/griffin/griffin) as
default repository. You can install it adding the name of package in `require`
section of `composer.json`, pointing to the latest stable version.

```json
{
  "require": {
    "griffin/griffin": "^1.0"
  }
}
```

### CLI

This package includes the Griffin framework. If you want a CLI to run your
migrations, please check
[Griffin CLI](https://github.com/griffin-php/griffin-cli).

## Introduction

Migrations are tools to change system current state, adding (or removing)
features based on previous state. Generally, they are used to create database
structures from scratch, provisioning tables or columns using a step-by-step
approach. There are standalone tools to run migrations, like Phinx. Also, there
are other ones embedded into frameworks, like Laravel or Doctrine.

If we inspect them, they use a linear approach, where next state must *migrate*
from current state. Migrations can be rolled back, so if we want to revert some
changes, we must *rollback* from current state to previous state. Each migration
knows how to create and destroy itself.

For example, we have three migrations `A`, `B` and `C` created sequentially. If
our current state is `A` and we must migrate to `C`, we must execute migrations
`B` and `C`, in that order, respectively. If we want to rollback from `C` to
`A`, we must execute them backwards, `B` and `A`. But if you want to execute
migrations `A` and `C`, because they are dependent, and ignore `B` for some
reason, you can't. Even, if you want to rollback `C` and `A` ignoring `B`, you
are locked.

Bringing to the world of database migrations, you can create migration `Orders`
that creates table into schema. Right after that, other developer creates a
migration called `Messages` without any dependency from `Orders`. Next, you
create a migration named `Items` with a foreign key to `Orders`. Everything
works fine and you deploy them to *stage* environment on friday.

```
./migrations/001_Orders.php
./migrations/002_Messages.php
./migrations/003_Items.php
```

On monday you find a problem with your migrations and you want to rollback. But
you don't want to remove `Messages` table because other developer is presenting
the newest features to Product Owner.

And here comes Griffin.

## Description

Griffin is a migration framework based on directed graphs, where each migration
can be migrated and rolled back independently. Also, you can define dependencies
for each migration and Griffin is responsible to plan the execution priority.

Based on provisioning tools like Puppet and Terraform, Griffin can plan
execution and run it using graph theory, where each migration works like a
vertice and dependencies define directed paths. Griffin searches for circular
dependencies on planning and can automatically rollback changes if errors were
found.

Griffin is a generic migration framework and it is not database focused. You are
free to use Griffin to provisioning what needed, like directory structures,
packages and even database schemas.

## Usage

Each migration must be defined using `Griffin\Migration\MigrationInterface`.
Migrations must return its name with `getName` method and dependencies with
`getDependencies`. Each migration must check if resource is created using
`assert` method, returning a boolean as result. Also, they are responsible to
create the resource using `up` method and to destroy using `down`. Griffin uses
these methods to plan and run migrations.

```php
namespace FooBar\Database\Migration;

use FooBar\Database\Driver;
use Griffin\Migration\MigrationInterface;

class Items implements MigrationInterface
{
    public function __construct(
        private Driver $driver,
    ) {}

    public function getName(): string
    {
        return self::class;
    }

    /**
     * @return string[]
     */
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

You can create objects from class `Griffin\Migration\Migration`, that implements
`Griffin\Migration\MigrationInterface` and behaviors can be defined using
immutable methods.

```php
use FooBar\Database\Driver;
use Griffin\Migration\Migration;

$driver = new Driver();

$migration = (new Migration())
    ->withName('items')
    ->withDependencies(['orders', 'products'])
    ->withAssert(fn() => $driver->hasTable('items'))
    ->withUp(fn() => $driver->createTable('items'))
    ->withDown(fn() => $driver->dropTable('items'));
```

### Planning

Griffin plans your migrations execution before running them using
`Griffin\Planner\Planner`. Every migration must be added to
`Griffin\Migration\Container` instances and attached to planner on construction.

```php
use FooBar\Database\Migration;
use Griffin\Migration\Container;
use Griffin\Migration\Exception as MigrationException;
use Griffin\Planner\Exception as PlannerException;
use Griffin\Planner\Planner;

$container = new Container();
$planner   = new Planner($container);

$planner->getContainer()
    ->addMigration(new Migration\Orders())
    ->addMigration(new Migration\Items())
    ->addMigration(new Migration\Products());

/** @var Griffin\Migration\Container $migrations **/

try {
    // plan up execution for every migration
    $migrations = $planner->up();
    // plan up execution for Orders and Items
    $migrations = $planner->up(Migration\Items::class)
    // plan down execution
    $migrations = $planner->down();
} catch (PlannerException $e) {
    // PlannerException::DEPENDENCY_CIRCULAR (Circular Dependency Found)
} catch (MigrationException $e) {
    // MigrationException::NAME_UNKNOWN (Unknown Migration Name)
    // MigrationException::NAME_DUPLICATED (Duplicated Migration Name)
    // MigrationException::CALLABLE_UNKNOWN (Unknown Callable Function)
}
```

You can add migrations to container in any order, because dependencies are
checked on planning stage. Migration names are unique and must not be
duplicated. Using objects from `Griffin\Migration\Migration` immutable class can
throw errors if callables were not defined.

This stage also search for circular dependencies, where `A` depends of `B` and
`B` depends of `A`. This type of requirement is not allowed and will rise an
exception describing the problem.

### Running

After planning, Griffin runs migration using `Griffin\Runner\Runner` class.
Internally, Griffin plans migrations execution first and after that it will
execute running on second stage.

```php
use FooBar\Database\Migration;
use Griffin\Migration\Container;
use Griffin\Migration\Exception as MigrationException;
use Griffin\Planner\Exception as PlannerException;
use Griffin\Planner\Planner;
use Griffin\Runner\Exception as RunnerException;
use Griffin\Runner\Runner;

$container = new Container();
$planner   = new Planner($container);
$runner    = new Runner($planner);

try {
    // run up for everything
    $runner->up();
    // run up for Orders and Items
    $runner->up(Migration\Items::class)
    // run complete down
    $runner->down();
} catch (RunnerException $e) {
    // RunnerException::ROLLBACK_CIRCULAR (Circular Rollback Found)
} catch (PlannerException $e) {
    // PlannerException::DEPENDENCY_CIRCULAR (Circular Dependency Found)
} catch (MigrationException $e) {
    // MigrationException::NAME_UNKNOWN (Unknown Migration Name)
    // MigrationException::NAME_DUPLICATED (Duplicated Migration Name)
    // MigrationException::CALLABLE_UNKNOWN (Unknown Callable Function)
}
```

For every planned migration `Griffin\Runner\Runner` will execute migration `up`
method if `assert` returns `false`. During a migration execution, errors can be
raised and Griffin will try to automatically rollback executed migrations. If
during rollback from this migration Griffin finds another error, an exeception
will be throw.

If you want to rollback migrations manually, Griffin will use migration `assert`
method to check if resource was created and if this method returns `true`,
migration method `down` will be called. As before, if Griffin finds an error it
will try to recreate resources.

### Event Dispatcher

Lastly, Griffin implements PSR-14 Event Dispatcher and triggers events after and
before migrations up and down. You can use it to create a logger, as example.

```php
use FooBar\Database\Migration;
use Griffin\Event;
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

$dispatcher->subscribeTo(Event\Migration\UpBefore::class, $logger);
$dispatcher->subscribeTo(Event\Migration\UpAfter::class, $logger);

$dispatcher->subscribeTo(Event\Migration\DownBefore::class, $logger);
$dispatcher->subscribeTo(Event\Migration\DownAfter::class, $logger);

$runner
    ->setEventDispatcher($dispatcher)
    ->addMigration(new Migration\Orders());

$runner->up();
$runner->down();

// Griffin\Event\Migration\UpBefore::Database\Migration\Table\Item
// Griffin\Event\Migration\UpAfter::Database\Migration\Table\Item
// Griffin\Event\Migration\DownBefore::Database\Migration\Table\Item
// Griffin\Event\Migration\DownAfter::Database\Migration\Table\Item
```

## Development

You can use Docker Compose to build an image and run a container to develop and
test this package.

```bash
docker-compose build
docker-compose run --rm php composer install
docker-compose run --rm php composer test
```

## References

* Wikipedia: [Graph Theory](https://en.wikipedia.org/wiki/Graph_theory)
* Wikipedia: [Path on Graph Theory](https://en.wikipedia.org/wiki/Path_%28graph_theory%29)
* Wikipedia: [Schema Migration](https://en.wikipedia.org/wiki/Schema_migration)

## License

This package is opensource and available under MIT license described in
[LICENSE](https://github.com/griffin-php/griffin/blob/main/LICENSE).

Icons made by [Freepik](https://www.freepik.com) from
[Flaticon](https://www.flaticon.com).
