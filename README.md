# Faktory PHP 5 Library
Faktory job queue library for PHP. Compatible with PHP 5.6 (and maybe lower, but untested)

### Supported Faktory versions
- 0.9.7
- 1.0.1

It could work on earlier versions, but untested.

## Installation / Usage

Formal instructions are coming soon. In the meantime, use Composer to install the package:
```
composer require jcs224/faktory_worker_php5
```

## Pushing jobs

```php
use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryJob;

// $client = new FaktoryClient('faktory', '7419', 'insecure_password'); // Example with password
$client = new FaktoryClient('faktory', '7419');
$job1 = new FaktoryJob('cooljob', [
    1,
    2
]);

$job2 = new FaktoryJob('cooljob2', [
    3,
    4
]);

$client->push($job1);
$client->push($job2);
```

## Starting a worker that listens for jobs

```php
use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryWorker;

// $client = new FaktoryClient('faktory', '7419', 'insecure_password'); // Example with password
$client = new FaktoryClient('faktory', '7419');
$worker = new FaktoryWorker($client);

$worker->register('cooljob', function($job) {
    echo "something cool: ".$job['args'][0].' '.$job['args'][1]."\n";
});

$worker->register('cooljob2', function($job) {
    echo "This is cooler: ".$job['args'][0].' '.$job['args'][1]."\n";
});

$worker->run(true);
```