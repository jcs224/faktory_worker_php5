<?php

namespace FaktoryQueue;

const Disconnected = 1;
const Connecting = 2;
const Connected = 3;

class FaktoryClient
{
    private $faktoryHost;
    private $faktoryPort;
    private $faktoryPassword;
    private $worker;
    private $connectionState;
    protected $socket;

    public function __construct($socketImpl, $host, $port, $password = null)
    {
        $this->faktoryHost = $host;
        $this->faktoryPort = $port;
        $this->faktoryPassword = $password;
        $this->worker = null;
        $this->socket = new $socketImpl("tcp://{$this->faktoryHost}:{$this->faktoryPort}", 30);
        $this->connectionState = Disconnected;
    }

    public function setWorker($worker)
    {
        $this->worker = $worker;
    }

    public function push($job): bool
    {
        $response = $this->writeLine('PUSH', json_encode($job));
        return $response == "+OK\r\n";
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function fetch($queues = array('default'))
    {
        $response = $this->writeLine('FETCH', implode(' ', $queues));
        $char = $response[0];
        if ($char === '$') {
            $count = trim(substr($response, 1, strpos($response, "\r\n")));
            if ($count > 0) {
                $data = $this->readLine();
                return json_decode($data, true);
            }
            return false;
        }
        return false;
    }

    public function ack($jobId): bool
    {
        $response = $this->writeLine('ACK', json_encode(['jid' => $jobId]));
        return $response == "+OK\r\n";
    }

    public function fail($jobId)
    {
        $response = $this->writeLine('FAIL', json_encode(['jid' => $jobId]));
        return $response == "+OK\r\n";
    }

    public function connect()
    {
        if ($this->connectionState !== Disconnected) {
            return;
        }

        $this->socket->connect();
        $this->connectionState = Connecting;

        $response = $this->readLine();
        $requestDefaults = [
            'v' => 2
        ];

        // If the client is a worker, send the wid with request
        if ($this->worker) {
            $requestDefaults = array_merge(['wid' => $this->worker->getID()], $requestDefaults);
        }

        if (strpos($response, "\"s\":") !== false && strpos($response, "\"i\":") !== false) {
            // Requires password
            if (!$this->faktoryPassword) {
                throw new \Exception('Password is required.');
            }

            $payloadArray = json_decode(substr($response, strpos($response, '{')));

            $authData = $this->faktoryPassword . $payloadArray->s;
            for ($i = 0; $i < $payloadArray->i; $i++) {
                $authData = hash('sha256', $authData, true);
            }

            $requestWithPassword = json_encode(array_merge(['pwdhash' => bin2hex($authData)], $requestDefaults));
            $responseWithPassword = $this->writeLine('HELLO', $requestWithPassword);
            if (strpos($responseWithPassword, "ERR Invalid password")) {
                throw new \Exception('Password is incorrect.');
            }
        } else {
            // Doesn't require password
            if ($response !== "+HI {\"v\":2}\r\n") {
                throw new \Exception('Hi not received');
            }

            $this->writeLine('HELLO', json_encode($requestDefaults));
        }
        $this->connectionState = Connected;
    }

    public function isConnected(): bool
    {
        return $this->connectionState == Connected;
    }

    private function readLine()
    {
        if ($this->connectionState == Disconnected) {
            $this->connect();
        }
        return $this->socket->read();
    }

    private function writeLine($command, $json)
    {
        if ($this->connectionState == Disconnected) {
            $this->connect();
        }
        $buffer = $command . ' ' . $json . "\r\n";
        $this->socket->write($buffer);
        return $this->readLine();
    }

    private function close()
    {
        $this->socket->close();
        $this->connectionState = Disconnected;
    }
}
