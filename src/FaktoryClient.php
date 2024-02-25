<?php

namespace FaktoryQueue;

class FaktoryClient
{
    private $faktoryHost;
    private $faktoryPort;
    private $faktoryPassword;
    private $worker;
    private $socket;

    public function __construct($host, $port, $password = null)
    {
        $this->faktoryHost = $host;
        $this->faktoryPort = $port;
        $this->faktoryPassword = $password;
        $this->worker = null;
        $this->socket = null;
    }

    public function setWorker($worker)
    {
        $this->worker = $worker;
    }

    public function push($job)
    {
        $this->writeLine('PUSH', json_encode($job));
    }

    public function fetch($queues = array('default'))
    {
        $response = $this->writeLine('FETCH', implode(' ', $queues));
        $char = $response[0];
        if ($char === '$') {
            $count = trim(substr($response, 1, strpos($response, "\r\n")));
            $data = null;
            if ($count > 0) {
                $data = $this->readLine();
                return json_decode($data, true);
            }
            return $data;
        }
        return $response;
    }

    public function ack($jobId)
    {
        $this->writeLine('ACK', json_encode(['jid' => $jobId]));
    }

    public function fail($jobId)
    {
        $this->writeLine('FAIL', json_encode(['jid' => $jobId]));
    }

    private function connect()
    {
        if ($this->socket !== null) {
            return true;
        }

        $this->socket = stream_socket_client("tcp://{$this->faktoryHost}:{$this->faktoryPort}", $errno, $errstr, 30);
        if (!$this->socket) {
            echo "$errstr ($errno)\n";
            return false;
        } else {
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
                    throw new \Exception('Hi not received :(');
                }

                $this->writeLine('HELLO', json_encode($requestDefaults));
            }
            return true;
        }
    }

    private function readLine()
    {
        if (!$this->connect()) {
            return null;
        }
        $contents = fgets($this->socket, 1024);
        while (strpos($contents, "\r\n") === false) {
            $contents .= fgets($this->socket, 1024 - strlen($contents));
        }
        return $contents;
    }

    private function writeLine($command, $json)
    {
        if (!$this->connect()) {
            return null;
        }
        $buffer = $command . ' ' . $json . "\r\n";
        stream_socket_sendto($this->socket, $buffer);
        return $this->readLine();
    }

    private function close()
    {
        fclose($this->socket);
        $this->socket = null;
    }
}
