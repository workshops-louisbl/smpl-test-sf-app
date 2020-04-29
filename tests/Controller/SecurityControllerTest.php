<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

    public function testLoginWithUserAdminForbidden()
    {
        $client = static::createClient();

        $client->request("GET", "/en/login");

        $this->assertResponseIsSuccessful();

        $crawler = $client->submitForm("Sign in", [
            "_username" => "john_user",
            "_password" => "kitten"
        ]);

        $this->assertResponseRedirects("http://localhost/en/admin/post/", Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testLoginWithUserRedirectISSuccessful()
    {
        $client = static::createClient();

        $client->request("GET", "/en/login?redirect_to=/en/blog/");

        $this->assertResponseIsSuccessful();

        $crawler = $client->submitForm("Sign in", [
            "_username" => "john_user",
            "_password" => "kitten"
        ]);

        $this->assertResponseRedirects("http://localhost/en/blog/", Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithoutUserFail()
    {
        $client = static::createClient();

        $client->request("GET", "/en/login");

        $this->assertResponseIsSuccessful();

        $client->submitForm("Sign in", [
            "_username" => "wrong user",
            "_password" => "no password"
        ]);

        $this->assertResponseRedirects("http://localhost/en/login", Response::HTTP_FOUND);

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();

        $actualMessage = $crawler->filter(".alert.alert-danger")->text();
        $this->assertSame("Invalid credentials.", $actualMessage);
    }


}