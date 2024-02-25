<?php

namespace FaktoryQueue;

class SocketMock
{
    private $socket;
    private string $connString;
    private int $timeout;
    private array $readResponses;

    public function __construct($connString, $timeout)
    {
        $this->socket = null;
        $this->connString = $connString;
        $this->timeout = $timeout;
    }

    public function connect()
    {
        $this->socket = 1;
    }

    public function setReadResponses(array $responses)
    {
        $this->readResponses = $responses;
    }

    public function read()
    {
        return array_shift($this->readResponses);
    }

    public function write(string $buffer)
    {
        return 1;
    }

    public function isConnected(): bool
    {
        return $this->socket !== null;
    }

    public function close()
    {
        $this->socket = null;
    }
}
