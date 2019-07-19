<?php

namespace FaktoryQueue;

class FaktoryWorker {
    private $client;
    private $queues;
    private $jobTypes = [];
    private $stop = false;
    
    public function __construct($client) {
        $this->client = $client;
        $this->queues = array('default');
    }

    public function setQueues($queues) {
        $this->queues = $queues;
    }

    public function register($jobType, $callable) {
        $this->jobTypes[$jobType] = $callable;
    }

    public function run($daemonize = false) {
        do {
            $job = $this->client->fetch($this->queues);

            if ($job !== null) {
                $callable = $this->jobTypes[$job['jobtype']];

                try {
                    call_user_func($callable, $job);
                    $this->client->ack($job['jid']);
                } catch (\Exception $e) {
                    $this->client->fail($job['jid']);
                }
            }

            usleep(100);
        } while ($daemonize && !$this->stop);
    }
}