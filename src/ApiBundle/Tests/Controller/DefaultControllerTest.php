<?php

namespace ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/api/Default/index');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }

    public function testIndexNoAction()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/api/Default');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }

    public function testIndexNoController()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }

    public function testRedirect() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/Default/redirect');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(json_encode(['success' => true, 'action' => 'redirect']), $client->getResponse()->getContent());

    }
}
