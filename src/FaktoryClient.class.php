<?php

class FaktoryClient {
    private $faktoryHost;
    private $faktoryPort;

    public function __construct($host, $port) {
        $this->faktoryHost = $host;
        $this->faktoryPort = $port;
    }

    public function push($job) {
        $socket = $this->connect();
        $this->writeLine($socket, 'PUSH', json_encode($job));
        $this->close($socket);
    }

    public function fetch($queues = array('default')) {
        $socket = $this->connect();
        $response = $this->writeLine($socket, 'FETCH', implode(' ', $queues));
        $char = $response[0];
        
        if ($char === '$') {
            $payload = $this->readLine($socket);
            $this->close($socket);
            return json_decode($payload, true);
        } else {
            $this->close($socket);
            return $response;
        }
    }

    public function ack($jobId) {
        $socket = $this->connect();
        $this->writeLine($socket, 'ACK', json_encode(['jid' => $jobId]));
        $this->close($socket);
    }

    public function fail($jobId) {
        $socket = $this->connect();
        $this->writeLine($socket, 'FAIL', json_encode(['jid' => $jobId]));
        $this->close($socket);
    }

    private function connect() {
        $socket = stream_socket_client("tcp://{$this->faktoryHost}:{$this->faktoryPort}", $errno, $errstr, 30);
        if (!$socket) {
            echo "$errstr ($errno)\n";
            return false;
        } else {
            $response = $this->readLine($socket);

            if ($response !== "+HI {\"v\":2}\r\n") {
                throw new \Exception('Hi not received :(');
            }

            $this->writeLine($socket, 'HELLO', "{\"wid\":\"foo\"}");
            return $socket;
        }
    }

    private function readLine($socket) {
        $contents = fgets($socket, 1024);
        while (strpos($contents, "\r\n") === false) {
            $contents .= fgets($socket, 1024 - strlen($contents));
        }
        return $contents;
    }

    private function writeLine($socket, $command, $json) {
        $buffer = $command.' '.$json."\r\n";
        stream_socket_sendto($socket, $buffer);
        $read = $this->readLine($socket);
        return $read;
    }

    private function close($socket) {
        fclose($socket);
    }
}