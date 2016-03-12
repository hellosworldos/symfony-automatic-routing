<?php

namespace ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }

    public function testRedirect() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/default/redirect');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello World', $client->getResponse()->getContent());

    }
}
