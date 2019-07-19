<?php

require __DIR__."/../vendor/autoload.php";

use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryJob;

$client = new FaktoryClient('faktory', '7419');
$job1 = new FaktoryJob('12345abcde', 'cooljob', [
    1,
    2
]);

$job2 = new FaktoryJob('6789fghijk', 'cooljob2', [
    3,
    4
]);

$client->push($job1);
$client->push($job2);