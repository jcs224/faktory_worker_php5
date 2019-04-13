<?php

class FaktoryWorker {
    private $client;
    private $queues;
    private $jobTypes = [];
    private $stop = false;
    
    public function __construct($client) {
        $this->client = $client;
        $this->queues = array('default');
    }

    public function setQUeues($queues) {
        $this->queues = $queues;
    }

    public function register($jobType, $callable) {
        $this->jobTypes[$jobType] = $callable;
    }

    public function run($daemonize = true) {
        do {
            echo "grabbing job\n";
            $job = $this->client->fetch($this->queues);
            
            if ($job !== null) {
                echo "registering job\n";
                $callable = $this->jobTypes[$job['jobtype']];

                try {
                    call_user_func($callable, $job);
                    $this->client->ack($job['jid']);
                    echo "job success";
                } catch (\Exception $e) {
                    echo "job failure";
                    $this->client->fail($job['jid']);
                } finally {
                    exit(0);
                }
            }

            usleep(100);
        } while (true);
    }
}