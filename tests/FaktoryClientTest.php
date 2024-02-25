<?php

use PHPUnit\Framework\TestCase;
use FaktoryQueue\FaktoryClient;
use FaktoryQueue\SocketMock;

use function PHPUnit\Framework\assertNotTrue;
use function PHPUnit\Framework\assertTrue;

class FaktoryClientTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = new FaktoryClient(SocketMock::class, 'localhost', 'port', 'password');
        $this->client->getSocket()->setReadResponses(array("+HI {\"v\":2}\r\n"));
        $this->client->connect();
    }

    public function testClientWithPassword(): void
    {
        // no password
        $passwordClient = new FaktoryClient(SocketMock::class, 'localhost', 'port');
        $passwordClient->getSocket()->setReadResponses(array("+HI {\"v\":2, \"s\":\"asdasd\", \"i\":1}\r\n"));
        $this->expectException('Exception');
        $passwordClient->connect();
        $this->assertNotTrue($passwordClient->isConnected());

        // invalid password
        $passwordClient = new FaktoryClient(SocketMock::class, 'localhost', 'port', 'password');
        $passwordClient->getSocket()->setReadResponses(array("+HI {\"v\":2, \"s\": \"asdasd\", \"i\": 1}\r\n", "+ERR Invalid password"));
        $this->expectException('Exception');
        $passwordClient->connect();
        $this->assertNotTrue($passwordClient->isConnected());

        // valid password
        $passwordClient = new FaktoryClient(SocketMock::class, 'localhost', 'port', 'password');
        $passwordClient->getSocket()->setReadResponses(array("+HI {\"v\":2, \"s\": \"asdasd\", \"i\": 1}\r\n", "+OK"));
        $passwordClient->connect();
        $this->assertTrue($passwordClient->isConnected());
    }

    public function testPush(): void
    {
        $job = ['some' => 'job'];

        // success
        $this->client->getSocket()->setReadResponses(array("+OK\r\n"));
        $ok = $this->client->push($job);
        $this->assertTrue($ok);

        // failed
        $this->client->getSocket()->setReadResponses(array("\+ERR SAD\r\n"));
        $ok = $this->client->push($job);
        $this->assertNotTrue($ok);
    }

    public function testAck(): void
    {
        $jobID = "jobID";

        // success
        $this->client->getSocket()->setReadResponses(array("+OK\r\n"));
        $ok = $this->client->ack($jobID);
        $this->assertTrue($ok);

        // failed
        $this->client->getSocket()->setReadResponses(array("\+ERR SAD\r\n"));
        $ok = $this->client->ack($jobID);
        $this->assertNotTrue($ok);
    }

    public function testFail(): void
    {
        $jobID = "jobID";

        // success
        $this->client->getSocket()->setReadResponses(array("+OK\r\n"));
        $ok = $this->client->fail($jobID);
        $this->assertTrue($ok);

        // failed
        $this->client->getSocket()->setReadResponses(array("\+ERR SAD\r\n"));
        $ok = $this->client->fail($jobID);
        $this->assertNotTrue($ok);
    }

    public function testFetch(): void
    {
        // no data
        $this->client->getSocket()->setReadResponses(array("\$-1\r\n"));
        $response = $this->client->fetch();
        $this->assertNotTrue($response);

        // no data - multiple queues
        $this->client->getSocket()->setReadResponses(array("\$-1\r\n"));
        $response = $this->client->fetch(array("default", "high-priority"));
        $this->assertNotTrue($response);

        // invalid response
        $this->client->getSocket()->setReadResponses(array("\$OK\r\n"));
        $response = $this->client->fetch();
        $this->assertNotTrue($response);

        //valid response
        $this->client->getSocket()->setReadResponses(array("\$211\r\n", '{"jid":"65db6e3bd4a6d","queue":"default","jobtype":"cooljob","args":[1,2],"created_at":"2024-02-25T16:43:39.876770883Z","enqueued_at":"2024-02-25T16:43:39.876783925Z","at":"2024-02-25T16:43:39+00:00","retry":25}', "\$-1\r\n"));
        $response = $this->client->fetch();
        $this->assertEquals("65db6e3bd4a6d", $response["jid"]);
        $this->assertEquals("default", $response["queue"]);

        $response = $this->client->fetch();
        $this->assertNotTrue($response);
    }
}
