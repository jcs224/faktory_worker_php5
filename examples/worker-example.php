<?php

require "../src/FaktoryClient.class.php";
require "../src/FaktoryWorker.class.php";

$client = new FaktoryClient('faktory', '7419');
$worker = new FaktoryWorker($client);

$worker->register('cooljob', function($job) {
    echo "something cool";
    var_dump($job);
});

$worker->run();