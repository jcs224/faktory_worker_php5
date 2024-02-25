<?php

namespace FaktoryQueue;

class FaktoryWorker
{
    private $client;
    private $queues;
    private $jobTypes = [];
    private $stop = false;
    private $id = null;

    public function __construct($client, $processId = null)
    {
        $this->client = $client;
        $this->queues = array('default');
        $this->id = $processId ?: substr(sha1(rand()), 0, 8);
        $this->client->setWorker($this);
    }

    public function getID()
    {
        return $this->id;
    }

    public function setQueues($queues)
    {
        $this->queues = $queues;
    }

    public function register($jobType, $callable)
    {
        $this->jobTypes[$jobType] = $callable;
    }

    public function run($daemonize = false)
    {
        do {
            $job = $this->client->fetch($this->queues);

            if ($job) {
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
