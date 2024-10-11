<?php

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagePostControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $uploadedFile = new UploadedFile(
            __DIR__.'/../fixtures/marcell-rubies-qmL6pgKtOrg-unsplash.jpg',
            'marcell-rubies-qmL6pgKtOrg-unsplash.jpg'
        );
        $client->request('POST', '/api/images', [], [
            'file' => $uploadedFile,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(201, $client->getResponse()->getStatusCode(), 'Expected HTTP status 201 for successful image upload');
        $this->assertJson($client->getResponse()->getContent(), 'Expected JSON response');

    }
}