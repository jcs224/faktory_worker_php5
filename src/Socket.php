<?php

namespace FaktoryQueue;

class Socket
{
    private $socket;
    private string $connString;
    private int $timeout;

    public function __construct($connString, $timeout)
    {
        $this->socket = null;
        $this->connString = $connString;
        $this->timeout = $timeout;
    }

    public function connect()
    {
        $this->socket = stream_socket_client($this->connString, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            echo "$errstr ($errno)\n";
            throw new \Exception("Error Creating Socket: " . $errstr . "(" . $errno . ")", 1);
        }
    }

    public function read()
    {
        $contents = fgets($this->socket, 1024);
        while (strpos($contents, "\r\n") === false) {
            $contents .= fgets($this->socket, 1024 - strlen($contents));
        }
        return $contents;
    }

    public function write(string $buffer)
    {
        return stream_socket_sendto($this->socket, $buffer);
    }

    public function isConnected(): bool
    {
        return $this->socket !== null;
    }

    public function close()
    {
        fclose($this->socket);
        $this->socket = null;
    }
}
