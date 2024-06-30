# Fluint API for dgraph

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zymawy/dgraph.svg?style=flat-square)](https://packagist.org/packages/zymawy/dgraph)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/zymawy/dgraph/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zymawy/dgraph/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/zymawy/dgraph/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zymawy/dgraph/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zymawy/dgraph.svg?style=flat-square)](https://packagist.org/packages/zymawy/dgraph)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/dgraph.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/dgraph)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require zymawy/dgraph
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="dgraph-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="dgraph-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="dgraph-views"
```

## Usage
## Create a Client
```php 
use Zymawy\Dgraph\DgraphClient;

/** @var DgraphClient $client */
$client = new DgraphClient('http://localhost:8080');

```
### Define and Alter Schema

```php
use Zymawy\Dgraph\Api\Operation;
use Zymawy\Dgraph\Exceptions\DgraphException;
use Zymawy\Dgraph\Types\StringType;
use Zymawy\Dgraph\Types\IntType;
use Zymawy\Dgraph\DgraphClient;

/** @var DgraphClient $client */
$client = new DgraphClient('http://localhost:8080');

// Define the schema using the Operation class
$operation = new Operation();
$operation
  ->addField("name", new StringType(["index(term)"]))
  ->addField("age", new IntType(["index(int)"]))
  ->addType("person", ["name", "age"]);

try {
    $response = $client->alter($operation);
    if ($response->hasErrors()) {
        throw new DgraphException("Schema alteration error: " . json_encode($response->getErrors()));
    } else {
        echo "Schema successfully altered.";
    }
} catch (DgraphException $e) {
    echo "Error: " . $e->getMessage();
}
```
## Add Initial Data
```php 
use Zymawy\Dgraph\DgraphClient;
use Zymawy\Dgraph\Txn;
use Zymawy\Dgraph\Responses\DgraphResponse;
use Zymawy\Dgraph\Exceptions\DgraphException;
use Zymawy\Dgraph\Types\StringType;
use Zymawy\Dgraph\Types\IntType;
/** @var DgraphClient $client */
$client = new DgraphClient("http://localhost:8080");

use Zymawy\Dgraph\Api\Mutation;
use Zymawy\Dgraph\Exceptions\DgraphException;

/** @var DgraphClient $client */
$client = new DgraphClient("http://localhost:8080");

$mutation = new Mutation();
$mutation->set([
  [
    "uid" => "_:account1",
    "name" => "Hamza",
    "balance" => 1000.0
  ],
  [
    "uid" => "_:account2",
    "name" => "Zymawy",
    "balance" => 500.0
  ]
]);

try {
  $response = $client->mutate($mutation, true); // commitNow set to true
  if ($response->hasErrors()) {
    throw new DgraphException(
      "Mutation error: " . json_encode($response->getErrors())
    );
  } else {
    print_r($response->getData());
  }
} catch (DgraphException $e) {
  echo "Error: " . $e->getMessage();
}

```

## Run a Basic Query


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [zymawy](https://github.com/zymawy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
