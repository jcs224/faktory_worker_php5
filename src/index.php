<?php

require "FaktoryClient.class.php";
require "FaktoryWorker.class.php";

$client = new FaktoryClient('faktory', '7419');

$client->push([
    "jid" => "12345abcdef",
    "jobtype" => "cooljob",
    "args" => [
        1,
        2
    ]
]);

$worker = new FaktoryWorker($client);
$worker->register('cooljob', function($job) {
    echo "something cool";
    var_dump($job);
});

$worker->run();