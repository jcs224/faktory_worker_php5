<?php

require __DIR__ . "/../vendor/autoload.php";

use FaktoryQueue\FaktoryClient;
use FaktoryQueue\FaktoryJob;
use FaktoryQueue\Socket;

// $client = new FaktoryClient('faktory', '7419', 'insecure_password'); // Example with password
$client = new FaktoryClient(Socket::class, 'localhost', '7419');
echo "I have a new client\n";

$job1 = new FaktoryJob('cooljob', [
    1,
    2
]);

$job2 = new FaktoryJob('cooljob2', [
    3,
    4
]);

echo "Will now push 2 jobs\n";
$client->push($job1);
echo "job 1 pushed\n";
$client->push($job2);
echo "job 2 pushed\n";
