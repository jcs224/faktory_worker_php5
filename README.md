# Faktory PHP Library

[Faktory](https://github.com/contribsys/faktory) job queue library for PHP.

## Supported Faktory versions

- 1.7.0

It could work on earlier versions, but untested.

## Installation / Usage

Use Composer to install the package:

```shell
composer require chrisvaughn/faktory_worker_php
```

## Pushing jobs

```php
use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryJob;

$client = new FaktoryClient('localhost', '7419', 'optionalpassword');
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

### Scheduled jobs

You can also schedule when jobs will run as you push them onto the queue. Here are some examples:

```php
$client = new FaktoryClient('faktory', '7419');

$job1 = new FaktoryJob('CoolJob1', [
    1,
    2
]);

$job2 = new FaktoryJob('CoolJob2', [
    3,
    4
]);

$job3 = new FaktoryJob('CoolJob3', [
    5,
    6
]);

$job4 = new FaktoryJob('CoolJob4', [
    7,
    8
]);

$job5 = new FaktoryJob('CoolJob5', [
    9,
    10
]);

$job1->inSeconds(300);
$job2->inMinutes(15);
$job3->inHours(24);

$date = date(DATE_RFC3339, strtotime('+5 days'));
$job4->at($date);    

$client->push($job1);
$client->push($job2);
$client->push($job3);
$client->push($job4);
$client->push($job5);
```

## Starting a worker that listens for jobs

```php
use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryWorker;

$client = new FaktoryClient('localhost', '7419', 'optionalpassword');
$worker = new FaktoryWorker($client);

$worker->register('cooljob', function($job) {
    echo "something cool: ".$job['args'][0].' '.$job['args'][1]."\n";
});

$worker->register('cooljob2', function($job) {
    echo "This is cooler: ".$job['args'][0].' '.$job['args'][1]."\n";
});

$worker->run(true);
```
