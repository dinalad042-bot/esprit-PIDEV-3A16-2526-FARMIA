<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuickRefControllerTest extends WebTestCase
{
    public function testDemoCenterAccessibleByAdmin(): void
    {
        $client = static::createClient();
        
        // This will redirect to login if not authenticated
        $client->request('GET', '/admin/demo/quick-ref');
        
        $this->assertResponseRedirects('/login');
    }

    public function testHomePageIsUp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'FARMIA');
    }
}
