Installation
============

Make sure Composer is installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Open a command console, enter your project directory and execute:

```console
composer require camelot/arbitration
```

Symfony Applications
--------------------

If you are using Symfony buy not using Flex then you must enable the bundle by adding it to the list of registered 
bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Camelot\Arbitration\CamelotArbitrationBundle::class => ['all' => true],
];
```

**Next:** [Configuration](./configuration.md)
