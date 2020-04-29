<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginWithAdminIsSuccessful()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request("GET", "/en/login");

        $this->assertResponseIsSuccessful();

        $crawler = $client->submitForm("Sign in", [
            "_username" => "jane_admin",
            "_password" => "kitten"
        ]);

        $actualTitle = $crawler->filter("h1")->text();

        $this->assertSame($actualTitle, "Post List");
    }

    public function testLoginWithUserISSuccessful()
    {
        // TODO: check redirect to app
    }

    public function testLoginWithoutUserFail()
    {
        // TODO: check login fail
    }


}