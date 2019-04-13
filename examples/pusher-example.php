<?php

require "../src/FaktoryClient.class.php";

$client = new FaktoryClient('faktory', '7419');

$client->push([
    "jid" => "12345abcdef",
    "jobtype" => "cooljob",
    "args" => [
        1,
        2
    ]
]);

$client->push([
    "jid" => "12345abcdef",
    "jobtype" => "cooljob2",
    "args" => [
        3,
        4
    ]
]);