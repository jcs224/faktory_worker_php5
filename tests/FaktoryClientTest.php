<?php

use PHPUnit\Framework\TestCase;
use FaktoryQueue\FaktoryClient;

class FaktoryClientTest extends TestCase
{
    public function testConnectionDetails()
    {
        $host = 'localhost';
        $port = '12345';
        $password = 'secret';

        $client = new FaktoryClient($host, $port, $password);
    }
}
